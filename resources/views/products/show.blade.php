@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div id="product-details" class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6 border-b border-gray-200 dark:border-gray-700 pb-4 print:hidden">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ $product->name }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $product->product_id }}</p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600">
                    Back
                </a>
                  <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase hover:bg-gray-300 dark:hover:bg-gray-600">
                    Back to List
                </a>
                <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-xs uppercase font-semibold">
                    Print
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Column 1 -->
            <div class="space-y-4">
                <div>
                    <strong class="block text-sm font-medium text-gray-500 dark:text-gray-400">Appear Name</strong>
                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-200">{{ $product->appear_name }}</p>
                </div>
                <div>
                    <strong class="block text-sm font-medium text-gray-500 dark:text-gray-400">Department</strong>
                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                        @if ($product->department)
                            <a href="{{ route('departments.show', $product->department->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                {{ $product->department->name }}
                            </a>
                        @else
                            N/A
                        @endif
                    </p>
                </div>
                 <div>
                    <strong class="block text-sm font-medium text-gray-500 dark:text-gray-400">Sub-Department</strong>
                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                        @if ($product->subDepartment)
                            <a href="{{ route('subdepartments.show', $product->subDepartment->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                {{ $product->subDepartment->name }}
                            </a>
                        @else
                            N/A
                        @endif
                    </p>
                </div>
            </div>
            <!-- Column 2 -->
            <div class="space-y-4">
                <div>
                    <strong class="block text-sm font-medium text-gray-500 dark:text-gray-400">Cost Price</strong>
                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-200">{{ number_format($product->cost_price, 2) }}</p>
                </div>
                <div>
                    <strong class="block text-sm font-medium text-gray-500 dark:text-gray-400">Selling Price</strong>
                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-200">{{ number_format($product->selling_price, 2) }}</p>
                </div>
                 <div>
                    <strong class="block text-sm font-medium text-gray-500 dark:text-gray-400">Reorder Quantity</strong>
                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-200">{{ $product->reorder_qty }}</p>
                </div>
            </div>
            <!-- Column 3 -->
            <div class="space-y-4">
                <div>
                    <strong class="block text-sm font-medium text-gray-500 dark:text-gray-400">Units Per Case</strong>
                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-200">{{ $product->units_per_case }}</p>
                </div>
                <div>
                    <strong class="block text-sm font-medium text-gray-500 dark:text-gray-400">Unit of Measure</strong>
                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-200">{{ $product->unit_of_measure }}</p>
                </div>
            </div>
             <!-- Stock Details -->
            <div class="md:col-span-3 pt-4 border-t border-gray-200 dark:border-gray-700 mt-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-200 mb-2">Stock Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <strong class="block text-sm font-medium text-gray-500 dark:text-gray-400">Clear Stock</strong>
                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-200">{{ $product->clear_stock_quantity }}</p>
                    </div>
                    <div>
                        <strong class="block text-sm font-medium text-gray-500 dark:text-gray-400">Non-Clear Stock</strong>
                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-200">{{ $product->non_clear_stock_quantity }}</p>
                    </div>
                    <div>
                        <strong class="block text-sm font-medium text-gray-500 dark:text-gray-400">Total Stock</strong>
                        <p class="mt-1 text-sm font-bold text-gray-900 dark:text-gray-200">{{ $product->total_stock }}</p>
                    </div>
                </div>
            </div>
             <!-- Statuses -->
            <div class="md:col-span-3 flex space-x-6 pt-4 border-t border-gray-200 dark:border-gray-700 mt-6">
                 <div>
                    <strong class="block text-sm font-medium text-gray-500 dark:text-gray-400">Status</strong>
                    <p class="mt-1 text-sm">
                         @if($product->is_active)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                        @endif
                    </p>
                </div>
                <div>
                    <strong class="block text-sm font-medium text-gray-500 dark:text-gray-400">VAT Status</strong>
                    <p class="mt-1 text-sm">
                         @if($product->is_vat)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">VAT Applicable</span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">No VAT</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #product-details, #product-details * {
            visibility: visible;
        }
        #product-details {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 20px; /* Add some padding for the print version */
            border: none;
            box-shadow: none;
        }
        .print\:hidden {
            display: none !important;
        }
    }
</style>
@endsection

