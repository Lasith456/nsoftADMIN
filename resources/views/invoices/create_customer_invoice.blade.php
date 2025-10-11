@extends('layouts.app')

@section('content')
<div class="container mx-auto p-2" x-data="customerInvoiceForm()">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4">
        <form id="customer-invoice-form"
              action="{{ route('invoices.storeCustomer') }}"
              method="POST"
              @submit.prevent="handleSubmit">
            @csrf

            {{-- HEADER --}}
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
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border rounded-md font-semibold text-xs text-white uppercase hover:bg-blue-700">
                        Generate Invoice
                    </button>
                </div>
            </div>

            {{-- ERRORS --}}
            @if ($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
                    <p class="font-bold">Error</p>
                    <ul class="list-disc pl-5 mt-2 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- STEP 1: SELECT COMPANY & CUSTOMER --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-bold mb-2 text-gray-800 dark:text-gray-200">1. Select Company & Customer</h3>

                    {{-- COMPANY --}}
                    <select x-model="selectedCompany" @change="filterCustomersByCompany"
                            class="block w-full border rounded-md dark:bg-gray-900 py-2 px-3 mb-2 text-sm">
                        <option value="">-- Select Company --</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                        @endforeach
                    </select>

                    {{-- CUSTOMER --}}
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
                </div>

                {{-- STEP 2: SELECT RECEIVE NOTES --}}
                <div x-show="selectedCustomer" x-cloak>
                    <h3 class="text-lg font-bold mb-2 text-gray-800 dark:text-gray-200">2. Select Receive Notes (Completed)</h3>

                    <div class="flex items-center mb-2" x-show="filteredReceiveNotes.length > 0">
                        <input type="checkbox" id="selectAll" @change="toggleSelectAll($event)" class="mr-2">
                        <label for="selectAll" class="text-sm">Select All</label>
                    </div>

                    <div class="space-y-2 max-h-60 overflow-y-auto border rounded-md p-2">
                        <template x-if="filteredReceiveNotes.length > 0">
                            <template x-for="rn in filteredReceiveNotes" :key="rn.id">
                                <label class="flex items-center p-2 hover:bg-gray-100 cursor-pointer">
                                    <input type="checkbox" :value="rn.id" x-model="selectedReceiveNotes" @change="loadProducts">
                                    <span class="ml-3 text-sm text-blue-600 underline" x-text="rn.receive_note_id"></span>
                                    <span class="ml-2 text-gray-500 text-xs" x-text="new Date(rn.received_date).toLocaleDateString()"></span>
                                </label>
                            </template>
                        </template>
                        <template x-if="filteredReceiveNotes.length === 0">
                            <p class="text-sm text-gray-500 p-2">No completed receive notes found.</p>
                        </template>
                    </div>
                </div>
            </div>

            {{-- STEP 3: REVIEW PRODUCTS & EDIT PRICES --}}
            <div x-show="productList.length > 0" x-cloak>
                <h3 class="text-lg font-bold mt-6 mb-2 text-gray-800 dark:text-gray-200">3. Review Products & Edit Prices</h3>

                <div class="overflow-x-auto border rounded-md">
                    <table class="min-w-full text-sm text-gray-800 dark:text-gray-200">
                        <thead class="bg-gray-100 dark:bg-gray-700">
                            <tr>
                                <th class="px-3 py-2 text-left">Product</th>
                                <th class="px-3 py-2 text-center">Quantity</th>
                                <th class="px-3 py-2 text-center">Default Price</th>
                                <th class="px-3 py-2 text-center">Edit Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(p, idx) in productList" :key="idx">
                                <tr class="border-b dark:border-gray-600">
                                    <td class="px-3 py-2" x-text="p.name"></td>
                                    <td class="px-3 py-2 text-center" x-text="p.quantity"></td>
                                    <td class="px-3 py-2 text-center" x-text="p.default_price.toFixed(2)"></td>
                                    <td class="px-3 py-2 text-center">
                                        <input type="number" step="0.01"
                                               x-model.number="p.updated_price"
                                               class="border rounded-md px-2 py-1 w-24 text-right dark:bg-gray-900">
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- TOTAL PREVIEW --}}
                <div class="text-right mt-2 text-sm text-gray-700 dark:text-gray-300">
                    <span>Total: Rs. </span>
                    <span x-text="productList.reduce((sum, p) => sum + (p.updated_price * p.quantity), 0).toFixed(2)"></span>
                </div>
            </div>

            {{-- Hidden inputs --}}
            <template x-for="id in selectedReceiveNotes" :key="id">
                <input type="hidden" name="receive_note_ids[]" :value="id">
            </template>
            <template x-for="(p, idx) in productList" :key="idx">
                <input type="hidden" name="updated_prices[]" :value="JSON.stringify(p)">
            </template>
        </form>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('customerInvoiceForm', () => ({
        companies: @json($companies),
        customers: @json($allCustomers),
        customersWithInvoices: @json($customersWithInvoices),

        selectedCompany: '',
        filteredCustomers: [],
        customerName: '',
        selectedCustomer: '',
        selectedReceiveNotes: [],
        productList: [],

        filterCustomersByCompany() {
            this.filteredCustomers = this.customers.filter(c => c.company_id == this.selectedCompany);
            this.customerName = '';
            this.selectedCustomer = '';
            this.selectedReceiveNotes = [];
            this.productList = [];
        },

        setCustomerId() {
            const match = this.filteredCustomers.find(c => c.customer_name === this.customerName);
            this.selectedCustomer = match ? match.id : '';
        },

        get selectedCustomerData() {
            return this.customersWithInvoices.find(c => c.id == this.selectedCustomer) || null;
        },

        get filteredReceiveNotes() {
            return this.selectedCustomerData?.uninvoiced_receive_notes || [];
        },

        toggleSelectAll(e) {
            this.selectedReceiveNotes = e.target.checked
                ? this.filteredReceiveNotes.map(rn => rn.id)
                : [];
            this.loadProducts();
        },

        async loadProducts() {
            if (this.selectedReceiveNotes.length === 0) {
                this.productList = [];
                return;
            }
            const res = await fetch("{{ route('receive-notes.products') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ receive_note_ids: this.selectedReceiveNotes })
            });
            const data = await res.json();
            this.productList = data.map(d => ({
                product_id: d.product_id,
                name: d.product_name,
                quantity: d.quantity_received,
                default_price: parseFloat(d.default_price),
                updated_price: parseFloat(d.default_price),
            }));
        },

        handleSubmit(e) {
            if (!this.selectedCustomer || this.selectedReceiveNotes.length === 0) {
                alert('Please select a company, customer, and at least one receive note.');
                return;
            }
            e.target.submit();
        },
    }));
});
</script>
@endsection
