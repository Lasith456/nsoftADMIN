@extends('layouts.app')

@section('content')
<div class="bg-white shadow-md rounded-lg p-4">
    <div id="report-content">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 pb-3 border-b print:hidden">
            <div>
                <h2 class="text-2xl font-bold text-black">Purchase Order Report</h2>
                <p class="text-sm text-gray-500">A detailed list of all purchase orders.</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('reports.purchase_orders.export.excel', request()->query()) }}" 
                   class="px-4 py-2 bg-green-600 text-white rounded-md text-xs uppercase font-semibold">Export Excel</a>
                <a href="{{ route('reports.purchase_orders.export.pdf', request()->query()) }}" 
                   class="px-4 py-2 bg-red-600 text-white rounded-md text-xs uppercase font-semibold">Export PDF</a>
                <button onclick="window.print()" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-md text-xs uppercase font-semibold">Print</button>
            </div>
        </div>
        
        <!-- Filters -->
        <form action="{{ route('reports.purchase_orders') }}" method="GET" 
              class="mb-4 print:hidden flex flex-wrap items-center gap-3">
            
            <div>
                <label for="start_date" class="text-sm">From:</label>
                <input type="date" name="start_date" id="start_date" 
                       value="{{ request('start_date') }}" 
                       class="border rounded-md p-1 text-sm">
            </div>

            <div>
                <label for="end_date" class="text-sm">To:</label>
                <input type="date" name="end_date" id="end_date" 
                       value="{{ request('end_date') }}" 
                       class="border rounded-md p-1 text-sm">
            </div>

            <!-- Company Filter -->
            <div>
                <label for="company_id" class="text-sm">Company:</label>
                <select name="company_id" id="company_id" class="border rounded-md p-1.5 text-sm">
                    <option value="">All Companies</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                            {{ $company->company_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Customer Filter -->
            <div>
                <label for="customer_id" class="text-sm">Customer:</label>
                <select name="customer_id" id="customer_id" class="border rounded-md p-1.5 text-sm">
                    <option value="">All Customers</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                            {{ $customer->customer_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label for="status" class="text-sm">Status:</label>
                <select name="status" id="status" class="border rounded-md p-1.5 text-sm">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="invoiced" {{ request('status') == 'invoiced' ? 'selected' : '' }}>Invoiced</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <button type="submit" 
                    class="px-3 py-1.5 bg-gray-800 text-white rounded-md text-xs uppercase hover:bg-gray-700">
                Filter
            </button>
            <a href="{{ route('reports.purchase_orders') }}" 
               class="px-3 py-1.5 bg-gray-200 text-black rounded-md text-xs uppercase hover:bg-gray-300">
                Clear
            </a>
        </form>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-black uppercase">PO ID</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-black uppercase">Company</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-black uppercase">Customer</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-black uppercase">Delivery Date</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-black uppercase">Products</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-black uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($purchaseOrders as $po)
                        <tr>
                            <td class="px-3 py-2 text-sm text-black">{{ $po->po_id }}</td>
                            <td class="px-3 py-2 text-sm text-black">
                                {{ $po->customer->company->company_name ?? 'N/A' }}
                            </td>
                            <td class="px-3 py-2 text-sm text-black">{{ $po->customer->customer_name ?? 'N/A' }}</td>
                            <td class="px-3 py-2 text-sm text-black">{{ $po->delivery_date->format('Y-m-d') }}</td>
                            <td class="px-3 py-2 text-sm text-black">
                                {{ $po->items->pluck('product.name')->implode(', ') ?: 'N/A' }}
                            </td>
                            <td class="px-3 py-2 text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @switch($po->status)
                                        @case('delivered')
                                        @case('invoiced') bg-green-100 text-green-800 @break
                                        @case('processing') bg-blue-100 text-blue-800 @break
                                        @case('cancelled')
                                        @case('rejected') bg-red-100 text-red-800 @break
                                        @default bg-yellow-100 text-yellow-800
                                    @endswitch">
                                    {{ ucfirst($po->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-2 text-center text-sm text-gray-500">
                                No purchase orders found for the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4 print:hidden">
            {!! $purchaseOrders->links() !!}
        </div>
    </div>
</div>

<!-- Print Styles -->
<style>
    @media print {
        body * { visibility: hidden; }
        #report-content, #report-content * { visibility: visible; }
        #report-content { position: absolute; left: 0; top: 0; width: 100%; }
        .print\:hidden { display: none !important; }
    }
</style>
@endsection
