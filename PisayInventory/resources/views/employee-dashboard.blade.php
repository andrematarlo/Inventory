@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Dashboard</h2>
        <div class="text-muted">Welcome back, {{ Auth::user()->Username ?? 'User' }}</div>
    </div>

<!-- Statistics Cards Row -->
<div class="row g-4 mb-4">
    <!-- Total Items Card -->
    <div class="col-xl-3 col-md-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="text-center" style="flex: 1;">
                        <h6 class="text-muted mb-3">Total Items</h6>
                        <h4 class="mb-0">{{ $totalItems }}</h4>
                        </div>
                    <div class="text-primary ms-3">
                        <i class="bi bi-box fs-1"></i>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Suppliers Card -->
    <div class="col-xl-3 col-md-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="text-center" style="flex: 1;">
                        <h6 class="text-muted mb-3">Total Suppliers</h6>
                        <h4 class="mb-0 stats-number">{{ $totalSuppliers }}</h4>
                    </div>
                    <div class="text-success ms-3">
                        <i class="bi bi-truck fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Items Card -->
    <div class="col-xl-3 col-md-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="text-center" style="flex: 1;">
                        <h6 class="text-muted mb-3">Low Stocks</h6>
                        <h4 class="mb-0 stats-number">{{ $lowStockItems }}</h4>
                    </div>
                    <div class="text-warning ms-3">
                        <i class="bi bi-exclamation-triangle fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Out of Stock Items Card -->
    <div class="col-xl-3 col-md-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="text-center" style="flex: 1;">
                        <h6 class="text-muted mb-3">Out of Stocks</h6>
                        <h4 class="mb-0 stats-number">{{ $outOfStockItems }}</h4>
                    </div>
                    <div class="text-danger ms-3">
                        <i class="bi bi-x-circle fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Items Overview Table -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center py-3 px-4">
        <div>
            <h5 class="m-0 d-flex align-items-center">
                <i class="bi bi-table me-2"></i>
                Items Overview
            </h5>
        </div>
    </div>
    <div class="card-body px-4">
        <div class="table-responsive">
            <table class="table table-hover" id="itemsOverviewTable">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Classification</th>
                        <th>Stocks</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($itemsOverview as $item)
                    <tr>
                        <td>{{ $item->ItemName }}</td>
                        <td>{{ $item->ClassificationName }}</td>
                        <td>{{ $item->StocksAvailable }}</td>
                        <td><span class="status-text {{ $item->status_class }} fw-bold">{{ $item->status }}</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center">No items found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Destroy existing DataTable if it exists
        if ($.fn.DataTable.isDataTable('#itemsOverviewTable')) {
            $('#itemsOverviewTable').DataTable().destroy();
        }
        
        // Initialize DataTable
        $('#itemsOverviewTable').DataTable({
            pageLength: 10,
            ordering: true,
            responsive: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search items...",
                lengthMenu: "_MENU_ items per page",
                info: "Showing _START_ to _END_ of _TOTAL_ items",
                infoEmpty: "Showing 0 to 0 of 0 items",
                infoFiltered: "(filtered from _MAX_ total items)"
            },
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            drawCallback: function(settings) {
                $('.dataTables_wrapper .row').addClass('g-3');
                $('.dataTables_length select').addClass('form-select form-select-sm');
                $('.dataTables_filter input').addClass('form-control form-control-sm');
                $('.dataTables_info').addClass('text-muted');
                $('.pagination').addClass('pagination-sm');
            }
        });
    });
</script>
@endsection

@section('styles')
<style>
    .stats-number {
        font-size: 3.5rem !important;
        font-weight: 600 !important;
        color: #2c3e50 !important;
    }
    .table {
        font-size: 0.875rem;
    }
    
    .table th, 
    .table td {
        padding: 1rem;
        vertical-align: middle;
    }
    
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    
    .status-text {
        font-weight: 700;
    }
    
    .text-success {
        color: rgb(0, 126, 21) !important;
    }
    
    .text-warning {
        color: rgb(182, 118, 0) !important;
    }
    
    .text-danger {
        color: #dc3545 !important;
    }

    .table thead th {
        border-bottom: 2px solid #dee2e6;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .table tbody td {
        line-height: 1.5;
    }
    
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0,0,0,.125);
    }

    .card-header h2 {
        font-weight: 600;
        color: #1e293b;
    }

    .card-header i {
        font-size: 1rem;
        color: #4b5563;
    }
    
    .card-body {
        padding: 1.5rem 1.5rem;
    }

    .table-responsive {
        margin: 0 -1px;
    }
    
    #itemsOverviewTable_wrapper {
        padding: 1rem 0;
    }
</style>
@endsection