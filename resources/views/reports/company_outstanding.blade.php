@extends('layouts.app')

@section('content')
<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4">Customer Outstanding Report</h2>

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <select name="company_id" class="border p-2 rounded">
            <option value="">All Companies</option>
            @foreach($companies as $c)
                <option value="{{ $c->id }}" {{ $selectedCompany && $selectedCompany->id == $c->id ? 'selected' : '' }}>
                    {{ $c->company_name }}
                </option>
            @endforeach
        </select>

        <input type="date" name="start_date" value="{{ $startDate }}" class="border p-2 rounded">
        <input type="date" name="end_date" value="{{ $endDate }}" class="border p-2 rounded">
        <button class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button>
    </form>

    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-gray-100">
                <th class="border p-2 text-left">Company</th>
                <th class="border p-2 text-left">Customer</th>
                <th class="border p-2 text-right">Total</th>
                <th class="border p-2 text-right">Paid</th>
                <th class="border p-2 text-right">Outstanding</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData as $row)
                <tr>
                    <td class="border p-2">{{ $row['company'] }}</td>
                    <td class="border p-2">{{ $row['customer'] }}</td>
                    <td class="border p-2 text-right">{{ number_format($row['total'], 2) }}</td>
                    <td class="border p-2 text-right">{{ number_format($row['paid'], 2) }}</td>
                    <td class="border p-2 text-right font-semibold text-red-600">{{ number_format($row['outstanding'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center p-3">No records found</td></tr>
            @endforelse
        </tbody>
    </table>

    @if(!empty($totals))
        <div class="mt-4 text-right">
            <p><strong>Total Invoiced:</strong> {{ number_format($totals['totalSum'], 2) }}</p>
            <p><strong>Total Paid:</strong> {{ number_format($totals['paidSum'], 2) }}</p>
            <p><strong>Total Outstanding:</strong> <span class="text-red-600 font-bold">{{ number_format($totals['outstandingSum'], 2) }}</span></p>
        </div>
    @endif

    <div class="mt-4 flex space-x-2">
        <a href="{{ route('reports.companyOutstanding.exportExcel', request()->query()) }}" class="bg-green-600 text-white px-4 py-2 rounded">Export Excel</a>
        <a href="{{ route('reports.companyOutstanding.exportPdf', request()->query()) }}" class="bg-red-600 text-white px-4 py-2 rounded">Export PDF</a>
    </div>
</div>
@endsection
