@extends('layouts.app')

@section('content')
<div class="container mx-auto" x-data="simplifiedPoForm()">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4">
        <form id="po-form" action="{{ route('purchase-orders.store') }}" method="POST">
            @csrf
            <div class="flex justify-between items-center mb-4 pb-3 border-b dark:border-gray-700">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Create Purchase Order</h2>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('purchase-orders.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase">
                        Back
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border rounded-md font-semibold text-xs text-white uppercase hover:bg-gray-700">Create Order</button>
                </div>
            </div>

            @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                 <ul class="list-disc pl-5 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Main PO Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="customer_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer <span class="text-red-500">*</span></label>
                    <input list="customers-list" id="customer_name" class="mt-1 block w-full dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-md text-gray-900 dark:text-gray-200 py-2 px-3" required>
                    <datalist id="customers-list">
                        @foreach($customers as $customer)
                            <option value="{{ $customer->customer_name }}" data-id="{{ $customer->id }}"></option>
                        @endforeach
                    </datalist>
                    <input type="hidden" name="customer_id" id="customer_id">
                </div>
                <div>
                    <label for="delivery_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Delivery Date <span class="text-red-500">*</span></label>
                    <input type="date" name="delivery_date" id="delivery_date" class="mt-1 block w-full dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-md text-gray-900 dark:text-gray-200 py-2 px-3" required>
                </div>
            </div>

            <!-- Item Entry Section -->
            <div class="border-t dark:border-gray-700 pt-4">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">Add Items</h3>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    {{-- Item Entry Form --}}
                    <div class="lg:col-span-1 space-y-2 bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">

                        <!-- Department Selection -->
                        <div>
                            <label class="block text-sm font-medium">Department</label>
                            <input list="departments-list" 
                                   id="department_name"
                                   x-model="departmentName"
                                   @change="departmentChangedByName"
                                   placeholder="Type to search department..."
                                   class="mt-1 block w-full dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-md text-gray-900 dark:text-gray-200 py-2 px-3">

                            <datalist id="departments-list">
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->name }}" data-id="{{ $dept->id }}"></option>
                                @endforeach
                            </datalist>

                            <p x-show="departmentError" class="text-red-600 text-xs mt-1" x-text="departmentError"></p>
                        </div>

                        <!-- Product Selection -->
                        <div>
                            <label class="block text-sm font-medium">Product</label>
                            <input list="products-list" 
                                   x-model="currentItem.product_name" 
                                   @change="productChangedByName" 
                                   :disabled="!selectedDepartment"
                                   placeholder="Select department first"
                                   class="mt-1 block w-full dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-md text-gray-900 dark:text-gray-200 py-2 px-3">
                            <datalist id="products-list">
                                <template x-for="product in filteredProducts" :key="product.id">
                                    <option :value="product.name" :data-id="product.id"></option>
                                </template>
                            </datalist>
                        </div>

                        <div>
                            <label class="block text-sm font-medium">Quantity</label>
                            <input type="number" x-model.number="quantity" min="1" class="mt-1 block w-full dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-md text-gray-900 dark:text-gray-200 py-2 px-3">
                        </div>

                        <button type="button" @click="addItem" class="w-full py-2 px-4 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">Add Item</button>
                    </div>

                    {{-- Items Table --}}
                    <div class="lg:col-span-2">
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-full divide-y divide-gray-300 dark:divide-gray-700">
                                <thead class="bg-gray-100 dark:bg-gray-700">
                                    <tr>
                                        <th class="py-2 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-200">Product</th>
                                        <th class="px-3 py-2 text-left text-sm font-semibold text-gray-900 dark:text-gray-200">Quantity</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr>
                                            <td class="py-2 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-gray-200" x-text="item.name"></td>
                                            <td class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400" x-text="item.quantity"></td>
                                            <td class="py-2 pl-3 pr-4 text-right text-sm font-medium">
                                                <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-900">&times;</button>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="items.length === 0"><td colspan="3" class="text-center py-4 text-sm text-gray-500 dark:text-gray-400">No items added.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Hidden Inputs for Submission -->
            <template x-for="(item, index) in items" :key="index">
                <div>
                    <input type="hidden" :name="`items[${index}][product_id]`" :value="item.product_id">
                    <input type="hidden" :name="`items[${index}][quantity]`" :value="item.quantity">
                </div>
            </template>
        </form>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('simplifiedPoForm', () => ({
            items: [],
            products: @json($products),
            selectedDepartment: '',
            departmentName: '',
            departmentError: '',
            currentItem: {
                product_id: '',
                product_name: ''
            },
            quantity: 1,
            
            get filteredProducts() {
                if (!this.selectedDepartment) return [];
                return this.products.filter(p => p.department_id == this.selectedDepartment);
            },

            init() {
                const customerInput = document.getElementById('customer_name');
                const customerIdInput = document.getElementById('customer_id');
                customerInput.addEventListener('change', function() {
                    const customerName = this.value;
                    let customerId = '';
                    const options = document.getElementById('customers-list').options;
                    for (let i = 0; i < options.length; i++) {
                        if (options[i].value === customerName) {
                            customerId = options[i].dataset.id;
                            break;
                        }
                    }
                    customerIdInput.value = customerId;
                });
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
                }
            },

            productChangedByName() {
                const product = this.filteredProducts.find(p => p.name === this.currentItem.product_name);
                this.currentItem.product_id = product ? product.id : '';
            },

            addItem() {
                if (!this.currentItem.product_id || this.quantity <= 0) return;
                const product = this.filteredProducts.find(p => p.id == this.currentItem.product_id);
                if (!product) return;

                const existingItem = this.items.find(i => i.product_id == this.currentItem.product_id);
                if (existingItem) {
                    existingItem.quantity = parseInt(existingItem.quantity) + parseInt(this.quantity);
                } else {
                    this.items.push({
                        product_id: product.id,
                        name: product.name,
                        quantity: this.quantity,
                    });
                }
                this.currentItem = { product_id: '', product_name: '' };
                this.quantity = 1;
            },
            
            removeItem(index) {
                this.items.splice(index, 1);
            }
        }));
    });
</script>
@endsection
