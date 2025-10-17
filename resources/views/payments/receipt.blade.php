@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div id="receipt" class="bg-white shadow-lg rounded-lg max-w-5xl mx-auto p-8 border border-gray-400 text-[13px]">

        {{-- Header & Print Button --}}
        <div class="flex justify-between items-center mb-4 print:hidden">
            <h2 class="text-2xl font-bold text-gray-800">Payment Receipt</h2>
            <button onclick="window.print()" 
                    class="px-3 py-1.5 bg-blue-600 text-white rounded-md text-xs uppercase font-semibold hover:bg-blue-700">
                Print
            </button>
        </div>

        {{-- Company Letterhead --}}
        <div class="text-center border-b border-gray-700 pb-3 mb-4">
            <h2 class="text-xl font-extrabold uppercase tracking-wide">H.G.P.M. (PVT) Ltd.</h2>
            <p class="text-xs">No: 412/B, Galle Road, Pamburana, Matara.</p>
            <p class="text-xs">Tel: 041 2229231, 041 2224121 | Fax: 041 2224122 | Email: hgpm.ltd@sltnet.lk</p>
        </div>

        {{-- Receipt Info --}}
        <div class="flex justify-between items-start mb-4 border-b border-gray-300 pb-2">
            <div>
                <h3 class="font-semibold text-gray-800">Receipt #: {{ $batchId }}</h3>
                <p class="text-xs text-gray-600">Date: {{ $payments->first()->payment_date->format('d/m/Y') }}</p>
            </div>
            <div class="text-right">
                <h3 class="text-lg font-semibold text-gray-700">PAYMENT RECEIPT</h3>
            </div>
        </div>

        {{-- Compact Details Row --}}
        <div class="grid grid-cols-3 gap-4 mb-4">
            {{-- Customer Details --}}
            <div>
                <h3 class="font-bold text-gray-700 mb-1 text-sm border-b border-gray-200 pb-1">Customer Details</h3>
                <p><strong>Name:</strong> 
                    <a href="{{ route('customers.show', $customer->id) }}" 
                       class="text-blue-600 hover:underline">
                       {{ $customer->customer_name }}
                    </a>
                </p>
            </div>

            {{-- Payment Information --}}
            <div>
                <h3 class="font-bold text-gray-700 mb-1 text-sm border-b border-gray-200 pb-1">Payment Information</h3>
                <p><strong>Method:</strong> {{ $payments->first()->payment_method }}</p>
                <p><strong>Reference:</strong> {{ $payments->first()->reference_number ?? 'N/A' }}</p>
            </div>

            {{-- Cheque Details --}}
            @if($payments->first()->payment_method === 'Cheque')
            <div>
                <h3 class="font-bold text-gray-700 mb-1 text-sm border-b border-gray-200 pb-1">Cheque Details</h3>
                <p><strong>Bank:</strong> {{ $payments->first()->bank?->name ?? 'N/A' }}</p>
                <p><strong>Cheque No:</strong> {{ $payments->first()->cheque_number ?? 'N/A' }}</p>
                <p><strong>Cheque Date:</strong> {{ optional($payments->first()->cheque_date)->format('d/m/Y') ?? 'N/A' }}</p>
            </div>
            @endif
        </div>

        {{-- Invoice Breakdown --}}
        <h3 class="text-sm font-bold text-gray-800 mb-2">Invoices Paid</h3>
        <table class="w-full border border-gray-700 text-xs mb-4">
            <thead class="bg-gray-200">
                <tr>
                    <th class="border px-2 py-1 text-left">Invoice ID</th>
                    <th class="border px-2 py-1 text-right">Amount Paid (LKR)</th>
                    <th class="border px-2 py-1 text-center">Type</th>
                    <th class="border px-2 py-1 text-center">Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $index => $payment)
                    @php
                        $isLast = ($index === $payments->count() - 1);
                    @endphp
                    <tr @if($isLast) class="bg-blue-50" @endif>
                        <td class="border px-2 py-1">
                            <a href="{{ route('invoices.show', $payment->invoice->id) }}" 
                               class="text-blue-600 hover:underline">
                               {{ $payment->invoice->invoice_id }}
                            </a>
                        </td>
                        <td class="border px-2 py-1 text-right">
                            {{ number_format($payment->amount, 2) }}
                        </td>
                        <td class="border px-2 py-1 text-center">
                            {{ $payment->invoice->is_vat_invoice ? 'VAT' : 'Non-VAT' }}
                        </td>
                        <td class="border px-2 py-1 text-center text-gray-600">
                            @if($isLast && ($payment->stamp_fee > 0 || $payment->surcharge_fee > 0 || $payment->used_debit > 0))
                                <span class="italic text-[11px] text-blue-700">
                                    Includes Stamp ({{ number_format($payment->stamp_fee, 2) }}), 
                                    Surcharge ({{ number_format($payment->surcharge_fee, 2) }}),
                                    @if($payment->used_debit > 0)
                                        Debit ({{ number_format($payment->used_debit, 2) }})
                                    @endif
                                </span>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totals Section --}}
        @php
            $invoiceTotal    = $vatTotal + $nonVatTotal;
            $cashPaid        = $payments->sum('amount');
            $stampFee        = $payments->last()->stamp_fee ?? 0;
            $surchargeFee    = $payments->last()->surcharge_fee ?? 0;
            $usedDebit       = $payments->last()->used_debit ?? 0;
            $totalSettlement = $cashPaid + $stampFee + $surchargeFee + $usedDebit;
        @endphp

        <div class="flex justify-end mb-6">
            <div class="w-full max-w-xs text-xs space-y-1">
                <div class="flex justify-between text-gray-600">
                    <span>Invoice Total:</span>
                    <span>LKR {{ number_format($invoiceTotal, 2) }}</span>
                </div>

                <div class="flex justify-between text-gray-600">
                    <span>Amount Paid (Cash/Bank):</span>
                    <span>LKR {{ number_format($cashPaid, 2) }}</span>
                </div>

                <div class="flex justify-between text-gray-600">
                    <span>Stamp Fee:</span>
                    <span>LKR {{ number_format($stampFee, 2) }}</span>
                </div>

                <div class="flex justify-between text-gray-600">
                    <span>Surcharge Fee:</span>
                    <span>LKR {{ number_format($surchargeFee, 2) }}</span>
                </div>

                @if($usedDebit > 0)
                <div class="flex justify-between text-gray-600">
                    <span>Debit Note Applied:</span>
                    <span>LKR {{ number_format($usedDebit, 2) }}</span>
                </div>
                @endif

                <div class="flex justify-between font-semibold text-gray-800 border-t pt-1">
                    <span>Total Settlement (All Included):</span>
                    <span>LKR {{ number_format($totalSettlement, 2) }}</span>
                </div>

                @if($totalSettlement + 0.01 >= $invoiceTotal)
                    <div class="flex justify-between text-green-700 font-semibold text-xs mt-1">
                        <span>Invoice Status:</span>
                        <span>Fully Settled ✅</span>
                    </div>
                    <p class="text-[11px] text-gray-600 mt-1 italic">
                        (Last invoice includes Stamp Fee, Surcharge Fee, and Debit Note adjustments.)
                    </p>
                @else
                    <div class="flex justify-between text-red-700 font-semibold text-xs mt-1">
                        <span>Invoice Status:</span>
                        <span>Partially Paid ⚠️</span>
                    </div>
                    <p class="text-[11px] text-gray-600 mt-1 italic">
                        (Remaining balance will be adjusted in next payment.)
                    </p>
                @endif
            </div>
        </div>

        {{-- Certification --}}
        <div class="mt-4 text-xs text-gray-700">
            <p>
                Certified that the above payment has been received in full and accurately recorded.
                Thank you for your continued business with H.G.P.M. (PVT) Ltd.
            </p>
        </div>

        {{-- Signature Section --}}
        <div class="grid grid-cols-2 gap-8 mt-12 text-xs">
            <div>
                <p>Received by:</p>
                <div class="mt-8 border-t border-gray-600 w-52"></div>
                <p class="mt-1">Name:</p>
                <p>Designation:</p>
            </div>
            <div class="text-right">
                <p>For and on behalf of</p>
                <p class="font-bold">H.G.P.M. (PVT) Ltd.</p>
                <div class="mt-8 border-t border-gray-600 w-52 ml-auto"></div>
                <p class="mt-1">Date: {{ now()->format('d/m/Y') }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Print Styling --}}
<style>
    @media print {
        @page {
            margin: 15mm;
            size: A4 portrait;
            @top-left, @top-center, @top-right,
            @bottom-left, @bottom-center, @bottom-right { content: none; }
        }
        body * { visibility: hidden !important; }
        #receipt, #receipt * { visibility: visible !important; }
        #receipt {
            position: absolute; left: 0; top: 0;
            width: 100%; margin: 0; padding: 0;
            border: none; box-shadow: none;
            font-size: 11px !important;
        }
        .print\:hidden { display: none !important; }
    }
</style>
@endsection
