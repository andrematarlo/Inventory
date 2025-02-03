@extends('reports.layout')

@section('report-content')
<h3>Inventory Report</h3>
<p class="text-muted">Period: {{ request('start_date') }} to {{ request('end_date') }}</p>

<div class="table-responsive mt-4">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Classification</th>
                <th>Current Stock</th>
                <th>Last Added</th>
                <th>Last Modified</th>
            </tr>
        </thead>
        <tbody>
            @forelse($inventoryItems as $inventory)
            <tr>
                <td>{{ $inventory->item->ItemName ?? 'N/A' }}</td>
                <td>{{ $inventory->item->classification->ClassificationName ?? 'N/A' }}</td>
                <td>{{ $inventory->StocksAvailable ?? 0 }}</td>
                <td>{{ $inventory->StocksAdded ?? 0 }}</td>
                <td>{{ $inventory->DateModified ? date('Y-m-d H:i', strtotime($inventory->DateModified)) : 'N/A' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">No inventory data found for this period</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection 