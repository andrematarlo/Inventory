@extends('layouts.app')

<style>
    .activity-feed {
        padding: 0;
        max-height: 600px;
        overflow-y: auto;
    }

    .activity-item {
        padding: 15px;
        border-bottom: 1px solid #f1f5f9;
        transition: background-color 0.2s ease;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-item:hover {
        background-color: #f8fafc;
    }

    .activity-icon .badge {
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    .activity-content {
        font-size: 0.95rem;
    }
</style>

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

    <!-- Activity Feed -->
<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Recent Activities</span>
        <div class="badge bg-primary">Last 7 Days</div>
    </div>
    <div class="card-body">
        <div class="activity-feed">
            @forelse($recentActivities as $activity)
            <div class="activity-item">
    <div class="d-flex align-items-start">
        <div class="activity-icon me-3">
@php
    $isDeleted = $activity['is_deleted'];
    
    $isNewlyCreated = !empty($activity['created_at']) && 
                      empty($activity['deleted_at']) && 
                      (empty($activity['modified_at']) || $activity['created_at'] == $activity['modified_at']);
    
    // If there are changes, it's a modification
    $isModified = !empty($activity['changes']);
    
    // If restored_by is set, it's a restore
    $isRestored = !empty($activity['restored_by']);

    if ($isDeleted) {
        $action = 'Deleted';
        $icon = 'trash';
        $color = 'danger';
        $timestamp = $activity['deleted_at'];
        $user = $activity['deleted_by'];
    } elseif ($isNewlyCreated) {
        $action = 'Added';
        $icon = 'plus-circle';
        $color = 'success';
        $timestamp = $activity['created_at'];
        $user = $activity['created_by'];
    } elseif ($isRestored) {
        $action = 'Restored';
        $icon = 'arrow-counterclockwise';
        $color = 'warning';
        $timestamp = $activity['modified_at'];
        $user = $activity['restored_by'];
    } elseif ($isModified) {
        $action = 'Modified';
        $icon = 'pencil';
        $color = 'primary';
        $timestamp = $activity['modified_at'];
        $user = $activity['modified_by'];
    } else {
        $action = 'Modified';
        $icon = 'pencil';
        $color = 'primary';
        $timestamp = $activity['modified_at'];
        $user = $activity['modified_by'];
    }
    
@endphp
            
            <div class="badge bg-{{ $color }}">
                <i class="bi bi-{{ $icon }}"></i>
            </div>
        </div>
        <div class="activity-content flex-grow-1">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="fw-medium">{{ $action }} {{ ucfirst($activity['type']) }}</span>
                    <strong>{{ $activity['name'] }}</strong>
                    
                    @if($isModified && !empty($activity['changes']))
                        <div class="mt-2">
                            @foreach($activity['changes'] as $field => $change)
                                <div class="text-muted small">
                                    <span class="fw-medium">{{ $field }}:</span> 
                                    <span class="text-danger">'{{ $change['old'] }}'</span> → 
                                    <span class="text-success">'{{ $change['new'] }}'</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if(!empty($activity['details']))
                        <div class="text-muted small mt-1">{{ $activity['details'] }}</div>
                    @endif
                </div>
                <small class="text-muted ms-2">
                    {{ \Carbon\Carbon::parse($timestamp)->diffForHumans() }}
                </small>
            </div>
            <div class="mt-1">
                <small class="text-muted">
                    by <span class="text-body-secondary">{{ $user }}</span>
                </small>
            </div>
        </div>
    </div>
</div>
            @empty
                <div class="text-center py-4">
                    <i class="bi bi-calendar-x text-muted mb-2" style="font-size: 2rem;"></i>
                    <p class="text-muted mb-0">No recent activities found</p>
                </div>
            @endforelse
            </div>
        </div>
    </div>
</div>
@endsection