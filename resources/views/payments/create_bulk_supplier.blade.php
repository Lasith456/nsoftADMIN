@extends('layouts.app')

@section('content')
<div class="container mx-auto p-2" x-data="bulkSupplierPaymentForm()">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4">
        <form id="bulk-supplier-payment-form" action="{{ route('payments.storeBulkSupplier') }}" method="POST">
            @csrf
            {{-- Header --}}
            <div class="flex justify-between items-center mb-4 pb-3 border-b dark:border-gray-700">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Record Bulk Supplier Payment</h2>
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

            {{-- Errors --}}
            @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                <p class="font-bold">Error</p>
                <ul class="list-disc pl-5 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Supplier Selection & Payment Details --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-2">1. Select Supplier</h3>
                    <select name="supplier_id" x-model="selectedSupplierId" @change="fetchInvoices"
                            class="mt-1 block w-full dark:bg-gray-900 rounded-md py-2 px-3 border border-gray-300 dark:border-gray-600" required>
                        <option value="">Select a supplier...</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ $selectedSupplierId == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->supplier_name }} ({{ $supplier->supplier_id }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Payment Info --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Date</label>
                        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}"
                               class="mt-1 block w-full dark:bg-gray-900 rounded-md py-2 px-3 border border-gray-300 dark:border-gray-600" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Method</label>
                        <select name="payment_method" x-model="paymentMethod"
                                class="mt-1 block w-full dark:bg-gray-900 rounded-md py-2 px-3 border border-gray-300 dark:border-gray-600" required>
                            <option value="">Select Method</option>
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>

                    {{-- Cheque Fields --}}
                    <div x-show="paymentMethod === 'Cheque'" x-cloak
                         class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3 col-span-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bank</label>
                            <select name="bank_id"
                                    class="mt-1 block w-full dark:bg-gray-900 rounded-md border-gray-300 dark:border-gray-600">
                                <option value="">Select Bank</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cheque Number</label>
                            <input type="text" name="cheque_number"
                                   class="mt-1 block w-full dark:bg-gray-900 rounded-md border-gray-300 dark:border-gray-600">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cheque Date</label>
                            <input type="date" name="cheque_date"
                                   class="mt-1 block w-full dark:bg-gray-900 rounded-md border-gray-300 dark:border-gray-600">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cheque Received Date</label>
                            <input type="date" name="cheque_received_date"
                                   class="mt-1 block w-full dark:bg-gray-900 rounded-md border-gray-300 dark:border-gray-600">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reference</label>
                        <input type="text" name="reference_number" placeholder="e.g., Cheque No."
                               class="mt-1 block w-full dark:bg-gray-900 rounded-md py-2 px-3 border border-gray-300 dark:border-gray-600">
                    </div>
                </div>
            </div>

            {{-- Invoice Selection --}}
            <div x-show="selectedSupplierId" x-cloak class="border-t pt-4">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">2. Select Invoices to Pay</h3>
                    <div class="text-right">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Total Outstanding:</span>
                        <span class="font-bold text-lg text-red-600" x-text="formatCurrency(totalOutstanding)"></span>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Amount to Pay</label>
                        <input type="number" step="0.01" name="amount" x-model.number="amountPaid"
                               placeholder="0.00"
                               class="mt-1 block w-full dark:bg-gray-900 text-lg rounded-md py-2 px-3 border border-gray-300 dark:border-gray-600" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stamp Fee</label>
                        <input type="number" step="0.01" name="stamp_fee" x-model.number="stampFee"
                               placeholder="0.00"
                               class="mt-1 block w-full dark:bg-gray-900 text-lg rounded-md py-2 px-3 border border-gray-300 dark:border-gray-600">
                    </div>

                    {{-- Totals --}}
                    <div class="pt-6 text-sm col-span-2">
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Total Selected:</span>
                            <span class="font-semibold" x-text="formatCurrency(totalSelectedForPayment)"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Stamp Fee:</span>
                            <span class="font-semibold" x-text="formatCurrency(stampFee || 0)"></span>
                        </div>
                        <div class="flex justify-between font-bold text-blue-600">
                            <span>Grand Total:</span>
                            <span x-text="formatCurrency(grandTotal)"></span>
                        </div>
                    </div>
                </div>

                {{-- Invoice Table --}}
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
                            <template x-if="isLoading">
                                <tr><td colspan="4" class="text-center p-4">Loading invoices...</td></tr>
                            </template>
                            <template x-if="!isLoading && invoices.length === 0">
                                <tr><td colspan="4" class="text-center p-4">No outstanding invoices found for this supplier.</td></tr>
                            </template>
                            <template x-for="invoice in invoices" :key="invoice.id">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="pl-4">
                                        <input type="checkbox" name="invoice_ids[]" :value="invoice.id"
                                               x-model="selectedInvoiceIds" class="rounded">
                                    </td>
                                    <td class="px-4 py-2 text-sm" x-text="invoice.invoice_id"></td>
                                    <td class="px-4 py-2 text-sm" x-text="new Date(invoice.created_at).toLocaleDateString()"></td>
                                    <td class="px-4 py-2 text-sm text-right" 
                                        x-text="formatCurrency(invoice.total_amount - invoice.amount_paid)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('bulkSupplierPaymentForm', () => ({
        selectedSupplierId: '{{ $selectedSupplierId ?? '' }}',
        invoices: [],
        isLoading: false,
        selectedInvoiceIds: [],
        amountPaid: '',
        stampFee: 0,
        paymentMethod: '',

        init() {
            if (this.selectedSupplierId) {
                this.fetchInvoices();
            }
        },
        fetchInvoices() {
            this.invoices = [];
            this.selectedInvoiceIds = [];
            if (!this.selectedSupplierId) return;

            this.isLoading = true;
            fetch(`/suppliers/${this.selectedSupplierId}/unpaid-invoices`)
                .then(res => res.json())
                .then(data => {
                    this.invoices = data;
                    this.isLoading = false;
                });
        },
        get totalOutstanding() {
            return this.invoices.reduce((total, inv) => total + (parseFloat(inv.total_amount) - parseFloat(inv.amount_paid)), 0);
        },
        get totalSelectedForPayment() {
            return this.invoices
                .filter(inv => this.selectedInvoiceIds.includes(String(inv.id)))
                .reduce((total, inv) => total + (parseFloat(inv.total_amount) - parseFloat(inv.amount_paid)), 0);
        },
        get grandTotal() {
            return (parseFloat(this.amountPaid) || 0) + (parseFloat(this.stampFee) || 0);
        },
        isFormValid() {
            return this.selectedSupplierId && this.selectedInvoiceIds.length > 0 && this.amountPaid > 0;
        },
        formatCurrency(amount) {
            return new Intl.NumberFormat('en-LK', { style: 'currency', currency: 'LKR' }).format(amount);
        }
    }));
});
</script>
@endsection
