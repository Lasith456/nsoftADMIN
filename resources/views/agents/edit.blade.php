@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4">
        <div class="flex justify-between items-center mb-4 pb-3 border-b dark:border-gray-700">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">Edit Agent</h2>
            <div class="flex items-center space-x-2">
                <a class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 text-xs uppercase font-semibold" href="{{ route('agents.index') }}">
                    Back
                </a>
                <button type="submit" form="agent-form" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-300 text-xs uppercase font-semibold">
                    Update Agent
                </button>
            </div>
        </div>

        @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-4" role="alert">
            <strong>Whoops!</strong> There were some problems with your input.<br><br>
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form id="agent-form" action="{{ route('agents.update', $agent->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-6">
                <!-- Agent Information -->
                <div>
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-200 border-b dark:border-gray-700 pb-2 mb-4">Agent Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Agent Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm" value="{{ old('name', $agent->name) }}" required>
                        </div>
                        <div>
                            <label for="contact_no" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contact No <span class="text-red-500">*</span></label>
                            <input type="text" name="contact_no" id="contact_no" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm" value="{{ old('contact_no', $agent->contact_no) }}" required>
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                            <input type="email" name="email" id="email" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm" value="{{ old('email', $agent->email) }}">
                        </div>
                        <div class="md:col-span-3">
                            <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address <span class="text-red-500">*</span></label>
                            <textarea name="address" id="address" rows="2" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm" required>{{ old('address', $agent->address) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Product & Pricing -->
                <div>
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-200 border-b dark:border-gray-700 pb-2 mb-4">Product & Pricing</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="product_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assign Product <span class="text-red-500">*</span></label>
                            <input list="products-list" id="product_name" class="mt-1 block w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-gray-900 dark:text-gray-200" value="{{ old('product_name', $agent->product->name ?? '') }}" required>
                            <datalist id="products-list">
                                @foreach($products as $product)
                                    <option value="{{ $product->name }}" data-id="{{ $product->id }}"></option>
                                @endforeach
                            </datalist>
                            <input type="hidden" name="product_id" id="product_id" value="{{ old('product_id', $agent->product_id) }}">
                        </div>
                        <div>
                            <label for="price_per_case" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Price Per Case <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="price_per_case" id="price_per_case" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm" value="{{ old('price_per_case', $agent->price_per_case) }}" required>
                        </div>
                    </div>
                </div>

                <!-- Credit Info -->
                <div>
                     <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-200 border-b dark:border-gray-700 pb-2 mb-4">Credit Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="credit_limit" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Credit Limit</label>
                            <input type="number" step="0.01" name="credit_limit" id="credit_limit" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm" value="{{ old('credit_limit', $agent->credit_limit) }}">
                        </div>
                        <div>
                            <label for="credit_period" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Credit Period (Days)</label>
                            <input type="number" name="credit_period" id="credit_period" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm" value="{{ old('credit_period', $agent->credit_period) }}">
                        </div>
                        <div class="flex items-end pb-1">
                            <input type="checkbox" name="is_active" id="is_active" class="h-4 w-4 text-indigo-600 border-gray-300 rounded" {{ old('is_active', $agent->is_active) ? 'checked' : '' }}>
                            <label for="is_active" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">Is Active</label>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const productInput = document.getElementById('product_name');
        const productIdInput = document.getElementById('product_id');

        productInput.addEventListener('change', function() {
            const productName = this.value;
            let productId = '';
            const options = document.getElementById('products-list').options;
            for (let i = 0; i < options.length; i++) {
                if (options[i].value === productName) {
                    productId = options[i].dataset.id;
                    break;
                }
            }
            productIdInput.value = productId;
        });
    });
</script>
@endsection

