@extends('layouts.app')

@section('content')
<div class="bg-white shadow-md rounded-lg p-4">
    <div id="report-content">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 pb-3 border-b print:hidden">
            <div>
                <h2 class="text-2xl font-bold text-black">Receive Note Report</h2>
                <p class="text-sm text-gray-500">A detailed list of all receive notes.</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('reports.receive_notes.export.excel', request()->query()) }}" 
                   class="px-4 py-2 bg-green-600 text-white rounded-md text-xs uppercase font-semibold">Export Excel</a>
                <a href="{{ route('reports.receive_notes.export.pdf', request()->query()) }}" 
                   class="px-4 py-2 bg-red-600 text-white rounded-md text-xs uppercase font-semibold">Export PDF</a>
                <button onclick="window.print()" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-md text-xs uppercase font-semibold">Print</button>
            </div>
        </div>
        
        <form action="{{ route('reports.receive_notes') }}" method="GET" class="mb-4 print:hidden flex flex-wrap items-center gap-4">
            <div>
                <label for="start_date" class="text-sm">From:</label>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="border rounded-md p-1 text-sm">
            </div>
             <div>
                <label for="end_date" class="text-sm">To:</label>
                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="border rounded-md p-1 text-sm">
            </div>
            <div>
                <select name="status" class="border rounded-md p-1.5 text-sm">
                    <option value="">All Statuses</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="discrepancy" {{ request('status') == 'discrepancy' ? 'selected' : '' }}>Discrepancy</option>
                    <option value="invoiced" {{ request('status') == 'invoiced' ? 'selected' : '' }}>Invoiced</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md text-xs uppercase">Filter</button>
             <a href="{{ route('reports.receive_notes') }}" class="px-4 py-2 bg-gray-200 text-black rounded-md text-xs uppercase">Clear</a>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">RN ID</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Associated DN(s)</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Assigned PO(s)</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Received Date</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($receiveNotes as $rn)
                    <tr>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-black">{{ $rn->receive_note_id }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-black">
                             @foreach($rn->deliveryNotes as $dn)
                                {{ $dn->delivery_note_id }}@if(!$loop->last), @endif
                             @endforeach
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-black">
                             @foreach($rn->deliveryNotes as $dn)
                                 @foreach($dn->purchaseOrders as $po)
                                {{ $rn->deliveryNotes->flatMap->purchaseOrders->pluck('po_id')->implode(', ') ?: 'N/A' }}
                                 @endforeach
                             @endforeach
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-black">{{ $rn->received_date->format('Y-m-d') }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @switch($rn->status)
                                    @case('completed') bg-green-100 text-green-800 @break
                                    @case('invoiced') bg-indigo-100 text-indigo-800 @break
                                    @case('discrepancy') bg-orange-100 text-orange-800 @break
                                @endswitch
                            ">
                                {{ ucfirst($rn->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-2 text-center text-sm text-gray-500">
                            No receive notes found for the selected filters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 print:hidden">
            {!! $receiveNotes->links() !!}
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
