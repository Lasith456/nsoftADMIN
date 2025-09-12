@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-4">Manage GRN Status</h2>
        <p class="text-gray-600 dark:text-gray-400 mb-6">Find pending GRNs to complete (add to stock) or cancel.</p>

        <!-- Search Form -->
        <form action="{{ route('grns.manage') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div>
                <label for="grn_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search by GRN ID</label>
                <input type="text" name="grn_id" id="grn_id" class="mt-1 block w-full dark:bg-gray-900 rounded-md py-2 px-3" value="{{ request('grn_id') }}">
            </div>
            <div>
                <label for="supplier_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Filter by Supplier</label>
                <select name="supplier_id" id="supplier_id" class="mt-1 block w-full dark:bg-gray-900 rounded-md py-2 px-3">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->supplier_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-800 border rounded-md font-semibold text-xs text-white uppercase hover:bg-gray-700">Search</button>
            </div>
        </form>

        @if ($message = Session::get('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert"><p>{{ $message }}</p></div>
        @endif
        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- Results Table -->
        <div class="overflow-x-auto">
            <table class="w-full min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">GRN ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($grns as $grn)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-200">{{ $grn->grn_id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $grn->supplier->supplier_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">{{ ucfirst($grn->status) }}</span></td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <form action="{{ route('grns.complete', $grn->id) }}" method="POST" class="inline-flex mr-2">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-900" onclick="return confirm('Are you sure you want to complete this GRN? This will add all items to your stock.')">Complete</button>
                            </form>
                            <form action="{{ route('grns.cancel', $grn->id) }}" method="POST" class="inline-flex">
                                @csrf
                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to cancel this GRN?')">Cancel</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                        @if(request()->filled('grn_id') || request()->filled('supplier_id'))
                        <tr><td colspan="4" class="text-center py-4">No pending GRNs found for your search.</td></tr>
                        @endif
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
