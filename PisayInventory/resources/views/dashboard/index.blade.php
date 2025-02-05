@extends('layouts.app')
<style>
    .activity-feed {
        padding: 0;
    }

    .activity-item {
        padding: 10px 0;
        border-bottom: 1px solid #f1f5f9;
        transition: background-color 0.2s ease;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-item:hover {
        background-color: #f8fafc;
    }

    .activity-item small {
        display: inline-block;
        margin-left: 5px;
        font-size: 0.85rem;
    }

    .activity-item strong {
        color: #0f172a;
    }

    .activity-item i {
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
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

    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Recent Activities</span>
            <div class="badge bg-primary">Last 7 Days</div>
        </div>
        <div class="card-body">
        <div class="activity-feed">
    @forelse($recentActivities as $activity)
        <div class="activity-item mb-3 d-flex align-items-center">
            <div class="me-3">
                @php
                    $isNewlyCreated = $activity->DateCreated && (!$activity->DateModified || $activity->DateCreated == $activity->DateModified) && !$activity->DateDeleted;
                    $isRestored = !$activity->IsDeleted && $activity->DateDeleted;
                    $isDeleted = $activity->IsDeleted && $activity->DateDeleted;
                    $isModified = $activity->DateModified && $activity->DateModified > $activity->DateCreated && !$activity->DateDeleted;
                @endphp

                @if($isDeleted)
                    <i class="bi bi-trash text-danger fs-5"></i>
                @elseif($isRestored)
                    <i class="bi bi-arrow-counterclockwise text-info fs-5"></i>
                @elseif($isModified)
                    <i class="bi bi-pencil text-warning fs-5"></i>
                @else
                    <i class="bi bi-plus-circle text-success fs-5"></i>
                @endif
            </div>
            <div class="flex-grow-1">
                <strong>
                    @if($activity->entity_type === 'employee')
                        {{ $activity->creator->Username ?? $activity->modifier->Username ?? $activity->deleter->Username ?? 'N/A' }}
                    @else
                        {{ $activity->created_by_user->Username ?? $activity->modified_by_user->Username ?? $activity->deleted_by_user->Username ?? 'N/A' }}
                    @endif
                </strong>

                @if($isDeleted)
                    deleted
                @elseif($isRestored)
                    restored
                @elseif($isModified)
                    modified
                @else
                    added
                @endif

                @switch($activity->entity_type)
                    @case('item')
                        item "<strong>{{ $activity->ItemName }}</strong>"
                        @if($activity->classification)
                            ({{ $activity->classification->ClassificationName }})
                        @endif
                        @break
                    @case('supplier')
                        supplier "<strong>{{ $activity->SupplierName }}</strong>"
                        @break
                    @case('employee')
                        employee "<strong>{{ $activity->FirstName }} {{ $activity->LastName }}</strong>"
                        @break
                    @case('classification')
                        classification "<strong>{{ $activity->ClassificationName }}</strong>"
                        @break
                    @case('unit')
                        unit "<strong>{{ $activity->UnitName }}</strong>"
                        @break
                @endswitch

                <small class="text-muted">
                    {{ \Carbon\Carbon::parse($activity->DateDeleted ?? $activity->DateModified ?? $activity->DateCreated)->diffForHumans() }}
                </small>
            </div>
        </div>
    @empty
        <p class="text-center text-muted">No recent activities</p>
    @endforelse
</div>
        </div>
    </div>
    </div>
</div>
@endsection 

