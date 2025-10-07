<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $company->company_name }} - Department Wise Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #f0f0f0; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h2>{{ $company->company_name }} - Department Wise Report</h2>
    <p>From: {{ $start_date ?? 'N/A' }} To: {{ $end_date ?? 'N/A' }}</p>

    <table>
        <thead>
            <tr>
                <th>Customer</th>
                <th>Department</th>
                <th class="right">Amount (LKR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportData as $row)
                @php $firstRow = true; @endphp
                @foreach($row['departmentWise'] as $dept => $amount)
                    <tr>
                        @if($firstRow)
                            <td rowspan="{{ count($row['departmentWise']) }}">
                                {{ $row['customer'] }}
                            </td>
                            @php $firstRow = false; @endphp
                        @endif
                        <td>{{ $dept }}</td>
                        <td class="right">{{ number_format($amount, 2) }}</td>
                    </tr>
                @endforeach
                <tr style="font-weight:bold;background:#f9f9f9;">
                    <td colspan="2" class="right">Total for {{ $row['customer'] }}</td>
                    <td class="right">{{ number_format($row['total'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
