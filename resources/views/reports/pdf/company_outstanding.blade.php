<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Outstanding Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 25px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .header h2 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }
        .header p {
            margin: 2px 0;
            font-size: 11px;
        }
        .report-title {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #777;
            padding: 6px;
            text-align: right;
        }
        th {
            background-color: #efefef;
            text-align: center;
            font-weight: bold;
        }
        td:first-child, td:nth-child(2) {
            text-align: left;
        }
        .summary {
            margin-top: 20px;
            border-top: 2px solid #000;
            padding-top: 8px;
            width: 40%;
            float: right;
        }
        .summary table {
            width: 100%;
            border: none;
        }
        .summary td {
            border: none;
            padding: 4px;
            font-size: 12px;
        }
        .footer {
            position: fixed;
            bottom: 25px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 11px;
            color: #777;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <h2>H.G.P.M. (PVT) LTD</h2>
        <p>No: 412/B, Galle Road, Pamburana, Matara.</p>
        <p>Tel: 041 2229231 / 041 2224121 | Fax: 041 2224122 | Email: hgpm.ltd@sltnet.lk</p>
    </div>

    <div class="report-title">
        Customer Outstanding Report
    </div>

    {{-- Report Info --}}
    <table>
        <tr>
            <td style="border:none; text-align:left;">
                <strong>Company:</strong> 
                {{ $company ? $company->company_name : 'All Companies' }}
            </td>
            <td style="border:none; text-align:left;">
                <strong>Date Range:</strong>
                {{ $startDate ? date('Y-m-d', strtotime($startDate)) : '—' }}
                to
                {{ $endDate ? date('Y-m-d', strtotime($endDate)) : '—' }}
            </td>
            <td style="border:none; text-align:right;">
                <strong>Generated On:</strong> {{ now()->format('Y-m-d H:i') }}
            </td>
        </tr>
    </table>

    {{-- Data Table --}}
    <table>
        <thead>
            <tr>
                <th style="width: 25%;">Company </th>
                <th style="width: 30%;">Customer </th>
                <th style="width: 15%;">Total (LKR)</th>
                <th style="width: 15%;">Paid (LKR)</th>
                <th style="width: 15%;">Outstanding (LKR)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData as $row)
                <tr>
                    <td>{{ $row['company'] }}</td>
                    <td>{{ $row['customer'] }}</td>
                    <td>{{ number_format($row['total'], 2) }}</td>
                    <td>{{ number_format($row['paid'], 2) }}</td>
                    <td style="font-weight:bold; color:red;">{{ number_format($row['outstanding'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;">No outstanding data found for the selected criteria.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Summary --}}
    @if(!empty($totals))
    <div class="summary">
        <table>
            <tr>
                <td><strong>Total Invoiced:</strong></td>
                <td style="text-align:right;">{{ number_format($totals['totalSum'], 2) }}</td>
            </tr>
            <tr>
                <td><strong>Total Paid:</strong></td>
                <td style="text-align:right;">{{ number_format($totals['paidSum'], 2) }}</td>
            </tr>
            <tr>
                <td><strong>Total Outstanding:</strong></td>
                <td style="text-align:right; color:red; font-weight:bold;">{{ number_format($totals['outstandingSum'], 2) }}</td>
            </tr>
        </table>
    </div>
    @endif


</body>
</html>
