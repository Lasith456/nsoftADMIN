@extends('layouts.app')

@section('content')
<div class="bg-gray-100 dark:bg-gray-900 p-1" x-data="grnForm()">
    <form action="{{ route('grns.store') }}" method="POST">
        @csrf
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-2">

            {{-- Header --}}
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-2 border-b dark:border-gray-700 pb-4">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-2 md:mb-0">Create GRN (Goods Received Note)</h2>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('grns.index') }}" class="px-4 py-1 bg-gray-200 dark:bg-gray-700 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase">Back</a>
                    <button type="submit" class="px-4 py-1 bg-gray-800 border rounded-md font-semibold text-xs text-white uppercase hover:bg-gray-700">Save GRN</button>
                </div>
            </div>

            {{-- Errors --}}
            @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-1 mb-1">
                <p class="font-bold">Whoops! Something went wrong.</p>
                <ul class="list-disc pl-5 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Main GRN Details --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-2 mb-1">
                <div class="space-y-2">
                    <div>
                        <label class="block text-sm font-medium">GRN No</label>
                        <input type="text" value="Auto-Generated" class="mt-1 block w-full bg-gray-100 dark:bg-gray-700 border-gray-300 rounded-md shadow-sm text-sm py-1 px-2" readonly>
                    </div>
                    <div>
                        <label for="delivery_date" class="block text-sm font-medium">Delivery Date*</label>
                        <input type="date" name="delivery_date" id="delivery_date" value="{{ old('delivery_date', date('Y-m-d')) }}" class="mt-1 block w-full dark:bg-gray-900 border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm py-1 px-2" required>
                    </div>
                </div>
                <div class="space-y-2">
                    <div>
                        <label for="supplier_name" class="block text-sm font-medium">Supplier*</label>
                        <input list="suppliers-list" id="supplier_name" class="mt-1 block w-full dark:bg-gray-900 border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm py-1 px-2" required>
                        <datalist id="suppliers-list">
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->supplier_name }}" data-id="{{ $supplier->id }}"></option>
                            @endforeach
                        </datalist>
                        <input type="hidden" name="supplier_id" id="supplier_id">
                    </div>
                    <div>
                        <label for="invoice_number" class="block text-sm font-medium">Invoice Number</label>
                        <input type="text" name="invoice_number" id="invoice_number" class="mt-1 block w-full dark:bg-gray-900 border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm py-1 px-2">
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 py-1 px-3 rounded-lg space-y-1">
                    <h3 class="text-lg font-bold">GRN Value Details</h3>
                    <div class="flex justify-between text-sm">
                        <span>Total GRN Amount:</span>
                        <span x-text="totals.totalAmount.toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span>Total Discount:</span>
                        <span x-text="totals.totalDiscount.toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between text-lg font-bold text-green-600">
                        <span>Net GRN Amount:</span>
                        <span x-text="totals.netAmount.toFixed(2)"></span>
                    </div>
                </div>
            </div>

            {{-- Items Section --}}
            <div>
                <h3 class="text-lg font-bold mb-1">Add Items to GRN</h3>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    
                    {{-- Entry Form --}}
                    <div class="lg:col-span-1 space-y-2 bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                        <h4 class="font-semibold">New Item</h4>

                        {{-- Department --}}
                        <div>
                            <label class="block text-sm font-medium">Department*</label>
                            <input list="departments-list" x-model="departmentName" @change="departmentChangedByName" placeholder="Type department..." class="mt-1 block w-full dark:bg-gray-900 border rounded-md text-sm py-2 px-3">
                            <datalist id="departments-list">
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->name }}" data-id="{{ $dept->id }}"></option>
                                @endforeach
                            </datalist>
                            <p x-show="departmentError" class="text-red-600 text-xs mt-1" x-text="departmentError"></p>
                        </div>

                        {{-- Product --}}
                        <div>
                            <label class="block text-sm font-medium">Product*</label>
                            <input list="products-list" x-model="currentItem.product_name" @change="productChangedByName" :disabled="!selectedDepartment" placeholder="Select department first" class="mt-1 block w-full dark:bg-gray-900 border rounded-md text-sm py-2 px-3">
                            <datalist id="products-list">
                                <template x-for="product in filteredProducts" :key="product.id">
                                    <option :value="product.name" :data-id="product.id"></option>
                                </template>
                            </datalist>
                        </div>

                        {{-- Receive In + Qty --}}
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-sm font-medium">Receive In*</label>
                                <select x-model="currentItem.unit_type" class="mt-1 block w-full dark:bg-gray-900 border rounded-md text-sm py-2 px-3">
                                    <option>Unit</option>
                                    <option>Case</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium">Qty*</label>
                                <input type="number" x-model.number="currentItem.quantity" min="0" class="mt-1 block w-full dark:bg-gray-900 border rounded-md text-sm py-2 px-3">
                            </div>
                        </div>

                        {{-- Stock Type --}}
                        <div>
                            <label class="block text-sm font-medium">Stock Type*</label>
                            <select x-model="currentItem.stock_type" class="mt-1 block w-full dark:bg-gray-900 border rounded-md text-sm py-2 px-3">
                                <option value="clear">Clear Stock</option>
                                <option value="non-clear">Non-Clear Stock</option>
                            </select>
                        </div>

                        {{-- Cost + Selling --}}
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-sm font-medium">Unit Cost*</label>
                                <input type="number" step="0.01" x-model.number="currentItem.cost_price" class="mt-1 block w-full dark:bg-gray-900 border rounded-md text-sm py-2 px-3">
                            </div>
                            <div>
                                <label class="block text-sm font-medium">Selling Price*</label>
                                <input type="number" step="0.01" x-model.number="currentItem.selling_price" class="mt-1 block w-full dark:bg-gray-900 border rounded-md text-sm py-2 px-3">
                            </div>
                        </div>

                        {{-- Free Issue --}}
                        <div class="flex items-center space-x-2">
                            <label class="flex items-center space-x-2 text-sm font-medium">
                                <input type="checkbox" x-model="currentItem.is_free_issue" class="form-checkbox h-4 w-4">
                                <span>Free Issue</span>
                            </label>
                        </div>
                        <div x-show="currentItem.is_free_issue">
                            <label class="block text-sm font-medium">Free Issue Qty</label>
                            <input type="number" min="0" x-model.number="currentItem.free_issue_qty" class="mt-1 block w-full dark:bg-gray-900 border rounded-md text-sm py-2 px-3">
                        </div>

                        {{-- Discount --}}
                        <div class="flex items-center space-x-2">
                            <label class="flex items-center text-sm font-medium">
                                <input type="checkbox" x-model="discountAsPercentage" class="form-checkbox h-4 w-4 mr-2">
                                <span x-text="discountAsPercentage ? 'Percentage' : 'Amount'"></span>
                            </label>
                        </div>
                        <div x-show="!discountAsPercentage">
                            <label class="block text-sm font-medium">Discount Amount</label>
                            <input type="number" step="0.01" x-model.number="currentItem.discount_amount" class="mt-1 block w-full dark:bg-gray-900 border rounded-md text-sm py-2 px-3">
                        </div>
                        <div x-show="discountAsPercentage">
                            <label class="block text-sm font-medium">Discount Percentage (%)</label>
                            <input type="number" step="0.01" x-model.number="currentItem.discount_percentage" min="0" max="100" class="mt-1 block w-full dark:bg-gray-900 border rounded-md text-sm py-2 px-3">
                        </div>

                        <button type="button" @click="addItem" class="w-full py-1 px-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">Add Item</button>
                    </div>

                    {{-- Items Table --}}
                    <div class="lg:col-span-2">
                        <div class="overflow-x-auto">
                            <table class="w-full divide-y divide-gray-300 dark:divide-gray-700">
                                <thead class="bg-gray-100 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-2 py-1 text-left text-xs uppercase">Department</th>
                                        <th class="px-2 py-1 text-left text-xs uppercase">Product</th>
                                        <th class="px-2 py-1 text-left text-xs uppercase">Qty</th>
                                        <th class="px-2 py-1 text-left text-xs uppercase">Free Issue</th>
                                        <th class="px-2 py-1 text-left text-xs uppercase">Cost</th>
                                        <th class="px-2 py-1 text-left text-xs uppercase">Discount</th>
                                        <th class="px-2 py-1 text-left text-xs uppercase">Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-700">
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr>
                                            <td class="px-2 py-1 text-sm" x-text="item.department_name"></td>
                                            <td class="px-2 py-1 text-sm" x-text="item.name"></td>
                                            <td class="px-2 py-1 text-sm" x-text="`${item.quantity} ${item.unit_type}(s)`"></td>
                                            <td class="px-2 py-1 text-sm" x-text="item.is_free_issue ? `${item.free_issue_qty} ${item.unit_type}(s)` : '-'"></td>
                                            <td class="px-2 py-1 text-sm" x-text="item.cost_price.toFixed(2)"></td>
                                            <td class="px-2 py-1 text-sm" x-text="item.discount.toFixed(2)"></td>
                                            <td class="px-2 py-1 text-sm" x-text="(!item.is_free_issue ? ((item.cost_price * (item.unit_type === 'Case' ? item.quantity * item.units_per_case : item.quantity)) - item.discount).toFixed(2) : '0.00')"></td>
                                            <td class="px-2 py-1 text-sm"><button type="button" @click="removeItem(index)" class="text-red-500">&times;</button></td>
                                        </tr>
                                    </template>
                                    <tr x-show="items.length === 0">
                                        <td colspan="8" class="text-center py-4 text-sm text-gray-500">No items added.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Hidden Inputs --}}
            <template x-for="(item, index) in items" :key="index">
                <div>
                    <input type="hidden" :name="`items[${index}][department_id]`" :value="item.department_id">
                    <input type="hidden" :name="`items[${index}][product_id]`" :value="item.product_id">
                    <input type="hidden" :name="`items[${index}][unit_type]`" :value="item.unit_type">
                    <input type="hidden" :name="`items[${index}][stock_type]`" :value="item.stock_type">
                    <input type="hidden" :name="`items[${index}][quantity]`" :value="item.unit_type === 'Case' ? item.quantity * item.units_per_case : item.quantity">
                    <input type="hidden" :name="`items[${index}][cost_price]`" :value="item.cost_price">
                    <input type="hidden" :name="`items[${index}][selling_price]`" :value="item.selling_price">
                    <input type="hidden" :name="`items[${index}][discount]`" :value="item.discount">
                    <input type="hidden" :name="`items[${index}][is_free_issue]`" :value="item.is_free_issue ? 1 : 0">
                    <input type="hidden" :name="`items[${index}][free_issue_qty]`" :value="item.free_issue_qty">
                </div>
            </template>
        </div>
    </form>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('grnForm', () => ({
        products: @json($products),
        items: [],
        departmentName: '',
        selectedDepartment: '',
        departmentError: '',
        discountAsPercentage: false,
        currentItem: {
            product_id: '',
            product_name: '',
            unit_type: 'Unit',
            stock_type: 'clear',
            quantity: 0, // ✅ Qty can start as 0
            cost_price: 0,
            selling_price: 0,
            discount_amount: 0,
            discount_percentage: 0,
            units_per_case: 1,
            is_free_issue: false,
            free_issue_qty: 0,
        },
init() {
    const supplierInput = document.getElementById('supplier_name');
    const supplierIdInput = document.getElementById('supplier_id');

    // === SUPPLIER HANDLER ===
    supplierInput.addEventListener('change', function () {
        const supplierName = this.value;
        const options = document.querySelectorAll('#suppliers-list option');
        const matched = Array.from(options).find(opt => opt.value === supplierName);
        if (matched) {
            supplierIdInput.value = matched.dataset.id;
        } else {
            supplierIdInput.value = '';
            alert('Supplier not found. Please select a valid supplier from the list.');
            this.value = '';
        }
    });

    // === PREFILL FROM URL PARAMETERS ===
    const params = new URLSearchParams(window.location.search);
    const supplierId = params.get('supplier_id');
    const departmentId = params.get('department_id');
    const productId = params.get('product_id');
    const shortage = params.get('shortage');
    const productName = params.get('product_name');

    // ✅ Prefill Supplier
    if (supplierId) {
        const match = Array.from(document.querySelectorAll('#suppliers-list option'))
            .find(opt => opt.dataset.id == supplierId);
        if (match) {
            supplierInput.value = match.value;
            supplierIdInput.value = supplierId;
        }
    }

    // ✅ Wait a tick so Alpine data is ready before setting department
    this.$nextTick(() => {
        const deptMatch = Array.from(document.querySelectorAll('#departments-list option'))
            .find(opt => opt.dataset.id == departmentId);

        if (deptMatch) {
            // Show department name
            this.departmentName = deptMatch.value;
            // Set selected ID
            this.selectedDepartment = parseInt(departmentId);
            // Reset any old error
            this.departmentError = '';
        } else if (departmentId) {
            // Only show error if ID was passed but not found
            this.departmentError = `Department ID ${departmentId} not found in list`;
        }

        // ✅ Prefill Product (after department known)
        if (productId) {
            const prod = this.products.find(p => p.id == productId);
            if (prod) {
                this.currentItem.product_id = prod.id;
                this.currentItem.product_name = prod.name;
                this.currentItem.cost_price = parseFloat(prod.cost_price);
                this.currentItem.selling_price = parseFloat(prod.selling_price);
                this.currentItem.units_per_case = prod.units_per_case || 1;
                this.currentItem.stock_type = prod.is_clear ? 'clear' : 'non-clear';
            }
        }

        // ✅ Prefill Shortage quantity
        if (shortage) {
            this.currentItem.quantity = parseFloat(shortage);
        }

        // ✅ Highlight product input for visual focus
        setTimeout(() => {
            const productInput = document.querySelector('input[list="products-list"]');
            if (productInput) {
                productInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                productInput.classList.add('ring', 'ring-blue-400');
                setTimeout(() => productInput.classList.remove('ring', 'ring-blue-400'), 2000);
            }
        }, 600);
    });
},



        get filteredProducts() {
            if (!this.selectedDepartment) return [];
            return this.products.filter(p => p.department_id == this.selectedDepartment);
        },

        departmentChangedByName() {
            const options = document.getElementById('departments-list').options;
            let deptId = '';
            for (let i = 0; i < options.length; i++) {
                if (options[i].value === this.departmentName) {
                    deptId = options[i].dataset.id;
                    break;
                }
            }
            if (deptId) {
                this.selectedDepartment = deptId;
                this.departmentError = '';
            } else {
                this.selectedDepartment = '';
                this.departmentError = 'Department not found';
                this.departmentName = '';
            }
        },

        productChangedByName(event) {
            const productName = event.target.value;
            const product = this.filteredProducts.find(p => p.name === productName);
            if (product) {
                this.currentItem.product_id = product.id;
                this.currentItem.cost_price = parseFloat(product.cost_price);
                this.currentItem.selling_price = parseFloat(product.selling_price);
                this.currentItem.units_per_case = product.units_per_case || 1;
                this.currentItem.stock_type = product.is_clear ? 'clear' : 'non-clear';
            } else {
                this.currentItem.product_id = '';
            }
        },
        addItem() {
            if (!this.selectedDepartment || !this.currentItem.product_id || this.currentItem.quantity < 0) {
                alert('Please select department, product, and enter a valid quantity (0 or more).');
                return;
            }
                if (this.currentItem.quantity < 0) {
                alert('Quantity cannot be negative.');
                return;
            }

            if (this.currentItem.cost_price <= 0) {
                alert('Cost price must be greater than 0.');
                return;
            }
            let discount = 0;
            if (this.discountAsPercentage) {
                const effectiveQty = this.currentItem.unit_type === 'Case'
                    ? this.currentItem.quantity * this.currentItem.units_per_case
                    : this.currentItem.quantity;
                discount = (this.currentItem.discount_percentage / 100) * (this.currentItem.cost_price * effectiveQty);
            } else {
                discount = this.currentItem.discount_amount;
            }

            const product = this.filteredProducts.find(p => p.id == this.currentItem.product_id);
            this.items.push({
                ...this.currentItem,
                name: product.name,
                department_id: this.selectedDepartment,
                department_name: this.departmentName,
                discount: discount
            });

            this.resetCurrentItem();
        },

        removeItem(index) {
            this.items.splice(index, 1);
        },

        resetCurrentItem() {
            this.currentItem = {
                product_id: '',
                product_name: '',
                unit_type: 'Unit',
                stock_type: 'clear',
                quantity: 0,   // ✅ allow 0 as valid
                cost_price: 0,
                selling_price: 0,
                discount_amount: 0,
                discount_percentage: 0,
                units_per_case: 1,
                is_free_issue: false,
                free_issue_qty: 0,
            };
        },

        get totals() {
            let totalAmount = 0;
            let totalDiscount = 0;
            this.items.forEach(item => {
                if (!item.is_free_issue) { // exclude free issues from totals
                    let effectiveQty = item.quantity;
                    if (item.unit_type === 'Case' && item.units_per_case > 0) {
                        effectiveQty *= item.units_per_case;
                    }
                    totalAmount += item.cost_price * effectiveQty;
                    totalDiscount += item.discount;
                }
            });
            return {
                totalAmount,
                totalDiscount,
                netAmount: totalAmount - totalDiscount,
            };
        }
    }));
});
</script>
@endsection
