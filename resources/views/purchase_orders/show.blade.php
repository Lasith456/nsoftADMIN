@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg max-w-4xl mx-auto p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Purchase Order Details</h2>
            <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase">
                Back 
            </a>
            <a href="{{ route('purchase-orders.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase hover:bg-gray-300 dark:hover:bg-gray-600">
                Back to List
            </a>
        </div>

        <!-- PO Main Details -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6 pb-6 border-b dark:border-gray-700">
            <div>
                <strong class="font-medium text-gray-900 dark:text-gray-200">PO ID:</strong>
                <p class="text-gray-600 dark:text-gray-400">{{ $purchaseOrder->po_id }}</p>
            </div>
            <div>
                <strong class="font-medium text-gray-900 dark:text-gray-200">Customer:</strong>
                <p class="text-gray-600 dark:text-gray-400">
                    @if($purchaseOrder->customer)
                        <a href="{{ route('customers.show', $purchaseOrder->customer->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                            {{ $purchaseOrder->customer->customer_name }}
                        </a>
                    @else
                        N/A
                    @endif
                </p>
            </div>
            <div>
                <strong class="font-medium text-gray-900 dark:text-gray-200">Delivery Date:</strong>
                <p class="text-gray-600 dark:text-gray-400">{{ $purchaseOrder->delivery_date->format('F j, Y') }}</p>
            </div>
             <div>
                <strong class="font-medium text-gray-900 dark:text-gray-200">Status:</strong>
                <p class="text-gray-600 dark:text-gray-400"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">{{ ucfirst($purchaseOrder->status) }}</span></p>
            </div>
        </div>

        <!-- PO Items Table -->
        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">Items on Order</h3>
        <div class="overflow-x-auto">
            <table class="w-full min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Quantity</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($purchaseOrder->items as $item)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-200">
                            @if($item->product)
                                <a href="{{ route('products.show', $item->product->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                    {{ $item->product_name }}
                                </a>
                            @else
                                {{ $item->product_name }}
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $item->quantity }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

