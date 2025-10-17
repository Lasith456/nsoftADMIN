@extends('layouts.app')

@section('content')
<div class="bg-white shadow-md rounded-lg p-4">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 border-b border-gray-200 pb-3">
        <div>
            <h2 class="text-2xl font-bold text-black">Wastage Report</h2>
            <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex">
                    <li class="flex items-center">
                        <a href="{{ route('home') }}" class="hover:underline text-black">Dashboard</a>
                        <svg class="fill-current w-3 h-3 mx-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569 9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z"/></svg>
                    </li>
                    <li class="text-black">Wastage Report</li>
                </ol>
            </nav>
        </div>

        {{-- Export Buttons --}}
        <div class="flex gap-2 mt-3 md:mt-0">
            <a href="{{ route('stock.wastage.export.excel', request()->query()) }}"
               class="inline-flex items-center px-3 py-2 bg-green-600 text-white rounded-md text-xs font-semibold hover:bg-green-700">
               Export Excel
            </a>
            <a href="{{ route('stock.wastage.export.pdf', request()->query()) }}"
               class="inline-flex items-center px-3 py-2 bg-red-600 text-white rounded-md text-xs font-semibold hover:bg-red-700">
               Export PDF
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('stock.wastage.report') }}" class="mb-4 grid grid-cols-1 md:grid-cols-7 gap-4">
        <select name="product_id" class="border border-gray-300 rounded-md p-2 text-sm text-black">
            <option value="">All Products</option>
            @foreach($products as $product)
                <option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>
                    {{ $product->name }}
                </option>
            @endforeach
        </select>

        <select name="department_id" class="border border-gray-300 rounded-md p-2 text-sm text-black">
            <option value="">All Departments</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}" @selected(request('department_id') == $dept->id)>
                    {{ $dept->name }}
                </option>
            @endforeach
        </select>

        {{-- 🆕 Stock Type Filter --}}
        <select name="stock_type" class="border border-gray-300 rounded-md p-2 text-sm text-black">
            <option value="">All Stock Types</option>
            <option value="clear" @selected(request('stock_type') == 'clear')>Clear</option>
            <option value="non-clear" @selected(request('stock_type') == 'non-clear')>Non-Clear</option>
            <option value="RN_wastage" @selected(request('stock_type') == 'RN_wastage')>RN Wastage</option>
        </select>

        {{-- 🆕 Status Filter --}}
        <select name="status" class="border border-gray-300 rounded-md p-2 text-sm text-black">
            <option value="">All Status</option>
            <option value="pending" @selected(request('status') == 'pending')>Pending</option>
            <option value="returned" @selected(request('status') == 'returned')>Returned</option>
        </select>

        <input type="date" name="from_date" value="{{ request('from_date') }}"
            class="border border-gray-300 rounded-md p-2 text-sm text-black">

        <input type="date" name="to_date" value="{{ request('to_date') }}"
            class="border border-gray-300 rounded-md p-2 text-sm text-black">

        <button class="bg-blue-600 text-white px-4 py-2 rounded text-xs font-semibold hover:bg-blue-700">Filter</button>
    </form>


    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Date</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Product</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Department</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Stock Type</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Quantity</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Reason</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($wastageLogs as $log)
                    <tr>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-black">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-black">{{ $log->product->name }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-black">{{ $log->product->department->name ?? '-' }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm capitalize text-black">{{ $log->stock_type }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-black">{{ $log->quantity }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-black">{{ $log->reason ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-2 whitespace-nowrap text-sm text-center text-gray-500">
                            No wastage records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {!! $wastageLogs->withQueryString()->links() !!}
    </div>
</div>
@endsection
