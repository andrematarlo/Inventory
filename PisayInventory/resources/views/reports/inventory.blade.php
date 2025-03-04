@extends('reports.layout')
<style>
    .table {
        background-color: white !important;
        
    }
    .report-section {
        background-color: white !important;
    }
</style>

@section('report-title', 'Inventory Report')

@section('report-subtitle')
Period: {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}
@endsection

@section('report-actions')
<a href="{{ route('reports.inventory', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d'), 'report_type' => 'all']) }}" 
   class="btn btn-outline-primary {{ $reportType === 'all' ? 'active' : '' }}">
    All Movements
</a>
<a href="{{ route('reports.inventory', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d'), 'report_type' => 'in']) }}"
   class="btn btn-outline-success {{ $reportType === 'in' ? 'active' : '' }}">
    Stock In
</a>
<a href="{{ route('reports.inventory', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d'), 'report_type' => 'out']) }}"
   class="btn btn-outline-danger {{ $reportType === 'out' ? 'active' : '' }}">
    Stock Out
</a>
<a href="{{ route('reports.inventory.pdf', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d'), 'report_type' => $reportType]) }}" 
   class="btn btn-primary">
    <i class="bi bi-file-pdf"></i> Download PDF
</a>
@endsection

@section('report-content')
<!-- Screen-only content -->
<div class="screen-only">
    <!-- Summary Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Movements</h6>
                    <h3 class="card-text">{{ number_format($summary['total_items']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Stock In</h6>
                    <h3 class="card-text">{{ number_format($summary['total_in']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Stock Out</h6>
                    <h3 class="card-text">{{ number_format($summary['total_out']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Unique Items</h6>
                    <h3 class="card-text">{{ number_format($summary['unique_items']) }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Print-only content -->
<div class="print-only">
    <div class="print-header text-center">
        <h1>PSHS Inventory System</h1>
        <h2>Inventory Report</h2>
        <p>Period: {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</p>
        
        <table class="table table-bordered mb-4">
            <tr>
                <td><strong>Total Movements:</strong> {{ number_format($summary['total_items']) }}</td>
                <td><strong>Total Stock In:</strong> {{ number_format($summary['total_in']) }}</td>
                <td><strong>Total Stock Out:</strong> {{ number_format($summary['total_out']) }}</td>
                <td><strong>Unique Items:</strong> {{ number_format($summary['unique_items']) }}</td>
            </tr>
        </table>
    </div>

    <!-- Stock Movements -->
    <div class="report-section">
        <h4>Stock Movements</h4>
        <table class="table">
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
                    <td>{{ $movement->StocksAdded > 0 ? '+' : '' }}{{ number_format($movement->StocksAdded) }}</td>
                    <td>{{ optional($movement->created_by_user)->Username ?? 'N/A' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">No movements found for this period</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Current Stock Levels -->
    <div class="report-section">
        <h4>Current Stock Levels</h4>
        <table class="table">
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
                @forelse($currentStock as $stock)
                <tr>
                    <td>{{ $stock->item->ItemName ?? 'N/A' }}</td>
                    <td>{{ optional($stock->item->classification)->ClassificationName ?? 'N/A' }}</td>
                    <td>{{ number_format($stock->current_stock) }}</td>
                    <td>+{{ number_format($stock->stock_in) }}</td>
                    <td>-{{ number_format($stock->stock_out) }}</td>
                    <td>{{ $stock->net_movement >= 0 ? '+' : '' }}{{ number_format($stock->net_movement) }}</td>
                    <td>{{ $stock->needs_reorder ? 'Needs Reorder' : 'OK' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">No items found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection