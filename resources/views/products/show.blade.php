@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div id="product-details" class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 max-w-4xl mx-auto">
        <!-- Header Section -->
        <div class="flex justify-between items-center mb-6 border-b border-gray-200 dark:border-gray-700 pb-4 print:hidden">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ $product->name }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $product->product_id }}</p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ url()->previous() }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600">
                    Back
                </a>
                <a href="{{ route('products.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase hover:bg-gray-300 dark:hover:bg-gray-600">
                    Back to List
                </a>
                <button onclick="window.print()" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-xs uppercase font-semibold">
                    Print
                </button>
            </div>
        </div>

        <!-- Product Details -->
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
            </div>

            <!-- Column 2 -->
            <div class="space-y-4">
                <div>
                    <strong class="block text-sm font-medium text-gray-500 dark:text-gray-400">Default Selling Price</strong>
                    <p class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                        @if(!is_null($product->selling_price))
                            {{ number_format($product->selling_price, 2) }}
                        @else
                            —
                        @endif
                    </p>
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

            <!-- =================== COMPANY-WISE PRICES =================== -->
            <div class="md:col-span-3 pt-4 border-t border-gray-200 dark:border-gray-700 mt-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-200 mb-3">Company Wise Prices</h3>

                @php
                    $companyDepartments = $product->relationLoaded('productDepartmentWise')
                        ? $product->productDepartmentWise
                        : $product->productDepartmentWise()->with(['company', 'department'])->get();
                @endphp
                @php
                    $companyPrices = $product->relationLoaded('companyPrices')
                        ? $product->companyPrices
                        : $product->companyPrices()->with('company')->get();
                @endphp

                @if($companyPrices->isEmpty())
                    <p class="text-sm text-gray-600 dark:text-gray-400">No company-specific prices configured.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-gray-200 dark:border-gray-700 rounded-md">
                            <thead>
                                <tr class="bg-gray-100 dark:bg-gray-700/40">
                                    <th class="p-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-200">Company</th>
                                    <th class="p-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-200">Department</th>
                                    <th class="p-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-200">Selling Price</th>
                                    <th class="p-2 text-left text-xs font-semibold text-gray-700 dark:text-gray-200">Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($companyPrices as $row)
                                    @php
                                        $dept = $companyDepartments->firstWhere('company_id', $row->company_id);
                                    @endphp
                                    <tr class="border-t border-gray-200 dark:border-gray-700">
                                        <td class="p-2 text-sm text-gray-900 dark:text-gray-200">
                                            {{ optional($row->company)->company_name ?? optional($row->company)->name ?? ('Company #'.$row->company_id) }}
                                        </td>
                                        <td class="p-2 text-sm text-gray-900 dark:text-gray-200">
                                            {{ optional($dept?->department)->name ?? 'N/A' }}
                                        </td>
                                        <td class="p-2 text-sm text-gray-900 dark:text-gray-200">
                                            {{ number_format((float) $row->selling_price, 2) }}
                                        </td>
                                        <td class="p-2 text-sm text-gray-600 dark:text-gray-400">
                                            {{ optional($row->updated_at ?? $row->created_at)->format('Y-m-d') }}
                                        </td>
                                    </tr>

                                    <!-- Customer-wise Overrides (only changed ones) -->
                                    @php
                                        $customerOverrides = $product->customerPrices()
                                            ->whereHas('customer', fn($q) => $q->where('company_id', $row->company_id))
                                            ->where('selling_price', '!=', $row->selling_price)
                                            ->with('customer')
                                            ->get();
                                    @endphp

                                    @if($customerOverrides->isNotEmpty())
                                        <tr>
                                            <td colspan="3" class="bg-gray-50 dark:bg-gray-700/30 p-3">
                                                <div class="ml-4">
                                                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                                        Customer-Specific Prices (Changed)
                                                    </h4>
                                                    <table class="w-full text-sm border border-gray-200 dark:border-gray-600 rounded-md">
                                                        <thead class="bg-gray-100 dark:bg-gray-700/40">
                                                            <tr>
                                                                <th class="p-2 text-left font-medium text-gray-700 dark:text-gray-300">Customer</th>
                                                                <th class="p-2 text-left font-medium text-gray-700 dark:text-gray-300">Selling Price</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($customerOverrides as $cust)
                                                                <tr class="border-t border-gray-200 dark:border-gray-600">
                                                                    <td class="p-2 text-gray-900 dark:text-gray-200">
                                                                        {{ $cust->customer->customer_name ?? 'Customer #'.$cust->customer_id }}
                                                                    </td>
                                                                    <td class="p-2 text-gray-900 dark:text-gray-200">
                                                                        {{ number_format((float) $cust->selling_price, 2) }}
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- =================== STOCK DETAILS =================== -->
            <div class="md:col-span-3 pt-4 border-t border-gray-200 dark:border-gray-700 mt-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-200 mb-2">Stock Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <strong class="block text-sm font-medium text-gray-500 dark:text-gray-400">Clear Stock</strong>
                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-200">{{ $product->clear_stock_quantity ?? 0 }}</p>
                    </div>
                    <div>
                        <strong class="block text-sm font-medium text-gray-500 dark:text-gray-400">Non-Clear Stock</strong>
                        <p class="mt-1 text-sm text-gray-900 dark:text-gray-200">{{ $product->non_clear_stock_quantity ?? 0 }}</p>
                    </div>
                    <div>
                        <strong class="block text-sm font-medium text-gray-500 dark:text-gray-400">Total Stock</strong>
                        <p class="mt-1 text-sm font-bold text-gray-900 dark:text-gray-200">{{ $product->total_stock ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- =================== STATUS SECTION =================== -->
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
                <div>
                    <strong class="block text-sm font-medium text-gray-500 dark:text-gray-400">Clear Product</strong>
                    <p class="mt-1 text-sm">
                        @if($product->is_clear)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Clear Stock Product</span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Standard Product</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Print Styling -->
<style>
@media print {
    body * { visibility: hidden; }
    #product-details, #product-details * { visibility: visible; }
    #product-details {
        position: absolute;
        left: 0; top: 0; width: 100%;
        margin: 0; padding: 20px;
        border: none; box-shadow: none;
    }
    .print\:hidden { display: none !important; }
}
</style>
@endsection
