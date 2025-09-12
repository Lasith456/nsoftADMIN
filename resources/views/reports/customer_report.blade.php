@extends('layouts.app')

@section('content')
<div class="bg-white shadow-md rounded-lg p-4">
    <div id="report-content">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 pb-3 border-b print:hidden">
            <div>
                <h2 class="text-2xl font-bold text-black">Customer Report</h2>
                <p class="text-sm text-gray-500">A summary of each customer's activity.</p>
            </div>
            <button onclick="window.print()" class="mt-3 md:mt-0 px-4 py-2 bg-blue-600 text-white rounded-md text-xs uppercase font-semibold">Print</button>
        </div>
        
        <form action="{{ route('reports.customers') }}" method="GET" class="mb-4 print:hidden flex items-center space-x-2">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by customer name..." class="border rounded-md p-1 text-sm w-64">
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md text-xs uppercase">Filter</button>
            <a href="{{ route('reports.customers') }}" class="px-4 py-2 bg-gray-200 text-black rounded-md text-xs uppercase">Clear</a>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Customer Name</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-black uppercase tracking-wider">Total POs</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-black uppercase tracking-wider">Total Invoiced Value (LKR)</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($customers as $customer)
                    <tr>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-black">{{ $customer->customer_name }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-black text-right">{{ $customer->purchase_orders_count }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-black text-right">{{ number_format($customer->invoices_sum_total_amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-4 py-2 text-center text-sm text-gray-500">
                            No customers found for the specified search criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 print:hidden">
            {!! $customers->links() !!}
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

