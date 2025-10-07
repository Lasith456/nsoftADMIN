@extends('layouts.app')

@section('content')
<div class="container mx-auto p-2" x-data="customerInvoiceForm()">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4">
        <form id="customer-invoice-form" 
              action="{{ route('invoices.storeCustomer') }}" 
              method="POST" 
              @submit.prevent="handleSubmit">
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
                    <button type="submit" 
                            @click="mode = 'invoice'"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border rounded-md font-semibold text-xs text-white uppercase hover:bg-blue-700">
                        Generate Invoice
                    </button>
                    @if(session('html_error'))
                        <button type="submit" 
                                @click="mode = 'invoice_po'"
                                class="inline-flex items-center px-4 py-2 bg-yellow-500 border rounded-md font-semibold text-xs text-white uppercase hover:bg-yellow-600">
                            Generate Invoice & Create PO
                        </button>
                    @endif
                </div>
            </div>

            <!-- Errors -->
            @if(session('html_error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" role="alert">
                    {!! session('html_error') !!}
                </div>
            @endif
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

            <div x-show="showAlert" 
                 class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
                Please select a company, customer, and at least one receive note before proceeding.
            </div>

            <!-- Main Form -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Company + Customer -->
                <div>
                    <h3 class="text-lg font-bold mb-2 text-gray-800 dark:text-gray-200">1. Select Company & Customer</h3>

                    <!-- Company -->
                    <select x-model="selectedCompany" @change="filterCustomersByCompany"
                            class="block w-full border rounded-md dark:bg-gray-900 py-2 px-3 mb-2 text-sm">
                        <option value="">-- Select Company --</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                        @endforeach
                    </select>

                    <!-- Customer -->
                    <input list="customers-list"
                           x-model="customerName"
                           @change="setCustomerId"
                           placeholder="Type customer name..."
                           class="block w-full border rounded-md dark:bg-gray-900 py-2 px-3 text-sm"
                           :disabled="!selectedCompany">
                    <datalist id="customers-list">
                        <template x-for="cust in filteredCustomers" :key="cust.id">
                            <option :value="cust.customer_name" :data-id="cust.id"></option>
                        </template>
                    </datalist>
                    <input type="hidden" name="customer_id" x-model="selectedCustomer">
                    <p x-show="customerError" class="text-red-600 text-xs mt-1" x-text="customerError"></p>
                </div>

                <!-- Receive Notes -->
                <div x-show="selectedCustomer" x-cloak>
                    <h3 class="text-lg font-bold mb-2 text-gray-800 dark:text-gray-200">2. Select Receive Notes</h3>

                    <!-- Compact Date Filter -->
                    <div class="flex space-x-1 mb-2 items-center text-xs">
                        <input type="date" x-model="dateFrom"
                               class="border rounded-md px-2 py-0.5 w-32 text-xs dark:bg-gray-900">
                        <input type="date" x-model="dateTo"
                               class="border rounded-md px-2 py-0.5 w-32 text-xs dark:bg-gray-900">
                        <button type="button" class="px-2 py-1 bg-gray-800 text-white rounded-md text-xs hover:bg-gray-700"
                                @click="applyDateFilter">Filter</button>
                        <button type="button" class="px-2 py-1 bg-gray-200 text-gray-800 rounded-md text-xs hover:bg-gray-300"
                                @click="clearDateFilter">Clear</button>
                    </div>

                    <!-- Select All -->
                    <div class="flex items-center mb-2" x-show="filteredReceiveNotes.length > 0">
                        <input type="checkbox" id="selectAll" @change="toggleSelectAll($event)" class="mr-2">
                        <label for="selectAll" class="text-sm">Select All Receive Notes</label>
                    </div>

                    <!-- Notes List -->
                    <div class="space-y-2 max-h-60 overflow-y-auto border rounded-md p-2">
                        <template x-if="filteredReceiveNotes.length > 0">
                            <template x-for="rn in filteredReceiveNotes" :key="rn.id">
                                <label class="flex items-center p-2 hover:bg-gray-100 cursor-pointer">
                                    <input type="checkbox" :value="rn.id" x-model="selectedReceiveNotes">
                                    <span class="ml-3 text-sm text-blue-600 underline cursor-pointer"
                                          @click.prevent="openRnPopup(rn.id)"
                                          x-text="`${rn.receive_note_id}`"></span>
                                    <span class="ml-2 text-gray-500 text-xs"
                                          x-text="new Date(rn.received_date).toLocaleDateString()"></span>
                                </label>
                            </template>
                        </template>
                        <template x-if="filteredReceiveNotes.length === 0">
                            <p class="text-sm text-gray-500 p-2">No receive notes found in this period.</p>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Hidden fields -->
            <template x-for="id in selectedReceiveNotes" :key="id">
                <input type="hidden" name="receive_note_ids[]" :value="id">
            </template>
            <input type="hidden" name="create_po" :value="mode === 'invoice_po' ? 1 : ''">
        </form>
    </div>

    <!-- Popup -->
    <div x-show="isRnPopupOpen" 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
         @click.away="isRnPopupOpen = false"
         x-cloak>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-5xl h-[80vh] flex flex-col">
            <div class="flex justify-between items-center p-3 border-b dark:border-gray-700">
                <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200">Receive Note Details</h2>
                <button @click="isRnPopupOpen = false" class="text-gray-500 hover:text-gray-800 dark:hover:text-gray-200">&times;</button>
            </div>
            <iframe :src="rnPopupUrl" class="flex-1 w-full border-0"></iframe>
        </div>
    </div>
</div>

<!-- Alpine.js -->
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('customerInvoiceForm', () => ({
        companies: @json($companies),
        customers: @json($allCustomers),
        selectedCompany: '',
        filteredCustomers: [],
        customerName: '',
        selectedCustomer: '',
        customerError: '',

        customersWithInvoices: @json($customersWithInvoices),
        selectedCustomerId: '',
        selectedReceiveNotes: [],
        dateFrom: '',
        dateTo: '',
        showAlert: false,
        mode: 'invoice',

        isRnPopupOpen: false,
        rnPopupUrl: '',

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
                this.selectedCustomerId = match.id;
                this.customerError = '';
            } else {
                this.selectedCustomer = '';
                this.customerError = 'Customer not found or not in this company';
            }
        },

        get selectedCustomerData() {
            return this.customersWithInvoices.find(c => c.id == this.selectedCustomer) || null;
        },
        get filteredReceiveNotes() {
            if (!this.selectedCustomerData) return [];
            let notes = this.selectedCustomerData.uninvoiced_receive_notes;
            if (!this.dateFrom && !this.dateTo) return notes;
            return notes.filter(rn => {
                let d = new Date(rn.received_date);
                let from = this.dateFrom ? new Date(this.dateFrom) : null;
                let to = this.dateTo ? new Date(this.dateTo) : null;
                return (!from || d >= from) && (!to || d <= to);
            });
        },

        toggleSelectAll(event) {
            this.selectedReceiveNotes = event.target.checked
                ? this.filteredReceiveNotes.map(rn => rn.id)
                : [];
        },
        applyDateFilter() { this.selectedReceiveNotes = []; },
        clearDateFilter() { this.dateFrom = ''; this.dateTo = ''; this.selectedReceiveNotes = []; },

        handleSubmit(e) {
            if (!this.selectedCustomer || this.selectedReceiveNotes.length === 0) {
                this.showAlert = true;
                return;
            }
            this.showAlert = false;
            e.target.submit();
        },
        openRnPopup(id) {
            this.rnPopupUrl = `/receive-notes/${id}/popup`;
            this.isRnPopupOpen = true;
        },
    }));
});
</script>
@endsection
