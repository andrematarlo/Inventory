@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <h2 class="mt-4">Dashboard</h2>
    
    <!-- Statistics Cards -->
    <div class="row mt-4">
        <!-- Total Items -->
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4 rounded-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $totalItems }}</h3>
                            <div>Total Items</div>
                        </div>
                        <div class="fs-1">
                            <i class="bi bi-box"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Employees -->
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4 rounded-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $totalEmployees }}</h3>
                            <div>Total Employees</div>
                        </div>
                        <div class="fs-1">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Suppliers -->
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4 rounded-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $totalSuppliers }}</h3>
                            <div>Total Suppliers</div>
                        </div>
                        <div class="fs-1">
                            <i class="bi bi-truck"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Items -->
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4 rounded-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $lowStockItems->count() }}</h3>
                            <div>Low Stock Items</div>
                        </div>
                        <div class="fs-1">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Items Table -->
    <div class="card mb-4 rounded-4">
        <div class="card-header rounded-top-4">
            <i class="bi bi-exclamation-triangle me-1"></i>
            Low Stock Items
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Current Stock</th>
                            <th>Reorder Point</th>
                            <th>Supplier</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lowStockItems as $item)
                            <tr>
                                <td>{{ $item->ItemName }}</td>
                                <td>{{ $item->StocksAvailable }}</td>
                                <td>{{ $item->ReorderPoint }}</td>
                                <td>{{ $item->supplier->SupplierName ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No low stock items found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection 