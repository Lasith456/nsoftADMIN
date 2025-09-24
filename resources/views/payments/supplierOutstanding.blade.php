@extends('layouts.app')

@section('content')
<div class="bg-white shadow-lg rounded-lg p-6 max-w-6xl mx-auto">
    <h2 class="text-2xl font-bold mb-4">Supplier Outstanding</h2>

    @if($suppliers->isEmpty())
        <p class="text-gray-600">No outstanding invoices found for suppliers.</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-300">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border p-2 text-left">Supplier Name</th>
                        <th class="border p-2 text-left">Supplier Code</th>
                        <th class="border p-2 text-center">Outstanding Invoices</th>
                        <th class="border p-2 text-right">Total Outstanding</th>
                        <th class="border p-2 text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($suppliers as $supplier)
                        <tr class="hover:bg-gray-50">
                            <td class="border p-2">{{ $supplier->supplier_name }}</td>
                            <td class="border p-2">{{ $supplier->supplier_id }}</td>
                            <td class="border p-2 text-center">{{ $supplier->invoices->count() }}</td>
                            <td class="border p-2 text-right">
                                LKR {{ number_format($supplier->invoices->sum(fn($inv) => $inv->total_amount - $inv->amount_paid), 2) }}
                            </td>
                            <td class="border p-2 text-center">
                                <a href="{{ route('payments.createBulkSupplier', $supplier->id) }}"
                                   class="px-3 py-1 bg-green-600 text-white rounded-md text-xs uppercase font-semibold hover:bg-green-700">
                                    Pay Now
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
