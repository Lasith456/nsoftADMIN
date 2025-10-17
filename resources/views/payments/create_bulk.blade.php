@extends('layouts.app')

@section('content')
<div class="container mx-auto p-2" x-data="bulkPaymentForm()">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4">
        <form id="bulk-payment-form" action="{{ route('payments.storeBulk') }}" method="POST">
            @csrf

            {{-- HEADER --}}
            <div class="flex justify-between items-center mb-4 pb-3 border-b dark:border-gray-700">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                    Record Bulk Customer Payment
                </h2>
                <div class="flex items-center space-x-2">
                    <a href="{{ url()->previous() }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase">
                       Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-green-600 border rounded-md font-semibold text-xs text-white uppercase hover:bg-green-700" 
                            :disabled="!isFormValid()">
                        Record Payment
                    </button>
                </div>
            </div>

            {{-- ERRORS --}}
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

            {{-- COMPANY + CUSTOMER --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-2">
                        1. Select Company & Customer
                    </h3>

                    <select x-model="selectedCompany" @change="filterCustomersByCompany"
                            class="block w-full border rounded-md dark:bg-gray-900 py-2 px-3 mb-2">
                        <option value="">Select Company</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                        @endforeach
                    </select>

                    <input list="customers-list" x-model="customerName" @change="setCustomerId"
                           placeholder="Type customer name..."
                           class="block w-full border rounded-md dark:bg-gray-900 py-2 px-3 mb-2"
                           :disabled="!selectedCompany">

                    <datalist id="customers-list">
                        <template x-for="cust in filteredCustomers" :key="cust.id">
                            <option :value="cust.customer_name" :data-id="cust.id"></option>
                        </template>
                    </datalist>

                    <input type="hidden" name="customer_id" x-model="selectedCustomerId">
                    <p x-show="customerError" class="text-red-600 text-xs mt-1" x-text="customerError"></p>

                    {{-- Debit Note Display --}}
                    <div x-show="selectedCustomerId" class="mt-2 text-sm">
                        <span class="text-gray-600 dark:text-gray-300">Available Debit Note Balance: </span>
                        <span class="font-semibold text-blue-600" x-text="formatCurrency(availableDebit)"></span>
                    </div>
                </div>

                {{-- PAYMENT DETAILS --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Date</label>
                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}"
                               class="mt-1 block w-full dark:bg-gray-900 rounded-md py-1 px-2 border border-gray-300 dark:border-gray-600" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Method</label>
                        <select name="payment_method" x-model="paymentMethod"
                                class="mt-1 block w-full dark:bg-gray-900 rounded-md py-1 px-2 border border-gray-300 dark:border-gray-600" required>
                            <option value="">Select Method</option>
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>

                    {{-- CHEQUE FIELDS --}}
                    <div x-show="paymentMethod === 'Cheque'" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3 col-span-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bank</label>
                            <select name="bank_id" class="mt-1 block w-full dark:bg-gray-900 rounded-md border-gray-300 dark:border-gray-600">
                                <option value="">Select Bank</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cheque Number</label>
                            <input type="text" name="cheque_number" class="mt-1 block w-full dark:bg-gray-900 rounded-md border-gray-300 dark:border-gray-600">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cheque Date</label>
                            <input type="date" name="cheque_date" class="mt-1 block w-full dark:bg-gray-900 rounded-md border-gray-300 dark:border-gray-600">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cheque Received Date</label>
                            <input type="date" name="cheque_received_date" class="mt-1 block w-full dark:bg-gray-900 rounded-md border-gray-300 dark:border-gray-600">
                        </div>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reference</label>
                        <input type="text" name="reference_number" placeholder="e.g., Cheque No."
                               class="mt-1 block w-full dark:bg-gray-900 rounded-md py-1 px-2 border border-gray-300 dark:border-gray-600">
                    </div>
                </div>
            </div>

            {{-- INVOICE SECTION --}}
            <div x-show="selectedCustomerId" x-cloak class="border-t pt-4">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">2. Select Invoices to Pay</h3>
                    <div class="text-right">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Total Outstanding:</span>
                        <span class="font-bold text-lg text-red-600" x-text="formatCurrency(totalOutstanding)"></span>
                    </div>
                </div>

                {{-- Payment Summary --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Amount to Pay</label>
                        <input type="number" step="0.01" name="amount" x-model.number="amountPaid"
                               placeholder="0.00"
                               class="mt-1 block w-full dark:bg-gray-900 text-lg rounded-md py-1 px-2 border border-gray-300 dark:border-gray-600" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stamp Fee</label>
                        <input type="number" step="0.01" name="stamp_fee" x-model.number="stampFee"
                               placeholder="0.00"
                               class="mt-1 block w-full dark:bg-gray-900 text-lg rounded-md py-1 px-2 border border-gray-300 dark:border-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Surcharge Fee</label>
                        <input type="number" step="0.01" name="surcharge_fee" x-model.number="surchargeFee"
                               placeholder="0.00"
                               class="mt-1 block w-full dark:bg-gray-900 text-lg rounded-md py-1 px-2 border border-gray-300 dark:border-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Debit Used (Optional)</label>
                        <input type="number" step="0.01" name="used_debit" x-model.number="usedDebit"
                               placeholder="0.00"
                               class="mt-1 block w-full dark:bg-gray-900 text-lg rounded-md py-1 px-2 border border-gray-300 dark:border-gray-600">
                    </div>

                    {{-- Summary Table --}}
                    <div class="pt-6 text-sm col-span-2 space-y-1">
                        <div class="flex justify-between"><span>Amount to Pay:</span><span x-text="formatCurrency(amountPaid || 0)"></span></div>
                        <div class="flex justify-between"><span>Stamp Fee:</span><span x-text="formatCurrency(stampFee || 0)"></span></div>
                        <div class="flex justify-between"><span>Surcharge Fee:</span><span x-text="formatCurrency(surchargeFee || 0)"></span></div>
                        <div class="flex justify-between"><span>Debit Used:</span><span x-text="formatCurrency(usedDebit || 0)"></span></div>

                        <div class="flex justify-between font-bold text-blue-600 border-t pt-2">
                            <span>Grand Total (Overall Payable):</span>
                            <span x-text="formatCurrency(grandTotal)"></span>
                        </div>

                        <p x-show="showWarning" class="text-xs text-red-600 font-medium mt-1" x-text="warningMessage"></p>
                    </div>
                </div>

                {{-- INVOICE TABLE --}}
                <div class="mt-4 max-h-80 overflow-y-auto border rounded-md">
                    <table class="w-full min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700 sticky top-0">
                            <tr>
                                <th class="w-10"></th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase">Invoice ID</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase">Date</th>
                                <th class="px-4 py-2 text-right text-xs font-medium uppercase">Balance Due</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="isLoading"><tr><td colspan="4" class="text-center p-4">Loading invoices...</td></tr></template>
                            <template x-if="!isLoading && invoices.length === 0"><tr><td colspan="4" class="text-center p-4">No outstanding invoices found.</td></tr></template>
                            <template x-for="invoice in invoices" :key="invoice.id">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="pl-4"><input type="checkbox" name="invoice_ids[]" :value="invoice.id" x-model="selectedInvoiceIds" @change="updateAmountToPay" class="rounded"></td>
                                    <td class="px-4 py-2 text-sm" x-text="invoice.invoice_id"></td>
                                    <td class="px-4 py-2 text-sm" x-text="new Date(invoice.created_at).toLocaleDateString()"></td>
                                    <td class="px-4 py-2 text-sm text-right" x-text="formatCurrency(invoice.total_amount - invoice.amount_paid)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Alpine Logic --}}
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('bulkPaymentForm', () => ({
        companies: @json($companies),
        customers: @json($customers),
        selectedCompany: '',
        filteredCustomers: [],
        customerName: '',
        customerError: '',
        selectedCustomerId: '{{ $selectedCustomerId ?? '' }}',
        invoices: [],
        isLoading: false,
        selectedInvoiceIds: [],
        amountPaid: '',
        stampFee: 0,
        surchargeFee: 0,
        usedDebit: 0,
        availableDebit: 0,
        warningMessage: '',
        showWarning: false,
        paymentMethod: '',

        init() {
            if (this.selectedCustomerId) {
                this.fetchInvoices();
                this.fetchDebitBalance();
            }
        },

        filterCustomersByCompany() {
            this.filteredCustomers = this.customers.filter(c => c.company_id == this.selectedCompany);
            this.customerName = '';
            this.selectedCustomerId = '';
            this.availableDebit = 0;
        },

        setCustomerId() {
            const match = this.filteredCustomers.find(c => c.customer_name === this.customerName);
            if (match) {
                this.selectedCustomerId = match.id;
                this.customerError = '';
                this.fetchInvoices();
                this.fetchDebitBalance();
            } else {
                this.selectedCustomerId = '';
                this.customerError = 'Customer not found or not in this company';
            }
        },

        fetchInvoices() {
            if (!this.selectedCustomerId) return;
            this.isLoading = true;
            fetch(`/customers/${this.selectedCustomerId}/unpaid-invoices`)
                .then(res => res.json())
                .then(data => { this.invoices = data; this.isLoading = false; });
        },

        fetchDebitBalance() {
            if (!this.selectedCustomerId) return;
            fetch(`/customers/${this.selectedCustomerId}/debit-balance`)
                .then(res => res.json())
                .then(data => this.availableDebit = data.balance || 0);
        },

        // ✅ Auto-calculate amount when invoices are selected
        updateAmountToPay() {
            this.amountPaid = this.invoices
                .filter(i => this.selectedInvoiceIds.includes(String(i.id)))
                .reduce((t, i) => t + (i.total_amount - i.amount_paid), 0)
                .toFixed(2);
        },

        get totalOutstanding() {
            return this.invoices.reduce((t, i) => t + (i.total_amount - i.amount_paid), 0);
        },

        get grandTotal() {
            const total = 
                (Number(this.amountPaid) || 0) +
                (Number(this.stampFee) || 0) +
                (Number(this.surchargeFee) || 0) +
                (Number(this.usedDebit) || 0);

            if (total <= 0) {
                this.warningMessage = "⚠️ Please enter at least one value (amount, fee, or debit).";
                this.showWarning = true;
            } else {
                this.showWarning = false;
                this.warningMessage = '';
            }
            return total;
        },

        isFormValid() {
            return this.selectedCustomerId && this.selectedInvoiceIds.length > 0 && this.amountPaid > 0;
        },

        formatCurrency(a) {
            return new Intl.NumberFormat('en-LK', { style: 'currency', currency: 'LKR' }).format(a);
        },
    }));
});
</script>
@endsection
