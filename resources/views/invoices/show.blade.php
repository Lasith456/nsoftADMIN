@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div id="invoice-details" class="bg-white shadow-lg rounded-lg max-w-5xl mx-auto p-10 border border-gray-400">

        {{-- Header & Buttons --}}
        <div class="flex justify-between items-center mb-6 print:hidden">
            <h2 class="text-3xl font-bold text-gray-800">Invoice Details</h2>
            <div class="flex items-center space-x-2">
                <a href="{{ route('invoices.index') }}" 
                   class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-xs uppercase font-semibold">Back to List</a>
                @can('payment-create')
                    @if($invoice->total_amount - $invoice->amount_paid > 0)
                        <a href="{{ route('payments.create', $invoice->id) }}" 
                           class="px-4 py-2 bg-green-600 text-white rounded-md text-xs uppercase font-semibold">Record Payment</a>
                    @endif
                @endcan
                <a href="{{ route('invoices.print', $invoice->id) }}" 
                   class="px-4 py-2 bg-blue-600 text-white rounded-md text-xs uppercase font-semibold">Print</a>
            </div>
        </div>

        {{-- Company Header --}}
        <div class="text-center border-b border-gray-700 pb-4 mb-4">
            <h2 class="text-2xl font-extrabold uppercase">H.G.P.M. (PVT) Ltd.</h2>
            <p class="text-sm">No: 412/B, Galle Road, Pamburana, Matara.</p>
            <p class="text-sm">Tel: 041 2229231, 041 2224121 | Fax: 041 2224122 | Email: hgpm.ltd@sltnet.lk</p>
        </div>

        {{-- Invoice Meta --}}
        <div class="flex justify-between text-sm mb-4">
            <div>
                <p><strong>Unit Bill No:</strong> {{ $invoice->invoice_id }}</p>
                @if($invoice->invoiceable_type === 'App\Models\Customer')
                    <p><strong>Unit:</strong> {{ $invoice->invoiceable->customer_name }}</p>
                    <p>{{ $invoice->invoiceable->primary_address ?? '' }}</p>
                @elseif($invoice->invoiceable_type === 'App\Models\Supplier')
                    <p><strong>Supplier:</strong> {{ $invoice->invoiceable->supplier_name }}</p>
                    <p>{{ $invoice->invoiceable->primary_address ?? '' }}</p>
                @elseif($invoice->invoiceable_type === 'App\Models\Agent')
                    <p><strong>Agent:</strong> {{ $invoice->invoiceable->name }}</p>
                    <p>{{ $invoice->invoiceable->address ?? '' }}</p>
                @endif
            </div>
            <div class="text-right">
                <p><strong>VAT Registration No:</strong> 11416467-7000</p>
                <p><strong>Date of Issue:</strong> {{ optional($invoice->created_at)->format('d/m/Y') }}</p>
                <p><strong>Due Date:</strong> {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') : 'N/A' }}</p>
            </div>
        </div>

        {{-- Items Table --}}
        <table class="w-full border border-gray-700 text-sm">
            <thead class="bg-gray-200">
                <tr>
                    <th class="border px-2 py-1 text-left">Commodity & Quantity</th>
                    <th class="border px-2 py-1 text-right">Rate</th>
                    <th class="border px-2 py-1 text-center">Unit</th>
                    <th class="border px-2 py-1 text-right">Amount (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                @php $currentDept = null; @endphp
                @foreach($invoice->items as $item)
                    @if($item->product && $item->product->department && $currentDept !== $item->product->department->name)
                        {{-- Department Header Row --}}
                        <tr class="bg-gray-100 font-bold uppercase">
                            <td colspan="4" class="border px-2 py-1">
                                SUPPLY OF {{ str_replace('Department: ', '', $invoice->notes) }}
                            </td>
                        </tr>
                        @php $currentDept = $item->product->department->name; @endphp
                    @endif
                    <tr>
                        <td class="border px-2 py-1">{{ $item->description ?? $item->product->name }}</td>
                        <td class="border px-2 py-1 text-right">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="border px-2 py-1 text-center">{{ $item->product->unit ?? 'PCS' }}</td>
                        <td class="border px-2 py-1 text-right">{{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totals --}}
        <div class="flex justify-end mt-6">
            <div class="text-right space-y-1">
                <p><strong>Grand Total:</strong> Rs. {{ number_format($invoice->total_amount, 2) }}</p>
                <p class="italic text-sm">
                    Amount in Words: {{ \App\Helpers\NumberToWords::convert($invoice->total_amount) }}
                </p>
            </div>
        </div>

        {{-- Certification --}}
        <div class="mt-10 text-sm">
            <p>Certificate that no bill has been tendered previously in respect of the articles now charged in.</p>
        </div>

        {{-- Signature Block --}}
        <div class="grid grid-cols-2 gap-10 mt-16 text-sm">
            <div>
                <p>Signature of Contractor:</p>
                <div class="mt-12 border-t border-gray-600 w-64"></div>
                <p class="mt-2">Name of Contractor:</p>
                <p>Address:</p>
            </div>
            <div class="text-right">
                <p>Director</p>
                <p class="font-bold">H.G.P.M. (PVT) Ltd.</p>
                <div class="mt-12 border-t border-gray-600 w-64 ml-auto"></div>
                <p class="mt-2">Date: {{ now()->format('d/m/Y') }}</p>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        body * { visibility: hidden; }
        #invoice-details, #invoice-details * { visibility: visible; }
        #invoice-details {
            position: absolute; left: 0; top: 0;
            width: 100%; margin: 0; padding: 0;
            border: none; box-shadow: none;
        }
        .print\:hidden { display: none !important; }
    }
</style>
@endsection
