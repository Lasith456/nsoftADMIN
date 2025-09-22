@extends('layouts.app')

@section('content')
<div class="bg-white shadow-md rounded-lg p-4">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 border-b border-gray-200 pb-3">
        <div>
            <h2 class="text-2xl font-bold text-black">Invoices</h2>
            <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex">
                    <li class="flex items-center">
                        <a href="{{ route('home') }}" class="hover:underline text-black">Dashboard</a>
                        <svg class="fill-current w-3 h-3 mx-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569 9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"/></svg>
                    </li>
                    <li class="text-black">Invoices</li>
                </ol>
            </nav>
        </div>
        @can('invoice-create')
            <a class="mt-3 md:mt-0 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700" href="{{ route('invoices.create') }}">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Create Invoice
            </a>
        @endcan
    </div>

    {{-- Success Message --}}
    @if ($message = Session::get('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p>{{ $message }}</p>
        </div>
    @endif

    {{-- Tabbed Navigation --}}
    <div class="mb-4 border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <a href="{{ route('invoices.index', ['type' => 'all']) }}" 
               class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $type === 'all' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                All
            </a>
            <a href="{{ route('invoices.index', ['type' => 'customer']) }}"
               class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $type === 'customer' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Customer
            </a>
            <a href="{{ route('invoices.index', ['type' => 'supplier']) }}"
               class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $type === 'supplier' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Supplier(GRN)
            </a>
            <a href="{{ route('invoices.index', ['type' => 'agent']) }}"
               class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $type === 'agent' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Agent
            </a>
        </nav>
    </div>

    {{-- Search Form --}}
    <form x-data x-ref="searchForm" action="{{ route('invoices.index') }}" method="GET" class="mb-4">
        <div class="flex justify-end">
            <div class="flex items-center">
                <input type="hidden" name="type" value="{{ $type }}">
                <label for="search" class="mr-2 text-sm text-black">Search:</label>
                <input type="search" name="search" id="search" class="border border-gray-300 rounded-md p-2 text-sm text-black" value="{{ request('search') }}" @input.debounce.300ms="$refs.searchForm.submit()" placeholder="Invoice ID, Name...">
            </div>
        </div>
    </form>

    {{-- Invoices Table --}}
    <div class="overflow-x-auto">
        <table class="w-full min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Invoice ID</th>
                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Type</th>
                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Billed To</th>
                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Date</th>
                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Total Amount</th>
                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-black uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($invoices as $invoice)
                <tr>
                    <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-black">{{ $invoice->invoice_id }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $invoice->is_vat_invoice ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $invoice->is_vat_invoice ? 'VAT' : 'Non-VAT' }}
                        </span>
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-black">
                        @if($invoice->invoiceable)
                            @php
                                $name = '';
                                if ($invoice->invoiceable_type === 'App\Models\Customer') $name = $invoice->invoiceable->customer_name;
                                elseif ($invoice->invoiceable_type === 'App\Models\Supplier') $name = $invoice->invoiceable->supplier_name;
                                elseif ($invoice->invoiceable_type === 'App\Models\Agent') $name = $invoice->invoiceable->name;
                            @endphp
                            {{ $name }}
                            <span class="text-xs text-gray-400">({{ Str::after($invoice->invoiceable_type, 'App\Models\\') }})</span>
                        @else
                            N/A
                        @endif
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-black">{{ $invoice->created_at->format('Y-m-d') }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-black">{{ number_format($invoice->total_amount, 2) }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($invoice->status == 'paid') bg-green-100 text-green-800 
                            @elseif($invoice->status == 'unpaid') bg-red-100 text-red-800
                            @else bg-yellow-100 text-yellow-800 @endif">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end items-center space-x-2">
                            <a href="{{ route('invoices.print', $invoice->id) }}" class="text-gray-400 hover:text-purple-600" title="Print Invoice">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm7-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            </a>
                            <a href="{{ route('invoices.show', $invoice->id) }}" class="text-blue-600 hover:text-blue-800" title="Show Details">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542 7z"></path></svg>
                            </a>
                            <a href="{{ route('invoices.showopt2', $invoice->id) }}" 
   class="px-4 py-2 bg-purple-600 text-white rounded-md text-xs uppercase font-semibold">
   Show Opt2
</a>
                        </div>
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-2 whitespace-nowrap text-sm text-center text-gray-500">
                            No invoices found for this category.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination Links --}}
    <div class="mt-4">
        {!! $invoices->withQueryString()->links() !!}
    </div>
</div>
@endsection

