<!DOCTYPE html>
<html>
<head>
    <title>Receive Note Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #f3f3f3; text-align: left; }
    </style>
</head>
<body>
    <h2>Receive Note Report</h2>
    <p>A detailed list of all receive notes.</p>

    @if(request('start_date') || request('end_date'))
        <p><strong>Date Range:</strong> {{ request('start_date') ?? '---' }} to {{ request('end_date') ?? '---' }}</p>
    @endif
    @if(request('status'))
        <p><strong>Status:</strong> {{ ucfirst(request('status')) }}</p>
    @endif

    <table>
        <thead>
            <tr>
                <th>RN ID</th>
                <th>Associated DNs</th>
                <th>Assigned POs</th>
                <th>Received Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($receiveNotes as $rn)
            <tr>
                <td>{{ $rn->receive_note_id }}</td>
                <td>{{ $rn->deliveryNotes->pluck('delivery_note_id')->implode(', ') }}</td>
                <td>{{ $rn->deliveryNotes->flatMap->purchaseOrders->pluck('po_id')->implode(', ') ?: 'N/A' }}</td>
                <td>{{ $rn->received_date->format('Y-m-d') }}</td>
                <td>{{ ucfirst($rn->status) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center;">No receive notes found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
