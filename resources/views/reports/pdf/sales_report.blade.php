<!DOCTYPE html>
<html>
<head>
    <title>Sales Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #f3f3f3; text-align: left; }
        tfoot { font-weight: bold; }
    </style>
</head>
<body>
    <h2>Sales Report</h2>

    @if(!empty($customer))
        <p><strong>Customer:</strong> {{ $customer->customer_name }}</p>
    @endif

    @if(!empty($type) && $type !== 'all')
        <p><strong>Invoice Type:</strong> {{ ucfirst($type) }} Invoices</p>
    @endif

    @if(!empty(request('start_date')) || !empty(request('end_date')))
        <p>
            <strong>Date Range:</strong> 
            {{ request('start_date') ?? '---' }} to {{ request('end_date') ?? '---' }}
        </p>
    @endif

    <p>A detailed list of all invoices within the selected filters.</p>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Invoice ID</th>
                <th>Billed To</th>
                <th style="text-align:right;">Amount (LKR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sales as $invoice)
            <tr>
                <td>{{ $invoice->created_at->format('Y-m-d') }}</td>
                <td>{{ $invoice->invoice_id }}</td>
                <td>{{ $invoice->invoiceable->customer_name ?? $invoice->invoiceable->supplier_name ?? $invoice->invoiceable->name ?? 'N/A' }}</td>
                <td style="text-align:right;">{{ number_format($invoice->total_amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align:right;">Total Sales:</td>
                <td style="text-align:right;">{{ number_format($total, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
