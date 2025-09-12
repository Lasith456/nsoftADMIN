@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div id="receive-note-details" class="bg-white dark:bg-gray-800 shadow-md rounded-lg max-w-4xl mx-auto p-4">
        <div class="flex justify-between items-center mb-4 pb-3 border-b dark:border-gray-700 print:hidden">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Receive Note Details</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $receiveNote->receive_note_id }}</p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase hover:bg-gray-300 dark:hover:bg-gray-600">
                    Back
                </a>
                <a href="{{ route('receive-notes.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase hover:bg-gray-300 dark:hover:bg-gray-600">
                    Back to List
                </a>
                <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-xs uppercase font-semibold">
                    Print
                </button>
            </div>
        </div>

        <!-- Main Details -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4 pb-4 border-b dark:border-gray-700">
            <div>
                <strong class="font-medium text-gray-900 dark:text-gray-200 text-sm">Receive Note ID:</strong>
                <p class="text-gray-600 dark:text-gray-400 text-sm">{{ $receiveNote->receive_note_id }}</p>
            </div>
            <div>
                <strong class="font-medium text-gray-900 dark:text-gray-200 text-sm">Associated Delivery Notes:</strong>
                <div class="text-gray-600 dark:text-gray-400 text-sm">
                    @forelse($receiveNote->deliveryNotes as $deliveryNote)
                        <a href="{{ route('delivery-notes.show', $deliveryNote->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline block">
                            {{ $deliveryNote->delivery_note_id }}
                        </a>
                    @empty
                        N/A
                    @endforelse
                </div>
            </div>
            <div>
                <strong class="font-medium text-gray-900 dark:text-gray-200 text-sm">Received Date:</strong>
                <p class="text-gray-600 dark:text-gray-400 text-sm">{{ $receiveNote->received_date->format('F j, Y') }}</p>
            </div>
            <div>
                <strong class="font-medium text-gray-900 dark:text-gray-200 text-sm">Status:</strong>
                <p class="text-sm">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        @if($receiveNote->status == 'completed') bg-green-100 text-green-800 
                        @else bg-orange-100 text-orange-800 @endif">
                        {{ ucfirst($receiveNote->status) }}
                    </span>
                </p>
            </div>
        </div>

        <!-- Items Table -->
        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-2">Items Received</h3>
        <div class="overflow-x-auto">
            <table class="w-full min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Product</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Expected</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Received</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Discrepancy Reason</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($receiveNote->items as $item)
                    <tr>
                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium">
                            @if($item->product)
                                <a href="{{ route('products.show', $item->product->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                    {{ $item->product->name }}
                                </a>
                            @else
                                <span class="text-gray-900 dark:text-gray-200">N/A</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $item->quantity_expected }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm font-bold {{ $item->quantity_received < $item->quantity_expected ? 'text-red-500' : 'text-green-500' }}">
                            {{ $item->quantity_received }}
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $item->discrepancy_reason ?? 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #receive-note-details, #receive-note-details * {
            visibility: visible;
        }
        #receive-note-details {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 20px;
            border: none;
            box-shadow: none;
        }
        .print\:hidden {
            display: none !important;
        }
    }
</style>
@endsection

