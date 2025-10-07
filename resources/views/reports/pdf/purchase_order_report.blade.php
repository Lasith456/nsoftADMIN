<!DOCTYPE html>
<html>
<head>
    <title>Purchase Order Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #f3f3f3; text-align: left; }
    </style>
</head>
<body>
    <h2>Purchase Order Report</h2>
    <p>A detailed list of all purchase orders with assigned products.</p>

    <table>
        <thead>
            <tr>
                <th>PO ID</th>
                <th>Customer</th>
                <th>Delivery Date</th>
                <th>Products</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchaseOrders as $po)
            <tr>
                <td>{{ $po->po_id }}</td>
                <td>{{ $po->customer->customer_name ?? 'N/A' }}</td>
                <td>{{ $po->delivery_date?->format('Y-m-d') }}</td>
                <td>{{ $po->items->pluck('product.name')->implode(', ') ?: 'N/A' }}</td>
                <td>{{ ucfirst($po->status) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
