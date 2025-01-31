@extends('reports.layout')

@section('report-content')
<h3>Low Stock Report</h3>
<p class="text-muted">Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>

<div class="table-responsive mt-4">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Classification</th>
                <th>Current Stock</th>
                <th>Minimum Required</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lowStockItems as $item)
            <tr>
                <td>{{ $item->item->ItemName }}</td>
                <td>{{ $item->classification->ClassificationName }}</td>
                <td>{{ $item->StocksAvailable }}</td>
                <td>10</td>
                <td>
                    <span class="badge bg-danger">Low Stock</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">No items are currently low in stock</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection 