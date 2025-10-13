@extends('layouts.app')

@section('content')
<div class="bg-gray-100 dark:bg-gray-900 p-2" x-data="createFromPoForm()">
    <form action="{{ route('grns.store') }}" method="POST">
        @csrf
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4">

            {{-- Header --}}
            <div class="flex justify-between items-center mb-4 border-b pb-3 dark:border-gray-700">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                    Create GRN from PO: {{ $grnpo->grnpo_id }}
                </h2>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('grnpos.index') }}"
                       class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase">
                        Back
                    </a>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 border rounded-md font-semibold text-xs text-white uppercase hover:bg-blue-700">
                        Save GRN
                    </button>
                </div>
            </div>

            {{-- Supplier Info --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium">Supplier</label>
                    <input type="text" value="{{ $supplier->supplier_name }}" class="w-full border-gray-300 rounded-md dark:bg-gray-700 text-sm px-2 py-1" readonly>
                    <input type="hidden" name="supplier_id" value="{{ $supplier->id }}">
                </div>

                <div>
                    <label class="block text-sm font-medium">Delivery Date*</label>
                    <input type="date" name="delivery_date" value="{{ date('Y-m-d') }}" class="w-full border-gray-300 rounded-md dark:bg-gray-700 text-sm px-2 py-1" required>
                </div>

                <div>
                    <label class="block text-sm font-medium">Invoice Number</label>
                    <input type="text" name="invoice_number" class="w-full border-gray-300 rounded-md dark:bg-gray-700 text-sm px-2 py-1">
                </div>
                <input type="hidden" name="grnpo_id" value="{{ $grnpo->id }}">

            </div>

            {{-- Items Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm divide-y divide-gray-300 dark:divide-gray-700">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th class="p-2 text-left">Department</th>
                            <th class="p-2 text-left">Product</th>
                            <th class="p-2 text-center">Expected Qty</th>
                            <th class="p-2 text-center">Received Qty</th>
                            <th class="p-2 text-center">Stock Type</th>
                            <th class="p-2 text-center">Cost Price</th>
                            <th class="p-2 text-center">Selling Price</th>
                            <th class="p-2 text-center">Discount</th>
                            <th class="p-2 text-center">Free Issue Qty</th>
                            <th class="p-2 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(item, index) in items" :key="index">
                            <tr>
                                <td class="p-2" x-text="item.department_name"></td>
                                <td class="p-2" x-text="item.product_name"></td>
                                <td class="p-2 text-center" x-text="item.expected_qty"></td>
                                <td class="p-2 text-center">
                                    <input type="number" min="0" x-model.number="item.quantity_received"
                                           class="w-20 border-gray-300 rounded-md dark:bg-gray-700 text-center text-sm py-1 px-2">
                                </td>
                                <td class="p-2 text-center">
                                    <select x-model="item.stock_type" class="w-28 border-gray-300 rounded-md dark:bg-gray-700 text-sm py-1 px-2">
                                        <option value="clear">Clear</option>
                                        <option value="non-clear">Non-Clear</option>
                                    </select>
                                </td>
                                <td class="p-2 text-center">
                                    <input type="number" step="0.01" x-model.number="item.cost_price"
                                           class="w-24 border-gray-300 rounded-md dark:bg-gray-700 text-center text-sm py-1 px-2">
                                </td>
                                <td class="p-2 text-center">
                                    <input type="number" step="0.01" x-model.number="item.selling_price"
                                           class="w-24 border-gray-300 rounded-md dark:bg-gray-700 text-center text-sm py-1 px-2">
                                </td>
                                <td class="p-2 text-center">
                                    <input type="number" step="0.01" x-model.number="item.discount"
                                           class="w-20 border-gray-300 rounded-md dark:bg-gray-700 text-center text-sm py-1 px-2">
                                </td>
                                <td class="p-2 text-center">
                                    <input type="number" min="0" x-model.number="item.free_issue_qty"
                                           class="w-20 border-gray-300 rounded-md dark:bg-gray-700 text-center text-sm py-1 px-2">
                                </td>
                                <td class="p-2 text-center">
                                    <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700">&times;</button>
                                </td>
                            </tr>
                        </template>

                        <tr x-show="items.length === 0">
                            <td colspan="10" class="text-center text-gray-500 py-3">No items found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Totals --}}
            <div class="mt-4 bg-gray-50 dark:bg-gray-700 p-3 rounded-md w-full md:w-1/2 ml-auto">
                <h3 class="font-bold mb-2 text-gray-800 dark:text-gray-100">GRN Summary</h3>
                <div class="flex justify-between text-sm text-gray-800 dark:text-gray-200">
                    <span>Total Amount:</span>
                    <span x-text="totals.totalAmount.toFixed(2)"></span>
                </div>
                <div class="flex justify-between text-sm text-gray-800 dark:text-gray-200">
                    <span>Total Discount:</span>
                    <span x-text="totals.totalDiscount.toFixed(2)"></span>
                </div>
                <div class="flex justify-between text-lg font-bold text-green-600">
                    <span>Net Amount:</span>
                    <span x-text="totals.netAmount.toFixed(2)"></span>
                </div>
            </div>

            {{-- Hidden Inputs for Submission --}}
            <template x-for="(item, index) in items" :key="index">
                <div>
                    <input type="hidden" :name="`items[${index}][department_id]`" :value="item.department_id">
                    <input type="hidden" :name="`items[${index}][product_id]`" :value="item.product_id">
                    <input type="hidden" :name="`items[${index}][unit_type]`" value="Unit">
                    <input type="hidden" :name="`items[${index}][stock_type]`" :value="item.stock_type">
                    <input type="hidden" :name="`items[${index}][quantity]`" :value="item.quantity_received">
                    <input type="hidden" :name="`items[${index}][cost_price]`" :value="item.cost_price">
                    <input type="hidden" :name="`items[${index}][selling_price]`" :value="item.selling_price">
                    <input type="hidden" :name="`items[${index}][discount]`" :value="item.discount">
                    <input type="hidden" :name="`items[${index}][is_free_issue]`" :value="item.free_issue_qty > 0 ? 1 : 0">
                    <input type="hidden" :name="`items[${index}][free_issue_qty]`" :value="item.free_issue_qty">
                </div>
            </template>
        </div>
    </form>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('createFromPoForm', () => ({
        items: @json($poItems),

        removeItem(index) {
            this.items.splice(index, 1);
        },

        get totals() {
            let totalAmount = 0;
            let totalDiscount = 0;
            this.items.forEach(item => {
                totalAmount += (item.cost_price * item.quantity_received);
                totalDiscount += parseFloat(item.discount ?? 0);
            });
            return {
                totalAmount,
                totalDiscount,
                netAmount: totalAmount - totalDiscount,
            };
        }
    }));
});
</script>
@endsection
