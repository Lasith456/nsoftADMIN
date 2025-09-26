@extends('layouts.app')

@section('content')
<div class="bg-white shadow-md rounded-lg p-6">
    <h2 class="text-xl font-bold mb-4">
        Outstanding Balance for {{ $agent->name }}
    </h2>

    <p class="mb-4 text-gray-700">
        <strong>Total Outstanding:</strong> Rs. {{ number_format($totalOutstanding, 2) }}
    </p>

    <table class="w-full border border-gray-300 rounded mb-6">
        <thead>
            <tr class="bg-gray-100">
                <th class="p-2 border">Invoice ID</th>
                <th class="p-2 border">Amount</th>
                <th class="p-2 border">Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($outstandings as $out)
                <tr>
                    <td class="p-2 border">{{ $out->invoice->invoice_id }}</td>
                    <td class="p-2 border">Rs. {{ number_format($out->amount, 2) }}</td>
                    <td class="p-2 border">{{ $out->created_at->format('Y-m-d') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="p-3 text-center text-gray-500">
                        No outstanding amounts found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <form action="{{ route('agents.outstanding.pay', $agent->id) }}" method="POST" class="flex items-center space-x-3">
        @csrf
        <input type="number" step="0.01" name="amount" placeholder="Enter payment amount"
               class="border rounded px-3 py-2 w-48" required>
        <button type="submit"
                class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded">
            Record Payment
        </button>
    </form>
</div>
@endsection
