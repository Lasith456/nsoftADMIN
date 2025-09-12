@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div id="vehicle-details" class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4">
        <div class="flex justify-between items-center mb-4 pb-3 border-b dark:border-gray-700 print:hidden">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">Vehicle & Driver Details</h2>
            <div class="flex items-center space-x-2">
                <a class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 text-xs uppercase font-semibold" href="{{ url()->previous() }}">
                    Back
                </a>
                  <a href="{{ route('vehicles.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase hover:bg-gray-300 dark:hover:bg-gray-600">
                    Back to List
                </a>
                <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-xs uppercase font-semibold">
                    Print
                </button>
            </div>
        </div>

        <div class="space-y-6">
            <!-- Vehicle Details -->
            <div>
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-200 border-b dark:border-gray-700 pb-2 mb-4">Vehicle Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <strong class="font-medium text-gray-600 dark:text-gray-400 block">Vehicle No:</strong>
                        <p class="text-gray-800 dark:text-gray-200">{{ $vehicle->vehicle_no }}</p>
                    </div>
                    <div>
                        <strong class="font-medium text-gray-600 dark:text-gray-400 block">Status:</strong>
                        @if($vehicle->is_active)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Driver Details -->
            <div>
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-200 border-b dark:border-gray-700 pb-2 mb-4">Driver's Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <strong class="font-medium text-gray-600 dark:text-gray-400 block">Driver ID:</strong>
                        <p class="text-gray-800 dark:text-gray-200">{{ $vehicle->driver_id }}</p>
                    </div>
                    <div>
                        <strong class="font-medium text-gray-600 dark:text-gray-400 block">Driver Name:</strong>
                        <p class="text-gray-800 dark:text-gray-200">{{ $vehicle->title }} {{ $vehicle->driver_name }}</p>
                    </div>
                    <div>
                        <strong class="font-medium text-gray-600 dark:text-gray-400 block">Driver NIC:</strong>
                        <p class="text-gray-800 dark:text-gray-200">{{ $vehicle->driver_nic ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <strong class="font-medium text-gray-600 dark:text-gray-400 block">Driver Mobile:</strong>
                        <p class="text-gray-800 dark:text-gray-200">{{ $vehicle->driver_mobile }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <strong class="font-medium text-gray-600 dark:text-gray-400 block">Driver Address:</strong>
                        <p class="text-gray-800 dark:text-gray-200 whitespace-pre-line">{{ $vehicle->driver_address }}</p>
                    </div>
                </div>
            </div>

            <!-- Assistant Details -->
            <div>
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-200 border-b dark:border-gray-700 pb-2 mb-4">Assistant's Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <strong class="font-medium text-gray-600 dark:text-gray-400 block">Assistant Name:</strong>
                        <p class="text-gray-800 dark:text-gray-200">{{ $vehicle->assistant_name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <strong class="font-medium text-gray-600 dark:text-gray-400 block">Assistant NIC:</strong>
                        <p class="text-gray-800 dark:text-gray-200">{{ $vehicle->assistant_nic ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <strong class="font-medium text-gray-600 dark:text-gray-400 block">Assistant Mobile:</strong>
                        <p class="text-gray-800 dark:text-gray-200">{{ $vehicle->assistant_mobile ?? 'N/A' }}</p>
                    </div>
                    <div class="md:col-span-3">
                        <strong class="font-medium text-gray-600 dark:text-gray-400 block">Assistant Address:</strong>
                        <p class="text-gray-800 dark:text-gray-200 whitespace-pre-line">{{ $vehicle->assistant_address ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Other Details -->
            <div>
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-200 border-b dark:border-gray-700 pb-2 mb-4">Other Details</h3>
                <div class="grid grid-cols-1 text-sm">
                    <div class="mb-4">
                        <strong class="font-medium text-gray-600 dark:text-gray-400 block">Remark:</strong>
                        <p class="text-gray-800 dark:text-gray-200 whitespace-pre-line">{{ $vehicle->remark ?? 'N/A' }}</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <strong class="font-medium text-gray-600 dark:text-gray-400 block">Created At:</strong>
                            <p class="text-gray-800 dark:text-gray-200">{{ $vehicle->created_at->format('Y-m-d H:i A') }}</p>
                        </div>
                        <div>
                            <strong class="font-medium text-gray-600 dark:text-gray-400 block">Last Updated:</strong>
                            <p class="text-gray-800 dark:text-gray-200">{{ $vehicle->updated_at->format('Y-m-d H:i A') }}</p>
                        </div>
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
        #vehicle-details, #vehicle-details * {
            visibility: visible;
        }
        #vehicle-details {
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

