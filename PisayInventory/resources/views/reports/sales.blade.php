@extends('reports.layout')

@section('report-content')
<h3>Sales Report</h3>
<p class="text-muted">Period: {{ request('start_date') }} to {{ request('end_date') }}</p>

<div class="table-responsive mt-4">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>Item</th>
                <th>Quantity</th>
                <th>Unit</th>
                <th>Classification</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $sale)
            <tr>
                <td>{{ $sale->DateCreated }}</td>
                <td>{{ $sale->item->ItemName }}</td>
                <td>{{ $sale->Quantity }}</td>
                <td>{{ $sale->unitOfMeasure->UnitName }}</td>
                <td>{{ $sale->classification->ClassificationName }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">No sales data found for this period</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection 