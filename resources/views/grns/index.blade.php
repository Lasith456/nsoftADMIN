@extends('layouts.app')

@section('content')
<div class="bg-white shadow-md rounded-lg p-4">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 border-b border-gray-200 pb-3">
        <div>
            <h2 class="text-2xl font-bold text-black">GRN Management</h2>
            <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex">
                    <li class="flex items-center">
                        <a href="{{ route('home') }}" class="hover:underline text-black">Dashboard</a>
                        <svg class="fill-current w-3 h-3 mx-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569 9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"/></svg>
                    </li>
                    <li class="text-black">GRN Management</li>
                </ol>
            </nav>
        </div>
        <div class="flex items-center space-x-2 mt-3 md:mt-0">
            @can('grn-create')
                <a class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700" href="{{ route('grns.create') }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Add GRN
                </a>
            @endcan
        </div>
    </div>

    {{-- Success & Error Messages --}}
    @if ($message = Session::get('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p>{{ $message }}</p>
        </div>
    @endif
     @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <ul class="list-disc pl-5 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- Search Form --}}
    <form action="{{ route('grns.index') }}" method="GET" class="mb-4">
        <div class="flex justify-end items-center space-x-4">
             <div>
                <label for="delivery_date" class="mr-2 text-sm text-black">Date:</label>
                <input type="date" name="delivery_date" id="delivery_date" class="border border-gray-300 rounded-md p-2 text-sm text-black" value="{{ request('delivery_date') }}">
            </div>
            <div>
                <label for="search" class="mr-2 text-sm text-black">Search:</label>
                <input type="search" name="search" id="search" class="border border-gray-300 rounded-md p-2 text-sm text-black" value="{{ request('search') }}" placeholder="GRN ID, Invoice, Supplier...">
            </div>
            <button type="submit" class="h-10 px-4 bg-gray-800 border rounded-md font-semibold text-xs text-white uppercase hover:bg-gray-700">Filter</button>
            @if(request()->has('search') || request()->has('delivery_date'))
                <a href="{{ route('grns.index') }}" class="h-10 px-4 flex items-center bg-gray-200 border rounded-md font-semibold text-xs text-black uppercase hover:bg-gray-300">Clear</a>
            @endif
        </div>
    </form>

    {{-- GRNs Table --}}
    <div class="overflow-x-auto">
        <table class="w-full min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">GRN ID</th>
                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider w-2/5">Supplier</th>
                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Delivery Date</th>
                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Net Amount</th>
                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-black uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($grns as $grn)
               <tr>
                    <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-black">{{ $grn->grn_id }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-black">{{ $grn->supplier->supplier_name ?? 'N/A' }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-black">{{ $grn->delivery_date->format('Y-m-d') }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-black">{{ number_format($grn->net_amount, 2) }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @switch($grn->status)
                                @case('pending') bg-yellow-100 text-yellow-800 @break
                                @case('confirmed') bg-green-100 text-green-800 @break
                                @case('invoiced') bg-blue-100 text-blue-800 @break
                                @case('cancelled') bg-red-100 text-red-800 @break
                                @default bg-gray-100 text-gray-800
                            @endswitch
                        ">
                            {{ ucfirst($grn->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end items-center space-x-4">
                            <a href="{{ route('grns.show', $grn->id) }}" class="text-blue-600 hover:text-blue-800" title="Show">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542 7z"></path></svg>
                            </a>
                            
                            {{-- Conditional buttons for invoicing --}}
                            @if ($grn->status == 'confirmed' && !$grn->invoice_id)
                                @can('invoice-create')
                                    <form action="{{ route('grns.generateInvoice', $grn->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:text-green-800" title="Generate Invoice">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        </button>
                                    </form>
                                @endcan
                            @elseif ($grn->invoice_id)
                                @can('invoice-show')
                                    <a href="{{ route('invoices.show', $grn->invoice_id) }}" class="text-indigo-600 hover:text-indigo-800" title="View Invoice">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                    </a>
                                    <a href="{{ route('invoices.print', $grn->invoice_id) }}" class="text-purple-600 hover:text-purple-800" title="Print Invoice">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm7-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                    </a>
                                @endcan
                            @endif

                            @if ($grn->status == 'pending')
                                @can('grn-manage')
                                    <form action="{{ route('grns.complete', $grn->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:text-green-800" title="Complete GRN" onclick="return confirm('Complete this GRN? This will add all items to your stock.')">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </form>
                                    <form action="{{ route('grns.cancel', $grn->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-yellow-600 hover:text-yellow-800" title="Cancel GRN" onclick="return confirm('Are you sure you want to cancel this GRN?')">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </form>
                                @endcan
                            @endif
                            @can('grn-delete')
                                @php
                                    $showDelete = false;

                                    if (!in_array($grn->status, ['invoiced','cancelled'])) {
                                        if ($grn->status === 'confirmed' && $grn->confirmed_at) {
                                            $showDelete = now()->diffInHours($grn->confirmed_at) <= 24;
                                        } elseif ($grn->status !== 'confirmed') {
                                            $showDelete = true;
                                        }
                                    }
                                @endphp

                                @if($showDelete)
                                    <form action="{{ route('grns.destroy', $grn->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800" title="Delete"
                                            onclick="return confirm('Are you sure? This will revert stock if the GRN was completed.')">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            @endcan

                        </div>
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-2 whitespace-nowrap text-sm text-center text-gray-500">
                            No GRNs found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination Links --}}
    <div class="mt-4">
        {!! $grns->withQueryString()->links() !!}
    </div>
</div>
@endsection

