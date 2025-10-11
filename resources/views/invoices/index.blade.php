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
                        <svg class="fill-current w-3 h-3 mx-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                            <path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 
                                9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901
                                L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901
                                l22.667-22.667c9.373-9.373 24.569 9.373 33.941 0L285.475 239.03
                                c9.373 9.372 9.373 24.568.001 33.941z"/>
                        </svg>
                    </li>
                    <li class="text-black">Invoices</li>
                </ol>
            </nav>
        </div>
        @can('invoice-create')
            <a class="mt-3 md:mt-0 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent 
                      rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700" 
               href="{{ route('invoices.create') }}">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Create Invoice
            </a>
        @endcan
    </div>

    {{-- Success & Error Messages --}}
    @if ($message = Session::get('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p>{{ $message }}</p>
        </div>
    @endif
    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p>{{ $errors->first() }}</p>
        </div>
    @endif

    {{-- Tabbed Navigation --}}
    <div class="mb-4 border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <a href="{{ route('invoices.index', ['type' => 'all']) }}" 
               class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm 
               {{ $type === 'all' ? 'border-indigo-500 text-indigo-600' 
                                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                All
            </a>
            <a href="{{ route('invoices.index', ['type' => 'customer']) }}"
               class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm 
               {{ $type === 'customer' ? 'border-indigo-500 text-indigo-600' 
                                       : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Customer
            </a>
            <a href="{{ route('invoices.index', ['type' => 'supplier']) }}"
               class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm 
               {{ $type === 'supplier' ? 'border-indigo-500 text-indigo-600' 
                                       : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Supplier (GRN)
            </a>
            <a href="{{ route('invoices.index', ['type' => 'agent']) }}"
               class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm 
               {{ $type === 'agent' ? 'border-indigo-500 text-indigo-600' 
                                     : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Agent
            </a>
        </nav>
    </div>

    {{-- Filters + Search --}}
    <form x-data x-ref="filterForm" action="{{ route('invoices.index') }}" method="GET" 
        class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 
                space-y-2 md:space-y-0 md:space-x-3">

        {{-- Type Preservation --}}
        <input type="hidden" name="type" value="{{ $type }}">

        {{-- Company Filter (only for customer invoices) --}}
        @if($type === 'customer')
            <div class="flex items-center">
                <label for="company_id" class="mr-2 text-sm text-black">Company:</label>
                <select name="company_id" id="company_id"
                        class="border border-gray-300 rounded-md p-2 text-sm text-black"
                        onchange="this.form.submit()">
                    <option value="">All Companies</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" 
                                {{ request('company_id') == $company->id ? 'selected' : '' }}>
                            {{ $company->company_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        {{-- Search Box --}}
        <div class="flex items-center">
            <label for="search" class="mr-2 text-sm text-black">Search:</label>
            <input type="search"
                name="search"
                id="search"
                class="border border-gray-300 rounded-md p-2 text-sm text-black"
                value="{{ request('search') }}"
                placeholder="Invoice ID, Name..."
                @input.debounce.500ms="$refs.filterForm.submit()">
        </div>
    </form>


    {{-- Invoices Table --}}
    <div class="overflow-x-auto">
        <table class="w-full min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase">Invoice ID</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase">Type</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase">Billed To</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase">Date</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase">Total Amount</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase">Status</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-black uppercase">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($invoices as $invoice)
                <tr>
                    <td class="px-4 py-2 text-sm font-medium text-black">{{ $invoice->invoice_id }}</td>
                    <td class="px-4 py-2 text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $invoice->is_vat_invoice ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $invoice->is_vat_invoice ? 'VAT' : 'Non-VAT' }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-sm text-black">
                        @if($invoice->invoiceable)
                            @php
                                $name = match ($invoice->invoiceable_type) {
                                    'App\Models\Customer' => $invoice->invoiceable->customer_name,
                                    'App\Models\Supplier' => $invoice->invoiceable->supplier_name,
                                    'App\Models\Agent'    => $invoice->invoiceable->name,
                                    default               => 'N/A',
                                };
                            @endphp
                            {{ $name }}
                            <span class="text-xs text-gray-400">
                                ({{ Str::after($invoice->invoiceable_type, 'App\Models\\') }})
                            </span>
                        @else
                            N/A
                        @endif
                    </td>
                    <td class="px-4 py-2 text-sm text-black">{{ $invoice->created_at->format('Y-m-d') }}</td>
                    <td class="px-4 py-2 text-sm text-black">{{ number_format($invoice->total_amount, 2) }}</td>
                    <td class="px-4 py-2 text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($invoice->status == 'paid') bg-green-100 text-green-800 
                            @elseif($invoice->status == 'unpaid') bg-red-100 text-red-800
                            @else bg-yellow-100 text-yellow-800 @endif">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-right text-sm font-medium">
                        <div class="flex justify-end items-center space-x-2">

                            {{-- Voucher & Bill Buttons — only show if company is Navy --}}
                            @if($invoice->invoiceable_type === \App\Models\Customer::class 
                                && $invoice->invoiceable 
                                && strtolower(optional($invoice->invoiceable->company)->company_name) === 'navy')
                                <a href="{{ route('invoices.showopt2', $invoice->id) }}" 
                                class="px-4 py-2 bg-purple-600 text-white rounded-md text-xs font-semibold">
                                    Voucher
                                </a>
                                <a href="{{ route('invoices.opt3', $invoice->id) }}" target="_blank" 
                                class="inline-flex items-center px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">
                                    Bill
                                </a>
                            @endif
                            <a href="{{ route('invoices.print', $invoice->id) }}" target="_blank" 
                               class="inline-flex items-center px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">
                                Invoice
                            </a>
                            {{-- Delete Button --}}
                            @can('invoice-delete')
                                <form action="{{ route('invoices.destroy', $invoice->id) }}" method="POST" 
                                      onsubmit="return confirm('Are you sure you want to delete this invoice?')" 
                                      class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    @if($invoice->status === 'unpaid')
                                        <button type="submit" 
                                                class="inline-flex items-center px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700">
                                            Del
                                        </button>
                                    @else
                                        <span class="px-3 py-1 bg-gray-300 text-gray-600 rounded cursor-not-allowed" 
                                              title="Cannot delete a paid or partially paid invoice">
                                            Del
                                        </span>
                                    @endif
                                </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-2 text-sm text-center text-gray-500">
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
