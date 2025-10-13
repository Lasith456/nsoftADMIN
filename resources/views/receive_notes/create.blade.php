@extends('layouts.app')

@section('content')
<div class="container mx-auto p-2" x-data="receiveNoteForm()">
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4">
        {{-- HEADER --}}
        <div class="flex justify-between items-center mb-4 pb-3 border-b dark:border-gray-700">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Create Receive Note</h2>
            <div class="flex items-center space-x-2">
                <a href="{{ route('receive-notes.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase">
                    Back
                </a>
                <button type="button"
                        @click="checkDiscrepancy"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border rounded-md font-semibold text-xs text-white uppercase hover:bg-blue-700">
                    Submit Note
                </button>
            </div>
        </div>

        {{-- ERRORS --}}
        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                <p class="font-bold">Whoops! Something went wrong.</p>
                <ul class="list-disc pl-5 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- MAIN GRID --}}
        <div class="grid grid-cols-1 lg:grid-cols-[1fr_2fr] gap-6">
            {{-- LEFT SIDE --}}
            <div class="lg:col-span-1 flex flex-col space-y-4">
                {{-- Filters --}}
                <form action="{{ route('receive-notes.create') }}" method="GET"
                      class="mb-2 p-2 bg-gray-50 dark:bg-gray-700 rounded-md flex flex-wrap gap-2 items-center text-xs">
                      <div class="w-full">
                        <label class="block text-sm font-medium">Company*</label>
                        <select x-model="selectedCompany" @change="filterCustomersByCompany"
                            class="w-full border rounded-md py-1 px-2">
                            <option value="">Select Company</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                    {{ $company->company_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full">
                        <label class="block text-sm font-medium">Customer*</label>
                        <input list="customers-list"
                            x-model="customerName"
                            value="{{ request('customer_name') }}"
                            @input="setCustomerIdFromDatalist($event)"
                            @change="setCustomerIdFromDatalist($event)"
                            placeholder="Type customer name..."
                            class="w-full border rounded-md py-1 px-2"
                            :disabled="!selectedCompany">

                        <datalist id="customers-list">
                            <template x-for="cust in filteredCustomers" :key="cust.id">
                                <option :value="cust.customer_name" :data-id="cust.id"></option>
                            </template>
                        </datalist>
                        <input type="hidden" name="customer_id" x-model="selectedCustomer">
                    </div>

                    <input type="hidden" name="company_id" :value="selectedCompany">
                    <input type="hidden" name="customer_name" :value="customerName">
                    <input type="hidden" name="customer_id" :value="selectedCustomer">

                    <input type="date" name="from_date" value="{{ request('from_date') }}"
                           class="border rounded-md px-2 py-0.5 w-32 text-xs">
                    <input type="date" name="to_date" value="{{ request('to_date') }}"
                           class="border rounded-md px-2 py-0.5 w-32 text-xs">
                    <button class="px-2 py-1 bg-gray-800 text-white rounded-md text-xs">Filter</button>
                    <a href="{{ route('receive-notes.create') }}" class="px-2 py-1 bg-gray-200 text-gray-800 rounded-md text-xs">Clear</a>
                </form>

                {{-- DELIVERY NOTES --}}
                <form id="receive-note-form" action="{{ route('receive-notes.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="session_token" x-model="sessionToken">
                    <div class="space-y-2 max-h-48 overflow-y-auto border rounded-md p-2">
                        @if(!request('customer_id'))
                            <p class="text-sm text-gray-500">Select a customer to see Delivery Notes.</p>
                        @else
                            @forelse($deliveryNotes as $dn)
                                <label class="flex items-center p-2 hover:bg-gray-100 rounded-md">
                                    <input type="checkbox" name="delivery_note_ids[]" value="{{ $dn->id }}"
                                           x-model="selectedDeliveryNoteIds" @change="fetchItems"
                                           class="rounded">
                                    <span class="ml-3 text-sm">{{ $dn->delivery_note_id }} – {{ $dn->customer?->customer_name ?? 'N/A' }}</span>
                                </label>
                            @empty
                                <p class="text-sm text-gray-500">No pending Delivery Notes found.</p>
                            @endforelse
                        @endif
                    </div>

                    {{-- Extra fields --}}
                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="text-sm font-medium">Received Date*</label>
                            <input type="date" name="received_date"
                                   value="{{ old('received_date', date('Y-m-d')) }}"
                                   class="w-full rounded-md py-2 px-3 border dark:bg-gray-900" required>
                        </div>
                        <div>
                            <label class="text-sm font-medium">Notes / Remarks</label>
                            <textarea name="notes" rows="3"
                                      class="w-full rounded-md py-2 px-3 border dark:bg-gray-900">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </form>
            </div>

            {{-- RIGHT SIDE --}}
            <div class="lg:col-span-1">
                <h3 class="text-lg font-bold mb-2">Verify Quantities</h3>
                <div class="overflow-y-auto max-h-[28rem] border rounded-md">
                    <table class="w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium uppercase">DN</th>
                            <th class="px-4 py-2 text-left text-xs font-medium uppercase">PO</th>
                            <th class="px-4 py-2 text-left text-xs font-medium uppercase">Category</th>
                            <th class="px-4 py-2 text-left text-xs font-medium uppercase">Product</th>
                            <th class="px-4 py-2 text-right text-xs font-medium uppercase">Expected</th>
                            <th class="px-4 py-2 text-right text-xs font-medium uppercase">Received</th>
                            <th class="px-4 py-2 text-right text-xs font-medium uppercase">Difference</th>
                        </tr>
                        </thead>
                        <tbody>
                        <template x-for="(item, index) in items" :key="`${item.delivery_note_id || 'DN'}-${item.purchase_order_id || 'PO'}-${item.product_id || index}`">
                        <tr>
                            <td class="px-4 py-2 text-sm font-semibold text-blue-600" x-text="item.delivery_note_id"></td>
                            <td class="px-4 py-2 text-sm font-semibold text-purple-600" x-text="item.po_code"></td>
                            <td class="px-4 py-2 text-sm text-gray-700" x-text="item.category_name"></td>
                            <td class="px-4 py-2 text-sm" x-text="item.product_name"></td>
                            <td class="px-4 py-2 text-sm text-right" x-text="item.quantity_expected"></td>
                            <td class="px-4 py-2 text-sm text-right">
                                <input type="number"
                                    :name="`items[${index}][quantity_received]`"
                                    x-model.number="item.quantity_received"
                                    @input="calculateDiff(item)"
                                    class="w-20 border rounded-md py-1 px-2 text-sm text-right"
                                    form="receive-note-form">
                                <input type="hidden" :name="`items[${index}][product_id]`" :value="item.product_id" form="receive-note-form">
                                <input type="hidden" :name="`items[${index}][quantity_expected]`" :value="item.quantity_expected" form="receive-note-form">
                            </td>
                            <td class="px-4 py-2 text-sm text-right" x-text="item.difference"></td>
                        </tr>
                        </template>
                        <tr x-show="items.length === 0">
                            <td colspan="7" class="text-center py-4 text-sm text-gray-500">Select delivery notes to load items.</td>
                        </tr>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>

        {{-- MULTI-ITEM DISCREPANCY MODAL --}}
        <div x-show="showDiscrepancyModal"
             class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg w-[38rem] shadow-lg max-h-[36rem] overflow-y-auto">
                <h3 class="text-lg font-bold mb-2">Resolve Quantity Differences</h3>
                <p class="text-sm text-gray-600 mb-3">Specify how to handle each product with mismatched quantities:</p>
                
                <template x-for="(item, index) in discrepancyItems" :key="item.product_id">
                    <div class="border-b pb-2 mb-3">
                        <div class="flex justify-between">
                            <span class="font-semibold text-sm" x-text="item.product_name"></span>
                            <span class="text-xs text-gray-600">Diff: <span x-text="item.difference"></span></span>
                        </div>
                        <div class="mt-1 grid grid-cols-2 gap-2">
                            {{-- 🟢 UPDATED: Dynamic options based on stock origin --}}
                            <template x-if="item.is_mixed_stock">
                                <select x-model="item.actionType" class="border rounded-md p-1 text-sm w-full">
                                    <option value="">-- Select Action --</option>
                                    <option value="return">Return to Own Stock</option>
                                    <option value="agent_return">Return to Agent</option>
                                    <option value="wastage">Add to Wastage</option>
                                </select>
                            </template>
                            <template x-if="!item.is_mixed_stock">
                                <select x-model="item.actionType" class="border rounded-md p-1 text-sm w-full">
                                    <option value="">-- Select Action --</option>
                                    <option x-show="!item.agent_id" value="return">Return to Stock</option>
                                    <option x-show="item.agent_id" value="agent_return">Return to Agent</option>
                                    <option value="wastage">Add to Wastage</option>
                                </select>
                            </template>
                            {{-- 🟢 END --}}
                            <input type="text" x-model="item.reason"
                                   placeholder="Enter reason..."
                                   class="border rounded-md p-1 text-sm w-full">
                        </div>
                    </div>
                </template>

                <div class="flex justify-end space-x-2 mt-4">
                    <button @click="showDiscrepancyModal=false"
                            class="px-3 py-1 bg-gray-300 rounded text-sm">Cancel</button>
                    <button @click="submitMultipleActions"
                            class="px-3 py-1 bg-green-600 text-white rounded text-sm">Confirm & Continue</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT --}}
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('receiveNoteForm', () => ({
        sessionToken: '{{ \Illuminate\Support\Str::uuid() }}',
        selectedCompany: '{{ $selectedCompanyId ?? '' }}',
        customerName: '{{ $selectedCustomerName ?? '' }}',
        selectedCustomer: '{{ $selectedCustomerId ?? '' }}',
        filteredCustomers: [],
        customers: @json($allCustomers),

        selectedDeliveryNoteIds: [],
        items: [],
        discrepancyItems: [],
        showDiscrepancyModal: false,

        filterCustomersByCompany() {
            this.filteredCustomers = this.customers.filter(c => c.company_id == this.selectedCompany);
        },

        setCustomerIdFromDatalist(event) {
            const value = event.target.value;
            const option = Array.from(event.target.list?.options || []).find(o => o.value === value);
            this.selectedCustomer = option ? option.dataset.id : null;
        },

        fetchItems() {
            if (!this.selectedDeliveryNoteIds.length) return this.items = [];
            fetch('{{ route("receive-notes.getItems") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ dn_ids: this.selectedDeliveryNoteIds })
            })
            .then(res => res.json())
            .then(data => {
                this.$nextTick(() => {
                    this.items.splice(0, this.items.length, ...data.items.map(it => ({
                        ...it,
                        quantity_received: it.quantity_expected,
                        difference: 0,
                        agent_id: it.agent_id || null,
                        is_mixed_stock: it.is_mixed_stock || false
                    })));
                });
            })

            .catch(err => console.error('⚠️ Failed to fetch items:', err));
        },

        calculateDiff(item) {
            const exp = parseFloat(item.quantity_expected || 0);
            const rec = parseFloat(item.quantity_received || 0);
            item.difference = rec - exp;
        },

        checkDiscrepancy() {
            this.discrepancyItems = this.items
                .filter(i => i.quantity_expected !== i.quantity_received)
                .map(i => ({ ...i, actionType: '', reason: '' }));

            if (this.discrepancyItems.length > 0) {
                this.showDiscrepancyModal = true;
            } else {
                document.getElementById('receive-note-form').submit();
            }
        },

        async submitMultipleActions() {
            const invalid = this.discrepancyItems.find(i => !i.actionType);
            if (invalid) {
                alert("⚠️ Please select action for all discrepancy items.");
                return;
            }

            for (const item of this.discrepancyItems) {
                const diff = Math.abs(item.difference);
                if (diff === 0) continue;

                if (item.actionType === 'return') {
                    await fetch('{{ route("return-notes.store.ajax") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({
                            reason: item.reason || 'Return due to mismatch',
                            company_id: this.selectedCompany,
                            customer_id: this.selectedCustomer,
                            agent_id: null,
                            product_id: item.product_id,
                            quantity: diff,
                            session_token: this.sessionToken,

                        })
                    });

                    await fetch('{{ route("stock-management.api.apiConvertINRN") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({
                            product_id: item.product_id,
                            quantity: diff,
                            delivery_note_id: item.delivery_note_id,
                            reason: item.reason || 'Returned item added to stock'
                        })
                    });
                }

                if (item.actionType === 'agent_return') {
                    await fetch('{{ route("return-notes.store.ajax") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({
                            reason: item.reason || 'Agent stock return',
                            company_id: this.selectedCompany,
                            customer_id: this.selectedCustomer,
                            agent_id: item.agent_id, // 🟢 agent linked
                            product_id: item.product_id,
                            quantity: diff,
                            session_token: this.sessionToken,

                        })
                    });
                }

                if (item.actionType === 'wastage') {
                    await fetch('{{ route("stock-management.api.wastageRN") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({
                            product_id: item.product_id,
                            stock_type: 'clear',
                            quantity: diff,
                            reason: item.reason || 'Wastage due to receive note difference'
                        })
                    });
                }
            }
            this.items.forEach((it, index) => {
                    const matched = this.discrepancyItems.find(d => d.product_id === it.product_id);
                    if (matched) {
                        const inputName = `items[${index}][discrepancy_reason]`;
                        let hiddenInput = document.querySelector(`input[name="${inputName}"]`);
                        if (!hiddenInput) {
                            hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = inputName;
                            document.getElementById('receive-note-form').appendChild(hiddenInput);
                        }
                        hiddenInput.value = matched.reason || '';
                    }
                });
                // ✅ Add a hidden input to mark that wastage exists
                const wastageSelected = this.discrepancyItems.some(i => i.actionType === 'wastage');
                let hiddenFlag = document.querySelector('input[name="has_wastage"]');
                if (!hiddenFlag) {
                    hiddenFlag = document.createElement('input');
                    hiddenFlag.type = 'hidden';
                    hiddenFlag.name = 'has_wastage';
                    document.getElementById('receive-note-form').appendChild(hiddenFlag);
                }
                hiddenFlag.value = wastageSelected ? '1' : '0';

            alert('✅ All discrepancies handled successfully.');
            this.showDiscrepancyModal = false;
            document.getElementById('receive-note-form').submit();
        }
    }));
});
</script>
@endsection
