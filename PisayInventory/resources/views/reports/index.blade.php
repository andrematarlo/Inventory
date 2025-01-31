@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="mb-4">
    <h2>Reports</h2>
    <p class="text-muted">Generate and view various inventory reports</p>
</div>

<!-- Report Types -->
<div class="row g-4">
    <!-- Inventory Report -->
    <div class="col-md-6 col-lg-4">
        <div class="card report-card h-100" data-bs-toggle="modal" data-bs-target="#inventoryReportModal">
            <div class="card-body text-center">
                <div class="report-icon text-primary">
                    <i class="bi bi-box-seam"></i>
                </div>
                <h5 class="card-title">Inventory Report</h5>
                <p class="card-text">View comprehensive inventory status and stock levels</p>
            </div>
        </div>
    </div>

    <!-- Sales Report -->
    <div class="col-md-6 col-lg-4">
        <div class="card report-card h-100" data-bs-toggle="modal" data-bs-target="#salesReportModal">
            <div class="card-body text-center">
                <div class="report-icon text-success">
                    <i class="bi bi-graph-up"></i>
                </div>
                <h5 class="card-title">Sales Report</h5>
                <p class="card-text">Track sales and transaction history</p>
            </div>
        </div>
    </div>

    <!-- Low Stock Report -->
    <div class="col-md-6 col-lg-4">
        <div class="card report-card h-100" data-bs-toggle="modal" data-bs-target="#lowStockReportModal">
            <div class="card-body text-center">
                <div class="report-icon text-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <h5 class="card-title">Low Stock Report</h5>
                <p class="card-text">Identify items that need restocking</p>
            </div>
        </div>
    </div>
</div>

<!-- Generated Reports Section -->
<div class="mt-5">
    <h3 class="mb-4">Recent Reports</h3>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Report Type</th>
                            <th>Generated Date</th>
                            <th>Date Range</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Add your recent reports here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Inventory Report Modal -->
<div class="modal fade" id="inventoryReportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Inventory Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('reports.inventory') }}" method="GET">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" name="start_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control" name="end_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Sales Report Modal -->
<div class="modal fade" id="salesReportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Sales Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('reports.sales') }}" method="GET">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" name="start_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control" name="end_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Low Stock Report Modal -->
<div class="modal fade" id="lowStockReportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Low Stock Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('reports.low-stock') }}" method="GET">
                <div class="modal-body">
                    <p>This report will show all items with stock levels below the minimum threshold.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 