@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div id="agent-details" class="bg-white dark:bg-gray-800 shadow-md rounded-lg max-w-4xl mx-auto p-6">
        <div class="flex justify-between items-center mb-6 border-b border-gray-200 dark:border-gray-700 pb-4 print:hidden">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ $agent->name }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $agent->agent_id }}</p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ url()->previous() }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase hover:bg-gray-300 dark:hover:bg-gray-600">
                    Back
                </a>
                <a href="{{ route('agents.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase hover:bg-gray-300 dark:hover:bg-gray-600">
                    Back to List
                </a>
                 <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-xs uppercase font-semibold">
                    Print
                </button>
            </div>
        </div>

        <div class="space-y-8">
            <!-- Agent Information -->
            <div>
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">Agent Information</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                    <div>
                        <strong class="font-medium text-gray-900 dark:text-gray-200">Agent ID:</strong>
                        <p class="text-gray-600 dark:text-gray-400">{{ $agent->agent_id }}</p>
                    </div>
                    <div>
                        <strong class="font-medium text-gray-900 dark:text-gray-200">Name:</strong>
                        <p class="text-gray-600 dark:text-gray-400">{{ $agent->name }}</p>
                    </div>
                    <div>
                        <strong class="font-medium text-gray-900 dark:text-gray-200">Contact No:</strong>
                        <p class="text-gray-600 dark:text-gray-400">{{ $agent->contact_no }}</p>
                    </div>
                     <div>
                        <strong class="font-medium text-gray-900 dark:text-gray-200">Email:</strong>
                        <p class="text-gray-600 dark:text-gray-400">{{ $agent->email ?? 'N/A' }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <strong class="font-medium text-gray-900 dark:text-gray-200">Address:</strong>
                        <p class="text-gray-600 dark:text-gray-400">{{ $agent->address }}</p>
                    </div>
                </div>
            </div>

             <!-- Product & Pricing Information -->
            <div>
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">Product & Pricing</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                     <div>
                        <strong class="font-medium text-gray-900 dark:text-gray-200">Assigned Product:</strong>
                        <p class="text-gray-600 dark:text-gray-400">
                            @if ($agent->product)
                                <a href="{{ route('products.show', $agent->product->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                    {{ $agent->product->name }}
                                </a>
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                    <div>
                        <strong class="font-medium text-gray-900 dark:text-gray-200">Price Per Case:</strong>
                        <p class="text-gray-600 dark:text-gray-400">{{ number_format($agent->price_per_case, 2) }}</p>
                    </div>
                    <div>
                        <strong class="font-medium text-gray-900 dark:text-gray-200">Unit of Measure:</strong>
                        <p class="text-gray-600 dark:text-gray-400">{{ $agent->unit_of_measure }}</p>
                    </div>
                </div>
            </div>

            <!-- Credit & Status Information -->
            <div>
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700 pb-2 mb-4">Credit & Status</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                    <div>
                        <strong class="font-medium text-gray-900 dark:text-gray-200">Credit Limit:</strong>
                        <p class="text-gray-600 dark:text-gray-400">{{ number_format($agent->credit_limit, 2) }}</p>
                    </div>
                    <div>
                        <strong class="font-medium text-gray-900 dark:text-gray-200">Credit Period:</strong>
                        <p class="text-gray-600 dark:text-gray-400">{{ $agent->credit_period ? $agent->credit_period . ' Days' : 'N/A' }}</p>
                    </div>
                    <div>
                        <strong class="font-medium text-gray-900 dark:text-gray-200">Status:</strong>
                        <p class="text-gray-600 dark:text-gray-400">
                             @if($agent->is_active)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #agent-details, #agent-details * {
            visibility: visible;
        }
        #agent-details {
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

