@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 max-w-2xl mx-auto">
        <div class="border-b pb-4 mb-4">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Generate Invoice from Receive Note</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">You are about to create an invoice for Receive Note: <strong>{{ $receiveNote->receive_note_id }}</strong></p>
        </div>

        @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <ul class="list-disc pl-5 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="space-y-4 text-sm">
            <p><strong>Customer:</strong> {{ $receiveNote->deliveryNotes->first()->purchaseOrders->first()->customer->customer_name ?? 'N/A' }}</p>
            <p><strong>Received Date:</strong> {{ $receiveNote->received_date->format('Y-m-d') }}</p>
            <h4 class="font-semibold mt-4">Items to be Invoiced:</h4>
            <ul class="list-disc pl-5">
                @foreach($receiveNote->items as $item)
                    <li>{{ $item->quantity_received }} x {{ $item->product->name }} @ {{ number_format($item->product->selling_price, 2) }} each</li>
                @endforeach
            </ul>
        </div>
        
        <form action="{{ route('invoices.storeFromRN', $receiveNote->id) }}" method="POST" class="mt-6">
            @csrf
            <div class="text-right">
                <a href="{{ route('receive-notes.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase">Cancel</a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border rounded-md font-semibold text-xs text-white uppercase hover:bg-gray-700">
                    Confirm & Generate Invoice
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
