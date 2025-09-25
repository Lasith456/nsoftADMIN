@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div id="delivery-note-details" class="bg-white dark:bg-gray-800 shadow-md rounded-lg max-w-4xl mx-auto p-4">
        <div class="flex justify-between items-center mb-4 pb-3 border-b dark:border-gray-700 print:hidden">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Delivery Note Details</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $deliveryNote->delivery_note_id }}</p>
            </div>
            <div class="flex items-center space-x-2">
                <a class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 text-xs uppercase font-semibold" href="{{ url()->previous() }}">
                    Back
                </a>
                <a class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 text-xs uppercase font-semibold" href="{{ route('delivery-notes.index') }}">
                    Back to List
                </a>
                <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-xs uppercase font-semibold">
                    Print
                </button>
            </div>
        </div>

        <!-- Main Details -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4 pb-4 border-b dark:border-gray-700">
            <div>
                <strong class="font-medium text-gray-900 dark:text-gray-200 text-sm">DN ID:</strong>
                <p class="text-gray-600 dark:text-gray-400 text-sm">{{ $deliveryNote->delivery_note_id }}</p>
            </div>
            <div>
                <strong class="font-medium text-gray-900 dark:text-gray-200 text-sm">Vehicle:</strong>
                <p class="text-gray-600 dark:text-gray-400 text-sm">
                    @if($deliveryNote->vehicle)
                        <a href="{{ route('vehicles.show', $deliveryNote->vehicle->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                            {{ $deliveryNote->vehicle->vehicle_no }}
                        </a>
                    @else
                        N/A
                    @endif
                </p>
                <p><strong>Driver:</strong> {{ $deliveryNote->driver_name }} ({{ $deliveryNote->driver_mobile }})</p>
            </div>
            <div>
                <strong class="font-medium text-gray-900 dark:text-gray-200 text-sm">Delivery Date:</strong>
                <p class="text-gray-600 dark:text-gray-400 text-sm">{{ $deliveryNote->delivery_date->format('F j, Y') }}</p>
            </div>
        </div>

        <!-- Assigned Purchase Orders -->
        <div class="mt-4 mb-6">
            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-2">Assigned Purchase Orders</h3>
            @if($deliveryNote->purchaseOrders->count() > 0)
                <ul class="list-disc list-inside text-sm text-gray-600 dark:text-gray-400">
                    @foreach($deliveryNote->purchaseOrders as $po)
                        <li>
                            <a href="{{ route('purchase-orders.show', $po->id) }}" 
                               class="text-blue-600 dark:text-blue-400 hover:underline">
                                {{ $po->po_id }}
                            </a>
                            – <span class="text-gray-500 dark:text-gray-400">{{ $po->customer->customer_name ?? 'N/A' }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">No purchase orders assigned.</p>
            @endif
        </div>

        <!-- Items Table -->
        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-2">Items for Delivery</h3>
        <div class="overflow-x-auto">
            <table class="w-full min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Product</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Total Qty</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Source</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($deliveryNote->items as $item)
                    <tr>
                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-200">
                            @if($item->product)
                                <a href="{{ route('products.show', $item->product->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                    {{ $item->product_name }}
                                </a>
                            @else
                                {{ $item->product_name }}
                            @endif
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $item->quantity_requested }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <p>From Stock: {{ $item->quantity_from_stock }}</p>
                            @if($item->quantity_from_agent > 0)
                                <p>From Agent ({{ $item->agent->name ?? 'N/A' }}): {{ $item->quantity_from_agent }}</p>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #delivery-note-details, #delivery-note-details * {
            visibility: visible;
        }
        #delivery-note-details {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 20px;
            border: none;
            box-shadow: none;
        }
        .print\:hidden {
            display: none !important;
        }
    }
</style>
@endsection
