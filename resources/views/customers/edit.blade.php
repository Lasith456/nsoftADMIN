@extends('layouts.app')

@section('content')
<div class="container mx-auto">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4">
        <div class="flex justify-between items-center mb-4 pb-3 border-b dark:border-gray-700">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">Edit Customer</h2>
            <div class="flex items-center space-x-2">
                <a class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 text-xs uppercase font-semibold" href="{{ route('customers.index') }}">
                    Back
                </a>
                <button type="submit" form="customer-form" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-300 text-xs uppercase font-semibold">
                    Update Customer
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

        <form id="customer-form" action="{{ route('customers.update', $customer->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <!-- Title, Customer Name, Display Name -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title <span class="text-red-500">*</span></label>
                    <select name="title" id="title" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm" required>
                        <option value="" disabled>Select a title</option>
                        @php
                            $titles = ['Mr', 'Miss', 'Ms', 'Dr', 'Ven'];
                        @endphp
                        @foreach($titles as $title)
                            <option value="{{ $title }}" {{ old('title', $customer->title) == $title ? 'selected' : '' }}>{{ $title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="customer_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer Name <span class="text-red-500">*</span></label>
                    <input type="text" name="customer_name" id="customer_name" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm" placeholder="Full Name" value="{{ old('customer_name', $customer->customer_name) }}" required>
                </div>
                <div>
                    <label for="display_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Display Name <span class="text-red-500">*</span></label>
                    <input type="text" name="display_name" id="display_name" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm" placeholder="Display Name" value="{{ old('display_name', $customer->display_name) }}" required>
                </div>

                <!-- Company, NIC, Mobile -->
                <div>
                    <label for="company_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company Name</label>
                    <input type="text" name="company_name" id="company_name" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm" placeholder="Company Name" value="{{ old('company_name', $customer->company_name) }}">
                </div>
                <div>
                    <label for="nic" class="block text-sm font-medium text-gray-700 dark:text-gray-300">NIC</label>
                    <input type="text" name="nic" id="nic" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm" placeholder="National Identity Card No." value="{{ old('nic', $customer->nic) }}">
                </div>
                <div>
                    <label for="customer_mobile" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer Mobile <span class="text-red-500">*</span></label>
                    <input type="tel" name="customer_mobile" id="customer_mobile" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm" placeholder="07..." value="{{ old('customer_mobile', $customer->customer_mobile) }}" required>
                </div>

                <!-- Phone, Work Phone, Email -->
                <div>
                    <label for="customer_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer Phone</label>
                    <input type="tel" name="customer_phone" id="customer_phone" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm" placeholder="011..." value="{{ old('customer_phone', $customer->customer_phone) }}">
                </div>
                <div>
                    <label for="work_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Work Phone</label>
                    <input type="tel" name="work_phone" id="work_phone" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm" placeholder="Work Phone" value="{{ old('work_phone', $customer->work_phone) }}">
                </div>
                <div>
                    <label for="customer_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Customer Email</label>
                    <input type="email" name="customer_email" id="customer_email" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm" placeholder="example@mail.com" value="{{ old('customer_email', $customer->customer_email) }}">
                </div>

                <!-- Addresses -->
                <div class="md:col-span-3">
                    <label for="primary_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Primary Address <span class="text-red-500">*</span></label>
                    <textarea name="primary_address" id="primary_address" rows="2" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm" placeholder="Primary Address" required>{{ old('primary_address', $customer->primary_address) }}</textarea>
                </div>
                <div class="md:col-span-3">
                    <label for="company_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company Address</label>
                    <textarea name="company_address" id="company_address" rows="2" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm" placeholder="Company Address">{{ old('company_address', $customer->company_address) }}</textarea>
                </div>
                
                <!-- Credit Limit and Remark -->
                <div>
                    <label for="credit_limit" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Credit Limit</label>
                    <input type="number" step="0.01" name="credit_limit" id="credit_limit" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm" placeholder="0.00" value="{{ old('credit_limit', $customer->credit_limit) }}">
                </div>
                <div class="md:col-span-2">
                    <label for="remark" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Remark</label>
                    <textarea name="remark" id="remark" rows="2" class="mt-1 block w-full px-3 py-1 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 rounded-md shadow-sm" placeholder="Any additional remarks">{{ old('remark', $customer->remark) }}</textarea>
                </div>

                <!-- Is Active Toggle -->
                <div class="md:col-span-3 flex items-center pt-2">
                    <input type="checkbox" name="is_active" id="is_active" class="h-4 w-4 text-blue-600 border-gray-300 rounded" {{ old('is_active', $customer->is_active) ? 'checked' : '' }}>
                    <label for="is_active" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">Is Active</label>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

