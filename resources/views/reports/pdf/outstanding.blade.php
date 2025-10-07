<!DOCTYPE html>
<html>
<head>
    <title>Outstanding Payments</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        th { background: #f3f3f3; }
        tfoot td { font-weight: bold; }
    </style>
</head>
<body>
    <h2>Outstanding Payments Report</h2>
    <p>Type: {{ ucfirst($type) }} | Period: {{ $from ?? 'All' }} - {{ $to ?? 'All' }}</p>

    <table>
        <thead>
            <tr>
                <th>Invoice ID</th>
                <th>Receipt ID</th>
                <th>Type</th>
                <th>Name</th>
                <th>Date</th>
                <th>Total</th>
                <th>Paid</th>
                <th>Outstanding</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoices as $row)
                <tr>
                    <td>{{ $row['invoice_id'] }}</td>
                    <td>{{ $row['receipt_id'] }}</td>
                    <td>{{ $row['type'] }}</td>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['date'] }}</td>
                    <td>{{ number_format($row['total'], 2) }}</td>
                    <td style="color:green">{{ number_format($row['paid'], 2) }}</td>
                    <td style="color:red">{{ number_format($row['outstanding'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="8" align="center">No outstanding payments found.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" align="right">TOTAL</td>
                <td>{{ number_format($totalSum, 2) }}</td>
                <td style="color:green">{{ number_format($paidSum, 2) }}</td>
                <td style="color:red">{{ number_format($outstandingSum, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
