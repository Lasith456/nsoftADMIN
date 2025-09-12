@extends('layouts.app')

@section('content')
<div class="container mx-auto" x-data="agentForm()">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4">
        <form id="agent-form" action="{{ route('agents.store') }}" method="POST">
            @csrf
            <div class="flex justify-between items-center mb-4 pb-3 border-b dark:border-gray-700">
                <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">Add New Agent</h2>
                <div class="flex items-center space-x-2">
                    <a class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 text-xs uppercase font-semibold" href="{{ route('agents.index') }}">
                        Back
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-300 text-xs uppercase font-semibold">
                        Save Agent
                    </button>
                </div>
            </div>

            @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                <p class="font-bold">Whoops! Something went wrong.</p>
                <ul class="list-disc pl-5 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="space-y-6">
                <!-- Agent Information -->
                <div>
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-200 border-b dark:border-gray-700 pb-2 mb-4">Agent Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Agent Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm" value="{{ old('name') }}" required>
                        </div>
                        <div>
                            <label for="contact_no" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contact No <span class="text-red-500">*</span></label>
                            <input type="text" name="contact_no" id="contact_no" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm" value="{{ old('contact_no') }}" required>
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                            <input type="email" name="email" id="email" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm" value="{{ old('email') }}">
                        </div>
                        <div class="md:col-span-3">
                            <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address <span class="text-red-500">*</span></label>
                            <textarea name="address" id="address" rows="2" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm" required>{{ old('address') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Product & Pricing -->
                <div>
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-200 border-b dark:border-gray-700 pb-2 mb-4">Product & Pricing</h3>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <div class="lg:col-span-1 space-y-2 bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                            <h4 class="font-semibold text-gray-800 dark:text-gray-200">Add Product</h4>
                            <div>
                                <label class="block text-sm font-medium">Product*</label>
                                <select x-model="currentItem.product_id" class="mt-1 block w-full dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-md py-2 px-3">
                                    <option value="">Select Product</option>
                                    @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium">Price Per Case*</label>
                                <input type="number" step="0.01" x-model.number="currentItem.price_per_case" class="mt-1 block w-full dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-md py-2 px-3">
                            </div>
                            <button type="button" @click="addProduct" class="w-full py-2 px-4 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">Add Product</button>
                        </div>
                        <div class="lg:col-span-2">
                             <div class="overflow-x-auto">
                                <table class="w-full min-w-full divide-y divide-gray-300 dark:divide-gray-700">
                                    <thead class="bg-gray-100 dark:bg-gray-700">
                                        <tr>
                                            <th class="py-2 pl-4 pr-3 text-left text-sm font-semibold">Product</th>
                                            <th class="px-3 py-2 text-right text-sm font-semibold">Price Per Case</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                                        <template x-for="(product, index) in assignedProducts" :key="index">
                                            <tr>
                                                <td class="py-2 pl-4 pr-3 text-sm font-medium" x-text="product.name"></td>
                                                <td class="px-3 py-2 text-sm text-right" x-text="product.price_per_case.toFixed(2)"></td>
                                                <td class="py-2 pl-3 pr-4 text-right text-sm font-medium">
                                                    <button type="button" @click="removeProduct(index)" class="text-red-600 hover:text-red-900">&times;</button>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr x-show="assignedProducts.length === 0">
                                            <td colspan="3" class="text-center py-4 text-sm text-gray-500">No products assigned.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Credit Info -->
                <div>
                     <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-200 border-b dark:border-gray-700 pb-2 mb-4">Credit Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="credit_limit" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Credit Limit</label>
                            <input type="number" step="0.01" name="credit_limit" id="credit_limit" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm" value="{{ old('credit_limit') }}">
                        </div>
                        <div>
                            <label for="credit_period" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Credit Period (Days)</label>
                            <input type="number" name="credit_period" id="credit_period" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm" value="{{ old('credit_period') }}">
                        </div>
                        <div class="flex items-end pb-1">
                            <input type="checkbox" name="is_active" id="is_active" class="h-4 w-4 text-indigo-600 border-gray-300 rounded" checked>
                            <label for="is_active" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">Is Active</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hidden Inputs for Submission -->
            <template x-for="(product, index) in assignedProducts" :key="index">
                <div>
                    <input type="hidden" :name="`products[${index}][product_id]`" :value="product.id">
                    <input type="hidden" :name="`products[${index}][price_per_case]`" :value="product.price_per_case">
                </div>
            </template>
        </form>
    </div>
</div>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('agentForm', () => ({
        products: @json($products),
        assignedProducts: [],
        currentItem: { product_id: '', price_per_case: 0 },
        
        addProduct() {
            if (!this.currentItem.product_id || this.currentItem.price_per_case <= 0) {
                alert('Please select a product and enter a valid price.');
                return;
            }
            const product = this.products.find(p => p.id == this.currentItem.product_id);
            if (!product || this.assignedProducts.some(p => p.id === product.id)) {
                alert('This product has already been added.');
                return;
            }
            
            this.assignedProducts.push({
                id: product.id,
                name: product.name,
                price_per_case: this.currentItem.price_per_case
            });
            this.currentItem = { product_id: '', price_per_case: 0 };
        },
        removeProduct(index) {
            this.assignedProducts.splice(index, 1);
        }
    }));
});
</script>
@endsection

