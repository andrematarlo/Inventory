@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Dashboard</h2>
        <div class="text-muted">Welcome back, {{ Auth::user()->Username }}</div>
    </div>

    <!-- Statistics Cards Row -->
    <div class="row g-4 mb-4">
        <!-- Total Items Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Items</h6>
                            <h4 class="mb-0">{{ $totalItems }}</h4>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-box fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Items Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Low Stock Items</h6>
                            <h4 class="mb-0">{{ $lowStockItems }}</h4>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-exclamation-triangle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Suppliers Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Suppliers</h6>
                            <h4 class="mb-0">{{ $totalSuppliers }}</h4>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-truck fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Classifications Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Classifications</h6>
                            <h4 class="mb-0">{{ $totalClassifications }}</h4>
                        </div>
                        <div class="text-info">
                            <i class="bi bi-diagram-3 fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities and Low Stock Tables Row -->
    <div class="row g-4">
        <!-- Recent Activities -->
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">Recent Activities</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" id="activitiesTable">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Action</th>
                                    <th>User</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentActivities as $activity)
                                <tr>
                                    <td>{{ $activity['item_name'] }}</td>
                                    <td>
                                        <span class="badge bg-{{ $activity['action_color'] }}">
                                            {{ $activity['action'] }}
                                        </span>
                                    </td>
                                    <td>{{ $activity['user_name'] }}</td>
                                    <td>{{ $activity['date'] }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">No recent activities</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Items -->
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">Low Stock Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" id="stockTable">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lowStockItemsList as $item)
                                <tr>
                                    <td>{{ $item->ItemName }}</td>
                                    <td>{{ $item->StocksAvailable }}</td>
                                    <td>
                                        <span class="badge bg-danger">Low Stock</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">No low stock items</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize tables only if they have data
        if ($('#activitiesTable tbody tr').length > 1) {
            $('#activitiesTable').DataTable({
                pageLength: 5,
                ordering: true,
                responsive: true,
                destroy: true,
                language: {
                    search: "Search",
                    searchPlaceholder: "Search activities..."
                }
            });
        }

        if ($('#stockTable tbody tr').length > 1) {
            $('#stockTable').DataTable({
                pageLength: 5,
                ordering: true,
                responsive: true,
                destroy: true,
                language: {
                    search: "Search",
                    searchPlaceholder: "Search items..."
                }
            });
        }
    });
</script>
@endsection