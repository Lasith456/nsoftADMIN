@extends('layouts.app')

@section('content')
<div class="bg-white shadow-md rounded-lg p-4">
    <div id="report-content">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 pb-3 border-b print:hidden">
            <div>
                <h2 class="text-2xl font-bold text-black">Stock Level Report</h2>
                <p class="text-sm text-gray-500">A real-time overview of all product stock levels.</p>
            </div>
            <button onclick="window.print()" class="mt-3 md:mt-0 px-4 py-2 bg-blue-600 text-white rounded-md text-xs uppercase font-semibold">Print</button>
        </div>
        
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

