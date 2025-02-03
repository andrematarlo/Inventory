@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>Dashboard</h1>
            
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Items</h5>
                            <p class="card-text display-4">{{ $totalItems ?? 0 }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Employees</h5>
                            <p class="card-text display-4">{{ $totalEmployees ?? 0 }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Suppliers</h5>
                            <p class="card-text display-4">{{ $totalSuppliers ?? 0 }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Low Stock Items</h5>
                            <p class="card-text display-4">{{ $lowStockItems ?? 0 }}</p>
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