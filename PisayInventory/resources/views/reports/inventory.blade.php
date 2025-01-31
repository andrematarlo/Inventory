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
            @forelse($inventory as $item)
            <tr>
                <td>{{ $item->item->ItemName }}</td>
                <td>{{ $item->classification->ClassificationName }}</td>
                <td>{{ $item->StocksAvailable }}</td>
                <td>{{ $item->StocksAdded }}</td>
                <td>{{ $item->DateModified }}</td>
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