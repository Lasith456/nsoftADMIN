@extends('layouts.app')

@section('content')
<div class="bg-white shadow-md rounded-lg p-4">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 border-b border-gray-200 pb-3">
        <div>
            <h2 class="text-2xl font-bold text-black">Receive Note Management</h2>
            <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex">
                    <li class="flex items-center">
                        <a href="{{ route('home') }}" class="hover:underline text-black">Dashboard</a>
                        <svg class="fill-current w-3 h-3 mx-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                            <path d="M285.476 272.971L91.132 467.314c-9.373 
                                     9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 
                                     256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 
                                     24.569-9.373 33.941 0L285.475 239.03c9.373 
                                     9.372 9.373 24.568.001 33.941z"/>
                        </svg>
                    </li>
                    <li class="text-black">Receive Notes</li>
                </ol>
            </nav>
        </div>
        
        {{-- Action Buttons --}}
        <div class="flex items-center space-x-2 mt-3 md:mt-0">
            @can('invoice-create')
                <a class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent 
                           rounded-md font-semibold text-xs text-white uppercase tracking-widest 
                           hover:bg-blue-700"
                   href="{{ route('invoices.createCustomer') }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" 
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 
                                 012-2h5.586a1 1 0 01.707.293l5.414 
                                 5.414a1 1 0 01.293.707V19a2 2 0 
                                 01-2 2z"/>
                    </svg>
                    Create Customer Invoice
                </a>
            @endcan
            @can('receive-note-create')
                <a class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent 
                           rounded-md font-semibold text-xs text-white uppercase tracking-widest 
                           hover:bg-gray-700"
                   href="{{ route('receive-notes.create') }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" 
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Create Receive Note
                </a>
            @endcan
        </div>
    </div>

    {{-- Success Message --}}
    @if ($message = Session::get('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p>{{ $message }}</p>
        </div>
    @endif

    {{-- Filters + Search --}}
    <form x-data x-ref="filterForm" action="{{ route('receive-notes.index') }}" method="GET" 
        class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 
                space-y-2 md:space-y-0 md:space-x-3">

        {{-- Company Filter --}}
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

        {{-- Search --}}
        <div class="flex items-center">
            <label for="search" class="mr-2 text-sm text-black">Search:</label>
            <input type="search"
                name="search"
                id="search"
                class="border border-gray-300 rounded-md p-2 text-sm text-black"
                value="{{ request('search') }}"
                placeholder="RN ID, DN ID..."
                @input.debounce.500ms="$refs.filterForm.submit()">
        </div>
    </form>


    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">RN ID</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider w-2/5">Associated DN(s)</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Customer Name</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Received Date</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Status</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-black uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($receiveNotes as $receiveNote)
                <tr>
                    <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-black">
                        {{ $receiveNote->receive_note_id }}
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-black">
                        @foreach($receiveNote->deliveryNotes as $dn)
                            {{ $dn->delivery_note_id }}@if(!$loop->last), @endif
                        @endforeach
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-black">
                        {{ $receiveNote->deliveryNotes->first()?->purchaseOrders->first()?->customer?->customer_name ?? 'N/A' }}
                    </td>

                    <td class="px-4 py-2 whitespace-nowrap text-sm text-black">
                        {{ $receiveNote->received_date->format('Y-m-d') }}
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @if($receiveNote->invoices->count() > 0) bg-indigo-100 text-indigo-800
                            @elseif($receiveNote->status == 'completed') bg-green-100 text-green-800
                            @elseif($receiveNote->status == 'discrepancy') bg-yellow-100 text-yellow-800
                            @else bg-orange-100 text-orange-800 @endif">
                            @if($receiveNote->invoices->count() > 0)
                                Invoiced
                            @else
                                {{ ucfirst($receiveNote->status) }}
                            @endif
                        </span>
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                        <form action="{{ route('receive-notes.destroy', $receiveNote->id) }}" 
                              method="POST" class="flex justify-end items-center space-x-2">
                            <!-- @if($receiveNote->invoices->count() === 0 && in_array($receiveNote->status, ['completed','discrepancy']))
                                <a href="{{ route('invoices.createFromRN', $receiveNote->id) }}"
                                   class="text-purple-600 hover:text-purple-800" title="Generate Invoice">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 
                                                 012-2h5.586a1 1 0 01.707.293l5.414 
                                                 5.414a1 1 0 01.293.707V19a2 2 0 
                                                 01-2 2z"/>
                                    </svg>
                                </a>
                            @endif -->

                            <a href="{{ route('receive-notes.show', $receiveNote->id) }}"
                               class="text-blue-600 hover:text-blue-800" title="Show">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M2.458 12C3.732 7.943 7.523 5 
                                             12 5c4.478 0 8.268 2.943 
                                             9.542 7-1.274 4.057-5.064 7-9.542 
                                             7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            @csrf
                            @method('DELETE')
                            @can('receive-note-delete')
                                @if($receiveNote->invoices->count() === 0)
                                    <button type="submit" 
                                            onclick="return confirm('Are you sure you want to delete this Receive Note?')"
                                            class="text-red-600 hover:text-red-800" title="Delete">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 
                                                    21H7.862a2 2 0 01-1.995-1.858L5 
                                                    7m5 4v6m4-6v6m1-10V4a1 1 
                                                    0 00-1-1h-4a1 1 0 00-1 
                                                    1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                @else
                                    <span class="text-gray-400 cursor-not-allowed" title="Cannot delete, invoice exists">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 
                                                    21H7.862a2 2 0 01-1.995-1.858L5 
                                                    7m5 4v6m4-6v6m1-10V4a1 1 
                                                    0 00-1-1h-4a1 1 0 00-1 
                                                    1v3M4 7h16"/>
                                        </svg>
                                    </span>
                                @endif
                            @endcan

                        </form>
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-gray-500">No receive notes found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {!! $receiveNotes->withQueryString()->links() !!}
    </div>
</div>
@endsection
