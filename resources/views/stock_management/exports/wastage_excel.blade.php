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
