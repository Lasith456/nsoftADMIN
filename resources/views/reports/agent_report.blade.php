@extends('layouts.app')

@section('content')
<div class="bg-white shadow-md rounded-lg p-4">
    <div id="report-content">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 pb-3 border-b print:hidden">
            <div>
                <h2 class="text-2xl font-bold text-black">Agent Report</h2>
                <p class="text-sm text-gray-500">A summary of each agent's activity.</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('reports.agents.export.excel', request()->query()) }}" 
                   class="px-4 py-2 bg-green-600 text-white rounded-md text-xs uppercase font-semibold">Export Excel</a>
                <a href="{{ route('reports.agents.export.pdf', request()->query()) }}" 
                   class="px-4 py-2 bg-red-600 text-white rounded-md text-xs uppercase font-semibold">Export PDF</a>
                <button onclick="window.print()" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-md text-xs uppercase font-semibold">Print</button>
            </div>
        </div>

        <!-- Filters -->
        <form action="{{ route('reports.agents') }}" method="GET" class="mb-4 print:hidden flex items-center space-x-2">
            <div>
                <label for="start_date" class="text-sm">Start Date:</label>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="border rounded-md p-1 text-sm">
            </div>
            <div>
                <label for="end_date" class="text-sm">End Date:</label>
                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="border rounded-md p-1 text-sm">
            </div>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by agent name..." class="border rounded-md p-1 text-sm w-64">
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md text-xs uppercase">Filter</button>
            <a href="{{ route('reports.agents') }}" class="px-4 py-2 bg-gray-200 text-black rounded-md text-xs uppercase">Clear</a>
        </form>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Agent Name</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-black uppercase tracking-wider">Deliveries Fulfilled</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-black uppercase tracking-wider">Total Payout Value (LKR)</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($agents as $agent)
                    <tr>
                        <td class="px-4 py-2 text-sm">{{ $agent->name }}</td>
                        <td class="px-4 py-2 text-sm text-right">{{ $agent->delivery_items_count }}</td>
                        <td class="px-4 py-2 text-sm text-right">{{ number_format($agent->invoices_sum_total_amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-4 py-2 text-center text-sm text-gray-500">
                            No agents found for the specified search criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50 font-bold">
                    <tr>
                        <td colspan="2" class="px-4 py-2 text-right">Total Payout Value (All Agents):</td>
                        <td class="px-4 py-2 text-right">{{ number_format($totalInvoices, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-4 print:hidden">
            {!! $agents->links() !!}
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
