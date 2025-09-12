@extends('layouts.app')

@section('content')
<div class="bg-white shadow-md rounded-lg p-4">
    <div id="report-content">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 pb-3 border-b print:hidden">
            <div>
                <h2 class="text-2xl font-bold text-black">Purchase Order Report</h2>
                <p class="text-sm text-gray-500">A detailed list of all purchase orders.</p>
            </div>
            <button onclick="window.print()" class="mt-3 md:mt-0 px-4 py-2 bg-blue-600 text-white rounded-md text-xs uppercase font-semibold">Print</button>
        </div>
        
        <form action="{{ route('reports.purchase_orders') }}" method="GET" class="mb-4 print:hidden flex flex-wrap items-center gap-4">
            <div>
                <label for="start_date" class="text-sm">From:</label>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="border rounded-md p-1 text-sm">
            </div>
             <div>
                <label for="end_date" class="text-sm">To:</label>
                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="border rounded-md p-1 text-sm">
            </div>
            <div>
                <select name="customer_id" class="border rounded-md p-1.5 text-sm">
                    <option value="">All Customers</option>
                    @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->customer_name }}</option>
                    @endforeach
                </select>
            </div>
             <div>
                <select name="status" class="border rounded-md p-1.5 text-sm">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="invoiced" {{ request('status') == 'invoiced' ? 'selected' : '' }}>Invoiced</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md text-xs uppercase">Filter</button>
             <a href="{{ route('reports.purchase_orders') }}" class="px-4 py-2 bg-gray-200 text-black rounded-md text-xs uppercase">Clear</a>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">PO ID</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Customer</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Delivery Date</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($purchaseOrders as $po)
                    <tr>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-black">{{ $po->po_id }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-black">{{ $po->customer->customer_name ?? 'N/A' }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-black">{{ $po->delivery_date->format('Y-m-d') }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @switch($po->status)
                                    @case('delivered'):
                                    @case('invoiced') 
                                        bg-green-100 text-green-800 @break
                                    @case('processing') bg-blue-100 text-blue-800 @break
                                    @case('cancelled')
                                    @case('rejected') bg-red-100 text-red-800 @break
                                    @default bg-yellow-100 text-yellow-800
                                @endswitch
                            ">
                                {{ ucfirst($po->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-2 text-center text-sm text-gray-500">
                            No purchase orders found for the selected filters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 print:hidden">
            {!! $purchaseOrders->links() !!}
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

