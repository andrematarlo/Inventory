@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <h2 class="mt-4">Dashboard</h2>
    
    <!-- Statistics Cards -->
    <div class="row mt-4">
                <!-- Total Items -->
                <div class="col-xl-3 col-md-6">
                <div class="card mb-4 rounded-5" style="box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0 text-dark">{{ $totalItems }}</h3>
                            <div class="text-muted">Total Items</div>
                        </div>
                        <div class="fs-1" style="font-size: 3.5rem !important; color: #0d6efd;">
                            <i class="bi bi-box"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Employees -->
        <div class="col-xl-3 col-md-6">
            <div class="card mb-4 rounded-5 shadow-lg">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0 text-dark">{{ $totalEmployees }}</h3>
                            <div class="text-muted">Total Employees</div>
                        </div>
                        <div class="fs-1" style="font-size: 3.5rem !important; color: #198754;">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Suppliers -->
        <div class="col-xl-3 col-md-6">
            <div class="card mb-4 rounded-5 shadow-lg">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0 text-dark">{{ $totalSuppliers }}</h3>
                            <div class="text-muted">Total Suppliers</div>
                        </div>
                        <div class="fs-1" style="font-size: 3.5rem !important; color: #ffc107;">
                            <i class="bi bi-truck"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Items -->
        <div class="col-xl-3 col-md-6">
            <div class="card mb-4 rounded-5 shadow-lg">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0 text-dark">{{ $lowStockItems->count() }}</h3>
                            <div class="text-muted">Low Stock Items</div>
                        </div>
                        <div class="fs-1" style="font-size: 3.5rem !important; color: #dc3545;">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
                <div class="card-header">
                    Recent Inventory Changes
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Employee</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($recentInventory) && count($recentInventory) > 0)
                                @foreach($recentInventory as $inventory)
                                    <tr>
                                        <td>{{ $inventory->item->ItemName ?? 'N/A' }}</td>
                                        <td>
                                            @if($inventory->employee)
                                                {{ $inventory->employee->FirstName }} {{ $inventory->employee->LastName }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>{{ $inventory->DateCreated ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="3" class="text-center">No recent inventory changes</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 