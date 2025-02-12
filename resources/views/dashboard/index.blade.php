@extends('layouts.app')

<style>
    body {
        
    }

    .container-fluid {
        padding: 20px 30px;
    }
    .card {
        background-color: #ffffff;
        border: none;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08) !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .card-body {
        padding: 1.5rem;
    }
    .card .fs-1 {
    font-size: 3.5rem !important;
}

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12) !important;
    }

    .statistics-card {
        border-radius: 15px !important;
        overflow: hidden;
        border: none;
    }

    .statistics-card .card-body {
        border-radius: 15px !important;
        background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
    }
    .statistics-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1) !important;
}

    .activity-feed {
        padding: 0;
        max-height: 600px;
        overflow-y: auto;
        background-color: #ffffff;
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
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .activity-content {
        font-size: 0.95rem;
    }

    /* Card header styling */
    .card-header {
    background-color: #ffffff;
    border-bottom: 1px solid #f1f5f9;
    padding: 1.5rem 1.5rem;
    min-height: 70px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    }
    .card-header span {
        font-size: 1.1rem;
        font-weight: 600;
        color: #2d3748;
        margin-left: 20px;
    }
    .card-header .badge {
        padding: 0.5rem 1rem;
        font-weight: 500;
        color: #2d3748;
    }

    .row {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -0.75rem;
    }

    .col-xl-3 {
        display: flex;
        padding: 0 0.75rem;
    }

    .card {
        flex-grow: 1;
    }

    /* Dashboard title styling */
    h2.mt-4 {
        color: #2d3748;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }
    .card.mt-4 {
        border-radius: 20px !important;
        overflow: hidden;
    }

    .card.mt-4 .card-header {
        border-top-left-radius: 20px !important;
        border-top-right-radius: 20px !important;
    }

    /* Scrollbar styling for activity feed */
    .activity-feed::-webkit-scrollbar {
        width: 6px;
    }

    .activity-feed::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .activity-feed::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 3px;
    }

    .activity-feed::-webkit-scrollbar-thumb:hover {
        background: #a0aec0;
    }
    .welcome-message {
                position: absolute;
                top: 30px;
                right: 30px;
                font-size: 14px;
                color:rgba(25, 135, 84, 0.74);
                font-style: italic;
            }
</style>

@section('content')
<div class="container-fluid px-4">
<div class="row">
        <div class="col-12 text-end mt-3">
            <span class="welcome-message">Welcome back, {{ Auth::user()->Username }}</span>
        </div>
    </div>
    <h2 class="mt-4">Dashboard</h2>

    <!-- Statistics Cards -->
    <div class="row mt-4">
        <!-- Total Items -->
        <div class="col-xl-3 col-md-6">
        <div class="card mb-4 rounded-5 statistics-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0 text-dark">{{ $totalItems }}</h3>
                            <div class="text-muted">Total Items</div>
                        </div>
                        <div class="fs-1" style="color: #0d6efd;">
                            <i class="bi bi-box"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Employees -->
        <div class="col-xl-3 col-md-6">
        <div class="card mb-4 rounded-5 statistics-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0 text-dark">{{ $totalEmployees }}</h3>
                            <div class="text-muted">Total Employees</div>
                        </div>
                        <div class="fs-1" style="color: #198754;">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Suppliers -->
        <div class="col-xl-3 col-md-6">
        <div class="card mb-4 rounded-5 statistics-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0 text-dark">{{ $totalSuppliers }}</h3>
                            <div class="text-muted">Total Suppliers</div>
                        </div>
                        <div class="fs-1" style="color: #ffc107;">
                            <i class="bi bi-truck"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Items -->
        <div class="col-xl-3 col-md-6">
        <div class="card mb-4 rounded-5 statistics-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0 text-dark">{{ $lowStockItems->count() }}</h3>
                            <div class="text-muted">Low Stock Items</div>
                        </div>
                        <div class="fs-1" style="color: #dc3545;">
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
            <div class="badge">(Last 7 Days)</div>
        </div>
        <div class="card-body">
            <div class="activity-feed">
                @forelse($recentActivities as $activity)
                <div class="activity-item">
                    <div class="d-flex align-items-start">
                        <div class="activity-icon me-3">
                @php
                    $isDeleted = $activity['is_deleted'];
                    
                    // Check if this is a new record by comparing dates
                    $createdTime = strtotime($activity['created_at']);
                    $modifiedTime = strtotime($activity['modified_at'] ?? '0');
                    $deletedTime = strtotime($activity['deleted_at'] ?? '0');
                    $restoredTime = strtotime($activity['restored_at'] ?? '0');
                    
                    // Find the most recent action
                    $times = [
                        'created' => $createdTime,
                        'modified' => $modifiedTime,
                        'deleted' => $deletedTime,
                        'restored' => $restoredTime
                    ];
                    
                    $mostRecentAction = array_search(max($times), $times);
                    
                    if ($isDeleted) {
                        $action = 'Deleted';
                        $icon = 'trash';
                        $color = 'danger';
                        $timestamp = $activity['deleted_at'];
                        $user = $activity['deleted_by'];
                    } elseif ($mostRecentAction === 'created') {
                        $action = 'Added';
                        $icon = 'plus-circle';
                        $color = 'success';
                        $timestamp = $activity['created_at'];
                        $user = $activity['created_by'];
                    } elseif ($mostRecentAction === 'restored') {
                        $action = 'Restored';
                        $icon = 'arrow-counterclockwise';
                        $color = 'warning';
                        $timestamp = $activity['restored_at'];
                        $user = $activity['restored_by'];
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
                                    
                                    @if($mostRecentAction === 'modified' && !empty($activity['changes']))
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