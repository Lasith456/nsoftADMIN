@extends('layouts.app')

@section('content')
<div class="bg-white shadow-md rounded-lg p-4">
    <div id="report-content">

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 pb-3 border-b print:hidden">
            <div>
                <h2 class="text-2xl font-bold text-black">Stock Level Report</h2>
                <p class="text-sm text-gray-500">A real-time overview of all product stock levels.</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('reports.stock_level.export.excel', request()->query()) }}" 
                class="px-4 py-2 bg-green-600 text-white rounded-md text-xs uppercase font-semibold">Export Excel</a>
                <a href="{{ route('reports.stock_level.export.pdf', request()->query()) }}" 
                class="px-4 py-2 bg-red-600 text-white rounded-md text-xs uppercase font-semibold">Export PDF</a>
                <button onclick="window.print()" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-md text-xs uppercase font-semibold">Print</button>
            </div>
        </div>
        <!-- Department Filter -->
        <form action="{{ route('reports.stock_levels') }}" method="GET" class="mb-4 print:hidden flex items-center space-x-2">
            <div>
                <label for="department_id" class="text-sm">Department:</label>
                <select name="department_id" id="department_id" class="border rounded-md p-1 text-sm">
                    <option value="">-- All Departments --</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md text-xs uppercase">Filter</button>
            <a href="{{ route('reports.stock_levels') }}" class="px-4 py-2 bg-gray-200 text-black rounded-md text-xs uppercase">Clear</a>
        </form>
        <div class="overflow-x-auto">
            <table class="w-full min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Product Name</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-black uppercase tracking-wider">Clear Stock</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-black uppercase tracking-wider">Non-Clear Stock</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-black uppercase tracking-wider">Total Stock</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($products as $product)
                    <tr>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-black">{{ $product->name }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-black text-right">{{ $product->clear_stock_quantity }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-black text-right">{{ $product->non_clear_stock_quantity }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-black text-right font-bold">{{ $product->total_stock }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-2 text-center text-sm text-gray-500">
                            No products found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 print:hidden">
            {!! $products->links() !!}
        </div>
    </div>
</div>
<style>
    @media print {
        body * { visibility: hidden; }
        #report-content, #report-content * { visibility: visible; }
        #report-content { position: absolute; left: 0; top: 0; width: 100%; }
        .print\:hidden { display: none !important; }
    }
</style>
@endsection

