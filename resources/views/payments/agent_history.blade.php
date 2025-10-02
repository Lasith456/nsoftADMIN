@extends('layouts.app')

@section('content')
<div class="bg-white shadow-md rounded-lg p-4">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 border-b border-gray-200 pb-3">
        <div>
            <h2 class="text-2xl font-bold text-black">Agent Invoice History</h2>
            <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex">
                    <li class="flex items-center">
                        <a href="{{ route('home') }}" class="hover:underline text-black">Dashboard</a>
                        <svg class="fill-current w-3 h-3 mx-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569 9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"/></svg>
                    </li>
                    <li class="text-black">Agent Invoice History</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase">Invoice ID</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase">Agent</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase">Total</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase">Paid</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase">Balance</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase">Status</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-black uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($invoices as $invoice)
                <tr>
                    <td class="px-4 py-2 text-sm"><a href="{{ route('invoices.print', $invoice->id) }}" class="text-blue-600 hover:underline">{{ $invoice->invoice_id }}</a></td>
                    <td class="px-4 py-2 text-sm">{{ $invoice->invoiceable->name ?? 'N/A' }}</td>
                    <td class="px-4 py-2 text-sm">{{ number_format($invoice->total_amount, 2) }}</td>
                    <td class="px-4 py-2 text-sm">{{ number_format($invoice->amount_paid, 2) }}</td>
                    <td class="px-4 py-2 text-sm font-bold">{{ number_format($invoice->total_amount - $invoice->amount_paid, 2) }}</td>
                    <td class="px-4 py-2 text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($invoice->status == 'paid') bg-green-100 text-green-800 
                            @elseif($invoice->status == 'unpaid') bg-red-100 text-red-800
                            @else bg-yellow-100 text-yellow-800 @endif">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-right text-sm">
                        <a href="{{ route('invoices.show', $invoice->id) }}" class="text-blue-600 hover:text-blue-800">Details</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4">No agent invoices found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{!! $invoices->links() !!}</div>
</div>
@endsection

