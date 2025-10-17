<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Return Note Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #f2f2f2; }
        h2 { text-align: center; margin-bottom: 10px; }
    </style>
</head>
<body>
    <h2>Return Note Report</h2>
    <table>
        <thead>
            <tr>
                <th>Return Note ID</th>
                <th>Company</th>
                <th>Customer</th>
                <th>Agent</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Reason</th>
                <th>Return Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($returnNotes as $rn)
            <tr>
                <td>{{ $rn->return_note_id }}</td>
                <td>{{ $rn->company->company_name ?? '-' }}</td>
                <td>{{ $rn->customer->customer_name ?? '-' }}</td>
                <td>{{ $rn->agent->name ?? '-' }}</td>
                <td>{{ $rn->product->name ?? '-' }}</td>
                <td>{{ $rn->quantity }}</td>
                <td>{{ $rn->reason ?? '-' }}</td>
                <td>{{ $rn->return_date?->format('Y-m-d') ?? '-' }}</td>
                <td>{{ ucfirst($rn->status) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
