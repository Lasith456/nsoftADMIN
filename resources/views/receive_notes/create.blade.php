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
                          class="mb-2 p-2 bg-gray-50 dark:bg-gray-700 rounded-md flex flex-wrap gap-2 items-center text-xs">

                        <!-- Company -->
                        <div class="w-full">
                            <label class="block text-sm font-medium text-gray-800 dark:text-gray-200">Company <span class="text-red-500">*</span></label>
                            <select id="company_id"
                                    x-model="selectedCompany"
                                    @change="filterCustomersByCompany"
                                    class="mt-1 block w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-900 rounded-md py-1 px-2">
                                <option value="">Select Company</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Customer -->
                        <div class="w-full">
                            <label class="block text-sm font-medium text-gray-800 dark:text-gray-200">Customer <span class="text-red-500">*</span></label>
                            <input list="customers-list"
                                   x-model="customerName"
                                   @change="setCustomerId"
                                   placeholder="Type customer name..."
                                   class="mt-1 block w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-900 rounded-md py-1 px-2"
                                   :disabled="!selectedCompany">
                            <datalist id="customers-list">
                                <template x-for="cust in filteredCustomers" :key="cust.id">
                                    <option :value="cust.customer_name" :data-id="cust.id"></option>
                                </template>
                            </datalist>
                            <input type="hidden" name="customer_id" x-model="selectedCustomer">
                            <p x-show="customerError" class="text-red-600 text-xs mt-1" x-text="customerError"></p>
                        </div>

                        <!-- Dates -->
                        <input type="date" name="from_date" value="{{ request('from_date') }}"
                               class="border border-gray-300 dark:border-gray-600 dark:bg-gray-900 rounded-md px-2 py-0.5 w-32 text-xs">
                        <input type="date" name="to_date" value="{{ request('to_date') }}"
                               class="border border-gray-300 dark:border-gray-600 dark:bg-gray-900 rounded-md px-2 py-0.5 w-32 text-xs">
                        <button type="submit" class="px-2 py-1 bg-gray-800 text-white rounded-md text-xs hover:bg-gray-700 transition">Filter</button>
                        <a href="{{ route('receive-notes.create') }}" class="px-2 py-1 bg-gray-200 text-gray-800 rounded-md text-xs hover:bg-gray-300 transition">Clear</a>
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
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase w-64">Product</th>
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
        selectedCompany: '',
        customerName: '',
        selectedCustomer: '',
        filteredCustomers: [],
        customerError: '',
        customers: @json($allCustomers),

        selectedDeliveryNoteIds: [],
        items: [],

        filterCustomersByCompany() {
            if (!this.selectedCompany) {
                this.filteredCustomers = [];
                this.customerName = '';
                this.selectedCustomer = '';
                return;
            }
            this.filteredCustomers = this.customers.filter(c => c.company_id == this.selectedCompany);
            this.customerName = '';
            this.selectedCustomer = '';
        },

        setCustomerId() {
            const match = this.filteredCustomers.find(c => c.customer_name === this.customerName);
            if (match) {
                this.selectedCustomer = match.id;
                this.customerError = '';
            } else {
                this.selectedCustomer = '';
                this.customerError = 'Customer not found or not in this company';
            }
        },

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
