@extends('layouts.app')

@section('content')
<div class="container mx-auto p-2" x-data="deliveryNoteForm">
    <!-- Stock Management Modal -->
    <div x-show="isStockModalOpen"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
         @click.away="isStockModalOpen = false" x-cloak>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-4xl">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-4">Stock Management</h2>
            <div x-show="stockModalMessage"
                 class="p-4 mb-4 text-sm rounded-lg"
                 :class="stockModalSuccess ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                 x-text="stockModalMessage"></div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Convert Form -->
                <form @submit.prevent="convertStock" class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-200 border-b pb-2">Convert Stock</h3>

                    <div>
                        <label class="block text-sm font-medium">Department*</label>
                        <input list="departments-list"
                               x-model="convert.department_name"
                               @change="departmentChangedByName('convert')"
                               placeholder="Type department..."
                               class="mt-1 block w-full dark:bg-gray-900 border rounded-md py-2 px-3">
                        <p x-show="convert.departmentError"
                           class="text-red-600 text-xs mt-1"
                           x-text="convert.departmentError"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Product*</label>
                        <input list="products-list-modal"
                               x-model="convert.product_name"
                               @change="updateSelectedProduct('convert')"
                               :disabled="!convert.selectedDepartment"
                               placeholder="Select department first"
                               class="mt-1 block w-full dark:bg-gray-900 border rounded-md py-2 px-3">
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
                        <input list="departments-list"
                               x-model="wastage.department_name"
                               @change="departmentChangedByName('wastage')"
                               placeholder="Type department..."
                               class="mt-1 block w-full dark:bg-gray-900 border rounded-md py-2 px-3">
                        <p x-show="wastage.departmentError"
                           class="text-red-600 text-xs mt-1"
                           x-text="wastage.departmentError"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Product*</label>
                        <input list="products-list-modal"
                               x-model="wastage.product_name"
                               @change="updateSelectedProduct('wastage')"
                               :disabled="!wastage.selectedDepartment"
                               placeholder="Select department first"
                               class="mt-1 block w-full dark:bg-gray-900 border rounded-md py-2 px-3">
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

    <!-- Department list -->
    <datalist id="departments-list">
        @foreach($departments as $dept)
            <option value="{{ $dept->name }}" data-id="{{ $dept->id }}"></option>
        @endforeach
    </datalist>

    <!-- Product list -->
    <datalist id="products-list-modal">
        <template x-for="product in filteredProducts" :key="product.id">
            <option :value="product.name" :data-id="product.id"></option>
        </template>
    </datalist>

    <!-- === Main Delivery Note Form === -->
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4">
        <div class="flex justify-between items-center mb-4 pb-3 border-b dark:border-gray-700">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Create Delivery Note</h2>
            <div class="flex items-center space-x-2">
                <a href="{{ route('delivery-notes.index') }}"
                   class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md text-xs uppercase font-semibold">Back</a>
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
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-2">1. Select POs & Details</h3>
                    <form action="{{ route('delivery-notes.create') }}" method="GET" class="mb-2 flex space-x-2">
                        <input type="date" name="from_date" value="{{ request('from_date') }}" class="border rounded-md p-1">
                        <input type="date" name="to_date" value="{{ request('to_date') }}" class="border rounded-md p-1">
                        <button type="submit" class="px-3 py-1 bg-gray-800 text-white rounded-md text-xs">Filter</button>
                        <a href="{{ route('delivery-notes.create') }}" class="px-3 py-1 bg-gray-200 rounded-md text-xs">Clear</a>
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
                                <p class="text-sm text-gray-500 p-2">No pending purchase orders found.</p>
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
                            <input type="text" name="driver_name" placeholder="Leave empty to use vehicle default" class="mt-1 block w-full border rounded-md p-2">
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Driver Mobile (Override)</label>
                            <input type="text" name="driver_mobile" placeholder="Leave empty to use vehicle default" class="mt-1 block w-full border rounded-md p-2">
                        </div>

                        <button type="button"
                                @click="isStockModalOpen = true"
                                class="w-full mt-2 px-4 py-2 bg-gray-500 text-white rounded-md text-sm font-semibold hover:bg-gray-600">
                            Manage Stock
                        </button>

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
                                        <div x-show="item.clear_stock_shortage > 0 && item.non_clear_stock >= item.clear_stock_shortage" class="text-xs text-orange-500">Convert Stock</div>
                                        <div x-show="item.clear_stock_shortage > 0 && item.non_clear_stock < item.clear_stock_shortage">
                                            <select x-model="agentSelections[item.product_id]" class="block w-full rounded-md border p-1 text-sm">
                                                <option value="">Select Agent</option>
                                                <template x-for="agent in item.agents" :key="agent.id">
                                                    <option :value="agent.id" x-text="`${agent.name} (${parseFloat(agent.price_per_case).toFixed(2)})`"></option>
                                                </template>
                                            </select>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="items.length === 0 && selectedPurchaseOrderIds.length > 0"><td colspan="6" class="text-center py-4 text-sm">Loading items...</td></tr>
                            <tr x-show="items.length === 0 && selectedPurchaseOrderIds.length === 0"><td colspan="6" class="text-center py-4 text-sm">Select one or more POs to check stock.</td></tr>
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
        selectedPurchaseOrderIds: [],
        items: [],
        agentSelections: {},
        isStockModalOpen: false,
        stockModalMessage: '',
        stockModalSuccess: false,

        convert: { department_name: '', selectedDepartment: '', departmentError: '', product_id: '', product_name: '', quantity: 1 },
        wastage: { department_name: '', selectedDepartment: '', departmentError: '', product_id: '', product_name: '', stock_type: 'clear', quantity: 1, reason: '' },

        get isStockSufficient() {
            if (this.items.length === 0 && this.selectedPurchaseOrderIds.length > 0) return false;
            if (this.items.length === 0) return false;
            return this.items.every(item => {
                if (item.clear_stock_shortage > 0) {
                    if (item.non_clear_stock >= item.clear_stock_shortage) return false;
                    const agentSelected = this.agentSelections[item.product_id] && this.agentSelections[item.product_id] !== '';
                    return agentSelected;
                }
                return true;
            });
        },

        get filteredProducts() {
            let dept = this.convert.selectedDepartment || this.wastage.selectedDepartment;
            if (!dept) return [];
            return this.products.filter(p => p.department_id == dept);
        },

        departmentChangedByName(formType) {
            const form = this[formType];
            const options = document.getElementById('departments-list').options;
            let deptId = '';
            for (let i = 0; i < options.length; i++) {
                if (options[i].value === form.department_name) {
                    deptId = options[i].dataset.id;
                    break;
                }
            }
            if (deptId) {
                form.selectedDepartment = deptId;
                form.departmentError = '';
            } else {
                form.selectedDepartment = '';
                form.departmentError = 'Department not found';
                form.department_name = '';
            }
        },

        updateSelectedProduct(formType) {
            const form = this[formType];
            const product = this.filteredProducts.find(p => p.name === form.product_name);
            form.product_id = product ? product.id : '';
        },

        checkStock() {
            this.agentSelections = {};
            if (this.selectedPurchaseOrderIds.length === 0) { this.items = []; return; }
            fetch('{{ route("delivery-notes.checkStock") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ po_ids: this.selectedPurchaseOrderIds })
            }).then(res => res.json()).then(data => { this.items = data.items; });
        },

        submitForm() {
            if (!this.isStockSufficient) {
                alert('Cannot create delivery note. Please resolve shortages.');
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
                this.stockModalSuccess = data.success;
                this.stockModalMessage = data.message;
                if (data.success) this.convert = { department_name: '', selectedDepartment: '', departmentError: '', product_id: '', product_name: '', quantity: 1 };
            });
        },

        logWastage() {
            this.stockModalMessage = '';
            fetch('{{ route("stock-management.api.wastage") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(this.wastage)
            }).then(res => res.json()).then(data => {
                this.stockModalSuccess = data.success;
                this.stockModalMessage = data.message;
                if (data.success) this.wastage = { department_name: '', selectedDepartment: '', departmentError: '', product_id: '', product_name: '', stock_type: 'clear', quantity: 1, reason: '' };
            });
        }
    }));
});
</script>
@endsection
