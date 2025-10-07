@extends('layouts.app')

@section('content')
<div class="bg-white shadow-md rounded-lg p-4">
    <div id="report-content">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 pb-3 border-b print:hidden">
            <div>
                <h2 class="text-2xl font-bold text-black">Order Flow Report</h2>
                <p class="text-sm text-gray-500">Compares quantities from Purchase Order to Delivery and final Receipt.</p>
            </div>
            <button onclick="window.print()" class="mt-3 md:mt-0 px-4 py-2 bg-blue-600 text-white rounded-md text-xs uppercase font-semibold">Print</button>
        </div>
        
        <form action="{{ route('reports.order_flow') }}" method="GET" class="mb-4 print:hidden flex flex-wrap items-center gap-4">
            <div>
                <label for="start_date" class="text-sm">From:</label>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="border rounded-md p-1 text-sm">
            </div>
            <div>
                <label for="end_date" class="text-sm">To:</label>
                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="border rounded-md p-1 text-sm">
            </div>
            <div>
                <select name="customer_id" class="border rounded-md p-1.5 text-sm">
                    <option value="">All Customers</option>
                    @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->customer_name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md text-xs uppercase">Filter</button>
            <a href="{{ route('reports.order_flow') }}" class="px-4 py-2 bg-gray-200 text-black rounded-md text-xs uppercase">Clear</a>

            {{-- Export Buttons --}}
            <a href="{{ route('reports.order_flow.export.excel', request()->all()) }}" 
            class="px-4 py-2 bg-green-600 text-white rounded-md text-xs uppercase">Export Excel</a>
            <a href="{{ route('reports.order_flow.export.pdf', request()->all()) }}" 
            class="px-4 py-2 bg-red-600 text-white rounded-md text-xs uppercase">Export PDF</a>
        </form>


        <div class="space-y-6">
            @forelse ($purchaseOrders as $po)
                <div class="border rounded-lg p-4">
                    <h3 class="font-bold text-lg text-gray-800">
                        PO: 
                        <a href="{{ route('purchase-orders.show', $po->id) }}" class="text-blue-600 hover:underline">
                            {{ $po->po_id }}
                        </a>
                        <span class="text-sm font-normal text-gray-600">
                            (Customer: {{ $po->customer->customer_name ?? 'N/A' }})
                        </span>
                    </h3>

                    {{-- Linked Delivery Notes & Receive Notes --}}
                    <div class="mt-2 text-sm text-gray-600">
                        <p>
                            <strong>Delivery Notes:</strong>
                            @forelse($po->deliveryNotes as $dn)
                                <a href="{{ route('delivery-notes.show', $dn->id) }}" class="text-purple-600 hover:underline">
                                    {{ $dn->delivery_note_id }}
                                </a>@if(!$loop->last), @endif
                            @empty
                                <span class="text-gray-400">None</span>
                            @endforelse
                        </p>

                        <p>
                            <strong>Receive Notes:</strong>
                            @php
                                $rns = $po->deliveryNotes->flatMap->receiveNotes->unique('id');
                            @endphp
                            @forelse($rns as $rn)
                                <a href="{{ route('receive-notes.show', $rn->id) }}" class="text-green-600 hover:underline">
                                    {{ $rn->receive_note_id }}
                                </a>@if(!$loop->last), @endif
                            @empty
                                <span class="text-gray-400">None</span>
                            @endforelse
                        </p>
                    </div>

                    <div class="overflow-x-auto mt-2">
                        <table class="w-full min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                 <tr>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-black uppercase tracking-wider">Product</th>
                                    <th class="px-2 py-2 text-right text-xs font-medium text-black uppercase tracking-wider">Ordered (PO)</th>
                                    <th class="px-2 py-2 text-right text-xs font-medium text-black uppercase tracking-wider">Delivered (DN)</th>
                                    <th class="px-2 py-2 text-right text-xs font-medium text-black uppercase tracking-wider">Received (RN)</th>
                                    <th class="px-2 py-2 text-right text-xs font-medium text-black uppercase tracking-wider">Discrepancy</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($po->items->groupBy('product_id') as $productId => $poItems)
                                    @php
                                        $productName = $poItems->first()->product_name;
                                        $poQty = $poItems->sum('quantity');
                                        
                                        $dnQty = $po->deliveryNotes->flatMap->items
                                            ->where('product_id', $productId)
                                            ->sum('quantity_requested');
                                        
                                        $rnQty = $po->deliveryNotes->flatMap->receiveNotes
                                            ->flatMap->items
                                            ->where('product_id', $productId)
                                            ->sum('quantity_received');
                                            
                                        $discrepancy = $poQty - $rnQty;
                                    @endphp
                                    <tr>
                                        <td class="px-2 py-2 text-sm text-gray-800">{{ $productName }}</td>
                                        <td class="px-2 py-2 text-sm text-gray-500 text-right">{{ $poQty }}</td>
                                        <td class="px-2 py-2 text-sm text-gray-500 text-right">{{ $dnQty }}</td>
                                        <td class="px-2 py-2 text-sm text-gray-500 text-right">{{ $rnQty }}</td>
                                        <td class="px-2 py-2 text-sm text-right font-bold {{ $discrepancy != 0 ? 'text-red-500' : 'text-green-500' }}">
                                            {{ $discrepancy }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="text-center py-4">
                    <p class="text-gray-500">No processed purchase orders found for the selected filters.</p>
                </div>
            @endforelse
        </div>
        <div class="mt-4 print:hidden">
            {!! $purchaseOrders->links() !!}
        </div>
    </div>
</div>
<style>
    @media print {
        body * { visibility: hidden; }
        #report-content, #report-content * { visibility: visible; }
        #report-content { position: absolute; left: 0; top: 0; width: 100%; }
        .print\:hidden { display: none !important; }
    }
</style>
@endsection
