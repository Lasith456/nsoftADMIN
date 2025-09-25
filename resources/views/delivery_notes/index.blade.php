@extends('layouts.app')

@section('content')
<div class="bg-white shadow-md rounded-lg p-4">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 border-b border-gray-200 pb-3">
        <div>
            <h2 class="text-2xl font-bold text-black">Delivery Notes</h2>
            <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex">
                    <li class="flex items-center">
                        <a href="{{ route('home') }}" class="hover:underline text-black">Dashboard</a>
                        <svg class="fill-current w-3 h-3 mx-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569 9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"/></svg>
                    </li>
                    <li class="text-black">Delivery Notes</li>
                </ol>
            </nav>
        </div>
        <div class="flex items-center space-x-2 mt-3 md:mt-0">
            @can('delivery-note-manage')
                <a class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700" href="{{ route('delivery-notes.manage') }}">Manage Statuses</a>
            @endcan
            @can('delivery-note-create')
                <a class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700" href="{{ route('delivery-notes.create') }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Create DN
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

    {{-- Error Message --}}
    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p>{{ $errors->first() }}</p>
        </div>
    @endif

    {{-- Search Form --}}
    <form x-data x-ref="searchForm" action="{{ route('delivery-notes.index') }}" method="GET" class="mb-4">
        <div class="flex justify-end">
            <div class="flex items-center">
                <label for="search" class="mr-2 text-sm text-black">Search:</label>
                <input type="search" name="search" id="search" class="border border-gray-300 rounded-md p-2 text-sm text-black" value="{{ request('search') }}" @input.debounce.300ms="$refs.searchForm.submit()" placeholder="DN ID, Vehicle No...">
            </div>
        </div>
    </form>

    {{-- Delivery Notes Table --}}
    <div class="overflow-x-auto">
        <table class="w-full min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">DN ID</th>
                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Vehicle</th>
                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Delivery Date</th>
                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-black uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($deliveryNotes as $deliveryNote)
                <tr>
                    <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-black">{{ $deliveryNote->delivery_note_id }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-black">{{ $deliveryNote->vehicle->vehicle_no ?? 'N/A' }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-black">{{ $deliveryNote->delivery_date->format('Y-m-d') }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            @switch($deliveryNote->status)
                                @case('delivered') bg-green-100 text-green-800 @break
                                @case('processing') bg-blue-100 text-blue-800 @break
                                @case('cancelled') bg-red-100 text-red-800 @break
                                @case('received') bg-purple-100 text-purple-800 @break
                                @default bg-yellow-100 text-yellow-800
                            @endswitch
                        ">
                            {{ ucfirst($deliveryNote->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end items-center space-x-6">
                            <a href="{{ route('delivery-notes.show', $deliveryNote->id) }}" class="text-blue-600 hover:text-blue-800" title="Show">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </a>

                            @can('delivery-note-delete')
                                @csrf
                                @method('DELETE')

                                @if($deliveryNote->receiveNotes && $deliveryNote->receiveNotes->count() > 0)
                                    {{-- Disable delete if linked to receive notes --}}
                                    <button type="button" class="text-gray-400 cursor-not-allowed" title="Delete Receive Notes first" disabled>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                @else
                                    <form action="{{ route('delivery-notes.destroy', $deliveryNote->id) }}" method="POST" onsubmit="return confirm('Are you sure? This will revert stock and set associated POs back to pending.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                @endif
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-2 whitespace-nowrap text-sm text-center text-gray-500">
                            No delivery notes found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination Links --}}
    <div class="mt-4">
        {!! $deliveryNotes->withQueryString()->links() !!}
    </div>
</div>
@endsection
