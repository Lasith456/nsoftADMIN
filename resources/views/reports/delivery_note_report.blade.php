@extends('layouts.app')

@section('content')
<div class="bg-white shadow-md rounded-lg p-4">
    <div id="report-content">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 pb-3 border-b print:hidden">
            <div>
                <h2 class="text-2xl font-bold text-black">Delivery Note Report</h2>
                <p class="text-sm text-gray-500">A detailed list of all delivery notes.</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('reports.delivery_notes.export.excel', request()->query()) }}" 
                   class="px-3 py-1.5 bg-green-600 text-white rounded-md text-xs uppercase font-semibold">Excel</a>
                <a href="{{ route('reports.delivery_notes.export.pdf', request()->query()) }}" 
                   class="px-3 py-1.5 bg-red-600 text-white rounded-md text-xs uppercase font-semibold">PDF</a>
                <button onclick="window.print()" 
                        class="px-3 py-1.5 bg-blue-600 text-white rounded-md text-xs uppercase font-semibold">Print</button>
            </div>
        </div>

        <form action="{{ route('reports.delivery_notes') }}" method="GET" class="mb-4 print:hidden flex flex-wrap items-center gap-3">
            <div>
                <label for="company_id" class="text-sm">Company:</label>
                <select name="company_id" id="company_id" class="border rounded-md p-1 text-sm">
                    <option value="">All Companies</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                            {{ $company->company_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="start_date" class="text-sm">From:</label>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="border rounded-md p-1 text-sm">
            </div>

            <div>
                <label for="end_date" class="text-sm">To:</label>
                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="border rounded-md p-1 text-sm">
            </div>

            <div>
                <select name="status" class="border rounded-md p-1 text-sm">
                    <option value="">All Statuses</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                    <option value="invoiced" {{ request('status') == 'invoiced' ? 'selected' : '' }}>Invoiced</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <button type="submit" class="px-3 py-1.5 bg-gray-800 text-white rounded-md text-xs uppercase">Filter</button>
            <a href="{{ route('reports.delivery_notes') }}" class="px-3 py-1.5 bg-gray-200 text-black rounded-md text-xs uppercase">Clear</a>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">DN ID</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Company</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Vehicle</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Driver</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Contact No</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Delivery Date</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($deliveryNotes as $dn)
                        <tr>
                            <td class="px-3 py-2 text-sm text-black">{{ $dn->delivery_note_id }}</td>
                            <td class="px-3 py-2 text-sm text-black">{{ $dn->company->company_name ?? 'N/A' }}</td>
                            <td class="px-3 py-2 text-sm text-black">{{ $dn->vehicle->vehicle_no ?? 'N/A' }}</td>
                            <td class="px-3 py-2 text-sm text-black">{{ $dn->driver_name ?? 'N/A' }}</td>
                            <td class="px-3 py-2 text-sm text-black">{{ $dn->driver_mobile ?? 'N/A' }}</td>
                            <td class="px-3 py-2 text-sm text-black">{{ $dn->delivery_date->format('Y-m-d') }}</td>
                            <td class="px-3 py-2 text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @switch($dn->status)
                                        @case('delivered') bg-green-100 text-green-800 @break
                                        @case('received') bg-teal-100 text-teal-800 @break
                                        @case('invoiced') bg-indigo-100 text-indigo-800 @break
                                        @case('processing') bg-blue-100 text-blue-800 @break
                                        @case('cancelled') bg-red-100 text-red-800 @break
                                        @default bg-yellow-100 text-yellow-800
                                    @endswitch">
                                    {{ ucfirst($dn->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-2 text-center text-sm text-gray-500">
                                No delivery notes found for selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3 print:hidden">
            {!! $deliveryNotes->links() !!}
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
