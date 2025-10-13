@extends('layouts.app')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4">

    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 border-b border-gray-200 pb-3">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-200">GRN PO Management</h2>
            <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex">
                    <li class="flex items-center">
                        <a href="{{ route('home') }}" class="hover:underline text-gray-700 dark:text-gray-300">Dashboard</a>
                        <svg class="fill-current w-3 h-3 mx-3 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                            <path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667
                            c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255
                            c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667
                            c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03
                            c9.373 9.372 9.373 24.568.001 33.941z"/>
                        </svg>
                    </li>
                    <li class="text-gray-700 dark:text-gray-300">GRN PO Management</li>
                </ol>
            </nav>
        </div>

        <div class="flex items-center space-x-2 mt-3 md:mt-0">
            <a href="{{ route('grnpos.create') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold
                      text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add GRN PO
            </a>
        </div>
    </div>

    {{-- Success & Error Messages --}}
    @if ($message = Session::get('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 mb-3 rounded">
            <p>{{ $message }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-3 rounded">
            <ul class="list-disc pl-5 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Filter & Search --}}
    <form action="{{ route('grnpos.index') }}" method="GET" class="mb-4">
        <div class="flex flex-col md:flex-row justify-end items-center space-y-2 md:space-y-0 md:space-x-4">
            <div>
                <label for="delivery_date" class="mr-2 text-sm text-gray-700 dark:text-gray-300">Date:</label>
                <input type="date" name="delivery_date" id="delivery_date"
                       value="{{ request('delivery_date') }}"
                       class="border border-gray-300 rounded-md p-2 text-sm dark:bg-gray-900 dark:text-white">
            </div>

            <div>
                <label for="search" class="mr-2 text-sm text-gray-700 dark:text-gray-300">Search:</label>
                <input type="search" name="search" id="search"
                       value="{{ request('search') }}"
                       placeholder="GRNPO ID, Supplier..."
                       class="border border-gray-300 rounded-md p-2 text-sm dark:bg-gray-900 dark:text-white">
            </div>

            <button type="submit"
                    class="h-10 px-4 bg-gray-800 border rounded-md font-semibold text-xs text-white uppercase hover:bg-gray-700">
                Filter
            </button>

            @if(request()->has('search') || request()->has('delivery_date'))
                <a href="{{ route('grnpos.index') }}"
                   class="h-10 px-4 flex items-center bg-gray-200 border rounded-md font-semibold
                          text-xs text-gray-800 uppercase hover:bg-gray-300">
                    Clear
                </a>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-100 dark:bg-gray-700">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">GRNPO ID</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">Supplier</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">Delivery Date</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">Net Amount</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">Status</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($grnpos as $grnpo)
                    <tr>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $grnpo->grnpo_id }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                            {{ $grnpo->supplier->supplier_name ?? 'N/A' }}
                        </td>
<td>{{ $grnpo->delivery_date->format('Y-m-d') }}</td>

                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                            {{ number_format($grnpo->net_amount, 2) }}
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @switch($grnpo->status)
                                    @case('pending') bg-yellow-100 text-yellow-800 @break
                                    @case('confirmed') bg-green-100 text-green-800 @break
                                    @case('cancelled') bg-red-100 text-red-800 @break
                                    @default bg-gray-100 text-gray-800
                                @endswitch">
                                {{ ucfirst($grnpo->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end items-center space-x-3">
                                <a href="{{ route('grnpos.show', $grnpo->id) }}"
                                   class="text-blue-600 hover:text-blue-800" title="View">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0
                                              8.268 2.943 9.542 7-1.274 4.057-5.064
                                              7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>

                                <form action="{{ route('grnpos.destroy', $grnpo->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800" title="Delete"
                                            onclick="return confirm('Are you sure you want to delete this GRN PO?')">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138
                                                  21H7.862a2 2 0 01-1.995-1.858L5
                                                  7m5 4v6m4-6v6m1-10V4a1 1
                                                  0 00-1-1h-4a1 1 0 00-1
                                                  1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            No GRN POs found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {!! $grnpos->withQueryString()->links() !!}
    </div>

</div>
@endsection
