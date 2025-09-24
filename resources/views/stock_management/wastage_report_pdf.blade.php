<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Wastage Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Wastage Report</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Product</th>
                <th>Department</th>
                <th>Stock Type</th>
                <th>Quantity</th>
                <th>Reason</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
                <tr>
                    <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $log->product->name }}</td>
                    <td>{{ $log->product->department->name ?? '-' }}</td>
                    <td>{{ ucfirst($log->stock_type) }}</td>
                    <td>{{ $log->quantity }}</td>
                    <td>{{ $log->reason ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
