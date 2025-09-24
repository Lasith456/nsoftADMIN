@extends('layouts.app')

@section('content')
<div class="bg-white shadow-lg rounded-lg p-6 max-w-5xl mx-auto">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold">Payment History - {{ $customer->customer_name }}</h2>
        <a href="{{ route('customers.index') }}" 
           class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
            ← Back
        </a>
    </div>

    @if($paymentsByBatch->isEmpty())
        <p class="text-gray-600">No payments found for this customer.</p>
    @else
        <div class="space-y-6">
            @foreach($paymentsByBatch as $batchId => $batchPayments)
                <div class="border rounded-lg p-4 shadow-sm">
                    <div class="flex justify-between items-center mb-3">
                        <div>
                            <p class="font-bold text-gray-700">Receipt #: {{ $batchId }}</p>
                            <p class="text-sm text-gray-500">Date: {{ $batchPayments->first()->payment_date->format('Y-m-d') }}</p>
                            <p class="text-sm text-gray-500">Method: {{ $batchPayments->first()->payment_method }}</p>
                            @if($batchPayments->first()->payment_method === 'Cheque')
                                <p class="text-sm text-gray-500">Cheque #: {{ $batchPayments->first()->cheque_number }}</p>
                                <p class="text-sm text-gray-500">Bank: {{ $batchPayments->first()->bank?->name }}</p>
                            @endif
                        </div>

                        {{-- ✅ Only show if batch_id exists --}}
                        @if($batchId)
                            <a href="{{ route('payments.receipt', $batchId) }}" target="_blank"
                               class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                                View Receipt
                            </a>
                        @else
                            <span class="text-gray-400 italic">No Receipt Available</span>
                        @endif
                    </div>

                    <table class="w-full border mt-4">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border p-2">Date</th>
                                <th class="border p-2">Method</th>
                                <th class="border p-2">Reference</th>
                                <th class="border p-2">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($batchPayments as $payment)
                                <tr>
                                    <td class="border p-2">{{ $payment->payment_date->format('Y-m-d') }}</td>
                                    <td class="border p-2">{{ $payment->payment_method }}</td>
                                    <td class="border p-2">{{ $payment->reference_number }}</td>
                                    <td class="border p-2 text-right">LKR {{ number_format($payment->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="text-right mt-2 font-bold">
                        Total: LKR {{ number_format($batchPayments->sum('amount'), 2) }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
