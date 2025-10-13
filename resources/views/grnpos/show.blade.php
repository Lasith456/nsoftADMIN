@extends('layouts.app')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4">
    <h2 class="text-2xl font-bold mb-3 text-gray-900 dark:text-gray-200">
        GRN PO Details – {{ $grnpo->grnpo_id }}
    </h2>

    <p><strong>Supplier:</strong> {{ $grnpo->supplier->supplier_name ?? 'N/A' }}</p>
    <p><strong>Delivery Date:</strong> {{ $grnpo->delivery_date }}</p>
    <p><strong>Status:</strong> {{ ucfirst($grnpo->status) }}</p>

    <h3 class="text-lg font-semibold mt-4 mb-2">Items</h3>
    <table class="w-full border-collapse border text-sm">
        <thead class="bg-gray-100 dark:bg-gray-700">
            <tr>
                <th class="border p-2">Department</th>
                <th class="border p-2">Product</th>
                <th class="border p-2">Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($grnpo->items as $item)
                <tr>
                    <td class="border p-2">{{ $item->department->name ?? 'N/A' }}</td>
                    <td class="border p-2">{{ $item->product->name ?? 'N/A' }}</td>
                    <td class="border p-2">{{ $item->quantity }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <a href="{{ route('grnpos.index') }}"
       class="inline-block mt-4 px-4 py-2 bg-gray-700 text-white rounded hover:bg-gray-800">
       Back to List
    </a>
</div>
@endsection
