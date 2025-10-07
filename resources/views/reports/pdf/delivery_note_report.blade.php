<!DOCTYPE html>
<html>
<head>
    <title>Delivery Note Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #f3f3f3; text-align: left; }
    </style>
</head>
<body>
    <h2>Delivery Note Report</h2>
    <p>A detailed list of all delivery notes.</p>

    @if(request('start_date') || request('end_date'))
        <p>
            <strong>Date Range:</strong> 
            {{ request('start_date') ?? '---' }} to {{ request('end_date') ?? '---' }}
        </p>
    @endif

    @if(request('status'))
        <p><strong>Status:</strong> {{ ucfirst(request('status')) }}</p>
    @endif

    <table>
        <thead>
            <tr>
                <th>DN ID</th>
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
                <td>{{ $dn->vehicle->vehicle_no ?? 'N/A' }}</td>
                <td>{{ $dn->driver_name ?? 'N/A' }}</td>
                <td>{{ $dn->driver_mobile ?? 'N/A' }}</td>
                <td>{{ $dn->delivery_date->format('Y-m-d') }}</td>
                <td>{{ ucfirst($dn->status) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center;">No delivery notes found for the selected filters.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
