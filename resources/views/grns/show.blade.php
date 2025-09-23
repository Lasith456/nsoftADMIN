@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div id="grn-details" class="bg-white dark:bg-gray-800 shadow-md rounded-lg max-w-4xl mx-auto p-4">
        
        <!-- Letterhead Section -->
        <div class="mb-6 border-b border-gray-300 pb-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">H.G.P.M. (PVT) Ltd.</h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400">No: 412/B, Galle Road, Pamburana, Matara.</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Tel: 041 2229231, 041 2224121 | Fax: 041 2224122 | Email: hgpm.ltd@sltnet.lk</p>
                </div>
                <div>
                    <img src="{{ asset('images/logo.png') }}" alt="Company Logo" class="h-16 w-auto">
                </div>
            </div>
        </div>

        <!-- Header Title & Actions -->
        <div class="flex justify-between items-center mb-4 print:hidden">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">GRN Details</h2>
            <div class="flex items-center space-x-2">
                <a href="{{ url()->previous() }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase">
                    Back to List
                </a>
                <button onclick="window.print()" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border rounded-md font-semibold text-xs text-white uppercase hover:bg-blue-700">
                    Print PDF
                </button>
            </div>
        </div>

        <!-- GRN Main Details -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4 pb-4 border-b dark:border-gray-700">
            <div>
                <strong class="font-medium text-gray-900 dark:text-gray-200 text-sm">GRN ID:</strong>
                <p class="text-gray-600 dark:text-gray-400 text-sm">{{ $grn->grn_id }}</p>
            </div>
            <div>
                <strong class="font-medium text-gray-900 dark:text-gray-200 text-sm">Supplier:</strong>
                <p class="text-gray-600 dark:text-gray-400 text-sm">
                    @if ($grn->supplier)
                        <a href="{{ route('suppliers.show', $grn->supplier->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                            {{ $grn->supplier->supplier_name }}
                        </a>
                    @else
                        N/A
                    @endif
                </p>
            </div>
            <div>
                <strong class="font-medium text-gray-900 dark:text-gray-200 text-sm">Delivery Date:</strong>
                <p class="text-gray-600 dark:text-gray-400 text-sm">{{ $grn->delivery_date->format('F j, Y') }}</p>
            </div>
            <div>
                <strong class="font-medium text-gray-900 dark:text-gray-200 text-sm">Invoice Number:</strong>
                <p class="text-gray-600 dark:text-gray-400 text-sm">{{ $grn->invoice_number ?? 'N/A' }}</p>
            </div>
        </div>

        <!-- GRN Items Table -->
        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-2">Items Received</h3>
        <div class="overflow-x-auto">
            <table class="w-full min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Product</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Qty Received</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Unit Cost</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Discount</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($grn->items as $item)
                    <tr>
                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-200">
                            @if ($item->product)
                                <a href="{{ route('products.show', $item->product->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                    {{ $item->product->name }}
                                </a>
                            @else
                                N/A
                            @endif
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $item->quantity_received }} {{ $item->unit_type }}(s)</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ number_format($item->cost_price, 2) }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ number_format($item->discount, 2) }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-right">{{ number_format(($item->cost_price * $item->quantity_received) - $item->discount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- GRN Totals -->
        <div class="mt-4 flex justify-end">
            <div class="w-full max-w-sm space-y-1">
                <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400">
                    <span>Total Amount</span>
                    <span>{{ number_format($grn->total_amount, 2) }}</span>
                </div>
                <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400">
                    <span>Total Discount</span>
                    <span>-{{ number_format($grn->total_discount, 2) }}</span>
                </div>
                <div class="flex justify-between font-semibold text-gray-900 dark:text-gray-200">
                    <span>Net Amount</span>
                    <span>{{ number_format($grn->net_amount, 2) }}</span>
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
        #grn-details, #grn-details * {
            visibility: visible;
        }
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
        .print\:hidden {
            display: none;
        }
    }
</style>
@endsection
