@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 max-w-2xl mx-auto">
        <div class="border-b pb-4 mb-4">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Record Payment for Invoice {{ $invoice->invoice_id }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Balance Due: LKR {{ number_format($invoice->total_amount - $invoice->amount_paid, 2) }}</p>
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

        <form action="{{ route('payments.store', $invoice->id) }}" method="POST" x-data="{ paymentMethod: '{{ old('payment_method', 'Cash') }}' }">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="payment_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Date*</label>
                    <input type="date" name="payment_date" id="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" class="mt-1 block w-full dark:bg-gray-900 border-gray-300 dark:border-gray-600 rounded-md shadow-sm" required>
                </div>
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Amount*</label>
                    <input type="number" step="0.01" name="amount" id="amount" class="mt-1 block w-full dark:bg-gray-900 border-gray-300 dark:border-gray-600 rounded-md shadow-sm" value="{{ old('amount', $invoice->total_amount - $invoice->amount_paid) }}" required>
                </div>
                <div class="md:col-span-2">
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Method*</label>
                    <select name="payment_method" id="payment_method" class="mt-1 block w-full dark:bg-gray-900 border-gray-300 dark:border-gray-600 rounded-md shadow-sm" x-model="paymentMethod" required>
                        <option value="Cash">Cash</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Cheque">Cheque</option>
                    </select>
                </div>

                <div x-show="paymentMethod === 'Bank Transfer'" x-transition class="md:col-span-2">
                    <label for="bank_id_transfer" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bank*</label>
                    <select name="bank_id_transfer" id="bank_id_transfer" class="mt-1 block w-full dark:bg-gray-900 border-gray-300 dark:border-gray-600 rounded-md shadow-sm">
                        <option value="">Select a Bank</option>
                        @foreach($banks as $bank)
                            <option value="{{ $bank->id }}" {{ old('bank_id_transfer') == $bank->id ? 'selected' : '' }}>{{ $bank->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label for="reference_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Reference</label>
                    <input type="text" name="reference_number" id="reference_number" class="mt-1 block w-full dark:bg-gray-900 border-gray-300 dark:border-gray-600 rounded-md shadow-sm" value="{{ old('reference_number') }}">
                </div>
                 <div class="md:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                    <textarea name="notes" id="notes" rows="3" class="mt-1 block w-full dark:bg-gray-900 border-gray-300 dark:border-gray-600 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div x-show="paymentMethod === 'Cheque'" x-transition class="md:col-span-2 mt-6 pt-4 border-t dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Cheque Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="bank_id_cheque" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bank*</label>
                       <select name="bank_id_transfer" id="bank_id_transfer" class="mt-1 block w-full dark:bg-gray-900 border-gray-300 dark:border-gray-600 rounded-md shadow-sm">
                        <option value="">Select a Bank</option>
                        @foreach($banks as $bank)
                            <option value="{{ $bank->id }}" {{ old('bank_id_transfer') == $bank->id ? 'selected' : '' }}>{{ $bank->name }}</option>
                        @endforeach
                    </select>
                    </div>
                    <div>
                        <label for="cheque_no" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cheque No*</label>
                        <input type="text" name="cheque_no" id="cheque_no" class="mt-1 block w-full dark:bg-gray-900 border-gray-300 dark:border-gray-600 rounded-md shadow-sm" value="{{ old('cheque_no') }}">
                    </div>
                    <div>
                        <label for="cheque_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date of Cheque*</label>
                        <input type="date" name="cheque_date" id="cheque_date" class="mt-1 block w-full dark:bg-gray-900 border-gray-300 dark:border-gray-600 rounded-md shadow-sm" value="{{ old('cheque_date') }}">
                    </div>
                    <div>
                        <label for="cheque_received_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cheque Received Date*</label>
                        <input type="date" name="cheque_received_date" id="cheque_received_date" class="mt-1 block w-full dark:bg-gray-900 border-gray-300 dark:border-gray-600 rounded-md shadow-sm" value="{{ old('cheque_received_date', date('Y-m-d')) }}">
                    </div>
                </div>
            </div>

            <div class="text-right pt-4 mt-4 border-t dark:border-gray-700">
                <a href="{{ route('invoices.show', $invoice->id) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase">Cancel</a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border rounded-md font-semibold text-xs text-white uppercase hover:bg-gray-700">
                    Record Payment
                </button>
            </div>
        </form>
    </div>
</div>
@endsection