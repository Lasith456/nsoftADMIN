@extends('layouts.app')

@section('content')
<div class="container mx-auto" x-data="stockManagementForm()">
    {{-- Shared Department and Product lists --}}
    <datalist id="departments-list">
        @foreach($departments as $dept)
            <option value="{{ $dept->name }}" data-id="{{ $dept->id }}"></option>
        @endforeach
    </datalist>

    <datalist id="products-list">
        <template x-for="product in filteredProducts" :key="product.id">
            <option :value="product.name" :data-id="product.id"></option>
        </template>
    </datalist>

    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-4">Stock Management</h2>

        {{-- Dynamic Alerts --}}
        <div x-show="alertMessage" class="mb-4" x-cloak>
            <div x-show="alertType === 'success'" 
                 class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4" 
                 role="alert">
                <p x-text="alertMessage"></p>
            </div>
            <div x-show="alertType === 'error'" 
                 class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" 
                 role="alert">
                <p x-text="alertMessage"></p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Stock Conversion Form -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-200 border-b pb-2 mb-4">
                    Convert Non-Clear to Clear Stock
                </h3>
                <form @submit.prevent="convertStock" class="space-y-4">
                    @csrf
                    {{-- Department --}}
                    <div>
                        <label class="block text-sm font-medium">Department*</label>
                        <input list="departments-list"
                               x-model="departmentNameConvert"
                               @change="departmentChangedByName('convert')"
                               placeholder="Type department..."
                               class="mt-1 block w-full dark:bg-gray-900 border rounded-md py-2 px-3">
                        <p x-show="departmentErrorConvert" class="text-red-600 text-xs mt-1" x-text="departmentErrorConvert"></p>
                    </div>
                    {{-- Product --}}
                    <div>
                        <label class="block text-sm font-medium">Product</label>
                        <input list="products-list"
                               x-model="selectedProductNameConvert"
                               @change="updateSelectedProduct('convert')"
                               :disabled="!selectedDepartmentConvert"
                               class="mt-1 block w-full dark:bg-gray-900 rounded-md py-2 px-3 border"
                               placeholder="Select department first">
                        <input type="hidden" x-model="selectedProductConvert">
                        <p x-show="selectedProductConvert" class="text-xs text-gray-500 mt-1" 
                           x-text="`Available Non-Clear Stock: ${productStock.non_clear}`" x-cloak></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Quantity to Convert</label>
                        <input type="number" x-model="quantityConvert" min="1" 
                               :max="productStock.non_clear"
                               class="mt-1 block w-full dark:bg-gray-900 rounded-md py-2 px-3 border" required>
                    </div>
                    <div class="text-right">
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold hover:bg-blue-700">
                            Convert Stock
                        </button>
                    </div>
                </form>
            </div>

            <!-- Wastage Form -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-200 border-b pb-2 mb-4">
                    Log Stock Wastage
                </h3>
                <form @submit.prevent="logWastage" class="space-y-4">
                    @csrf
                    {{-- Department --}}
                    <div>
                        <label class="block text-sm font-medium">Department*</label>
                        <input list="departments-list"
                               x-model="departmentNameWastage"
                               @change="departmentChangedByName('wastage')"
                               placeholder="Type department..."
                               class="mt-1 block w-full dark:bg-gray-900 border rounded-md py-2 px-3">
                        <p x-show="departmentErrorWastage" class="text-red-600 text-xs mt-1" x-text="departmentErrorWastage"></p>
                    </div>
                    {{-- Product --}}
                    <div>
                        <label class="block text-sm font-medium">Product</label>
                        <input list="products-list"
                               x-model="selectedProductNameWastage"
                               @change="updateSelectedProduct('wastage')"
                               :disabled="!selectedDepartmentWastage"
                               class="mt-1 block w-full dark:bg-gray-900 rounded-md py-2 px-3 border"
                               placeholder="Select department first">
                        <input type="hidden" x-model="selectedProductWastage">
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Stock Type</label>
                        <select x-model="selectedStockTypeWastage" 
                                class="mt-1 block w-full dark:bg-gray-900 rounded-md py-2 px-3 border">
                            <option value="clear">Clear Stock</option>
                            <option value="non-clear">Non-Clear Stock</option>
                        </select>
                        <p x-show="selectedProductWastage" class="text-xs text-gray-500 mt-1" 
                           x-text="`Available: ${wastageStockAvailable}`" x-cloak></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Wastage Quantity</label>
                        <input type="number" x-model="quantityWastage" min="1" :max="wastageStockAvailable" 
                               class="mt-1 block w-full dark:bg-gray-900 rounded-md py-2 px-3 border" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Reason (Optional)</label>
                        <input type="text" x-model="reasonWastage" 
                               class="mt-1 block w-full dark:bg-gray-900 rounded-md py-2 px-3 border">
                    </div>
                    <div class="text-right">
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md text-sm font-semibold hover:bg-red-700">
                            Log Wastage
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('stockManagementForm', () => ({
        productsArray: @json($products->values()),

        // Alert
        alertMessage: '',
        alertType: '',

        // Departments
        departmentNameConvert: '',
        selectedDepartmentConvert: '',
        departmentErrorConvert: '',

        departmentNameWastage: '',
        selectedDepartmentWastage: '',
        departmentErrorWastage: '',

        // Conversion
        selectedProductConvert: '',
        selectedProductNameConvert: '',
        quantityConvert: 1,

        // Wastage
        selectedProductWastage: '',
        selectedProductNameWastage: '',
        selectedStockTypeWastage: 'clear',
        quantityWastage: 1,
        reasonWastage: '',

        departmentChangedByName(formType) {
            const input = formType === 'convert' ? this.departmentNameConvert : this.departmentNameWastage;
            const options = document.getElementById('departments-list').options;
            let deptId = '';
            for (let i = 0; i < options.length; i++) {
                if (options[i].value === input) {
                    deptId = options[i].dataset.id;
                    break;
                }
            }
            if (deptId) {
                if (formType === 'convert') {
                    this.selectedDepartmentConvert = deptId;
                    this.departmentErrorConvert = '';
                } else {
                    this.selectedDepartmentWastage = deptId;
                    this.departmentErrorWastage = '';
                }
            } else {
                if (formType === 'convert') {
                    this.selectedDepartmentConvert = '';
                    this.departmentErrorConvert = 'Department not found';
                    this.departmentNameConvert = '';
                } else {
                    this.selectedDepartmentWastage = '';
                    this.departmentErrorWastage = 'Department not found';
                    this.departmentNameWastage = '';
                }
            }
        },

        updateSelectedProduct(formType) {
            if (formType === 'convert') {
                const product = this.filteredProductsConvert.find(p => p.name === this.selectedProductNameConvert);
                this.selectedProductConvert = product ? product.id : '';
            } else {
                const product = this.filteredProductsWastage.find(p => p.name === this.selectedProductNameWastage);
                this.selectedProductWastage = product ? product.id : '';
            }
        },

        get filteredProductsConvert() {
            if (!this.selectedDepartmentConvert) return [];
            return this.productsArray.filter(p => p.department_id == this.selectedDepartmentConvert);
        },

        get filteredProductsWastage() {
            if (!this.selectedDepartmentWastage) return [];
            return this.productsArray.filter(p => p.department_id == this.selectedDepartmentWastage);
        },

        get filteredProducts() {
            return [...this.filteredProductsConvert, ...this.filteredProductsWastage];
        },

        get productStock() {
            if (!this.selectedProductConvert) return { non_clear: 0 };
            const product = this.productsArray.find(p => p.id == this.selectedProductConvert);
            return { non_clear: product ? product.non_clear_stock_quantity : 0 };
        },

        get wastageStockAvailable() {
            if (!this.selectedProductWastage) return 0;
            const product = this.productsArray.find(p => p.id == this.selectedProductWastage);
            if (!product) return 0;
            return this.selectedStockTypeWastage === 'clear' ? product.clear_stock_quantity : product.non_clear_stock_quantity;
        },

        async convertStock() {
            this.alertMessage = '';
            try {
                const response = await fetch("{{ route('stock-management.api.convert') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        product_id: this.selectedProductConvert,
                        quantity: this.quantityConvert
                    })
                });
                const data = await response.json();
                this.alertType = data.success ? 'success' : 'error';
                this.alertMessage = data.message;

                if (data.success) {
                    const idx = this.productsArray.findIndex(p => p.id == data.product.id);
                    if (idx !== -1) this.productsArray[idx] = data.product;
                }
            } catch (e) {
                this.alertType = 'error';
                this.alertMessage = 'Server error. Please try again.';
            }
        },

        async logWastage() {
            this.alertMessage = '';
            try {
                const response = await fetch("{{ route('stock-management.api.wastage') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        product_id: this.selectedProductWastage,
                        stock_type: this.selectedStockTypeWastage,
                        quantity: this.quantityWastage,
                        reason: this.reasonWastage
                    })
                });
                const data = await response.json();
                this.alertType = data.success ? 'success' : 'error';
                this.alertMessage = data.message;

                if (data.success) {
                    const idx = this.productsArray.findIndex(p => p.id == data.product.id);
                    if (idx !== -1) this.productsArray[idx] = data.product;
                }
            } catch (e) {
                this.alertType = 'error';
                this.alertMessage = 'Server error. Please try again.';
            }
        }
    }));
});
</script>
@endsection
