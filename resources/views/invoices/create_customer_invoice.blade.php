@extends('layouts.app')

@section('content')
<div class="container mx-auto p-2" x-data="customerInvoiceForm()">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4">
        <form id="customer-invoice-form" action="{{ route('invoices.storeCustomer') }}" method="POST" @submit.prevent="handleSubmit">
            @csrf

            <!-- Header -->
            <div class="flex justify-between items-center mb-4 pb-3 border-b dark:border-gray-700">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                    Generate Customer Invoice
                </h2>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('invoices.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase">
                        Cancel
                    </a>

                    <!-- Blue button (normal) -->
                    <button type="submit" 
                            @click="mode = 'invoice'"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border rounded-md font-semibold text-xs text-white uppercase hover:bg-blue-700">
                        Generate Invoice
                    </button>

                    <!-- Yellow button (only visible if discrepancy error exists) -->
                    @if(session('html_error'))
                        <button type="submit" 
                                @click="mode = 'invoice_po'"
                                class="inline-flex items-center px-4 py-2 bg-yellow-500 border rounded-md font-semibold text-xs text-white uppercase hover:bg-yellow-600">
                            Generate Invoice & Create PO
                        </button>
                    @endif
                </div>
            </div>

            <!-- Discrepancy Error -->
            @if(session('html_error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" role="alert">
                    {!! session('html_error') !!}
                </div>
            @endif

            <!-- Validation Errors (Laravel) -->
            @if ($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" role="alert">
                    <p class="font-bold">Error</p>
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Inline Alert (Alpine) -->
            <div x-show="showAlert" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
                Please select a customer and at least one receive note before proceeding.
            </div>

            <!-- Form Body -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Customer Search -->
                <div>
                    <h3 class="text-lg font-bold mb-2 text-gray-800 dark:text-gray-200">1. Select Customer</h3>
                    <input id="customer-search-input" type="text" placeholder="Search customer name..." 
                           class="mt-1 block w-full border rounded-md dark:bg-gray-900 py-2 px-3"
                           x-model="customerSearch" @input="filterCustomers">

                    <ul class="border rounded-md mt-2 max-h-40 overflow-y-auto bg-white dark:bg-gray-800" 
                        x-show="filteredCustomers.length > 0">
                        <template x-for="cust in filteredCustomers" :key="cust.id">
                            <li @click="selectCustomer(cust)"
                                class="px-3 py-2 hover:bg-indigo-100 dark:hover:bg-gray-700 cursor-pointer"
                                x-text="`${cust.customer_name} (${cust.customer_id})`"></li>
                        </template>
                    </ul>

                    <!-- Hidden bound field -->
                    <input type="hidden" name="customer_id" :value="selectedCustomerId">
                </div>

                <!-- Receive Notes -->
                <div>
                    <h3 class="text-lg font-bold mb-2 text-gray-800 dark:text-gray-200">2. Select Receive Notes</h3>

                    <!-- Date Filter -->
                    <div class="flex space-x-2 mb-2">
                        <input type="date" x-model="dateFrom" class="border rounded p-1 text-sm">
                        <input type="date" x-model="dateTo" class="border rounded p-1 text-sm">
                        <button type="button" class="px-3 py-1 bg-gray-200 rounded text-xs" @click="applyDateFilter">Filter</button>
                    </div>

                    <!-- Select All -->
                    <div class="flex items-center mb-2" x-show="filteredReceiveNotes.length > 0">
                        <input type="checkbox" id="selectAll" @change="toggleSelectAll($event)" class="mr-2">
                        <label for="selectAll" class="text-sm">Select All Receive Notes</label>
                    </div>

                    <!-- Notes List -->
                    <div x-show="selectedCustomerId" class="space-y-2 max-h-60 overflow-y-auto border rounded-md p-2" x-cloak>
                        <template x-if="filteredReceiveNotes.length > 0">
                            <template x-for="rn in filteredReceiveNotes" :key="rn.id">
                                <label class="flex items-center p-2 hover:bg-gray-100 cursor-pointer">
                                    <input type="checkbox" :value="rn.id" x-model="selectedReceiveNotes">
                                    <span class="ml-3 text-sm" 
                                          x-text="`${rn.receive_note_id} - ${new Date(rn.received_date).toLocaleDateString()}`"></span>
                                </label>
                            </template>
                        </template>
                        <template x-if="filteredReceiveNotes.length === 0">
                            <p class="text-sm text-gray-500 p-2">No receive notes found in this period.</p>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Hidden sync for selected notes -->
            <template x-for="id in selectedReceiveNotes" :key="id">
                <input type="hidden" name="receive_note_ids[]" :value="id">
            </template>

            <!-- Hidden mode input -->
            <input type="hidden" name="create_po" :value="mode === 'invoice_po' ? 1 : ''">
        </form>
    </div>
</div>

<!-- Alpine.js Script -->
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('customerInvoiceForm', () => ({
        customers: @json($customersWithInvoices),
        filteredCustomers: [],
        customerSearch: '',
        selectedCustomerId: null,
        selectedCustomer: null,
        selectedReceiveNotes: [],
        dateFrom: '',
        dateTo: '',
        showAlert: false,
        mode: 'invoice',

        get availableReceiveNotes() {
            return this.selectedCustomer ? this.selectedCustomer.uninvoiced_receive_notes : [];
        },
        get filteredReceiveNotes() {
            if (!this.dateFrom && !this.dateTo) return this.availableReceiveNotes;
            return this.availableReceiveNotes.filter(rn => {
                let date = new Date(rn.received_date);
                let from = this.dateFrom ? new Date(this.dateFrom) : null;
                let to = this.dateTo ? new Date(this.dateTo) : null;
                return (!from || date >= from) && (!to || date <= to);
            });
        },
        filterCustomers() {
            let search = this.customerSearch.toLowerCase();
            this.filteredCustomers = this.customers.filter(c => 
                c.customer_name.toLowerCase().includes(search) || 
                c.customer_id.toLowerCase().includes(search)
            );
        },
        selectCustomer(cust) {
            this.selectedCustomer = cust;
            this.selectedCustomerId = cust.id;
            this.filteredCustomers = [];
            this.customerSearch = `${cust.customer_name} (${cust.customer_id})`;
            this.selectedReceiveNotes = [];
        },
        toggleSelectAll(event) {
            if (event.target.checked) {
                this.selectedReceiveNotes = this.filteredReceiveNotes.map(rn => rn.id);
            } else {
                this.selectedReceiveNotes = [];
            }
        },
        applyDateFilter() {
            this.selectedReceiveNotes = [];
        },
        handleSubmit(e) {
            if (!this.selectedCustomerId || this.selectedReceiveNotes.length === 0) {
                this.showAlert = true;
                return;
            }
            this.showAlert = false;
            e.target.submit();
        }
    }));
});
</script>
@endsection
