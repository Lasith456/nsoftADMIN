@extends('layouts.app')

@section('content')
<div class="bg-white shadow-md rounded-lg p-4">
    <div id="report-content">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 pb-3 border-b print:hidden">
            <div>
                <h2 class="text-2xl font-bold text-black">Receive Note Report</h2>
                <p class="text-sm text-gray-500">A detailed list of all receive notes.</p>
            </div>
            <div class="flex space-x-2 mt-2 md:mt-0">
                <a href="{{ route('reports.receive_notes.export.excel', request()->query()) }}" 
                   class="px-3 py-1.5 bg-green-600 text-white rounded-md text-xs uppercase font-semibold hover:bg-green-700">Excel</a>
                <a href="{{ route('reports.receive_notes.export.pdf', request()->query()) }}" 
                   class="px-3 py-1.5 bg-red-600 text-white rounded-md text-xs uppercase font-semibold hover:bg-red-700">PDF</a>
                <button onclick="window.print()" 
                        class="px-3 py-1.5 bg-blue-600 text-white rounded-md text-xs uppercase font-semibold hover:bg-blue-700">Print</button>
            </div>
        </div>

        <!-- Filter Form -->
        <form action="{{ route('reports.receive_notes') }}" method="GET" class="mb-4 print:hidden flex flex-wrap items-center gap-3">
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

            <div>
                <label for="status" class="text-sm">Status:</label>
                <select name="status" id="status" class="border rounded-md p-1.5 text-sm">
                    <option value="">All Statuses</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="discrepancy" {{ request('status') == 'discrepancy' ? 'selected' : '' }}>Discrepancy</option>
                    <option value="invoiced" {{ request('status') == 'invoiced' ? 'selected' : '' }}>Invoiced</option>
                </select>
            </div>

            <button type="submit" 
                class="px-3 py-1.5 bg-gray-800 text-white rounded-md text-xs uppercase hover:bg-gray-700">
                Filter
            </button>
            <a href="{{ route('reports.receive_notes') }}" 
               class="px-3 py-1.5 bg-gray-200 text-black rounded-md text-xs uppercase hover:bg-gray-300">
                Clear
            </a>
        </form>

        <!-- Report Table -->
        <div class="overflow-x-auto">
            <table class="w-full min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">RN ID</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Associated DN(s)</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Assigned PO(s)</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Company</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Received Date</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($receiveNotes as $rn)
                        @php
                            $dnList = $rn->deliveryNotes->pluck('delivery_note_id')->implode(', ');
                            $poList = $rn->deliveryNotes
                                ->flatMap(function($dn) {
                                    return $dn->purchaseOrders->pluck('purchase_order_id')
                                        ->merge($dn->purchaseOrders->pluck('po_id'));
                                })
                                ->filter()
                                ->unique()
                                ->implode(', ');

                            $companyName = $rn->deliveryNotes
                                ->flatMap(fn($dn) => $dn->purchaseOrders->pluck('customer.company.company_name'))
                                ->filter()
                                ->unique()
                                ->implode(', ');
                        @endphp

                        <tr>
                            <td class="px-3 py-2 text-sm text-black">{{ $rn->receive_note_id }}</td>
                            <td class="px-3 py-2 text-sm text-black">{{ $dnList ?: 'N/A' }}</td>
                            <td class="px-3 py-2 text-sm text-black">{{ $poList ?: 'N/A' }}</td>
                            <td class="px-3 py-2 text-sm text-black">{{ $companyName ?: 'N/A' }}</td>
                            <td class="px-3 py-2 text-sm text-black">{{ $rn->received_date->format('Y-m-d') }}</td>
                            <td class="px-3 py-2 text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @switch($rn->status)
                                        @case('completed') bg-green-100 text-green-800 @break
                                        @case('invoiced') bg-indigo-100 text-indigo-800 @break
                                        @case('discrepancy') bg-orange-100 text-orange-800 @break
                                        @default bg-gray-100 text-gray-800
                                    @endswitch">
                                    {{ ucfirst($rn->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-2 text-center text-sm text-gray-500">
                                No receive notes found for the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4 print:hidden">
            {!! $receiveNotes->links() !!}
        </div>
    </div>
</div>

<!-- Print Styling -->
<style>
    @media print {
        body * { visibility: hidden; }
        #report-content, #report-content * { visibility: visible; }
        #report-content { position: absolute; left: 0; top: 0; width: 100%; }
        .print\:hidden { display: none !important; }
    }
</style>
@endsection
