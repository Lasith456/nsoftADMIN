@extends('layouts.app')

@section('content')
<div class="container mx-auto" x-data="supplierInvoiceForm()">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 max-w-4xl mx-auto">
        <div class="border-b pb-4 mb-4">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Generate Supplier Invoice</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Select a supplier and the GRNs you wish to include in the invoice.</p>
        </div>

        @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <ul class="list-disc pl-5 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
        @endif

        <form action="{{ route('invoices.storeSupplier') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="supplier_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select Supplier</label>
                    <select id="supplier_id" @change="selectedGrnIds = []" x-model="selectedSupplierId" class="mt-1 block w-full dark:bg-gray-900 rounded-md py-2 px-3 border-gray-300 dark:border-gray-600" required>
                        <option value="">Select a supplier with confirmed GRNs...</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->supplier_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div x-show="selectedSupplierId" class="mt-6 border-t pt-4" x-cloak>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Select GRNs to Invoice</h3>
                <div class="space-y-2 max-h-60 overflow-y-auto border dark:border-gray-700 p-2 rounded-md">
                    <template x-if="availableGrns.length > 0">
                        <template x-for="grn in availableGrns" :key="grn.id">
                            <label class="flex items-center p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700">
                                <input type="checkbox" name="grn_ids[]" :value="grn.id" x-model="selectedGrnIds" class="dark:bg-gray-900 rounded">
                                <span class="ml-3 text-sm" x-text="`${grn.grn_id} - ${grn.delivery_date} - LKR ${parseFloat(grn.net_amount).toFixed(2)}`"></span>
                            </label>
                        </template>
                    </template>
                     <template x-if="availableGrns.length === 0">
                        <p class="text-sm text-gray-500 dark:text-gray-400 p-2">No confirmed GRNs available for this supplier.</p>
                    </template>
                </div>
            </div>
            
            <div class="text-right pt-6 mt-6 border-t dark:border-gray-700">
                <a href="{{ route('invoices.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase">Cancel</a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border rounded-md font-semibold text-xs text-white uppercase hover:bg-gray-700">
                    Generate Consolidated Invoice
                </button>
            </div>
        </form>
    </div>
</div>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('supplierInvoiceForm', () => ({
        suppliers: @json($suppliers->keyBy('id')),
        selectedSupplierId: '',
        selectedGrnIds: [],
        
        get availableGrns() {
            if (!this.selectedSupplierId) return [];
            const supplier = this.suppliers[this.selectedSupplierId];
            return supplier ? supplier.grns : [];
        }
    }));
});
</script>
@endsection

