<!DOCTYPE html>
<html>
<head>
    <title>Stock Level Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #f3f3f3; text-align: left; }
    </style>
</head>
<body>
    <h2>Stock Level Report</h2>

    @if(!empty($department))
        <p><strong>Department:</strong> {{ $department->name }}</p>
    @endif

    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th style="text-align:right;">Clear Stock</th>
                <th style="text-align:right;">Non-Clear Stock</th>
                <th style="text-align:right;">Total Stock</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
            <tr>
                <td>{{ $product->name }}</td>
                <td style="text-align:right;">{{ $product->clear_stock_quantity }}</td>
                <td style="text-align:right;">{{ $product->non_clear_stock_quantity }}</td>
                <td style="text-align:right; font-weight:bold;">{{ $product->total_stock }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
