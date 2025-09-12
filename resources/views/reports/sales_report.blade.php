@extends('layouts.app')

@section('content')
<div class="bg-white shadow-md rounded-lg p-4">
    <div id="report-content">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 pb-3 border-b print:hidden">
            <div>
                <h2 class="text-2xl font-bold text-black">Sales Report</h2>
                <p class="text-sm text-gray-500">A detailed list of all invoices within a specified date range.</p>
            </div>
            <button onclick="window.print()" class="mt-3 md:mt-0 px-4 py-2 bg-blue-600 text-white rounded-md text-xs uppercase font-semibold">Print</button>
        </div>
        
        <form action="{{ route('reports.sales') }}" method="GET" class="mb-4 print:hidden flex items-center space-x-2">
            <div>
                <label for="start_date" class="text-sm">Start Date:</label>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="border rounded-md p-1 text-sm">
            </div>
             <div>
                <label for="end_date" class="text-sm">End Date:</label>
                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="border rounded-md p-1 text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md text-xs uppercase">Filter</button>
             <a href="{{ route('reports.sales') }}" class="px-4 py-2 bg-gray-200 text-black rounded-md text-xs uppercase">Clear</a>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium uppercase">Date</th>
                        <th class="px-4 py-2 text-left text-xs font-medium uppercase">Invoice ID</th>
                        <th class="px-4 py-2 text-left text-xs font-medium uppercase">Billed To</th>
                        <th class="px-4 py-2 text-right text-xs font-medium uppercase">Amount</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($sales as $invoice)
                    <tr>
                        <td class="px-4 py-2 text-sm">{{ $invoice->created_at->format('Y-m-d') }}</td>
                        <td class="px-4 py-2 text-sm">{{ $invoice->invoice_id }}</td>
                        <td class="px-4 py-2 text-sm">{{ $invoice->invoiceable->customer_name ?? $invoice->invoiceable->supplier_name ?? $invoice->invoiceable->name ?? 'N/A' }}</td>
                        <td class="px-4 py-2 text-sm text-right">{{ number_format($invoice->total_amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center py-4">No sales data found for the selected period.</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 font-bold">
                    <tr>
                        <td colspan="3" class="px-4 py-2 text-right">Total Sales:</td>
                        <td class="px-4 py-2 text-right">{{ number_format($sales->sum('total_amount'), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
         <div class="mt-4 print:hidden">{!! $sales->links() !!}</div>
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

