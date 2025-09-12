@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4" x-data="settingsPage()">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-4">Application Settings</h1>

    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif
    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- VAT Settings -->
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100 border-b pb-2">Financial Settings</h2>
            <form action="{{ route('settings.updateVat') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label for="vat_rate" class="block text-sm font-medium text-gray-700 dark:text-gray-300">VAT Rate (%)</label>
                    <input type="number" step="0.01" name="vat_rate" id="vat_rate" value="{{ old('vat_rate', $vatRate->value ?? '18.00') }}" class="mt-1 block w-full dark:bg-gray-900 border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3">
                </div>
                <div class="text-right">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold">Update VAT</button>
                </div>
            </form>
        </div>

        <!-- Bank Management -->
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Bank Management</h2>
                <button @click="isBankModalOpen = true" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm font-semibold">Add Bank</button>
            </div>
            <ul class="space-y-2">
                @forelse($banks as $bank)
                    <li class="flex justify-between items-center p-2 rounded-md {{ $bank->is_active ? '' : 'bg-gray-100 dark:bg-gray-700' }}">
                        <div>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $bank->name }}</span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">({{ $bank->code ?? 'N/A' }})</span>
                        </div>
                        {{-- ** THE FIX IS HERE: Replaced span with a toggle button form ** --}}
                        <form action="{{ route('settings.banks.toggleStatus', $bank->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="text-xs font-semibold px-3 py-1 rounded-full {{ $bank->is_active ? 'bg-green-200 text-green-800 hover:bg-green-300' : 'bg-red-200 text-red-800 hover:bg-red-300' }}">
                                {{ $bank->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                    </li>
                @empty
                    <li class="text-center text-gray-500 dark:text-gray-400 py-4">No banks have been added yet.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <!-- Add Bank Modal -->
    <div x-show="isBankModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @click.away="isBankModalOpen = false" x-cloak>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-4">Add New Bank</h2>
            <form action="{{ route('settings.storeBank') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-medium">Bank Name*</label>
                    <input type="text" name="name" id="name" class="mt-1 block w-full dark:bg-gray-900 border-gray-300 dark:border-gray-600 rounded-md" required>
                </div>
                <div>
                    <label for="code" class="block text-sm font-medium">Bank Code (e.g., COMBANK)</label>
                    <input type="text" name="code" id="code" class="mt-1 block w-full dark:bg-gray-900 border-gray-300 dark:border-gray-600 rounded-md">
                </div>
                 <div class="text-right space-x-2">
                    <button type="button" @click="isBankModalOpen = false" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Save Bank</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('settingsPage', () => ({
            isBankModalOpen: false,
        }));
    });
</script>
@endsection

