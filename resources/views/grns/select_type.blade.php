@extends('layouts.app')

@section('content')
<div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow-md" x-data="poModal()">
    <h2 class="text-2xl font-bold mb-4 text-gray-900 dark:text-gray-200">Create GRN</h2>

    <div class="flex space-x-4">
        {{-- Create Without PO --}}
        <a href="{{ route('grns.create') }}"
           class="px-6 py-3 bg-gray-800 text-white rounded-lg hover:bg-gray-700">
           Create Without PO
        </a>

        {{-- Open PO Selection Modal --}}
        <button @click="showPoModal = true"
                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Create Using GRN PO
        </button>
    </div>

    {{-- Modal --}}
    <div x-show="showPoModal"
         class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
         x-transition>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-3xl relative">
            
            {{-- Close Button --}}
            <button @click="showPoModal = false"
                    class="absolute top-3 right-3 text-gray-500 hover:text-gray-800 text-2xl leading-none">&times;</button>

            <h3 class="text-xl font-bold text-gray-800 dark:text-gray-200 mb-4">Select GRN PO (Pending)</h3>

            {{-- Filter Section --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Filter by Supplier:
                </label>
                <select id="supplierFilter"
                        class="mt-1 block w-full border rounded-md dark:bg-gray-900 dark:text-white"
                        @change="fetchPos($event.target.value)">
                    <option value="">-- Select Supplier --</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->supplier_name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- PO List --}}
            <div class="overflow-x-auto max-h-80 border rounded-md">
                <table class="w-full text-sm divide-y divide-gray-300 dark:divide-gray-700">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th class="p-2 text-left">GRN PO ID</th>
                            <th class="p-2 text-left">Supplier</th>
                            <th class="p-2 text-center">Delivery Date</th>
                            <th class="p-2 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="poList"
                           class="bg-white dark:bg-gray-800 divide-y divide-gray-300 dark:divide-gray-700">
                        <tr>
                            <td colspan="4" class="text-center text-gray-500 p-3">
                                Select a supplier to view pending POs
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Alpine + Fetch Script --}}
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('poModal', () => ({
        showPoModal: false,
        isLoading: false,

        fetchPos(supplierId) {
            const poList = document.getElementById('poList');

            if (!supplierId) {
                poList.innerHTML =
                    `<tr><td colspan="4" class="text-center text-gray-500 p-3">
                        Select a supplier to view pending POs
                    </td></tr>`;
                return;
            }

            this.isLoading = true;
            poList.innerHTML =
                `<tr><td colspan="4" class="text-center p-3 text-blue-500">
                    Loading pending GRN POs...
                </td></tr>`;

            fetch(`/grnpos/pending/${supplierId}`)
                .then(res => {
                    if (!res.ok) throw new Error('Network response was not ok');
                    return res.json();
                })
                .then(data => {
                    let html = '';
                    if (data.length === 0) {
                        html = `<tr><td colspan="4" class="text-center p-3 text-gray-500">
                                    No pending GRN POs found for this supplier.
                                </td></tr>`;
                    } else {
                        data.forEach(po => {
                            html += `
                                <tr>
                                    <td class="p-2">${po.grnpo_id}</td>
                                    <td class="p-2">${po.supplier_name}</td>
                                    <td class="p-2 text-center">${po.delivery_date}</td>
                                    <td class="p-2 text-center">
                                        <a href="/grns/create/from-po/${po.id}"
                                           @click="showPoModal = false"
                                           class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-xs">
                                           Use This PO
                                        </a>
                                    </td>
                                </tr>`;
                        });
                    }
                    poList.innerHTML = html;
                })
                .catch(err => {
                    console.error('Error fetching pending GRN POs:', err);
                    poList.innerHTML =
                        `<tr><td colspan="4" class="text-center text-red-500 p-3">
                            Failed to load pending GRN POs.
                        </td></tr>`;
                })
                .finally(() => {
                    this.isLoading = false;
                });
        }
    }));
});
</script>
@endsection
