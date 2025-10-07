@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div id="grn-details" class="bg-white shadow-lg rounded-lg max-w-5xl mx-auto p-10 border border-gray-400">

        {{-- Header & Action Buttons --}}
        <div class="flex justify-between items-center mb-6 print:hidden">
            <h2 class="text-3xl font-bold text-gray-800">Goods Received Note (GRN)</h2>
            <div class="flex items-center space-x-2">
                <a href="{{ route('grns.index') }}" 
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
            <!-- <img src="{{ asset('images/logo.png') }}" alt="Company Logo" class="h-16 w-auto mx-auto mb-2"> -->
            <h2 class="text-2xl font-extrabold uppercase">H.G.P.M. (PVT) Ltd.</h2>
            <p class="text-sm">No: 412/B, Galle Road, Pamburana, Matara.</p>
            <p class="text-sm">Tel: 041 2229231, 041 2224121 | Fax: 041 2224122 | Email: hgpm.ltd@sltnet.lk</p>
        </div>

        {{-- GRN Metadata --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 pb-4 border-b border-gray-300">
            <div>
                <p><strong>GRN No:</strong> {{ $grn->grn_id }}</p>
                <p><strong>Date of Delivery:</strong> {{ $grn->delivery_date->format('d/m/Y') }}</p>
            </div>
            <div>
                <p><strong>Supplier:</strong></p>
                @if($grn->supplier)
                    <a href="{{ route('suppliers.show', $grn->supplier->id) }}" 
                       class="text-blue-600 hover:underline font-medium">
                       {{ $grn->supplier->supplier_name }}
                    </a>
                @else
                    <span class="text-gray-500">N/A</span>
                @endif
                <p class="text-gray-600">{{ $grn->supplier->address ?? '' }}</p>
            </div>
            <div>
                <p><strong>Invoice No:</strong> {{ $grn->invoice_number ?? 'N/A' }}</p>
                <p><strong>Status:</strong> 
                    <span class="px-2 inline-flex text-xs font-semibold rounded-full 
                        @if($grn->status == 'completed') bg-green-100 text-green-800 
                        @else bg-yellow-100 text-yellow-800 @endif">
                        {{ ucfirst($grn->status ?? 'Pending') }}
                    </span>
                </p>
            </div>
        </div>

        {{-- GRN Items Table --}}
        <h3 class="text-lg font-bold text-gray-800 mb-4">Items Received</h3>
        <table class="w-full border border-gray-700 text-sm mb-8">
            <thead class="bg-gray-200">
                <tr>
                    <th class="border px-2 py-1 text-left">Product Description</th>
                    <th class="border px-2 py-1 text-center">Qty Received</th>
                    <th class="border px-2 py-1 text-right">Unit Cost (Rs.)</th>
                    <th class="border px-2 py-1 text-right">Discount (Rs.)</th>
                    <th class="border px-2 py-1 text-right">Subtotal (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($grn->items as $item)
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
                        <td class="border px-2 py-1 text-center">{{ $item->quantity_received }} {{ $item->unit_type }}(s)</td>
                        <td class="border px-2 py-1 text-right">{{ number_format($item->cost_price, 2) }}</td>
                        <td class="border px-2 py-1 text-right">{{ number_format($item->discount, 2) }}</td>
                        <td class="border px-2 py-1 text-right">
                            {{ number_format(($item->cost_price * $item->quantity_received) - $item->discount, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- GRN Totals --}}
        <div class="flex justify-end mt-6">
            <div class="w-full max-w-sm space-y-1 text-sm">
                <div class="flex justify-between text-gray-600">
                    <span>Total Amount:</span>
                    <span>Rs. {{ number_format($grn->total_amount, 2) }}</span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>Total Discount:</span>
                    <span>- Rs. {{ number_format($grn->total_discount, 2) }}</span>
                </div>
                <div class="flex justify-between font-semibold text-gray-800 border-t pt-1">
                    <span>Net Amount:</span>
                    <span>Rs. {{ number_format($grn->net_amount, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Certification --}}
        <div class="mt-10 text-sm">
            <p>
                Certified that the above items have been received in good condition and match the supplier’s invoice.
                Any discrepancies have been documented and communicated to the procurement department.
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

            /* 🔥 Hide browser title & URL in header/footer */
            @top-left, @top-center, @top-right,
            @bottom-left, @bottom-center, @bottom-right {
                content: none;
            }
        }

        /* Only print GRN section */
        body * { visibility: hidden !important; }
        #grn-details, #grn-details * { visibility: visible !important; }

        #grn-details {
            position: absolute;
            left: 0;
            top: 0;
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
