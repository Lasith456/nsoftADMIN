@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg max-w-4xl mx-auto p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Edit Purchase Order</h2>
            <a href="{{ route('purchase-orders.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase">
                Back to List
            </a>
        </div>

        @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p class="font-bold">Whoops!</p>
            <p>There were some problems with your input.</p>
            <ul class="list-disc pl-5 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('purchase-orders.update', $purchaseOrder->id) }}" method="POST">
            @csrf
            @method('PUT')
            <!-- PO Main Details -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6 pb-6 border-b dark:border-gray-700">
                <div>
                    <strong class="font-medium text-gray-900 dark:text-gray-200">PO ID:</strong>
                    <p class="text-gray-600 dark:text-gray-400">{{ $purchaseOrder->po_id }}</p>
                </div>
                <div>
                    <label for="customer_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer</label>
                    <select name="customer_id" id="customer_id" class="mt-1 block w-full dark:bg-gray-900 rounded-md text-gray-900 dark:text-gray-200" required>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ $purchaseOrder->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->customer_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="delivery_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Delivery Date</label>
                    <input type="date" name="delivery_date" id="delivery_date" class="mt-1 block w-full dark:bg-gray-900 rounded-md text-gray-900 dark:text-gray-200" value="{{ $purchaseOrder->delivery_date->format('Y-m-d') }}" required>
                </div>
                 <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                    <select name="status" id="status" class="mt-1 block w-full dark:bg-gray-900 rounded-md text-gray-900 dark:text-gray-200" required>
                        <option value="pending" {{ $purchaseOrder->status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ $purchaseOrder->status == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="delivered" {{ $purchaseOrder->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="cancelled" {{ $purchaseOrder->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
            </div>

            <!-- PO Items Table (Read-only) -->
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-200">{{ $item->product_name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $item->quantity }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Hidden inputs to re-submit existing items -->
            @foreach($purchaseOrder->items as $index => $item)
                <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                <input type="hidden" name="items[{{ $index }}][quantity]" value="{{ $item->quantity }}">
            @endforeach

            <div class="text-right pt-8">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border rounded-md font-semibold text-xs text-white uppercase hover:bg-gray-700">
                    Update Purchase Order
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

