<!DOCTYPE html>
<html>
<head>
    <title>Purchase Order Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #222;
        }
        h2 { margin-bottom: 5px; }
        p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #999; padding: 6px 8px; }
        th { background: #f2f2f2; font-weight: bold; text-align: left; }
        td { vertical-align: top; }
        .meta { margin-bottom: 8px; }
    </style>
</head>
<body>
    <h2>Purchase Order Report</h2>
    <p>A detailed list of all purchase orders with assigned products.</p>

    <div class="meta">
        @if(request('company_id'))
            <p><strong>Company:</strong>
                {{ optional(App\Models\Company::find(request('company_id')))->company_name ?? 'All Companies' }}
            </p>
        @endif

        @if(request('start_date') || request('end_date'))
            <p><strong>Date Range:</strong>
                {{ request('start_date') ?? '---' }} to {{ request('end_date') ?? '---' }}
            </p>
        @endif

        @if(request('status'))
            <p><strong>Status:</strong> {{ ucfirst(request('status')) }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>PO ID</th>
                <th>Company</th>
                <th>Customer</th>
                <th>Delivery Date</th>
                <th>Products</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($purchaseOrders as $po)
                <tr>
                    <td>{{ $po->po_id }}</td>
                    <td>{{ $po->customer->company->company_name ?? 'N/A' }}</td>
                    <td>{{ $po->customer->customer_name ?? 'N/A' }}</td>
                    <td>{{ $po->delivery_date?->format('Y-m-d') ?? 'N/A' }}</td>
                    <td>{{ $po->items->pluck('product.name')->implode(', ') ?: 'N/A' }}</td>
                    <td>{{ ucfirst($po->status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;">No purchase orders found for the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
