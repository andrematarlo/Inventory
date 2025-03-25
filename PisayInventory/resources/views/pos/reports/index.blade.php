@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">POS Reports Dashboard</h5>
                    <div>
                        <a href="{{ route('pos.reports.sales') }}" class="btn btn-sm btn-light">
                            <i class="bi bi-graph-up"></i> Sales Reports
                        </a>
                        <a href="{{ route('pos.reports.deposits') }}" class="btn btn-sm btn-light">
                            <i class="bi bi-wallet2"></i> Deposit Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Summary Cards -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow-sm border-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Sales Summary</h6>
                            <h3 class="mb-0">Sales Reports</h3>
                            <p class="text-muted">View detailed sales data and trends</p>
                        </div>
                        <div class="bg-light p-3 rounded">
                            <i class="bi bi-cash text-primary" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <a href="{{ route('pos.reports.sales') }}" class="btn btn-primary mt-3 w-100">View Sales</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow-sm border-success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Deposit Summary</h6>
                            <h3 class="mb-0">Deposit Reports</h3>
                            <p class="text-muted">Track student deposits and withdrawals</p>
                        </div>
                        <div class="bg-light p-3 rounded">
                            <i class="bi bi-wallet2 text-success" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <a href="{{ route('pos.reports.deposits') }}" class="btn btn-success mt-3 w-100">View Deposits</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow-sm border-info h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Inventory Summary</h6>
                            <h3 class="mb-0">Inventory Reports</h3>
                            <p class="text-muted">Monitor stock levels and consumption</p>
                        </div>
                        <div class="bg-light p-3 rounded">
                            <i class="bi bi-box-seam text-info" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <a href="#" class="btn btn-info mt-3 w-100">View Inventory</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0">Report Categories</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="bi bi-graph-up-arrow text-primary" style="font-size: 2.5rem;"></i>
                                    </div>
                                    <h5>Daily Sales</h5>
                                    <p class="text-muted small">View day-by-day sales performance</p>
                                    <a href="#" class="btn btn-sm btn-outline-primary">View Report</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="bi bi-currency-exchange text-success" style="font-size: 2.5rem;"></i>
                                    </div>
                                    <h5>Transaction History</h5>
                                    <p class="text-muted small">Detailed transaction logs</p>
                                    <a href="#" class="btn btn-sm btn-outline-success">View Report</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="bi bi-people text-danger" style="font-size: 2.5rem;"></i>
                                    </div>
                                    <h5>Student Activity</h5>
                                    <p class="text-muted small">Track student purchasing patterns</p>
                                    <a href="#" class="btn btn-sm btn-outline-danger">View Report</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="bi bi-bar-chart text-warning" style="font-size: 2.5rem;"></i>
                                    </div>
                                    <h5>Popular Items</h5>
                                    <p class="text-muted small">Most frequently purchased items</p>
                                    <a href="#" class="btn btn-sm btn-outline-warning">View Report</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 