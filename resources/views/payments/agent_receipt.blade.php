@extends('layouts.app')

@section('content')
<div id="receipt" class="bg-white shadow-lg rounded-lg p-8 max-w-4xl mx-auto border border-gray-300">
    {{-- Header --}}
    <div class="flex justify-between items-center border-b pb-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">H.G.P.M. (PVT) Ltd.</h1>
            <p class="text-sm text-gray-600">No: 412/B, Galle Road, Pamburana, Matara.</p>
            <p class="text-sm text-gray-600">Tel: 041 2229231, 041 2224121 | Fax: 041 2224122 | Email: hgpm.ltd@sltnet.lk</p>
        </div>
        <div class="text-right">
            <h2 class="text-xl font-bold text-gray-700">PAYMENT RECEIPT</h2>
            <p class="text-gray-500">Receipt #: <span class="font-semibold">{{ $batchId }}</span></p>
            <p class="text-gray-500">Date: <span class="font-semibold">{{ $payments->first()->payment_date->format('Y-m-d') }}</span></p>
        </div>
    </div>

    {{-- Agent + Payment Details --}}
    <div class="grid grid-cols-2 gap-6 mb-6">
        <div>
            <h3 class="font-bold text-gray-700 mb-2">Agent Details</h3>
            <p><strong>Name:</strong> {{ $agent->name }}</p>
            <p><strong>Agent Code:</strong> {{ $agent->agent_id }}</p>
            <p><strong>Contact:</strong> {{ $agent->contact_no }}</p>
            <p><strong>Email:</strong> {{ $agent->email }}</p>
        </div>
        <div>
            <h3 class="font-bold text-gray-700 mb-2">Payment Info</h3>
            <p><strong>Method:</strong> {{ $payments->first()->payment_method }}</p>
            <p><strong>Reference:</strong> {{ $payments->first()->reference_number }}</p>
        </div>
    </div>

    @if($payments->first()->payment_method === 'Cheque')
        <div class="mb-6">
            <h3 class="font-bold text-gray-700 mb-2">Cheque Details</h3>
            <p><strong>Bank:</strong> {{ $payments->first()->bank?->name }}</p>
            <p><strong>Cheque Number:</strong> {{ $payments->first()->cheque_number }}</p>
            <p><strong>Cheque Date:</strong> {{ optional($payments->first()->cheque_date)->format('Y-m-d') }}</p>
            <p><strong>Received Date:</strong> {{ optional($payments->first()->cheque_received_date)->format('Y-m-d') }}</p>
        </div>
    @endif

    {{-- Invoice Breakdown --}}
    <h3 class="font-bold text-gray-700 mb-2">Invoices Paid</h3>
    <table class="w-full border border-gray-300 mb-6">
        <thead class="bg-gray-100">
            <tr>
                <th class="border p-2 text-left">Invoice ID</th>
                <th class="border p-2 text-right">Amount Paid</th>
                <th class="border p-2 text-center">Type</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
                <tr>
                    <td class="border p-2">{{ $payment->invoice->invoice_id }}</td>
                    <td class="border p-2 text-right">LKR {{ number_format($payment->amount, 2) }}</td>
                    <td class="border p-2 text-center">{{ $payment->invoice->is_vat_invoice ? 'VAT' : 'Non-VAT' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <div class="text-right mb-6">
        <p><strong>VAT Total:</strong> LKR {{ number_format($vatTotal, 2) }}</p>
        <p><strong>Non-VAT Total:</strong> LKR {{ number_format($nonVatTotal, 2) }}</p>
        <p><strong>Stamp Fee:</strong> LKR {{ number_format($stampFee, 2) }}</p>
        <p class="text-xl font-bold">Grand Total: LKR {{ number_format($vatTotal + $nonVatTotal + $stampFee, 2) }}</p>
    </div>

    {{-- Footer + Print --}}
    <div class="flex justify-between items-center border-t pt-4">
        <p class="text-sm text-gray-500">Thank you for your payment.</p>
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 print:hidden">
            Print Receipt
        </button>
    </div>
</div>

{{-- Print-only style --}}
<style>
@media print {
    body * { visibility: hidden; }
    #receipt, #receipt * { visibility: visible; }
    #receipt { position: absolute; left: 0; top: 0; width: 100%; }
    .print\:hidden { display: none !important; }
}
</style>
@endsection
