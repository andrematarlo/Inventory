@extends('reports.layout')
<style>
    /* Table styling */
    .table {
        background-color: white !important;
        margin-bottom: 1.5rem;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
        width: 100%;
    }

    .table thead th {
        background-color: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        color: #4a5568;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.875rem;
        padding: 1rem;
        white-space: nowrap;
    }

    .table tbody td {
        padding: 1rem;
        vertical-align: middle;
        color: #4a5568;
        border-color: #e2e8f0;
    }

    .table tbody tr:hover {
        background-color: #f8fafc;
    }

    /* Section styling */
    .report-section {
        background-color: white !important;
        margin-bottom: 2rem;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
        width: 100%;
    }

    .report-section h4 {
        color: #4a5568;
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 1rem;
        padding: 0.5rem 0;
        margin-left: 1rem;
    }

    .screen-only {
    display: block;
}

    /* Hide print-only content on screen */
    .print-only {
        display: none;
    }

    /* Print styles */
    @media print {

        /* Hide screen-only content when printing */
        .screen-only {
            display: none !important;
        }

        .print-only {
            display: block !important;
            background: white !important;
            color: black !important;
            padding: 2rem;
        }

        /* Ensure tables print cleanly */
        .print-only table {
            width: 100%;
            border-collapse: collapse;
        }

        .print-only th, .print-only td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        .print-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .print-header h1 {
            font-size: 24pt;
            margin-bottom: 10px;
        }

        .print-header h2 {
            font-size: 18pt;
            margin-bottom: 10px;
        }

        /* Keep table headers on new pages */
        thead {
            display: table-header-group;
        }

        /* Prevent awkward table row splitting */
        tr {
            page-break-inside: avoid;
        }


        /* Page settings */
        @page {
            margin: 2cm;
            size: portrait;
        }
    }

    /* Spacing utilities */
    .gap-2 {
        gap: 0.5rem !important;
    }

    .gap-3 {
        gap: 1rem !important;
    }


    /* Responsive adjustments */
    @media (max-width: 768px) {
        .report-section {
            padding: 1rem;
        }

        .table {
            font-size: 0.875rem;
        }
    }
</style>

@section('report-content')
<!-- SCREEN VIEW -->
<div class="screen-only">
    <!-- Header and Filters -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Inventory Report</h1>
            <p>Period: {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</p>
        </div>
        
        <div class="btn-group">
            <a href="{{ route('reports.inventory', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d'), 'report_type' => 'all']) }}" 
               class="btn btn-outline-primary {{ $reportType === 'all' ? 'active' : '' }}">
                <i class="bi bi-list-ul"></i> All Movements
            </a>
            <a href="{{ route('reports.inventory', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d'), 'report_type' => 'in']) }}"
               class="btn btn-outline-success {{ $reportType === 'in' ? 'active' : '' }}">
                <i class="bi bi-box-arrow-in-down"></i> Stock In
            </a>
            <a href="{{ route('reports.inventory', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d'), 'report_type' => 'out']) }}"
               class="btn btn-outline-danger {{ $reportType === 'out' ? 'active' : '' }}">
                <i class="bi bi-box-arrow-up"></i> Stock Out
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
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

    <!-- Screen Tables -->
    <div class="report-section mb-4">
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

<!-- PRINT VIEW -->
<div class="print-only">
    <!-- Print Header -->
    <div class="text-center mb-4">
        <h1>PSHS Inventory System</h1>
        <h2>Inventory Report</h2>
        <p>Period: {{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</p>
        
        <!-- Print Summary Stats -->
        <div class="summary-stats mt-4">
            <div class="d-flex justify-content-between">
                <div>Total Movements: {{ number_format($summary['total_items']) }}</div>
                <div>Total Stock In: {{ number_format($summary['total_in']) }}</div>
                <div>Total Stock Out: {{ number_format($summary['total_out']) }}</div>
                <div>Unique Items: {{ number_format($summary['unique_items']) }}</div>
            </div>
        </div>
    </div>

    <!-- Print Tables -->
    <div class="report-section mb-4">
        <h4>Stock Movements</h4>
        <table class="table table-bordered">
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

    <div class="report-section">
        <h4>Current Stock Levels</h4>
        <table class="table table-bordered">
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