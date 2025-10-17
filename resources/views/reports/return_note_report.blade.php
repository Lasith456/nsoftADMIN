@extends('layouts.app')

@section('content')
<div class="bg-white shadow-md rounded-lg p-4">
    <h2 class="text-2xl font-bold mb-4">Return Note Report</h2>

    {{-- Filters --}}
    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
        <select name="company_id" class="border rounded-md p-2">
            <option value="">All Companies</option>
            @foreach($companies as $company)
                <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                    {{ $company->company_name }}
                </option>
            @endforeach
        </select>

        <select name="customer_id" class="border rounded-md p-2">
            <option value="">All Customers</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                    {{ $customer->customer_name }}
                </option>
            @endforeach
        </select>

        <select name="status" class="border rounded-md p-2">
            <option value="">All Status</option>
            <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Pending</option>
            <option value="processed" {{ request('status')=='processed' ? 'selected' : '' }}>Processed</option>
            <option value="ignored" {{ request('status')=='ignored' ? 'selected' : '' }}>Ignored</option>
        </select>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md">Filter</button>
    </form>

    {{-- Export Buttons --}}
    <div class="flex justify-end mb-3">
        <a href="{{ route('reports.returnnotes.export.excel', request()->query()) }}" class="px-4 py-2 bg-green-600 text-white rounded-md mr-2">Export Excel</a>
        <a href="{{ route('reports.returnnotes.export.pdf', request()->query()) }}" class="px-4 py-2 bg-red-600 text-white rounded-md">Export PDF</a>
    </div>

    {{-- Table --}}
    <table class="w-full border-collapse border">
        <thead class="bg-gray-100">
            <tr>
                <th class="border p-2">Return Note ID</th>
                <th class="border p-2">Company</th>
                <th class="border p-2">Customer</th>
                <th class="border p-2">Agent</th>
                <th class="border p-2">Product</th>
                <th class="border p-2">Qty</th>
                <th class="border p-2">Reason</th>
                <th class="border p-2">Return Date</th>
                <th class="border p-2">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($returnNotes as $rn)
                <tr>
                    <td class="border p-2">{{ $rn->return_note_id }}</td>
                    <td class="border p-2">{{ $rn->company->company_name ?? '-' }}</td>
                    <td class="border p-2">{{ $rn->customer->customer_name ?? '-' }}</td>
                    <td class="border p-2">{{ $rn->agent->name ?? '-' }}</td>
                    <td class="border p-2">{{ $rn->product->name ?? '-' }}</td>
                    <td class="border p-2 text-center">{{ $rn->quantity }}</td>
                    <td class="border p-2">{{ $rn->reason ?? '-' }}</td>
                    <td class="border p-2">{{ $rn->return_date?->format('Y-m-d') ?? '-' }}</td>
                    <td class="border p-2">{{ ucfirst($rn->status) }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center p-3">No records found</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">{{ $returnNotes->links() }}</div>
</div>
@endsection
