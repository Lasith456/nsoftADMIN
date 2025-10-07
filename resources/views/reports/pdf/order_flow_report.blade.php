<!DOCTYPE html>
<html>
<head>
    <title>Order Flow Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        th { background: #f3f3f3; }
    </style>
</head>
<body>
    <h2>Order Flow Report</h2>
    <p>Comparison of Ordered (PO), Delivered (DN), and Received (RN) quantities.</p>

    <table>
        <thead>
            <tr>
                <th>PO ID</th>
                <th>Customer</th>
                <th>Product</th>
                <th>Ordered</th>
                <th>Delivered</th>
                <th>Received</th>
                <th>Discrepancy</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrders as $po)
                @foreach($po->items->groupBy('product_id') as $productId => $poItems)
                    @php
                        $productName = $poItems->first()->product_name;
                        $poQty = $poItems->sum('quantity');
                        $dnQty = $po->deliveryNotes->flatMap->items->where('product_id', $productId)->sum('quantity_requested');
                        $rnQty = $po->deliveryNotes->flatMap->receiveNotes->flatMap->items->where('product_id', $productId)->sum('quantity_received');
                        $discrepancy = $poQty - $rnQty;
                    @endphp
                    <tr>
                        <td>{{ $po->po_id }}</td>
                        <td>{{ $po->customer->customer_name ?? 'N/A' }}</td>
                        <td>{{ $productName }}</td>
                        <td>{{ $poQty }}</td>
                        <td>{{ $dnQty }}</td>
                        <td>{{ $rnQty }}</td>
                        <td style="color: {{ $discrepancy == 0 ? 'green' : 'red' }}">
                            {{ $discrepancy }}
                        </td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</body>
</html>
