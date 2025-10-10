@extends('layouts.app')

@section('content')
<div class="container mx-auto p-2" x-data="deliveryNoteForm">
    <!-- Stock Management Modal -->
    <div x-show="isStockModalOpen"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
         @click.away="isStockModalOpen = false" x-cloak>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-6xl">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-4">Stock Management</h2>

            <div x-show="stockModalMessage"
                 class="p-4 mb-4 text-sm rounded-lg"
                 :class="stockModalSuccess ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                 x-text="stockModalMessage"></div>

            <!-- === Products needing conversion only === -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-200 border-b pb-2 mb-2">
                    PO Products Needing Conversion
                </h3>
                <div class="overflow-x-auto max-h-60">
                    <table class="w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-100 dark:bg-gray-700">
                            <tr>
                                <th class="px-3 py-2 text-left">Product</th>
                                <th class="px-3 py-2 text-left">Department</th> 
                                <th class="px-3 py-2 text-right">Clear Stock</th>
                                <th class="px-3 py-2 text-right">Non-Clear Stock</th>
                                <th class="px-3 py-2 text-right">Requested</th>
                                <th class="px-3 py-2 text-right">Shortage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="item in convertibleItems" :key="item.product_id">
                                <tr>
                                    <td class="px-3 py-2" x-text="item.product_name"></td>
                                    <td class="px-3 py-2" x-text="item.department_name || 'N/A'"></td>
                                    <td class="px-3 py-2 text-right" x-text="item.clear_stock"></td>
                                    <td class="px-3 py-2 text-right" x-text="item.non_clear_stock"></td>
                                    <td class="px-3 py-2 text-right" x-text="item.requested"></td>
                                    <td class="px-3 py-2 text-right text-orange-600 font-semibold"
                                        x-text="item.clear_stock_shortage"></td>
                                </tr>
                            </template>
                            <tr x-show="convertibleItems.length === 0">
                                <td colspan="6" class="px-3 py-4 text-center text-gray-500">
                                    No products require conversion.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- === Convert & Wastage Forms === -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Convert Stock Form -->
                <form @submit.prevent="convertStock" class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-200 border-b pb-2">
                        Convert Stock
                    </h3>

                    <div>
                        <label class="block text-sm font-medium">Department*</label>
                        <select x-model="convert.selectedDepartment"
                                @change="updateDepartmentName('convert')"
                                class="mt-1 block w-full dark:bg-gray-900 border rounded-md py-2 px-3">
                            <option value="">Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                        <p x-show="convert.departmentError"
                           class="text-red-600 text-xs mt-1"
                           x-text="convert.departmentError"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Product*</label>
                        <select x-model="convert.product_id"
                                class="mt-1 block w-full dark:bg-gray-900 border rounded-md py-2 px-3"
                                :disabled="!convert.selectedDepartment">
                            <option value="">Select Product</option>
                            <template x-for="p in filteredProducts(convert.selectedDepartment)" :key="p.id">
                                <option :value="p.id" x-text="p.name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Quantity to Convert</label>
                        <input type="number"
                               x-model.number="convert.quantity"
                               min="1"
                               class="mt-1 block w-full dark:bg-gray-900 border rounded-md py-2 px-3"
                               required>
                    </div>

                    <div class="text-right">
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold">
                            Convert
                        </button>
                    </div>
                </form>

                <!-- Wastage Form -->
                <form @submit.prevent="logWastage" class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-200 border-b pb-2">Log Wastage</h3>

                    <div>
                        <label class="block text-sm font-medium">Department*</label>
                        <select x-model="wastage.selectedDepartment"
                                @change="updateDepartmentName('wastage')"
                                class="mt-1 block w-full dark:bg-gray-900 border rounded-md py-2 px-3">
                            <option value="">Select Department</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                        <p x-show="wastage.departmentError"
                           class="text-red-600 text-xs mt-1"
                           x-text="wastage.departmentError"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Product*</label>
                        <select x-model="wastage.product_id"
                                class="mt-1 block w-full dark:bg-gray-900 border rounded-md py-2 px-3"
                                :disabled="!wastage.selectedDepartment">
                            <option value="">Select Product</option>
                            <template x-for="p in filteredProducts(wastage.selectedDepartment)" :key="p.id">
                                <option :value="p.id" x-text="p.name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Stock Type</label>
                        <select x-model="wastage.stock_type"
                                class="mt-1 block w-full dark:bg-gray-900 border rounded-md py-2 px-3">
                            <option value="clear">Clear Stock</option>
                            <option value="non-clear">Non-Clear Stock</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Wastage Quantity</label>
                        <input type="number"
                               x-model.number="wastage.quantity"
                               min="1"
                               class="mt-1 block w-full dark:bg-gray-900 border rounded-md py-2 px-3"
                               required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Reason</label>
                        <input type="text"
                               x-model="wastage.reason"
                               class="mt-1 block w-full dark:bg-gray-900 border rounded-md py-2 px-3">
                    </div>

                    <div class="text-right">
                        <button type="submit"
                                class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-semibold">
                            Log Wastage
                        </button>
                    </div>
                </form>
            </div>

            <div class="text-right mt-4">
                <button type="button"
                        @click="isStockModalOpen = false"
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- === Main Delivery Note Form === -->
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4">
        <div class="flex justify-between items-center mb-4 pb-3 border-b dark:border-gray-700">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Create Delivery Note</h2>
            <div class="flex items-center space-x-2">
                <a href="{{ route('delivery-notes.index') }}"
                   class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md text-xs uppercase font-semibold">Back</a>
                <a href="{{ route('agents.create') }}"
                   class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md text-xs uppercase font-semibold">Create Agent</a>

                <button type="submit"
                        form="deliveryForm"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md text-xs uppercase font-semibold"
                        :disabled="!isStockSufficient">
                    Create Delivery Note
                </button>
            </div>
        </div>

        @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
            <p class="font-bold">Error</p>
            <ul class="list-disc pl-5 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
            <!-- Left Column -->
            <div class="lg:col-span-2 flex flex-col space-y-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-2">
                        1. Select Company, Customer & POs
                    </h3>

                    <div>
                        <label class="block text-sm font-medium">Company <span class="text-red-500">*</span></label>
                        <select id="company_id"
                                x-model="selectedCompany"
                                @change="filterCustomersByCompany"
                                class="mt-1 block w-full border rounded-md p-1 dark:bg-gray-900"
                                required>
                            <option value="">Select Company</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mt-2">Customer <span class="text-red-500">*</span></label>
                        <input list="customers-list"
                               id="customer_name"
                               x-model="customerName"
                               @change="setCustomerId"
                               placeholder="Type customer name..."
                               class="mt-1 block w-full border rounded-md p-1 dark:bg-gray-900"
                               :disabled="!selectedCompany"
                               required>
                        <datalist id="customers-list">
                            <template x-for="cust in filteredCustomers" :key="cust.id">
                                <option :value="cust.customer_name" :data-id="cust.id"></option>
                            </template>
                        </datalist>
                        <input type="hidden" name="customer_id" x-model="selectedCustomer">
                        <p x-show="customerError" class="text-red-600 text-xs mt-1" x-text="customerError"></p>
                    </div>

                    <form action="{{ route('delivery-notes.create') }}" method="GET" class="mt-3 mb-2 flex items-center space-x-1 text-xs">
                        <input type="date" name="from_date" value="{{ request('from_date') }}"
                            class="border rounded-md px-2 py-0.5 w-32 dark:bg-gray-900">
                        <input type="date" name="to_date" value="{{ request('to_date') }}"
                            class="border rounded-md px-2 py-0.5 w-32 dark:bg-gray-900">
                        <input type="hidden" name="customer_id" :value="selectedCustomer">
                        <button type="submit" class="px-2 py-1 bg-gray-800 text-white rounded-md text-xs hover:bg-gray-700">
                            Filter
                        </button>
                        <a href="{{ route('delivery-notes.create') }}"
                           class="px-2 py-1 bg-gray-200 rounded-md text-xs text-gray-800 hover:bg-gray-300">
                            Clear
                        </a>
                    </form>

                    <form id="deliveryForm" x-ref="deliveryForm"
                          action="{{ route('delivery-notes.store') }}"
                          method="POST" class="space-y-4"
                          @submit.prevent="submitForm">
                        @csrf
                        <div class="space-y-2 max-h-48 overflow-y-auto border p-2 rounded-md">
                            @forelse($purchaseOrders as $po)
                                <label class="flex items-center p-2 rounded-md hover:bg-gray-100">
                                    <input type="checkbox"
                                           name="purchase_order_ids[]"
                                           value="{{ $po->id }}"
                                           x-model="selectedPurchaseOrderIds"
                                           @change="checkStock"
                                           class="rounded">
                                    <span class="ml-3 text-sm">{{ $po->po_id }} - {{ $po->customer->customer_name }}</span>
                                </label>
                            @empty
                                <p class="text-sm text-gray-500 p-2">No purchase orders found. Select a customer above.</p>
                            @endforelse
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Delivery Date</label>
                            <input type="date" name="delivery_date" class="mt-1 block w-full border rounded-md p-2" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Assign Vehicle</label>
                            <select name="vehicle_id" class="mt-1 block w-full border rounded-md p-2" required>
                                <option value="">Select Vehicle</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}">{{ $vehicle->vehicle_no }} - {{ $vehicle->driver_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Driver Name (Override)</label>
                            <input type="text" name="driver_name" placeholder="Leave empty to use vehicle default"
                                   class="mt-1 block w-full border rounded-md p-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Driver Mobile (Override)</label>
                            <input type="text" name="driver_mobile" placeholder="Leave empty to use vehicle default"
                                   class="mt-1 block w-full border rounded-md p-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Helper Name (Override)</label>
                            <input type="text" name="assistant_name"
                                   placeholder="Leave empty to use vehicle default"
                                   class="mt-1 block w-full border rounded-md p-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Helper Mobile (Override)</label>
                            <input type="text" name="assistant_mobile"
                                   placeholder="Leave empty to use vehicle default"
                                   class="mt-1 block w-full border rounded-md p-2">
                        </div>

                        <button type="button"
                                @click="isStockModalOpen = true"
                                class="w-full mt-2 px-4 py-2 bg-gray-500 text-white rounded-md text-sm font-semibold hover:bg-gray-600">
                            Manage Stock
                        </button>

                        <!-- Hidden agent selections -->
                        <template x-for="(agentId, productId) in agentSelections">
                            <input type="hidden" :name="`agent_selections[${productId}]`" :value="agentId">
                        </template>
                    </form>
                </div>
            </div>

            <!-- Right Column -->
            <div class="lg:col-span-3">
                <h3 class="text-lg font-bold text-gray-800 mb-2">2. Stock Check</h3>
                <div class="overflow-x-auto max-h-96">
                    <table class="w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-2 py-2 text-left text-xs font-medium uppercase">Product</th>
                                <th class="px-2 py-2 text-right text-xs font-medium uppercase">Req.</th>
                                <th class="px-2 py-2 text-right text-xs font-medium uppercase">Clear Stock</th>
                                <th class="px-2 py-2 text-right text-xs font-medium uppercase">Non-Clear</th>
                                <th class="px-2 py-2 text-right text-xs font-medium uppercase">Shortage</th>
                                <th class="px-2 py-2 text-left text-xs font-medium uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="item in items" :key="item.product_id">
                                <tr>
                                    <td class="px-2 py-2 text-sm" x-text="item.product_name"></td>
                                    <td class="px-2 py-2 text-sm text-right" x-text="item.requested"></td>
                                    <td class="px-2 py-2 text-sm text-right" x-text="item.clear_stock"></td>
                                    <td class="px-2 py-2 text-sm text-right" x-text="item.non_clear_stock"></td>
                                    <td class="px-2 py-2 text-sm text-right font-bold"
                                        :class="item.clear_stock_shortage > 0 ? 'text-red-500' : 'text-green-500'"
                                        x-text="item.clear_stock_shortage"></td>
                                    <td class="px-2 py-2 text-sm">
                                        <div x-show="item.clear_stock_shortage > 0 && item.non_clear_stock >= item.clear_stock_shortage"
                                             class="text-xs text-orange-500">Convert Stock</div>

                                        <!-- Fulfilment selector -->
                                        <div x-show="item.clear_stock_shortage > 0 && item.non_clear_stock < item.clear_stock_shortage"
                                             class="mb-2">
                                            <select
                                                x-model="stockFulfillment[item.product_id] ? stockFulfillment[item.product_id].type : ''"
                                                @change="handleFulfillmentTypeChange(item.product_id, $event.target.value)"
                                                class="block w-full rounded-md border p-1 text-sm">
                                                <option value="">Select Fulfilment</option>
                                                <option value="agent">Agent</option>
                                                <option value="supplier">Supplier</option>
                                            </select>
                                        </div>

                                        <div x-show="item.clear_stock_shortage > 0 && item.non_clear_stock < item.clear_stock_shortage && stockFulfillment[item.product_id]?.type === 'agent'">
                                            <select x-model="agentSelections[item.product_id]" class="block w-full rounded-md border p-1 text-sm">
                                                <option value="">Select Agent</option>
                                                <template x-for="agent in item.agents" :key="agent.id">
                                                    <option :value="agent.id" x-text="`${agent.name} (${parseFloat(agent.price_per_case).toFixed(2)})`"></option>
                                                </template>
                                            </select>
                                        </div>

                                        <div x-show="item.clear_stock_shortage > 0 && item.non_clear_stock < item.clear_stock_shortage && stockFulfillment[item.product_id]?.type === 'supplier'"
                                             class="mt-2 text-center">
                                            <button type="button"
                                                    @click="createNewGRN(item)"
                                                    class="px-3 py-1 bg-green-600 text-white rounded-md text-xs font-semibold hover:bg-green-700">
                                                Create GRN for Supplier
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="items.length === 0 && selectedPurchaseOrderIds.length > 0">
                                <td colspan="6" class="text-center py-4 text-sm">Loading items...</td>
                            </tr>
                            <tr x-show="items.length === 0 && selectedPurchaseOrderIds.length === 0">
                                <td colspan="6" class="text-center py-4 text-sm">Select a customer and PO above.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('deliveryNoteForm', () => ({
        products: @json($products),
        customers: @json($allCustomers),
        selectedCompany: '',
        filteredCustomers: [],
        customerName: '',
        selectedCustomer: '',
        customerError: '',
        selectedPurchaseOrderIds: [],
        items: [],
        agentSelections: {},
        isStockModalOpen: false,
        stockModalMessage: '',
        stockModalSuccess: false,
        stockFulfillment: {},

        filterCustomersByCompany() {
            if (!this.selectedCompany) {
                this.filteredCustomers = [];
                this.customerName = '';
                this.selectedCustomer = '';
                return;
            }
            this.filteredCustomers = this.customers.filter(c => c.company_id == this.selectedCompany);
            this.customerName = '';
            this.selectedCustomer = '';
        },
        setCustomerId() {
            const match = this.filteredCustomers.find(c => c.customer_name === this.customerName);
            if (match) { this.selectedCustomer = match.id; this.customerError = ''; }
            else { this.selectedCustomer = ''; this.customerError = 'Customer not found or not in this company'; }
        },
        get convertibleItems() {
            return this.items.filter(item => item.clear_stock_shortage > 0 && item.non_clear_stock >= item.clear_stock_shortage);
        },
        get isStockSufficient() {
            if (this.items.length === 0 && this.selectedPurchaseOrderIds.length > 0) return false;
            if (this.items.length === 0) return false;
            return this.items.every(item => {
                if (item.clear_stock_shortage > 0) {
                    if (item.non_clear_stock >= item.clear_stock_shortage) return false;
                    const fulfill = this.stockFulfillment[item.product_id];
                    if (fulfill && fulfill.type === 'supplier') return false; // 🚫 block supplier path
                    const agentSelected = this.agentSelections[item.product_id] && this.agentSelections[item.product_id] !== '';
                    return agentSelected;
                }
                return true;
            });
        },
        filteredProducts(deptId) {
            if (!deptId) return [];
            return this.products.filter(p => p.department_id == deptId);
        },
        updateDepartmentName(formType) {
            const form = this[formType];
            if (form.selectedDepartment) {
                form.departmentError = '';
                form.department_name = this.products.find(p => p.department_id == form.selectedDepartment)?.department_name || '';
            } else form.departmentError = 'Please select a department';
        },
        checkStock() {
            this.agentSelections = {}; this.stockFulfillment = {};
            if (this.selectedPurchaseOrderIds.length === 0) { this.items = []; return; }
            fetch('{{ route("delivery-notes.checkStock") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ po_ids: this.selectedPurchaseOrderIds })
            }).then(res => res.json()).then(data => { this.items = data.items; });
        },
        submitForm() {
            if (!this.isStockSufficient) {
                alert('Cannot create delivery note. Supplier fulfilments must have GRNs created first.');
                return;
            }
            this.$refs.deliveryForm.submit();
        },
        convertStock() {
            this.stockModalMessage = '';
            fetch('{{ route("stock-management.api.convert") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(this.convert)
            }).then(res => res.json()).then(data => {
                this.stockModalSuccess = data.success; this.stockModalMessage = data.message;
                if (data.success) { this.convert = { department_name:'',selectedDepartment:'',departmentError:'',product_id:'',quantity:1 }; this.checkStock(); setTimeout(()=>{this.stockModalMessage=''},3000); }
            });
        },
        logWastage() {
            this.stockModalMessage = '';
            fetch('{{ route("stock-management.api.wastage") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(this.wastage)
            }).then(res => res.json()).then(data => {
                this.stockModalSuccess = data.success; this.stockModalMessage = data.message;
                if (data.success) { this.wastage = { department_name:'',selectedDepartment:'',departmentError:'',product_id:'',stock_type:'clear',quantity:1,reason:'' }; this.checkStock(); setTimeout(()=>{this.stockModalMessage=''},3000); }
            });
        },
        handleFulfillmentTypeChange(productId, value) { this.stockFulfillment[productId] = { type: value }; },
        createNewGRN(item) {
            const url = `/grns/create?supplier_id=${item.supplier_id}&department_id=${item.department_id}&product_id=${item.product_id}&product_name=${encodeURIComponent(item.product_name)}&shortage=${item.clear_stock_shortage}`;
            window.location.href = url;
        },

        convert:{department_name:'',selectedDepartment:'',departmentError:'',product_id:'',quantity:1},
        wastage:{department_name:'',selectedDepartment:'',departmentError:'',product_id:'',stock_type:'clear',quantity:1,reason:''},
    }));
});
</script>
@endsection
