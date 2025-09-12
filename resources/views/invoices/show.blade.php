@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div id="invoice-details" class="bg-white dark:bg-gray-800 shadow-lg rounded-lg max-w-4xl mx-auto p-8">
        {{-- Header & Buttons --}}
        <div class="flex justify-between items-center mb-6 print:hidden">
            <h2 class="text-3xl font-bold text-gray-800 dark:text-gray-200">Invoice</h2>
            <div class="flex items-center space-x-2">
                <a href="{{ route('invoices.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md text-xs uppercase font-semibold">Back to List</a>
                @can('payment-create')
                    @if($invoice->total_amount - $invoice->amount_paid > 0)
                        <a href="{{ route('payments.create', $invoice->id) }}" class="px-4 py-2 bg-green-600 text-white rounded-md text-xs uppercase font-semibold">Record Payment</a>
                    @endif
                @endcan
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
                <h4 class="text-xl font-semibold text-gray-700 dark:text-gray-300">INVOICE</h4>
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
                        $address = '';
                        $email = '';
                        if ($invoice->invoiceable_type === 'App\Models\Customer') {
                            $name = $invoice->invoiceable->customer_name;
                            $address = $invoice->invoiceable->primary_address;
                            $email = $invoice->invoiceable->customer_email;
                        } elseif ($invoice->invoiceable_type === 'App\Models\Supplier') {
                            $name = $invoice->invoiceable->supplier_name;
                            $address = $invoice->invoiceable->primary_address;
                            $email = $invoice->invoiceable->email;
                        } elseif ($invoice->invoiceable_type === 'App\Models\Agent') {
                            $name = $invoice->invoiceable->name;
                            $address = $invoice->invoiceable->address;
                            $email = $invoice->invoiceable->email;
                        }
                    @endphp
                    <p class="text-gray-900 dark:text-gray-200 font-semibold">{{ $name }}</p>
                    <p class="text-gray-600 dark:text-gray-400">{{ $address }}</p>
                    <p class="text-gray-600 dark:text-gray-400">{{ $email }}</p>
                @else
                    <p class="text-gray-900 dark:text-gray-200">N/A</p>
                @endif
            </div>
            <div class="text-right">
                <strong class="font-medium text-gray-500 dark:text-gray-400">Date of Issue:</strong>
                <p class="text-gray-900 dark:text-gray-200">{{ $invoice->created_at->format('F j, Y') }}</p>
                <strong class="font-medium text-gray-500 dark:text-gray-400 mt-2">Due Date:</strong>
                <p class="text-gray-900 dark:text-gray-200">{{ $invoice->due_date->format('F j, Y') }}</p>

                @if ($invoice->invoiceable_type === 'App\Models\Customer' && isset($outstandingBalance))
                    <div class="mt-4">
                        <strong class="font-medium text-gray-500 dark:text-gray-400">Total Outstanding Balance:</strong>
                        <p class="text-gray-900 dark:text-gray-200 font-bold text-lg">LKR {{ number_format($outstandingBalance, 2) }}</p>
                    </div>
                @endif
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
                        <td class="px-4 py-2 whitespace-normal text-sm font-medium text-gray-900 dark:text-gray-200 align-top">
                            @php
                                $description = $item->description;
                                $output = '';

                                // Pattern 1: "Product Name (from GRN: GRN-1234)" or "(from RN: RN-1234)"
                                if (preg_match('/^(.*) \(from (GRN|RN): (.*?)\)$/', $description, $matches)) {
                                    [$original, $productName, $sourceType, $sourceIdString] = $matches;
                                    $productName = trim($productName);

                                    $product = \App\Models\Product::where('name', $productName)->first();
                                    $productLink = $product ? "<a href='" . route('products.show', $product->id) . "' class='text-blue-500 hover:underline'>{$productName}</a>" : e($productName);

                                    $sourceLink = e("(from {$sourceType}: {$sourceIdString})"); // Default text
                                    if ($sourceType === 'GRN') {
                                        $sourceModel = \App\Models\Grn::where('grn_id', $sourceIdString)->first();
                                        if ($sourceModel) {
                                            $sourceLink = "(from <a href='" . route('grns.show', $sourceModel->id) . "' class='text-blue-500 hover:underline'>GRN: {$sourceIdString}</a>)";
                                        }
                                    } else { // RN
                                        $sourceModel = \App\Models\ReceiveNote::where('receive_note_id', $sourceIdString)->first();
                                        if ($sourceModel) {
                                            $sourceLink = "(from <a href='" . route('receive-notes.show', $sourceModel->id) . "' class='text-blue-500 hover:underline'>RN: {$sourceIdString}</a>)";
                                        }
                                    }
                                    $output = "{$productLink} {$sourceLink}";
                                }
                                // Pattern 2: "Fulfilled Shortage: Qty x Product Name for DN-..."
                                elseif (preg_match('/^Fulfilled Shortage: (\d+) x (.*?) for (DN-.*?)$/', $description, $matches)) {
                                    [$original, $qty, $productName, $sourceIdString] = $matches;
                                    $productName = trim($productName);
                                    
                                    $product = \App\Models\Product::where('name', $productName)->first();
                                    $productLink = $product ? "<a href='" . route('products.show', $product->id) . "' class='text-blue-500 hover:underline'>{$productName}</a>" : e($productName);

                                    // **THE FIX IS HERE**
                                    // Remove only the first "DN-" prefix to get the actual ID (e.g., "DN-DN-0001" -> "DN-0001")
                                    $actualDeliveryNoteId = preg_replace('/^DN-/', '', $sourceIdString, 1);
                                    
                                    $sourceModel = \App\Models\DeliveryNote::where('delivery_note_id', $actualDeliveryNoteId)->first();
                                    $sourceLink = $sourceModel ? "<a href='" . route('delivery-notes.show', $sourceModel->id) . "' class='text-blue-500 hover:underline'>{$sourceIdString}</a>" : e($sourceIdString);
                                    
                                    $output = "Fulfilled Shortage: {$qty} x {$productLink} for {$sourceLink}";
                                }
                                // Fallback: Just a product name
                                else {
                                    $product = \App\Models\Product::where('name', $description)->first();
                                    $output = $product ? "<a href='" . route('products.show', $product->id) . "' class='text-blue-500 hover:underline'>" . e($description) . "</a>" : e($description);
                                }

                                echo $output;
                            @endphp
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 align-top">{{ $item->quantity }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 align-top">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-right align-top">{{ number_format($item->total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        {{-- Totals Section --}}
        <div class="flex justify-end mb-8">
            <div class="w-full max-w-xs space-y-2">
                <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400">
                    <span>Subtotal</span>
                    <span>{{ number_format($invoice->total_amount, 2) }}</span>
                </div>
                <div class="flex justify-between text-lg font-bold text-gray-900 dark:text-gray-200">
                    <span>Grand Total</span>
                    <span>LKR {{ number_format($invoice->total_amount, 2) }}</span>
                </div>
                 <div class="flex justify-between text-sm text-green-600">
                    <span>Amount Paid</span>
                    <span>-{{ number_format($invoice->amount_paid, 2) }}</span>
                </div>
                <div class="flex justify-between font-bold text-xl text-red-600 border-t pt-2 mt-2">
                    <span>Balance Due</span>
                    <span>LKR {{ number_format($invoice->total_amount - $invoice->amount_paid, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="mt-8 pt-6 border-t dark:border-gray-700">
            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-200 mb-4">Payment History</h3>
            <div class="overflow-x-auto">
                <table class="w-full min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Amount</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Method</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Reference</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($invoice->payments as $payment)
                        <tr>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $payment->payment_date->format('Y-m-d') }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ number_format($payment->amount, 2) }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $payment->payment_method }}</td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $payment->reference_number ?? 'N/A' }}</td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-2 text-center text-sm text-gray-500 dark:text-gray-400">No payments have been recorded for this invoice.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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