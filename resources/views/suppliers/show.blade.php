@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div id="supplier-details" class="bg-white dark:bg-gray-800 shadow-md rounded-lg max-w-4xl mx-auto p-4">
        <div class="flex justify-between items-center mb-4 pb-3 border-b dark:border-gray-700 print:hidden">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ $supplier->supplier_name }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $supplier->supplier_id }}</p>
            </div>
            <div class="flex items-center space-x-2">
                <a class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 text-xs uppercase font-semibold" href="{{ url()->previous() }}">
                    Back
                </a>
                  <a href="{{ route('suppliers.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase hover:bg-gray-300 dark:hover:bg-gray-600">
                    Back to List
                </a>
                <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-xs uppercase font-semibold">
                    Print
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4">
            <!-- Details Section -->
            <div>
                <strong class="font-medium text-gray-500 dark:text-gray-400 text-sm">Title:</strong>
                <p class="text-gray-900 dark:text-gray-200 text-sm">{{ $supplier->title }}</p>
            </div>
             <div>
                <strong class="font-medium text-gray-500 dark:text-gray-400 text-sm">Display Name:</strong>
                <p class="text-gray-900 dark:text-gray-200 text-sm">{{ $supplier->display_name }}</p>
            </div>
            <div>
                <strong class="font-medium text-gray-500 dark:text-gray-400 text-sm">Company Name:</strong>
                <p class="text-gray-900 dark:text-gray-200 text-sm">{{ $supplier->company_name ?? 'N/A' }}</p>
            </div>
            <div>
                <strong class="font-medium text-gray-500 dark:text-gray-400 text-sm">NIC:</strong>
                <p class="text-gray-900 dark:text-gray-200 text-sm">{{ $supplier->nic ?? 'N/A' }}</p>
            </div>
            <div>
                <strong class="font-medium text-gray-500 dark:text-gray-400 text-sm">Supplier Mobile:</strong>
                <p class="text-gray-900 dark:text-gray-200 text-sm">{{ $supplier->supplier_mobile }}</p>
            </div>
            <div>
                <strong class="font-medium text-gray-500 dark:text-gray-400 text-sm">Office No:</strong>
                <p class="text-gray-900 dark:text-gray-200 text-sm">{{ $supplier->office_no ?? 'N/A' }}</p>
            </div>
            <div>
                <strong class="font-medium text-gray-500 dark:text-gray-400 text-sm">Fax:</strong>
                <p class="text-gray-900 dark:text-gray-200 text-sm">{{ $supplier->fax ?? 'N/A' }}</p>
            </div>
            <div>
                <strong class="font-medium text-gray-500 dark:text-gray-400 text-sm">Work Phone:</strong>
                <p class="text-gray-900 dark:text-gray-200 text-sm">{{ $supplier->work_phone ?? 'N/A' }}</p>
            </div>
             <div>
                <strong class="font-medium text-gray-500 dark:text-gray-400 text-sm">Email:</strong>
                <p class="text-gray-900 dark:text-gray-200 text-sm">{{ $supplier->email ?? 'N/A' }}</p>
            </div>

            <!-- Addresses -->
            <div class="md:col-span-3">
                <strong class="font-medium text-gray-500 dark:text-gray-400 text-sm">Primary Address:</strong>
                <p class="text-gray-900 dark:text-gray-200 text-sm whitespace-pre-wrap">{{ $supplier->primary_address }}</p>
            </div>
            <div class="md:col-span-3">
                <strong class="font-medium text-gray-500 dark:text-gray-400 text-sm">Company Address:</strong>
                <p class="text-gray-900 dark:text-gray-200 text-sm whitespace-pre-wrap">{{ $supplier->company_address ?? 'N/A' }}</p>
            </div>

            <!-- Credit Info -->
            <div>
                <strong class="font-medium text-gray-500 dark:text-gray-400 text-sm">Credit Limit:</strong>
                <p class="text-gray-900 dark:text-gray-200 text-sm">Lkr.{{ number_format($supplier->credit_limit, 2) }}</p>
            </div>
            <div>
                <strong class="font-medium text-gray-500 dark:text-gray-400 text-sm">Credit Period:</strong>
                <p class="text-gray-900 dark:text-gray-200 text-sm">{{ $supplier->credit_period ? $supplier->credit_period . ' Days' : 'N/A' }}</p>
            </div>
             <div>
                <strong class="font-medium text-gray-500 dark:text-gray-400 text-sm">Status:</strong>
                <p class="text-sm">
                    @if($supplier->is_active)
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                    @else
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                    @endif
                </p>
            </div>
            <div class="md:col-span-3">
                <strong class="font-medium text-gray-500 dark:text-gray-400 text-sm">Remark:</strong>
                <p class="text-gray-900 dark:text-gray-200 text-sm whitespace-pre-wrap">{{ $supplier->remark ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #supplier-details, #supplier-details * {
            visibility: visible;
        }
        #supplier-details {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 20px;
            border: none;
            box-shadow: none;
        }
        .print\:hidden {
            display: none !important;
        }
    }
</style>
@endsection

