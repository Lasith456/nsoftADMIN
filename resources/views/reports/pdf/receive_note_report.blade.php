<!DOCTYPE html>
<html>
<head>
    <title>Receive Note Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #222;
        }
        h2 { margin-bottom: 4px; }
        p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #999; padding: 6px 8px; }
        th { background: #f2f2f2; font-weight: bold; }
        td { vertical-align: top; }
        .meta { margin-bottom: 10px; }
    </style>
</head>
<body>
    <h2>Receive Note Report</h2>
    <p>A detailed list of all receive notes.</p>

    <div class="meta">
        @if(request('company_id'))
            <p><strong>Company:</strong>
                {{ optional(App\Models\Company::find(request('company_id')))->company_name ?? 'All Companies' }}
            </p>
        @endif

        @if(request('start_date') || request('end_date'))
            <p><strong>Date Range:</strong>
                {{ request('start_date') ?? '---' }} to {{ request('end_date') ?? '---' }}
            </p>
        @endif

        @if(request('status'))
            <p><strong>Status:</strong> {{ ucfirst(request('status')) }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>RN ID</th>
                <th>Company</th>
                <th>Associated DNs</th>
                <th>Assigned POs</th>
                <th>Received Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($receiveNotes as $rn)
                @php
                    // 🟢 Collect all PO IDs safely (works for both po_id and purchase_order_id)
                    $poIds = $rn->deliveryNotes
                        ->flatMap(function ($dn) {
                            return $dn->purchaseOrders->pluck('purchase_order_id')
                                ->merge($dn->purchaseOrders->pluck('po_id'));
                        })
                        ->filter()
                        ->unique()
                        ->implode(', ');

                    // 🟢 Collect all companies related to these POs
                    $companyNames = $rn->deliveryNotes
                        ->flatMap(fn($dn) => $dn->purchaseOrders->pluck('customer.company.company_name'))
                        ->filter()
                        ->unique()
                        ->implode(', ');
                @endphp

                <tr>
                    <td>{{ $rn->receive_note_id }}</td>
                    <td>{{ $companyNames ?: 'N/A' }}</td>
                    <td>{{ $rn->deliveryNotes->pluck('delivery_note_id')->implode(', ') ?: 'N/A' }}</td>
                    <td>{{ $poIds ?: 'N/A' }}</td>
                    <td>{{ $rn->received_date?->format('Y-m-d') ?? 'N/A' }}</td>
                    <td>{{ ucfirst($rn->status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;">No receive notes found for the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
