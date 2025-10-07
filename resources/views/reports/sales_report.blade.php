@extends('layouts.app')

@section('content')
<div class="bg-white shadow-md rounded-lg p-4">
    <div id="report-content">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 pb-3 border-b print:hidden">
            <div>
                <h2 class="text-2xl font-bold text-black">Sales Report</h2>
                <p class="text-sm text-gray-500">A detailed list of all invoices within a specified date range.</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('reports.sales.export.excel', request()->query()) }}" 
                class="px-4 py-2 bg-green-600 text-white rounded-md text-xs uppercase font-semibold">Export Excel</a>
                <a href="{{ route('reports.sales.export.pdf', request()->query()) }}" 
                class="px-4 py-2 bg-red-600 text-white rounded-md text-xs uppercase font-semibold">Export PDF</a>
                <button onclick="window.print()" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-md text-xs uppercase font-semibold">Print</button>
            </div>        
        </div>
        
        <form action="{{ route('reports.sales') }}" method="GET" 
            x-data="{ type: '{{ request('type', 'all') }}' }" 
            class="mb-4 print:hidden flex items-center space-x-2">

            <!-- Date Filters -->
            <div>
                <label for="start_date" class="text-sm">Start Date:</label>
                <input type="date" name="start_date" id="start_date" 
                    value="{{ request('start_date') }}" class="border rounded-md p-1 text-sm">
            </div>
            <div>
                <label for="end_date" class="text-sm">End Date:</label>
                <input type="date" name="end_date" id="end_date" 
                    value="{{ request('end_date') }}" class="border rounded-md p-1 text-sm">
            </div>

            <!-- Type Select -->
            <div>
                <label for="type" class="text-sm">Invoice Type:</label>
                <select name="type" id="type" x-model="type" class="border rounded-md p-1 text-sm">
                    <option value="all">All Invoices</option>
                    <option value="customer">Customer Invoices</option>
                    <option value="agent">Agent Invoices</option>
                    <option value="supplier">Supplier Invoices</option>
                </select>
            </div>

            <!-- Customer Select -->
            <div x-show="type === 'customer'">
                <label for="customer_id" class="text-sm">Customer:</label>
                <select name="customer_id" id="customer_id" class="border rounded-md p-1 text-sm">
                    <option value="">-- All Customers --</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" 
                            {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                            {{ $customer->customer_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Agent Select -->
            <div x-show="type === 'agent'">
                <label for="agent_id" class="text-sm">Agent:</label>
                <select name="agent_id" id="agent_id" class="border rounded-md p-1 text-sm">
                    <option value="">-- All Agents --</option>
                    @foreach ($agents as $agent)
                        <option value="{{ $agent->id }}" 
                            {{ request('agent_id') == $agent->id ? 'selected' : '' }}>
                            {{ $agent->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Supplier Select -->
            <div x-show="type === 'supplier'">
                <label for="supplier_id" class="text-sm">Supplier:</label>
                <select name="supplier_id" id="supplier_id" class="border rounded-md p-1 text-sm">
                    <option value="">-- All Suppliers --</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" 
                            {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->supplier_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Buttons -->
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md text-xs uppercase">Filter</button>
            <a href="{{ route('reports.sales') }}" 
            class="px-4 py-2 bg-gray-200 text-black rounded-md text-xs uppercase">Clear</a>
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

