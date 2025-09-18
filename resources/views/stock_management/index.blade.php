@extends('layouts.app')

@section('content')
<div class="container mx-auto" x-data="stockManagementForm()">
    {{-- This datalist is shared by both forms --}}
    <datalist id="products-list">
        @foreach($products as $product)
            <option value="{{ $product->name }}" data-id="{{ $product->id }}"></option>
        @endforeach
    </datalist>

    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-4">Stock Management</h2>

        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Stock Conversion Form -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-200 border-b pb-2 mb-4">Convert Non-Clear to Clear Stock</h3>
                @if ($errors->has('error_convert'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                        <p>{{ $errors->first('error_convert') }}</p>
                    </div>
                @endif
                <form action="{{ route('stock-management.api.convert') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="product_name_convert" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Product</label>
                        <input list="products-list" id="product_name_convert" x-model="selectedProductNameConvert" @change="updateSelectedProduct('convert')" class="mt-1 block w-full dark:bg-gray-900 rounded-md py-2 px-3 border border-gray-300 dark:border-gray-600" placeholder="Type to search for a product...">
                        <input type="hidden" name="product_id_convert" x-model="selectedProductConvert">
                        <p x-show="selectedProductConvert" class="text-xs text-gray-500 mt-1" x-text="`Available Non-Clear Stock: ${productStock.non_clear}`" x-cloak></p>
                    </div>
                    <div>
                        <label for="quantity_convert" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantity to Convert</label>
                        <input type="number" name="quantity_convert" min="1" :max="productStock.non_clear" class="mt-1 block w-full dark:bg-gray-900 rounded-md py-2 px-3 border border-gray-300 dark:border-gray-600" required>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold hover:bg-blue-700">Convert Stock</button>
                    </div>
                </form>
            </div>

            <!-- Wastage Form -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-200 border-b pb-2 mb-4">Log Stock Wastage</h3>
                 @if ($errors->has('error_wastage'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                        <p>{{ $errors->first('error_wastage') }}</p>
                    </div>
                @endif
                <form action="{{ route('stock-management.api.wastage') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="product_name_wastage" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Product</label>
                         <input list="products-list" id="product_name_wastage" x-model="selectedProductNameWastage" @change="updateSelectedProduct('wastage')" class="mt-1 block w-full dark:bg-gray-900 rounded-md py-2 px-3 border border-gray-300 dark:border-gray-600" placeholder="Type to search for a product...">
                        <input type="hidden" name="product_id_wastage" x-model="selectedProductWastage">
                    </div>
                    <div>
                        <label for="stock_type_wastage" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stock Type</label>
                        <select name="stock_type_wastage" x-model="selectedStockTypeWastage" class="mt-1 block w-full dark:bg-gray-900 rounded-md py-2 px-3 border border-gray-300 dark:border-gray-600">
                            <option value="clear">Clear Stock</option>
                            <option value="non-clear">Non-Clear Stock</option>
                        </select>
                        <p x-show="selectedProductWastage" class="text-xs text-gray-500 mt-1" x-text="`Available: ${wastageStockAvailable}`" x-cloak></p>
                    </div>
                    <div>
                        <label for="quantity_wastage" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Wastage Quantity</label>
                        <input type="number" name="quantity_wastage" min="1" :max="wastageStockAvailable" class="mt-1 block w-full dark:bg-gray-900 rounded-md py-2 px-3 border border-gray-300 dark:border-gray-600" required>
                    </div>
                    <div>
                        <label for="reason_wastage" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reason (Optional)</label>
                        <input type="text" name="reason_wastage" class="mt-1 block w-full dark:bg-gray-900 rounded-md py-2 px-3 border border-gray-300 dark:border-gray-600">
                    </div>
                    <div class="text-right">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md text-sm font-semibold hover:bg-red-700">Log Wastage</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('stockManagementForm', () => ({
        products: @json($products->keyBy('id')),
        productsArray: @json($products->values()), // For searching by name

        // Conversion Form properties
        selectedProductConvert: '{{ old('product_id_convert') }}',
        selectedProductNameConvert: '',

        // Wastage Form properties
        selectedProductWastage: '{{ old('product_id_wastage') }}',
        selectedProductNameWastage: '',
        selectedStockTypeWastage: '{{ old('stock_type_wastage', 'clear') }}',

        init() {
            // Pre-fill names if old data exists (from validation failure)
            if (this.selectedProductConvert) {
                const product = this.products[this.selectedProductConvert];
                if (product) this.selectedProductNameConvert = product.name;
            }
            if (this.selectedProductWastage) {
                const product = this.products[this.selectedProductWastage];
                if (product) this.selectedProductNameWastage = product.name;
            }
        },

        updateSelectedProduct(formType) {
            if (formType === 'convert') {
                const product = this.productsArray.find(p => p.name === this.selectedProductNameConvert);
                this.selectedProductConvert = product ? product.id : '';
            } else if (formType === 'wastage') {
                const product = this.productsArray.find(p => p.name === this.selectedProductNameWastage);
                this.selectedProductWastage = product ? product.id : '';
            }
        },
        
        get productStock() {
            if (!this.selectedProductConvert) return { non_clear: 0 };
            const product = this.products[this.selectedProductConvert];
            return {
                non_clear: product ? product.non_clear_stock_quantity : 0,
            };
        },

        get wastageStockAvailable() {
            if (!this.selectedProductWastage) return 0;
            const product = this.products[this.selectedProductWastage];
            if (!product) return 0;
            return this.selectedStockTypeWastage === 'clear' ? product.clear_stock_quantity : product.non_clear_stock_quantity;
        }
    }));
});
</script>
@endsection

