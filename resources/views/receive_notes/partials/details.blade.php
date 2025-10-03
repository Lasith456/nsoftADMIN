<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receive Note - {{ $receiveNote->receive_note_id }}</title>
    <!-- ✅ TailwindCSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 p-6">

    <div id="receive-note-details" class="bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6 max-w-5xl mx-auto">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6 border-b pb-2">
            Receive Note: {{ $receiveNote->receive_note_id }}
        </h2>

        <!-- Meta Info -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
            <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg shadow-inner">
                <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Received Date</p>
                <p class="font-semibold text-gray-800 dark:text-gray-200">
                    {{ $receiveNote->received_date->format('F j, Y') }}
                </p>
            </div>
            <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg shadow-inner">
                <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Customer</p>
                <p class="font-semibold text-gray-800 dark:text-gray-200">
                    {{ $receiveNote->deliveryNotes->first()?->purchaseOrders->first()?->customer?->customer_name ?? 'N/A' }}
                </p>
            </div>
            <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg shadow-inner">
                <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Status</p>
                <span class="inline-block mt-1 px-3 py-1 text-xs font-semibold rounded-full 
                    @if($receiveNote->status == 'completed') bg-green-100 text-green-700 
                    @else bg-yellow-100 text-yellow-700 @endif">
                    {{ ucfirst($receiveNote->status) }}
                </span>
            </div>
        </div>

        <!-- Delivery Notes -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-2">Assigned Delivery Notes</h3>
            @if($receiveNote->deliveryNotes->count() > 0)
                <div class="flex flex-wrap gap-2">
                    @foreach($receiveNote->deliveryNotes as $dn)
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded-full text-sm font-medium">
                            {{ $dn->delivery_note_id }}
                        </span>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-sm">No Delivery Notes assigned.</p>
            @endif
        </div>

        <!-- Purchase Orders -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-2">Assigned Purchase Orders</h3>
            @php
                $purchaseOrders = $receiveNote->deliveryNotes->flatMap->purchaseOrders->unique('id');
            @endphp
            @if($purchaseOrders->count() > 0)
                <div class="flex flex-wrap gap-2">
                    @foreach($purchaseOrders as $po)
                        <span class="px-3 py-1 bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200 rounded-full text-sm font-medium">
                            {{ $po->po_id }}
                        </span>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-sm">No Purchase Orders assigned.</p>
            @endif
        </div>

        <!-- Items Table -->
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3">Items Received</h3>
        <div class="overflow-x-auto rounded-lg shadow">
            <table class="w-full text-sm border-collapse">
                <thead class="bg-gray-100 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Product</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Expected</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Received</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Discrepancy</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-600 bg-white dark:bg-gray-800">
                    @foreach($receiveNote->items as $item)
                    <tr>
                        <td class="px-4 py-2 font-medium text-gray-800 dark:text-gray-200">
                            {{ $item->product->name ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-2 text-gray-600 dark:text-gray-400">
                            {{ $item->quantity_expected }}
                        </td>
                        <td class="px-4 py-2 font-bold 
                            {{ $item->quantity_received < $item->quantity_expected ? 'text-red-500' : 'text-green-600' }}">
                            {{ $item->quantity_received }}
                        </td>
                        <td class="px-4 py-2 text-gray-600 dark:text-gray-400">
                            {{ $item->discrepancy_reason ?? 'N/A' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
