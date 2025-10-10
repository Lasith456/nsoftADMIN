@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div id="delivery-note-details" class="bg-white shadow-lg rounded-lg max-w-5xl mx-auto p-10 border border-gray-400">

        {{-- Header & Action Buttons --}}
        <div class="flex justify-between items-center mb-6 print:hidden">
            <h2 class="text-3xl font-bold text-gray-800">Delivery Note Details</h2>
            <div class="flex items-center space-x-2">
                <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600">
                    Back
                </a>
                <a href="{{ route('delivery-notes.index') }}"
                   class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-xs uppercase font-semibold">
                    Back to List
                </a>
                <button onclick="window.print()"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md text-xs uppercase font-semibold hover:bg-blue-700">
                    Print
                </button>
            </div>
        </div>

        {{-- Company Letterhead --}}
        <div class="text-center border-b border-gray-700 pb-4 mb-6">
            <h2 class="text-2xl font-extrabold uppercase">H.G.P.M. (PVT) Ltd.</h2>
            <p class="text-sm">No: 412/B, Galle Road, Pamburana, Matara.</p>
            <p class="text-sm">Tel: 041 2229231, 041 2224121 | Fax: 041 2224122 | Email: hgpm.ltd@sltnet.lk</p>
        </div>

        {{-- Delivery Note Meta Details --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 pb-4 border-b border-gray-300">
            <div>
                <p><strong>Delivery Note No:</strong> {{ $deliveryNote->delivery_note_id }}</p>
                <p><strong>Delivery Date:</strong> {{ $deliveryNote->delivery_date->format('d/m/Y') }}</p>
            </div>
            <div>
                <p>
                    <strong>Vehicle:</strong>
                    @if($deliveryNote->vehicle)
                        <a href="{{ route('vehicles.show', $deliveryNote->vehicle->id) }}"
                           class="text-blue-600 hover:underline font-medium">
                           {{ $deliveryNote->vehicle->vehicle_no }}
                        </a>
                    @else
                        N/A
                    @endif
                </p>
                <p><strong>Driver:</strong> {{ $deliveryNote->driver_name }} ({{ $deliveryNote->driver_mobile }})</p>

            </div>
            <div>
                <strong>Status:</strong>
                <span class="px-2 inline-flex text-xs font-semibold rounded-full 
                            @if($deliveryNote->status === 'delivered') bg-green-100 text-green-800 
                            @elseif($deliveryNote->status === 'cancelled') bg-red-100 text-red-800 
                            @else bg-yellow-100 text-yellow-800 @endif">
                    {{ ucfirst($deliveryNote->status ?? 'Pending') }}
                </span>

                {{-- ✅ Added Assistant Details --}}
                @if($deliveryNote->assistant_name || $deliveryNote->assistant_mobile)
                    <p><strong>Helper:</strong>
                        {{ $deliveryNote->assistant_name ?? 'N/A' }}
                        @if($deliveryNote->assistant_mobile)
                            ({{ $deliveryNote->assistant_mobile }})
                        @endif
                    </p>
                @endif
            </div>
        </div>

        {{-- Assigned Purchase Orders --}}
        <div class="mt-4 mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-2">Assigned Purchase Orders</h3>
            @if($deliveryNote->purchaseOrders->count() > 0)
                <ul class="list-disc list-inside text-sm text-gray-700">
                    @foreach($deliveryNote->purchaseOrders as $po)
                        <li>
                            <a href="{{ route('purchase-orders.show', $po->id) }}"
                               class="text-blue-600 hover:underline font-medium">
                                {{ $po->po_id }}
                            </a>
                            – 
                            @if($po->customer)
                                <a href="{{ route('customers.show', $po->customer->id) }}"
                                   class="text-blue-600 hover:underline font-medium">
                                   {{ $po->customer->customer_name }}
                                </a>
                            @else
                                <span class="text-gray-500">N/A</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-gray-500">No purchase orders assigned.</p>
            @endif
        </div>

        {{-- Items Table --}}
        <h3 class="text-lg font-bold text-gray-800 mb-4">Items for Delivery</h3>
        <table class="w-full border border-gray-700 text-sm mb-8">
            <thead class="bg-gray-200">
                <tr>
                    <th class="border px-2 py-1 text-left">Product Description</th>
                    <th class="border px-2 py-1 text-right">Total Qty</th>
                    <th class="border px-2 py-1 text-left">Source</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deliveryNote->items as $item)
                    <tr>
                        <td class="border px-2 py-1">
                            @if($item->product)
                                <a href="{{ route('products.show', $item->product->id) }}"
                                   class="text-blue-600 hover:underline font-medium">
                                   {{ $item->product_name }}
                                </a>
                            @else
                                {{ $item->product_name }}
                            @endif
                        </td>
                        <td class="border px-2 py-1 text-right">{{ $item->quantity_requested }}</td>
                        <td class="border px-2 py-1">
                            <p>From Stock: {{ $item->quantity_from_stock }}</p>
                            @if($item->quantity_from_agent > 0)
                                <p>
                                    From Agent 
                                    (<a href="{{ route('agents.show', $item->agent->id ?? '#') }}"
                                       class="text-blue-600 hover:underline">
                                       {{ $item->agent->name ?? 'N/A' }}
                                    </a>): 
                                    {{ $item->quantity_from_agent }}
                                </p>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Certification --}}
        <div class="mt-8 text-sm">
            <p>
                Certified that the above items have been verified and dispatched according to company 
                procedures and approved purchase orders.
            </p>
        </div>

        {{-- Signature Section --}}
        <div class="grid grid-cols-2 gap-10 mt-16 text-sm">
            <div>
                <p>Dispatched by:</p>
                <div class="mt-12 border-t border-gray-600 w-64"></div>
                <p class="mt-2">Name:</p>
                <p>Designation:</p>
            </div>
            <div class="text-right">
                <p>For and on behalf of</p>
                <p class="font-bold">H.G.P.M. (PVT) Ltd.</p>
                <div class="mt-12 border-t border-gray-600 w-64 ml-auto"></div>
                <p class="mt-2">Date: {{ now()->format('d/m/Y') }}</p>
            </div>
        </div>
    </div>
</div>

{{-- PRINT STYLING --}}
<style>
    @media print {
        @page {
            margin: 20mm;
            size: A4 portrait;
            @top-left, @top-center, @top-right,
            @bottom-left, @bottom-center, @bottom-right {
                content: none;
            }
        }
        body * { visibility: hidden !important; }
        #delivery-note-details, #delivery-note-details * { visibility: visible !important; }
        #delivery-note-details {
            position: absolute;
            left: 0; top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
            border: none;
            box-shadow: none;
        }
        .print\:hidden { display: none !important; }
    }
</style>
@endsection
