@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 max-w-2xl mx-auto">
        <div class="border-b pb-4 mb-4">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Create New Invoice</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Select the type of invoice you would like to generate.</p>
        </div>
        
        <div class="space-y-4">
            <a href="{{ route('invoices.createCustomer') }}" class="block w-full text-left p-4 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg">
                <h3 class="font-semibold text-gray-900 dark:text-gray-200">Customer Invoice </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Generate an invoice for a customer from a completed purchase order.</p>
            </a>
            
            <a href="{{ route('invoices.createAgent') }}" class="block w-full text-left p-4 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg">
                <h3 class="font-semibold text-gray-900 dark:text-gray-200">Agent Payout Invoice</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Generate a payout invoice for an agent based on fulfilled shortages.</p>
            </a> 

            <a href="{{ route('invoices.createSupplier') }}" class="block w-full text-left p-4 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg">
                <h3 class="font-semibold text-gray-900 dark:text-gray-200">Supplier Invoice (from GRN)</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Generate a consolidated invoice from one or more confirmed GRNs.</p>
            </a>
        </div>
    </div>
</div>
@endsection