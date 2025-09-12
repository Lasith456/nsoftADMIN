@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 max-w-2xl mx-auto">
        <div class="border-b pb-4 mb-4">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Reports</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Select a report to view.</p>
        </div>
        
        <div class="space-y-4">
            <a href="{{ route('reports.sales') }}" class="block w-full text-left p-4 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg">
                <h3 class="font-semibold text-gray-900 dark:text-gray-200">Sales Report</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">View a detailed list of all invoices within a specific date range.</p>
            </a>
             <a href="{{ route('reports.customers') }}" class="block w-full text-left p-4 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg">
                <h3 class="font-semibold text-gray-900 dark:text-gray-200">Customer Outstanding Report</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">See a summary of all customers with an outstanding balance.</p>
            </a>
            <a href="{{ route('reports.stock_levels') }}" class="block w-full text-left p-4 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg">
                <h3 class="font-semibold text-gray-900 dark:text-gray-200">Stock Level Report</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">View current stock levels for all products, separated by clear and non-clear stock.</p>
            </a>
        </div>
    </div>
</div>
@endsection
