@extends('layouts.app')

@section('content')
<div class="bg-white shadow-md rounded-lg p-4">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 border-b border-gray-200 pb-3">
        <div>
            <h2 class="text-2xl font-bold text-black">Companies</h2>
            <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex">
                    <li class="flex items-center">
                        <a href="{{ route('home') }}" class="hover:underline text-black">Dashboard</a>
                        <svg class="fill-current w-3 h-3 mx-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569 9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"/></svg>
                    </li>
                    <li class="text-black">Companies</li>
                </ol>
            </nav>
        </div>
        {{-- Add Company Button --}}
        @can('company-create')
            <a class="mt-3 md:mt-0 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900" href="{{ route('companies.create') }}">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Add Company
            </a>
        @endcan
    </div>

    {{-- Success Message --}}
    @if ($message = Session::get('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p>{{ $message }}</p>
        </div>
    @endif

    {{-- Search Form --}}
    <form x-data x-ref="searchForm" action="{{ route('companies.index') }}" method="GET" class="mb-4">
        <div class="flex justify-end">
            <div class="flex items-center">
                <label for="search" class="mr-2 text-sm text-black">Search:</label>
                <input type="search" name="search" id="search" 
                       class="border border-gray-300 rounded-md p-2 text-sm text-black" 
                       value="{{ request('search') }}" 
                       @input.debounce.600ms="$refs.searchForm.submit()">
            </div>
        </div>
    </form>

    {{-- Companies Table --}}
    <div class="overflow-x-auto">
        <table class="w-full min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider w-1/6">#</th>
                    <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider w-2/3">Company Name</th>
                    <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-black uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($companies as $company)
                <tr>
                    <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-black">{{ $company->id }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-black">{{ $company->company_name }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                        <form action="{{ route('companies.destroy',$company->id) }}" method="POST" class="flex justify-end items-center space-x-4">
                            <a href="{{ route('companies.edit',$company->id) }}" class="text-green-600 hover:text-green-800" title="Edit">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L15.232 5.232z"></path></svg>
                            </a>
                            @csrf
                            @method('DELETE')
                            @can('company-delete')
                                <button type="submit" class="text-red-600 hover:text-red-800" title="Delete" onclick="return confirm('Are you sure you want to delete this company?')">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            @endcan
                        </form>
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-2 whitespace-nowrap text-sm text-center text-gray-500">
                            No companies found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination Links --}}
    <div class="mt-4">
        {!! $companies->withQueryString()->links() !!}
    </div>
</div>
@endsection
