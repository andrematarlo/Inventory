@extends('reports.layout')

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
<!-- Summary Stats Cards (Screen only) -->
<div class="row g-4 mb-4 no-print">
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

<!-- Main Content (Both Print and Screen) -->
<div class="print-content">
    <!-- Print-only Header -->
    <div class="text-center mb-4 print-only">
        <h2>PSHS Inventory System</h2>
        <h3>Inventory Report</h3>
        <p>Period: {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</p>
    </div>

    <!-- Summary Table (Print-only) -->
    <div class="mb-4 print-only">
        <table class="table table-bordered">
            <tr>
                <td><strong>Total Movements:</strong> {{ number_format($summary['total_items']) }}</td>
                <td><strong>Total Stock In:</strong> {{ number_format($summary['total_in']) }}</td>
                <td><strong>Total Stock Out:</strong> {{ number_format($summary['total_out']) }}</td>
                <td><strong>Unique Items:</strong> {{ number_format($summary['unique_items']) }}</td>
            </tr>
        </table>
    </div>

    <!-- Stock Movements -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Stock Movements</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
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
                            <td colspan="5" class="text-center">No movements found for this period</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Current Stock Levels -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Current Stock Levels</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
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
                            <td class="text-success">+{{ number_format($stock->stock_in) }}</td>
                            <td class="text-danger">-{{ number_format($stock->stock_out) }}</td>
                            <td class="{{ $stock->net_movement >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $stock->net_movement >= 0 ? '+' : '' }}{{ number_format($stock->net_movement) }}
                            </td>
                            <td>
                                @if($stock->needs_reorder)
                                    <span class="badge bg-danger">Needs Reorder</span>
                                @else
                                    <span class="badge bg-success">OK</span>
                                @endif
                            </td>
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
    </div>
</div>
@endsection