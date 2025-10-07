<!DOCTYPE html>
<html>
<head>
    <title>Delivery Note Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #222;
        }
        h2 {
            margin-bottom: 4px;
        }
        p {
            margin: 2px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        th, td {
            border: 1px solid #999;
            padding: 6px 8px;
        }
        th {
            background: #f2f2f2;
            font-weight: bold;
        }
        td {
            vertical-align: top;
        }
        .meta {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <h2>Delivery Note Report</h2>
    <p>A detailed list of all delivery notes.</p>

    <div class="meta">
        @if(request('company_id'))
            <p>
                <strong>Company:</strong>
                {{ optional(App\Models\Company::find(request('company_id')))->company_name ?? 'All Companies' }}
            </p>
        @endif

        @if(request('start_date') || request('end_date'))
            <p>
                <strong>Date Range:</strong>
                {{ request('start_date') ?? '---' }} to {{ request('end_date') ?? '---' }}
            </p>
        @endif

        @if(request('status'))
            <p>
                <strong>Status:</strong> {{ ucfirst(request('status')) }}
            </p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>DN ID</th>
                <th>Company</th>
                <th>Vehicle</th>
                <th>Driver Name</th>
                <th>Contact No</th>
                <th>Delivery Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($deliveryNotes as $dn)
                <tr>
                    <td>{{ $dn->delivery_note_id }}</td>
                    <td>{{ $dn->company->company_name ?? 'N/A' }}</td>
                    <td>{{ $dn->vehicle->vehicle_no ?? 'N/A' }}</td>
                    <td>{{ $dn->driver_name ?? 'N/A' }}</td>
                    <td>{{ $dn->driver_mobile ?? 'N/A' }}</td>
                    <td>{{ $dn->delivery_date?->format('Y-m-d') ?? 'N/A' }}</td>
                    <td>{{ ucfirst($dn->status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;">No delivery notes found for the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
