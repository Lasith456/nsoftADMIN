<!DOCTYPE html>
<html>
<head>
    <title>Supplier Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #f3f3f3; text-align: left; }
        tfoot { font-weight: bold; }
    </style>
</head>
<body>
    <h2>Supplier Report</h2>

    @if(request('start_date') || request('end_date'))
        <p>
            <strong>Date Range:</strong> 
            {{ request('start_date') ?? '---' }} to {{ request('end_date') ?? '---' }}
        </p>
    @endif

    @if(request('search'))
        <p><strong>Filtered by Supplier Name:</strong> {{ request('search') }}</p>
    @endif

    <table>
        <thead>
            <tr>
                <th>Supplier Name</th>
                <th style="text-align:right;">Total GRNs</th>
                <th style="text-align:right;">Total Invoiced (LKR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($suppliers as $supplier)
            <tr>
                <td>{{ $supplier->supplier_name }}</td>
                <td style="text-align:right;">{{ $supplier->grns_count }}</td>
                <td style="text-align:right;">{{ number_format($supplier->invoices_sum_total_amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" style="text-align:right;">Grand Total:</td>
                <td style="text-align:right;">{{ number_format($totalInvoices, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
