<!DOCTYPE html>
<html>
<head>
    <title>Customer Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f3f3f3; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Customer Report</h2>
    <table>
        <thead>
            <tr>
                <th>Customer Name</th>
                <th>Total POs</th>
                <th>Total Invoiced (LKR)</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($customers as $customer)
            <tr>
                <td class="px-4 py-2 whitespace-nowrap text-sm text-black">{{ $customer->customer_name }}</td>
                <td class="px-4 py-2 whitespace-nowrap text-sm text-black text-right">{{ $customer->purchase_orders_count }}</td>
                <td class="px-4 py-2 whitespace-nowrap text-sm text-black text-right">{{ number_format($customer->invoices_sum_total_amount, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="px-4 py-2 text-center text-sm text-gray-500">
                    No customers found for the specified search criteria.
                </td>
            </tr>
            @endforelse
        </tbody>
        <tfoot class="bg-gray-50 font-bold">
            <tr>
                <td colspan="2" class="px-4 py-2 text-right">Total Invoiced Value (All Customers):</td>
                <td class="px-4 py-2 text-right">{{ number_format($totalInvoices, 2) }}</td>
            </tr>
        </tfoot>

    </table>
</body>
</html>
