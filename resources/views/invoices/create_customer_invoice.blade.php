@extends('layouts.app')

@section('content')
<div class="container mx-auto p-2" x-data="customerInvoiceForm()">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4">
        <form id="customer-invoice-form" action="{{ route('invoices.storeCustomer') }}" method="POST">
            @csrf
            <div class="flex justify-between items-center mb-4 pb-3 border-b dark:border-gray-700">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Generate Customer Invoice</h2>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('invoices.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase">Cancel</a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border rounded-md font-semibold text-xs text-white uppercase hover:bg-blue-700" :disabled="!selectedCustomerId">
                        Generate Invoice
                    </button>
                </div>
            </div>

            @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                <p class="font-bold">Error</p>
                <ul class="list-disc pl-5 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Left Column: Customer Selection -->
                <div class="lg:col-span-1">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-2">1. Select Customer</h3>
                    <select id="customer_id" x-model="selectedCustomerId" class="mt-1 block w-full dark:bg-gray-900 rounded-md py-2 px-3 border border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500" required>
                        <option value="">Select a customer with uninvoiced notes...</option>
                        @foreach($customersWithInvoices as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->customer_name }} ({{ $customer->customer_id }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Right Column: Receive Note Selection -->
                <div class="lg:col-span-1">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-2">2. Select Receive Notes</h3>
                    <div x-show="selectedCustomerId" class="space-y-2 max-h-60 overflow-y-auto border border-gray-300 dark:border-gray-700 p-2 rounded-md" x-cloak>
                        <template x-if="availableReceiveNotes.length > 0">
                            <template x-for="rn in availableReceiveNotes" :key="rn.id">
                                <label class="flex items-center p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer">
                                    <input type="checkbox" name="receive_note_ids[]" :value="rn.id" class="dark:bg-gray-900 rounded focus:ring-indigo-500">
                                    <span class="ml-3 text-sm text-gray-800 dark:text-gray-200" x-text="`${rn.receive_note_id} - Received on: ${new Date(rn.received_date).toLocaleDateString()}`"></span>
                                </label>
                            </template>
                        </template>
                         <template x-if="!availableReceiveNotes.length">
                            <p class="text-sm text-gray-500 dark:text-gray-400 p-2">No uninvoiced receive notes available for this customer.</p>
                        </template>
                    </div>
                     <div x-show="!selectedCustomerId" class="text-sm text-gray-500 dark:text-gray-400 p-2 border border-dashed rounded-md text-center">
                        Please select a customer to see available receive notes.
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('customerInvoiceForm', () => ({
        customers: @json($customersWithInvoices->keyBy('id')),
        selectedCustomerId: '',
        
        get availableReceiveNotes() {
            if (!this.selectedCustomerId) {
                return [];
            }
            const customer = this.customers[this.selectedCustomerId];
            return customer ? customer.receive_notes : [];
        }
    }));
});
</script>
@endsection

