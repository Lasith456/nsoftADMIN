@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div id="po-details" class="bg-white shadow-lg rounded-lg max-w-5xl mx-auto p-10 border border-gray-400">

        {{-- Header & Action Buttons --}}
        <div class="flex justify-between items-center mb-6 print:hidden">
            <h2 class="text-3xl font-bold text-gray-800">Purchase Order Details</h2>
            <div class="flex items-center space-x-2">
                <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600">
                    Back
                </a>
                <a href="{{ route('purchase-orders.index') }}" 
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

        {{-- PO Metadata --}}
        <div class="flex justify-between text-sm mb-6">
            <div>
                <p><strong>Purchase Order No:</strong> {{ $purchaseOrder->po_id }}</p>
                <p>
                    <strong>Customer:</strong>
                    @if($purchaseOrder->customer)
                        <a href="{{ route('customers.show', $purchaseOrder->customer->id) }}"
                           class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
                            {{ $purchaseOrder->customer->customer_name }}
                        </a>
                    @else
                        N/A
                    @endif
                </p>

                @if($purchaseOrder->customer && $purchaseOrder->customer->primary_address)
                    <p>{{ $purchaseOrder->customer->primary_address }}</p>
                @endif
            </div>
            <div class="text-right">
                <p><strong>Date of Issue:</strong> {{ optional($purchaseOrder->created_at)->format('d/m/Y') }}</p>
                <p><strong>Delivery Date:</strong> {{ $purchaseOrder->delivery_date ? $purchaseOrder->delivery_date->format('d/m/Y') : 'N/A' }}</p>
                <p><strong>Status:</strong> 
                    <span class="px-2 inline-flex text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                        {{ ucfirst($purchaseOrder->status) }}
                    </span>
                </p>
            </div>
        </div>

        {{-- Ordered Items Table --}}
        <h3 class="text-lg font-bold text-gray-800 mb-4">Ordered Items</h3>
        <table class="w-full border border-gray-700 text-sm mb-8">
            <thead class="bg-gray-200">
                <tr>
                    <th class="border px-2 py-1 text-left">Product Description</th>
                    <th class="border px-2 py-1 text-center">Unit</th>
                    <th class="border px-2 py-1 text-right">Quantity</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseOrder->items as $item)
                    <tr>
                        <td class="border px-2 py-1">
                            @if($item->product)
                                <a href="{{ route('products.show', $item->product->id) }}"
                                   class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
                                   {{ $item->product->name ?? $item->product_name }}
                                </a>
                            @else
                                {{ $item->product_name }}
                            @endif
                        </td>
                        <td class="border px-2 py-1 text-center">{{ $item->unit ?? $item->product->unit_of_measure ?? 'PCS' }}</td>
                        <td class="border px-2 py-1 text-right">{{ $item->quantity }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Remarks Section --}}
        @if(!empty($purchaseOrder->remarks))
        <div class="mt-6">
            <p class="font-semibold text-gray-800">Remarks:</p>
            <p class="text-gray-600">{{ $purchaseOrder->remarks }}</p>
        </div>
        @endif

        {{-- Certification --}}
        <div class="mt-8 text-sm">
            <p>
                Certified that the above items have been ordered in accordance with company requirements 
                and are essential for operational use.
            </p>
        </div>

        {{-- Signature Section --}}
        <div class="grid grid-cols-2 gap-10 mt-16 text-sm">
            <div>
                <p>Authorized by:</p>
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

            /* 🔥 Hides browser headers and footers */
            @top-left {
                content: none;
            }
            @top-center {
                content: none;
            }
            @top-right {
                content: none;
            }
            @bottom-left {
                content: none;
            }
            @bottom-center {
                content: none;
            }
            @bottom-right {
                content: none;
            }
        }

        /* Ensure only PO details are visible */
        body * { visibility: hidden !important; }
        #po-details, #po-details * { visibility: visible !important; }

        #po-details {
            position: absolute; left: 0; top: 0;
            width: 100%; margin: 0; padding: 0;
            border: none; box-shadow: none;
        }

        .print\:hidden { display: none !important; }
    }
</style>

@endsection
