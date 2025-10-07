@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div id="receive-note-details" class="bg-white shadow-lg rounded-lg max-w-5xl mx-auto p-10 border border-gray-400">

        {{-- Header & Action Buttons --}}
        <div class="flex justify-between items-center mb-6 print:hidden">
            <h2 class="text-3xl font-bold text-gray-800">Receive Note Details</h2>
            <div class="flex items-center space-x-2">
                <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600">
                    Back
                </a>
                <a href="{{ route('receive-notes.index') }}" 
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

        {{-- Receive Note Metadata --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 pb-4 border-b border-gray-300">
            <div>
                <p><strong>Receive Note No:</strong> {{ $receiveNote->receive_note_id }}</p>
                <p><strong>Received Date:</strong> {{ $receiveNote->received_date->format('d/m/Y') }}</p>
            </div>
            <div>
                <p><strong>Associated Delivery Notes:</strong></p>
                @forelse($receiveNote->deliveryNotes as $deliveryNote)
                    <a href="{{ route('delivery-notes.show', $deliveryNote->id) }}" 
                       class="text-blue-600 hover:underline block">
                       {{ $deliveryNote->delivery_note_id }}
                    </a>
                @empty
                    <p class="text-gray-500">N/A</p>
                @endforelse
            </div>
            <div>
                <p><strong>Customer:</strong></p>
                @php
                    $customer = $receiveNote->deliveryNotes->first()?->purchaseOrders->first()?->customer;
                @endphp
                @if($customer)
                    <a href="{{ route('customers.show', $customer->id) }}" 
                       class="text-blue-600 hover:underline font-medium">
                       {{ $customer->customer_name }}
                    </a>
                @else
                    <span class="text-gray-500">N/A</span>
                @endif
            </div>
            <div>
                <p><strong>Status:</strong></p>
                <span class="px-2 inline-flex text-xs font-semibold rounded-full 
                    @if($receiveNote->status == 'completed') bg-green-100 text-green-800 
                    @else bg-yellow-100 text-yellow-800 @endif">
                    {{ ucfirst($receiveNote->status) }}
                </span>
            </div>
        </div>

        {{-- Items Table --}}
        <h3 class="text-lg font-bold text-gray-800 mb-4">Items Received</h3>
        <table class="w-full border border-gray-700 text-sm mb-8">
            <thead class="bg-gray-200">
                <tr>
                    <th class="border px-2 py-1 text-left">Product Description</th>
                    <th class="border px-2 py-1 text-right">Expected Qty</th>
                    <th class="border px-2 py-1 text-right">Received Qty</th>
                    <th class="border px-2 py-1 text-left">Discrepancy Reason</th>
                </tr>
            </thead>
            <tbody>
                @foreach($receiveNote->items as $item)
                    <tr>
                        <td class="border px-2 py-1">
                            @if($item->product)
                                <a href="{{ route('products.show', $item->product->id) }}" 
                                   class="text-blue-600 hover:underline font-medium">
                                   {{ $item->product->name }}
                                </a>
                            @else
                                {{ $item->product_name ?? 'N/A' }}
                            @endif
                        </td>
                        <td class="border px-2 py-1 text-right">{{ $item->quantity_expected }}</td>
                        <td class="border px-2 py-1 text-right font-bold 
                            {{ $item->quantity_received < $item->quantity_expected ? 'text-red-600' : 'text-green-600' }}">
                            {{ $item->quantity_received }}
                        </td>
                        <td class="border px-2 py-1 text-gray-700">
                            {{ $item->discrepancy_reason ?? 'N/A' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Certification --}}
        <div class="mt-8 text-sm">
            <p>
                Certified that the above goods have been received in good condition and verified against the corresponding
                delivery notes. Any discrepancies have been noted and communicated to the relevant departments.
            </p>
        </div>

        {{-- Signature Section --}}
        <div class="grid grid-cols-2 gap-10 mt-16 text-sm">
            <div>
                <p>Received by:</p>
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

{{-- Print Styling --}}
<style>
    @media print {
        @page {
            margin: 20mm;
            size: A4 portrait;

            /* 🔥 Hide browser header/footer title & URL */
            @top-left, @top-center, @top-right,
            @bottom-left, @bottom-center, @bottom-right {
                content: none;
            }
        }

        /* Only print the receive note area */
        body * { visibility: hidden !important; }
        #receive-note-details, #receive-note-details * { visibility: visible !important; }

        #receive-note-details {
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
