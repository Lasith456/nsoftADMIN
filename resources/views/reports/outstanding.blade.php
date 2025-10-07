@extends('layouts.app')

@section('content')
<div class="bg-white shadow-md rounded-lg p-4">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 border-b border-gray-200 pb-3">
        <div>
            <h2 class="text-2xl font-bold text-black">Outstanding Payments</h2>
            <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
                <ol class="list-none p-0 inline-flex">
                    <li class="flex items-center">
                        <a href="{{ route('home') }}" class="hover:underline text-black">Dashboard</a>
                        <svg class="fill-current w-3 h-3 mx-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                            <path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667
                                     c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 
                                     34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667
                                     c9.373-9.373 24.569 9.373 33.941 0L285.475 239.03
                                     c9.373 9.372 9.373 24.568.001 33.941z"/>
                        </svg>
                    </li>
                    <li class="text-black">Outstanding Payments</li>
                </ol>
            </nav>
        </div>
        <button onclick="printTable()" class="mt-3 md:mt-0 inline-flex items-center px-3 py-1 bg-green-600 text-white rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-green-700 active:bg-green-900 print:hidden">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18h12v4H6v-4zM6 14h12v2H6v-2z"/>
            </svg>
            Print
        </button>
    </div>

    {{-- Filter Form --}}
<form action="{{ route('reports.outstanding') }}" method="GET" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-3 print:hidden">
    <div>
        <label for="type" class="block text-xs font-medium text-black">Type</label>
        <select name="type" id="type" class="w-full border border-gray-300 rounded-md px-2 py-1 text-xs text-black">
            <option value="all" {{ $type == 'all' ? 'selected' : '' }}>All</option>
            <option value="customer" {{ $type == 'customer' ? 'selected' : '' }}>Customer</option>
            <option value="supplier" {{ $type == 'supplier' ? 'selected' : '' }}>Supplier</option>
            <option value="agent" {{ $type == 'agent' ? 'selected' : '' }}>Agent</option>
        </select>
    </div>
    <div>
        <label for="from" class="block text-xs font-medium text-black">From</label>
        <input type="date" name="from" id="from" value="{{ $from }}" class="w-full border border-gray-300 rounded-md px-2 py-1 text-xs text-black">
    </div>
    <div>
        <label for="to" class="block text-xs font-medium text-black">To</label>
        <input type="date" name="to" id="to" value="{{ $to }}" class="w-full border border-gray-300 rounded-md px-2 py-1 text-xs text-black">
    </div>
    <div class="flex items-end space-x-2">
        <button type="submit" class="px-3 py-1 bg-blue-600 text-white rounded-md text-xs">Filter</button>
        <a href="{{ route('reports.outstanding.export.excel', request()->all()) }}" 
           class="px-3 py-1 bg-green-600 text-white rounded-md text-xs">Export Excel</a>
        <a href="{{ route('reports.outstanding.export.pdf', request()->all()) }}" 
           class="px-3 py-1 bg-red-600 text-white rounded-md text-xs">Export PDF</a>
    </div>
</form>


    {{-- Outstanding Payments Table --}}
    <div class="overflow-x-auto" id="printArea">
        <table class="w-full min-w-full divide-y divide-gray-200 border">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Invoice ID</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Receipt ID</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Type</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Name</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Date</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-black uppercase tracking-wider">Total</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-black uppercase tracking-wider">Paid</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-black uppercase tracking-wider">Outstanding</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($invoices as $row)
                    <tr>
                        <td class="px-3 py-2 text-sm font-medium text-blue-600 hover:underline">
                            <a href="{{ route('invoices.show', $row['inv_id']) }}">
                                {{ $row['invoice_id'] }}
                            </a>
                        </td>
                        <td class="px-3 py-2 text-sm">
                            @if($row['receipt_id'])
                                <a href="{{ route('payments.receipt', $row['receipt_id']) }}" class="text-purple-600 hover:underline">
                                    {{ $row['receipt_id'] }}
                                </a>
                            @else
                                <span class="text-gray-500">—</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-sm text-black">{{ $row['type'] }}</td>
                        <td class="px-3 py-2 text-sm text-black">{{ $row['name'] }}</td>
                        <td class="px-3 py-2 text-sm text-black">{{ $row['date'] }}</td>
                        <td class="px-3 py-2 text-sm text-right text-black">{{ number_format($row['total'], 2) }}</td>
                        <td class="px-3 py-2 text-sm text-right text-green-600">{{ number_format($row['paid'], 2) }}</td>
                        <td class="px-3 py-2 text-sm text-right font-bold text-red-600">{{ number_format($row['outstanding'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-3 py-2 text-sm text-center text-gray-500">No outstanding payments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Print JS --}}
<script>
function printTable() {
    var printContent = document.getElementById('printArea').innerHTML;
    var originalContent = document.body.innerHTML;
    document.body.innerHTML = printContent;
    window.print();
    document.body.innerHTML = originalContent;
    location.reload(); // reload to restore events
}
</script>
@endsection
