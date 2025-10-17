@extends('layouts.app')

@section('content')

<div class="bg-white shadow-md rounded-lg p-4">
    <div class="flex justify-between items-center mb-4 border-b pb-3">
        <div>
            <h2 class="text-2xl font-bold text-black">{{ $company->company_name }} - Department Wise Report</h2>
            <p class="text-sm text-gray-500">Shows VAT and Non-VAT sales department wise for each customer.</p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('reports.company.export.excel', request()->query()) }}" 
               class="px-3 py-1 bg-green-600 text-white rounded-md text-xs">Export Excel</a>
            <a href="{{ route('reports.company.export.pdf', request()->query()) }}" 
               class="px-3 py-1 bg-red-600 text-white rounded-md text-xs">Export PDF</a>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('reports.company') }}" class="flex space-x-3 mb-4">
        <input type="hidden" name="company_id" value="{{ $company->id }}">
        <div>
            <label class="text-sm">From:</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="border p-1 text-sm rounded">
        </div>
        <div>
            <label class="text-sm">To:</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="border p-1 text-sm rounded">
        </div>
        <button type="submit" class="px-3 py-1 bg-blue-600 text-white rounded-md text-xs">Filter</button>
                {{-- Clear Filter Button --}}
        <a href="{{ route('reports.company', ['company_id' => $company->id]) }}" 
           class="px-3 py-1 bg-gray-300 text-black rounded-md text-xs">Clear</a>
    </form>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full border divide-y">
            @php use Illuminate\Support\Str; @endphp
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-3 py-2 text-left text-xs">Customer</th>
                    <th class="px-3 py-2 text-left text-xs">Department</th>
                    <th class="px-3 py-2 text-right text-xs">Amount (LKR)</th>
                    <th class="px-3 py-2 text-right text-xs">VAT (LKR)</th>
                    <th class="px-3 py-2 text-right text-xs">Total Amount (LKR)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData as $row)
                    @php $firstRow = true; @endphp
                    @foreach($row['departmentWise'] as $dept => $values)
                        <tr>
                            @if($firstRow)
                                <td class="px-3 py-2 text-sm font-bold align-top" rowspan="{{ count($row['departmentWise']) }}">
                                    {{ $row['customer'] }}
                                </td>
                                @php $firstRow = false; @endphp
                            @endif
                            <td class="px-3 py-2 text-sm">{{ $dept }}</td>
                            <td class="px-3 py-2 text-sm text-right">{{ number_format($values['amount'], 2) }}</td>
                            <td class="px-3 py-2 text-sm text-right">
                                @if(Str::contains($dept, '(VAT)'))
                                    {{ number_format($values['vat'], 2) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-3 py-2 text-sm text-right">{{ number_format($values['total'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="bg-gray-50 font-bold">
                        <td colspan="4" class="px-3 py-2 text-sm text-right">Total for {{ $row['customer'] }}</td>
                        <td class="px-3 py-2 text-sm text-right">{{ number_format($row['total'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>

        </table>
    </div>
</div>
@endsection
