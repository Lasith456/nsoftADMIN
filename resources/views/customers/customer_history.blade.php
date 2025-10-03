@extends('layouts.app')

@section('content')
<div class="bg-white shadow-md rounded-lg p-4">
    <div class="flex justify-between items-center mb-4 border-b pb-3">
        <h2 class="text-2xl font-bold text-black">
            Invoice History - {{ $customer->customer_name }}
        </h2>
        <a href="{{ route('customers.index') }}" 
           class="text-blue-600 hover:underline">← Back to Customers</a>
    </div>

    {{-- Invoices Table --}}
    <div class="overflow-x-auto">
        <table class="w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase">Invoice No</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase">Invoice Date</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase">Total Amount</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase">Paid</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase">Balance</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase">Status</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-black uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($customer->invoices as $invoice)
                    <tr>
                        <td class="px-4 py-2 text-sm text-blue-600">
                            <a href="{{ route('invoices.print', $invoice->id) }}" class="hover:underline">
                                {{ $invoice->invoice_no ?? $invoice->invoice_id ?? $invoice->id }}
                            </a>
                        </td>
                        <td class="px-4 py-2 text-sm text-black">
                            {{ $invoice->created_at->format('d M Y') }}
                        </td>
                        <td class="px-4 py-2 text-sm text-black">
                            {{ number_format($invoice->total, 2) }}
                        </td>
                        <td class="px-4 py-2 text-sm text-green-600">
                            {{ number_format($invoice->payments->sum('amount'), 2) }}
                        </td>
                        <td class="px-4 py-2 text-sm text-red-600 font-bold">
                            {{ number_format($invoice->total - $invoice->payments->sum('amount'), 2) }}
                        </td>
                        <td class="px-4 py-2 text-sm">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($invoice->status == 'paid') bg-green-100 text-green-800
                                @elseif($invoice->status == 'unpaid') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-right text-sm">
                            <a href="{{ route('invoices.show', $invoice->id) }}" 
                               class="text-indigo-600 hover:text-indigo-800">Details</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-2 text-center text-gray-500">
                            No invoices found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
