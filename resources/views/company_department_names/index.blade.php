@extends('layouts.app')

@section('content')
<div class="bg-white shadow-md rounded-lg p-4">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 border-b border-gray-200 pb-3">
        <div>
            <h2 class="text-2xl font-bold text-black">Companywise Department Names</h2>
            <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex">
                    <li class="flex items-center">
                        <a href="{{ route('home') }}" class="hover:underline text-black">Dashboard</a>
                        <svg class="fill-current w-3 h-3 mx-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                            <path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 
                            9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-
                            33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-
                            24.544.04-33.901l22.667-22.667c9.373-9.373 
                            24.569 9.373 33.941 0L285.475 239.03c9.373 
                            9.372 9.373 24.568.001 33.941z"/>
                        </svg>
                    </li>
                    <li class="text-black">Companywise Department Names</li>
                </ol>
            </nav>
        </div>
        <div class="flex items-center space-x-2 mt-3 md:mt-0">
            <a href="{{ route('company_department_names.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add New
            </a>
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
            <ul class="list-disc pl-5 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Company</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Department</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Appear Name</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-black uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($records as $record)
                    <tr>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-black">{{ $record->company->company_name }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-black">{{ $record->department->name }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-black">{{ $record->appear_name }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end items-center space-x-3">
                                {{-- Edit Button --}}
                                <a href="{{ route('company_department_names.edit', $record->id) }}" class="text-yellow-600 hover:text-yellow-800" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                            d="M11 5h2m-1-1v2m4 4l5 5-9 9H6a2 2 0 01-2-2v-4l9-9z" />
                                    </svg>
                                </a>

                                {{-- Delete Button --}}
                                <form action="{{ route('company_department_names.destroy', $record->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800" title="Delete" onclick="return confirm('Are you sure?')">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 
                                                4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 
                                                00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-2 whitespace-nowrap text-sm text-center text-gray-500">
                            No records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {!! $records->withQueryString()->links() !!}
    </div>
</div>
@endsection
