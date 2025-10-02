@extends('layouts.app')

@section('content')
<div class="container mx-auto p-2" x-data="receiveNoteForm()">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4">
        <div class="flex justify-between items-center mb-4 pb-3 border-b dark:border-gray-700">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Create Receive Note</h2>
            <div class="flex items-center space-x-2">
                <a href="{{ route('receive-notes.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase">
                    Back
                </a>
                <button type="submit" form="receive-note-form"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border rounded-md font-semibold text-xs text-white uppercase hover:bg-blue-700">
                    Submit Note
                </button>
            </div>
        </div>

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                <p class="font-bold">Whoops! Something went wrong.</p>
                <ul class="list-disc pl-5 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- LEFT COLUMN -->
            <div class="lg:col-span-1 flex flex-col space-y-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-2">
                        1. Select Delivery Notes
                    </h3>
                    
                    <!-- Filters -->
                    <form action="{{ route('receive-notes.create') }}" method="GET"
                          class="mb-2 p-2 bg-gray-50 dark:bg-gray-700 rounded-md flex flex-wrap gap-2 items-center text-sm">
                        <input type="date" name="from_date" value="{{ request('from_date') }}"
                               class="border border-gray-300 dark:border-gray-600 dark:bg-gray-900 rounded-md p-1">
                        <input type="date" name="to_date" value="{{ request('to_date') }}"
                               class="border border-gray-300 dark:border-gray-600 dark:bg-gray-900 rounded-md p-1">
                        <button type="submit" class="px-3 py-1 bg-gray-800 text-white rounded-md text-xs">Filter</button>
                        <a href="{{ route('receive-notes.create') }}" class="px-3 py-1 bg-gray-200 text-black rounded-md text-xs">Clear</a>
                        <select name="customer_id"
                                class="border border-gray-300 dark:border-gray-600 dark:bg-gray-900 rounded-md p-1 w-full"
                                onchange="this.form.submit()">
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->customer_name }}
                                </option>
                            @endforeach
                        </select>


                    </form>

                    <!-- Delivery Notes -->
                    <form id="receive-note-form" action="{{ route('receive-notes.store') }}" method="POST">
                        @csrf
                        <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-300 dark:border-gray-700 p-2 rounded-md">
                            @if(!request('customer_id'))
                                <p class="text-sm text-gray-500 dark:text-gray-400 p-2">
                                    Please select a customer to see Delivery Notes.
                                </p>
                            @else
                                @forelse($deliveryNotes as $dn)
                                    <label class="flex items-center p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <input type="checkbox" name="delivery_note_ids[]" value="{{ $dn->id }}"
                                               x-model="selectedDeliveryNoteIds" @change="fetchItems"
                                               class="dark:bg-gray-900 rounded">
                                        <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">
                                            {{ $dn->delivery_note_id }} – {{ $dn->customer?->customer_name ?? 'N/A' }} ({{ $dn->delivery_date }})
                                        </span>
                                    </label>
                                @empty
                                    <p class="text-sm text-gray-500 dark:text-gray-400 p-2">
                                        No pending Delivery Notes found for this customer.
                                    </p>
                                @endforelse
                            @endif
                        </div>

                        <!-- Form Fields -->
                        <div class="mt-4 space-y-4">
                            <div>
                                <label for="received_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Received Date*
                                </label>
                                <input type="date" name="received_date" id="received_date"
                                       value="{{ old('received_date', date('Y-m-d')) }}"
                                       class="mt-1 block w-full dark:bg-gray-900 rounded-md py-2 px-3 border border-gray-300 dark:border-gray-600"
                                       required>
                            </div>
                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Notes / Remarks
                                </label>
                                <textarea name="notes" id="notes" rows="3"
                                          class="mt-1 block w-full dark:bg-gray-900 rounded-md py-2 px-3 border border-gray-300 dark:border-gray-600">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- RIGHT COLUMN -->
            <div class="lg:col-span-1">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-2">2. Verify Received Quantities</h3>
                <div class="overflow-y-auto max-h-[28rem] border border-gray-300 dark:border-gray-700 rounded-md">
                    <table class="w-full min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase  w-64">Product</th>
                                <th class="px-4 py-2 text-right text-xs font-medium uppercase">Expected</th>
                                <th class="px-4 py-2 text-right text-xs font-medium uppercase">Received</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase">Discrepancy Reason</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                            <template x-for="(item, index) in items" :key="item.product_id">
                                <tr>
                                    <td class="px-4 py-2 text-sm" x-text="item.product_name"></td>
                                    <td class="px-4 py-2 text-sm text-right" x-text="item.quantity_expected"></td>
                                    <td class="px-4 py-2 text-sm text-right">
                                        <input type="number"
                                               :name="`items[${index}][quantity_received]`"
                                               x-model.number="item.quantity_received"
                                               class="w-24 dark:bg-gray-900 rounded-md py-1 px-2 border border-gray-300 dark:border-gray-600"
                                               form="receive-note-form">
                                        <input type="hidden" :name="`items[${index}][product_id]`" :value="item.product_id" form="receive-note-form">
                                        <input type="hidden" :name="`items[${index}][quantity_expected]`" :value="item.quantity_expected" form="receive-note-form">
                                    </td>
                                    <td class="px-4 py-2 text-sm">
                                        <template x-if="item.quantity_received != item.quantity_expected">
                                            <input type="text"
                                                   :name="`items[${index}][discrepancy_reason]`"
                                                   placeholder="e.g., damaged"
                                                   class="block w-full dark:bg-gray-900 rounded-md py-1 px-2 text-sm border border-gray-300 dark:border-gray-600"
                                                   form="receive-note-form">
                                        </template>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="items.length === 0">
                                <td colspan="4" class="text-center py-4 text-sm text-gray-500">
                                    Select one or more delivery notes to see items.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('receiveNoteForm', () => ({
        selectedDeliveryNoteIds: [],
        items: [],

        fetchItems() {
            if (this.selectedDeliveryNoteIds.length === 0) {
                this.items = [];
                return;
            }

            fetch('{{ route("receive-notes.getItems") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ dn_ids: this.selectedDeliveryNoteIds })
            })
            .then(response => response.json())
            .then(data => {
                this.items = data.items;
            });
        },
    }));
});
</script>
@endsection
