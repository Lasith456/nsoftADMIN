@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div id="invoice-details" class="bg-white dark:bg-gray-800 shadow-lg rounded-lg max-w-4xl mx-auto p-8">
        {{-- Header & Buttons --}}
        <div class="flex justify-between items-center mb-6 print:hidden">
            <h2 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Print Invoice</h2>
            <div class="flex items-center space-x-2">
                <a href="{{ route('invoices.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md text-xs uppercase font-semibold">Back to List</a>
                <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-md text-xs uppercase font-semibold">Print</button>
            </div>
        </div>

        {{-- Company Letterhead --}}
        <div class="flex justify-between items-start border-b dark:border-gray-700 pb-4 mb-4">
            <div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-200">NSoft Pvt Ltd.</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">123 Main Street, Colombo, Sri Lanka</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">contact@nsoft.com | +94 11 234 5678</p>
            </div>
            <div class="text-right">
                <h4 class="text-xl font-semibold text-gray-700 dark:text-gray-300">
                    @if($invoice->is_vat_invoice)
                        TAX INVOICE
                    @else
                        INVOICE
                    @endif
                </h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $invoice->invoice_id }}</p>
            </div>
        </div>

        {{-- Invoice Details --}}
        <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
            <div>
                <strong class="font-medium text-gray-500 dark:text-gray-400">Billed To:</strong>
                @if($invoice->invoiceable)
                    @php
                        $name = '';
                        if ($invoice->invoiceable_type === 'App\Models\Customer') $name = $invoice->invoiceable->customer_name;
                        elseif ($invoice->invoiceable_type === 'App\Models\Supplier') $name = $invoice->invoiceable->supplier_name;
                        elseif ($invoice->invoiceable_type === 'App\Models\Agent') $name = $invoice->invoiceable->name;
                    @endphp
                    <p class="text-gray-900 dark:text-gray-200 font-semibold">{{ $name }}</p>
                @endif
            </div>
            <div class="text-right">
                <strong class="font-medium text-gray-500 dark:text-gray-400">Date of Issue:</strong>
                <p class="text-gray-900 dark:text-gray-200">{{ $invoice->created_at->format('F j, Y') }}</p>
                 <strong class="font-medium text-gray-500 dark:text-gray-400 mt-2">Due Date:</strong>
                <p class="text-gray-900 dark:text-gray-200">{{ $invoice->due_date->format('F j, Y') }}</p>
            </div>
        </div>

        {{-- Items Table --}}
        <div class="overflow-x-auto mb-6">
            <table class="w-full min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Description</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Qty</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Unit Price</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($invoice->items as $item)
                    <tr>
                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-200">{{ $item->description }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $item->quantity }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-right">{{ number_format($item->total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        {{-- ** THE FIX IS HERE: Updated Totals Section for Print View ** --}}
        <div class="flex justify-end mb-8">
            <div class="w-full max-w-sm space-y-2">
                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                    <span>Subtotal</span>
                    <span>{{ number_format($invoice->sub_total, 2) }}</span>
                </div>
                @if($invoice->is_vat_invoice)
                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                    <span>VAT ({{ number_format($invoice->vat_percentage, 2) }}%)</span>
                    <span>{{ number_format($invoice->vat_amount, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between text-lg font-bold text-gray-900 dark:text-gray-200">
                    <span>Grand Total</span>
                    <span>LKR {{ number_format($invoice->total_amount, 2) }}</span>
                </div>
                {{-- Simplified for print view - omitting paid/due amounts --}}
            </div>
        </div>

        {{-- Footer & Signatures --}}
        <div class="border-t dark:border-gray-700 pt-6 mt-6 text-xs text-gray-500 dark:text-gray-400">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="font-semibold">Notes:</p>
                    <p>Thank you for your business. Please make payments within 30 days.</p>
                </div>
                <div class="grid grid-cols-2 gap-4 mt-16">
                    <div class="border-t border-gray-400 text-center pt-2">
                        <p>Authorized Signature</p>
                    </div>
                     <div class="border-t border-gray-400 text-center pt-2">
                        <p>Customer Signature</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #invoice-details, #invoice-details * {
            visibility: visible;
        }
        #invoice-details {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
            border: none;
            box-shadow: none;
        }
        .print\:hidden {
            display: none !important;
        }
    }
</style>
@endsection

