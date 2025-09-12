@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div id="customer-details" class="bg-white dark:bg-gray-800 shadow-md rounded-lg max-w-4xl mx-auto p-4">
        <div class="flex justify-between items-center mb-4 pb-3 border-b dark:border-gray-700 print:hidden">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">Customer Details</h2>
            <div class="flex items-center space-x-2">
                <a class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 text-xs uppercase font-semibold" href="{{ url()->previous() }}">
                    Back
                </a>
                <a href="{{ route('customers.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase hover:bg-gray-300 dark:hover:bg-gray-600">
                    Back to List
                </a>
                <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-xs uppercase font-semibold">
                    Print
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4">
            <div>
                <strong class="font-medium text-gray-500 dark:text-gray-400 text-sm">Customer ID:</strong>
                <p class="text-gray-900 dark:text-gray-200 text-sm">{{ $customer->customer_id }}</p>
            </div>
            <div>
                <strong class="font-medium text-gray-500 dark:text-gray-400 text-sm">Status:</strong>
                <p class="text-sm">
                    @if($customer->is_active)
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                    @else
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                    @endif
                </p>
            </div>
            <div>
                <strong class="font-medium text-gray-500 dark:text-gray-400 text-sm">Title:</strong>
                <p class="text-gray-900 dark:text-gray-200 text-sm">{{ $customer->title }}</p>
            </div>
             <div>
                <strong class="font-medium text-gray-500 dark:text-gray-400 text-sm">Customer Name:</strong>
                <p class="text-gray-900 dark:text-gray-200 text-sm">{{ $customer->customer_name }}</p>
            </div>
            <div>
                <strong class="font-medium text-gray-500 dark:text-gray-400 text-sm">Display Name:</strong>
                <p class="text-gray-900 dark:text-gray-200 text-sm">{{ $customer->display_name }}</p>
            </div>
            <div>
                <strong class="font-medium text-gray-500 dark:text-gray-400 text-sm">Company Name:</strong>
                <p class="text-gray-900 dark:text-gray-200 text-sm">{{ $customer->company_name ?? 'N/A' }}</p>
            </div>
            <div>
                <strong class="font-medium text-gray-500 dark:text-gray-400 text-sm">NIC:</strong>
                <p class="text-gray-900 dark:text-gray-200 text-sm">{{ $customer->nic ?? 'N/A' }}</p>
            </div>
            <div>
                <strong class="font-medium text-gray-500 dark:text-gray-400 text-sm">Customer Mobile:</strong>
                <p class="text-gray-900 dark:text-gray-200 text-sm">{{ $customer->customer_mobile }}</p>
            </div>
            <div>
                <strong class="font-medium text-gray-500 dark:text-gray-400 text-sm">Customer Phone:</strong>
                <p class="text-gray-900 dark:text-gray-200 text-sm">{{ $customer->customer_phone ?? 'N/A' }}</p>
            </div>
            <div>
                <strong class="font-medium text-gray-500 dark:text-gray-400 text-sm">Work Phone:</strong>
                <p class="text-gray-900 dark:text-gray-200 text-sm">{{ $customer->work_phone ?? 'N/A' }}</p>
            </div>
            <div class="md:col-span-2">
                <strong class="font-medium text-gray-500 dark:text-gray-400 text-sm">Customer Email:</strong>
                <p class="text-gray-900 dark:text-gray-200 text-sm">{{ $customer->customer_email ?? 'N/A' }}</p>
            </div>
            <div class="md:col-span-3">
                <strong class="font-medium text-gray-500 dark:text-gray-400 text-sm">Primary Address:</strong>
                <p class="text-gray-900 dark:text-gray-200 text-sm whitespace-pre-wrap">{{ $customer->primary_address }}</p>
            </div>
            <div class="md:col-span-3">
                <strong class="font-medium text-gray-500 dark:text-gray-400 text-sm">Company Address:</strong>
                <p class="text-gray-900 dark:text-gray-200 text-sm whitespace-pre-wrap">{{ $customer->company_address ?? 'N/A' }}</p>
            </div>
            <div>
                <strong class="font-medium text-gray-500 dark:text-gray-400 text-sm">Credit Limit:</strong>
                <p class="text-gray-900 dark:text-gray-200 text-sm">Lkr.{{ number_format($customer->credit_limit, 2) }}</p>
            </div>
            <div class="md:col-span-2">
                <strong class="font-medium text-gray-500 dark:text-gray-400 text-sm">Remark:</strong>
                <p class="text-gray-900 dark:text-gray-200 text-sm whitespace-pre-wrap">{{ $customer->remark ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #customer-details, #customer-details * {
            visibility: visible;
        }
        #customer-details {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
            border: none;
            box-shadow: none;
        }
        .print\:hidden {
            display: none !important;
        }
    }
</style>
@endsection

