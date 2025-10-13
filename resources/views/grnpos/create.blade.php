@extends('layouts.app')

@section('content')
<div class="bg-gray-100 dark:bg-gray-900 p-2" x-data="grnpoForm()">
    <form action="{{ route('grnpos.store') }}" method="POST">
        @csrf

        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4">

            {{-- Header --}}
            <div class="flex justify-between items-center mb-3 border-b pb-2">
                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">
                    Create GRN (From Purchase Order)
                </h2>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Save
                </button>
            </div>

            {{-- Supplier --}}
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Supplier*</label>
                <select name="supplier_id" x-model="supplier_id"
                        class="w-full border rounded p-2 dark:bg-gray-900 dark:text-white" required>
                    <option value="">Select supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->supplier_name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Delivery Date --}}
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Delivery Date*</label>
                <input type="date" name="delivery_date"
                       class="w-full border rounded p-2 dark:bg-gray-900 dark:text-white"
                       value="{{ date('Y-m-d') }}" required>
            </div>

            {{-- Department & Product Add Section --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 bg-gray-50 dark:bg-gray-700 p-3 rounded-lg mb-4">
                {{-- Department --}}
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Department*</label>
                    <input list="dept-list" x-model="current.department_name" @change="departmentChangedByName"
                           placeholder="Select Department"
                           class="w-full border rounded p-2 dark:bg-gray-900 dark:text-white">
                    <datalist id="dept-list">
                        @foreach($departments as $d)
                            <option value="{{ $d->name }}" data-id="{{ $d->id }}"></option>
                        @endforeach
                    </datalist>
                </div>

                {{-- Product --}}
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Product*</label>
                    <input list="prod-list" x-model="current.product_name" @change="productChangedByName"
                           :disabled="!current.department_id"
                           placeholder="Select Product"
                           class="w-full border rounded p-2 dark:bg-gray-900 dark:text-white">
                    <datalist id="prod-list">
                        <template x-for="p in filteredProducts" :key="p.id">
                            <option :value="p.name" :data-id="p.id"></option>
                        </template>
                    </datalist>
                </div>

                {{-- Quantity --}}
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Quantity*</label>
                    <input type="number" x-model.number="current.quantity"
                           class="w-full border rounded p-2 dark:bg-gray-900 dark:text-white" placeholder="0">
                </div>

                {{-- Add Button --}}
                <div class="col-span-3">
                    <button type="button" @click="addItem"
                            class="px-4 py-1 bg-green-600 text-white rounded hover:bg-green-700 text-sm w-full md:w-auto">
                        Add Item
                    </button>
                </div>
            </div>

            {{-- Added Items Table --}}
            <table class="w-full text-sm border">
                <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                    <tr>
                        <th class="p-2 text-left">Department</th>
                        <th class="p-2 text-left">Product</th>
                        <th class="p-2 text-left">Quantity</th>
                        <th class="p-2 text-center">Remove</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(item, i) in items" :key="i">
                        <tr class="border-t">
                            <td class="p-2" x-text="item.department_name"></td>
                            <td class="p-2" x-text="item.product_name"></td>
                            <td class="p-2" x-text="item.quantity"></td>
                            <td class="p-2 text-center text-red-500 cursor-pointer" @click="removeItem(i)">×</td>
                        </tr>
                    </template>

                    <tr x-show="items.length === 0">
                        <td colspan="4" class="text-center text-gray-500 dark:text-gray-400 py-3">
                            No items added.
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- Hidden Inputs for Items --}}
            <div x-show="items.length > 0">
                <template x-for="(item, i) in items" :key="i">
                    <div>
                        <input type="hidden" :name="`items[${i}][department_id]`" :value="item.department_id">
                        <input type="hidden" :name="`items[${i}][product_id]`" :value="item.product_id">
                        <input type="hidden" :name="`items[${i}][quantity]`" :value="item.quantity">
                    </div>
                </template>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('grnpoForm', () => ({
        products: @json($products),
        items: [],
        supplier_id: '',
        current: {
            department_id: '',
            department_name: '',
            product_id: '',
            product_name: '',
            quantity: 0
        },

        // Get department id from datalist
        departmentChangedByName() {
            const opt = [...document.getElementById('dept-list').options]
                .find(o => o.value === this.current.department_name);
            this.current.department_id = opt ? opt.dataset.id : '';
        },

        // Filter products by department
        get filteredProducts() {
            return this.products.filter(p => p.department_id == this.current.department_id);
        },

        // Get product id from datalist
        productChangedByName(e) {
            const prod = this.filteredProducts.find(p => p.name === e.target.value);
            this.current.product_id = prod ? prod.id : '';
        },

        // Add item to table
        addItem() {
            if (!this.current.department_id || !this.current.product_id) {
                alert('Please select a department and product.');
                return;
            }

            if (!this.current.quantity || this.current.quantity < 1) {
                alert('Please enter a valid quantity greater than 0.');
                return;
            }

            const exists = this.items.find(i => i.product_id === this.current.product_id);
            if (exists) {
                alert('This product is already added.');
                return;
            }

            this.items.push({ ...this.current });
            this.current = { department_id: '', department_name: '', product_id: '', product_name: '', quantity: 0 };
        },

        // Remove item
        removeItem(i) {
            this.items.splice(i, 1);
        }
    }));
});
</script>
@endsection
