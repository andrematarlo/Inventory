<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Inventory Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .summary-box {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
        }
        .summary-item {
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        .bg-success {
            background-color: #d4edda;
            color: #155724;
        }
        .bg-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .bg-warning {
            background-color: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Inventory Report</h1>
        <p>Period: {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</p>
        <p>Report Type: {{ ucfirst($reportType) }} Movements</p>
    </div>

    <div class="summary-box">
        <h3>Summary</h3>
        <div class="summary-item">Total Movements: {{ number_format($summary['total_items']) }}</div>
        <div class="summary-item">Total Stock In: {{ number_format($summary['total_in']) }}</div>
        <div class="summary-item">Total Stock Out: {{ number_format($summary['total_out']) }}</div>
        <div class="summary-item">Unique Items: {{ number_format($summary['unique_items']) }}</div>
    </div>

    <h3>Stock Movements</h3>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Item</th>
                <th>Classification</th>
                <th>Movement</th>
                <th>Created By</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movements as $movement)
            <tr>
                <td>{{ Carbon\Carbon::parse($movement->DateCreated)->format('M d, Y h:i A') }}</td>
                <td>{{ $movement->item->ItemName ?? 'N/A' }}</td>
                <td>{{ optional($movement->item->classification)->ClassificationName ?? 'N/A' }}</td>
                <td>
                    <span class="badge {{ $movement->StocksAdded > 0 ? 'bg-success' : 'bg-danger' }}">
                        {{ $movement->StocksAdded > 0 ? '+' : '' }}{{ number_format($movement->StocksAdded) }}
                    </span>
                </td>
                <td>{{ optional($movement->created_by_user)->Username ?? 'N/A' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center;">No movements found</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <h3>Current Stock Levels</h3>
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Classification</th>
                <th>Current Stock</th>
                <th>Stock In</th>
                <th>Stock Out</th>
                <th>Net Movement</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($currentStock as $stock)
            <tr>
                <td>{{ $stock->item->ItemName ?? 'N/A' }}</td>
                <td>{{ optional($stock->item->classification)->ClassificationName ?? 'N/A' }}</td>
                <td>{{ number_format($stock->current_stock) }}</td>
                <td>{{ number_format($stock->stock_in) }}</td>
                <td>{{ number_format($stock->stock_out) }}</td>
                <td>{{ number_format($stock->net_movement) }}</td>
                <td>
                    <span class="badge {{ $stock->needs_reorder ? 'bg-warning' : 'bg-success' }}">
                        {{ $stock->needs_reorder ? 'Reorder' : 'OK' }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
