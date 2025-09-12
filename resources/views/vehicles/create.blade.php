@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4">
        <div class="flex justify-between items-center mb-4 pb-3 border-b dark:border-gray-700">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">Add New Vehicle</h2>
            <div class="flex items-center space-x-2">
                <a class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 text-xs uppercase font-semibold" href="{{ route('vehicles.index') }}">
                    Back
                </a>
                <button type="submit" form="vehicle-form" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-300 text-xs uppercase font-semibold">
                    Save Vehicle
                </button>
            </div>
        </div>

        @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-4" role="alert">
            <strong>Whoops!</strong> There were some problems with your input.<br><br>
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form id="vehicle-form" action="{{ route('vehicles.store') }}" method="POST">
            @csrf
            <div class="space-y-6">
                <!-- Vehicle Details -->
                <div>
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-200 border-b dark:border-gray-700 pb-2 mb-4">Vehicle Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="vehicle_no" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Vehicle No <span class="text-red-500">*</span></label>
                            <input type="text" name="vehicle_no" id="vehicle_no" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm" placeholder="e.g., ABC-1234" value="{{ old('vehicle_no') }}" required>
                        </div>
                        <div class="md:col-span-1">
                            <label for="remark" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Remark</label>
                            <textarea name="remark" id="remark" rows="1" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm">{{ old('remark') }}</textarea>
                        </div>
                        <div class="flex items-end pb-1">
                            <input type="checkbox" name="is_active" id="is_active" class="h-4 w-4 text-blue-600 border-gray-300 rounded" checked>
                            <label for="is_active" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">Is Active</label>
                        </div>
                    </div>
                </div>

                <!-- Driver Details -->
                <div>
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-200 border-b dark:border-gray-700 pb-2 mb-4">Driver's Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title <span class="text-red-500">*</span></label>
                            <select name="title" id="title" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm text-sm" required>
                                <option value="" disabled selected>Select a title</option>
                                <option value="Mr" {{ old('title') == 'Mr' ? 'selected' : '' }}>Mr</option>
                                <option value="Miss" {{ old('title') == 'Miss' ? 'selected' : '' }}>Miss</option>
                                <option value="Ms" {{ old('title') == 'Ms' ? 'selected' : '' }}>Ms</option>
                            </select>
                        </div>
                        <div>
                            <label for="driver_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Driver Name <span class="text-red-500">*</span></label>
                            <input type="text" name="driver_name" id="driver_name" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm" placeholder="Driver's Full Name" value="{{ old('driver_name') }}" required>
                        </div>
                        <div>
                            <label for="driver_nic" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Driver NIC</label>
                            <input type="text" name="driver_nic" id="driver_nic" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm" placeholder="Driver's NIC" value="{{ old('driver_nic') }}">
                        </div>
                        <div>
                            <label for="driver_mobile" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Driver Mobile <span class="text-red-500">*</span></label>
                            <input type="tel" name="driver_mobile" id="driver_mobile" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm" placeholder="Driver's Mobile No." value="{{ old('driver_mobile') }}" required>
                        </div>
                        <div class="md:col-span-2">
                            <label for="driver_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Driver Address <span class="text-red-500">*</span></label>
                            <textarea name="driver_address" id="driver_address" rows="1" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm" required>{{ old('driver_address') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Assistant Details -->
                <div>
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-200 border-b dark:border-gray-700 pb-2 mb-4">Assistant's Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="assistant_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assistant Name</label>
                            <input type="text" name="assistant_name" id="assistant_name" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm" placeholder="Assistant's Full Name" value="{{ old('assistant_name') }}">
                        </div>
                        <div>
                            <label for="assistant_nic" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assistant NIC</label>
                            <input type="text" name="assistant_nic" id="assistant_nic" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm" placeholder="Assistant's NIC" value="{{ old('assistant_nic') }}">
                        </div>
                         <div>
                            <label for="assistant_mobile" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assistant Mobile</label>
                            <input type="tel" name="assistant_mobile" id="assistant_mobile" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm" placeholder="Assistant's Mobile No." value="{{ old('assistant_mobile') }}">
                        </div>
                        <div class="md:col-span-3">
                            <label for="assistant_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assistant Address</label>
                            <textarea name="assistant_address" id="assistant_address" rows="1" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm">{{ old('assistant_address') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

