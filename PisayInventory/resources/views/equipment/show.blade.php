@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Equipment Details</h1>
        <a href="{{ route('equipment.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <h6 class="font-weight-bold">Equipment ID</h6>
                    <p>{{ $equipment->equipment_id }}</p>
                </div>
                <div class="col-md-6">
                    <h6 class="font-weight-bold">Equipment Name</h6>
                    <p>{{ $equipment->equipment_name }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <h6 class="font-weight-bold">Laboratory</h6>
                    <p>{{ $equipment->laboratory ? $equipment->laboratory->laboratory_name : 'Unassigned' }}</p>
                </div>
                <div class="col-md-6">
                    <h6 class="font-weight-bold">Status</h6>
                    <span class="badge bg-{{ $equipment->status === 'Available' ? 'success' : ($equipment->status === 'In Use' ? 'warning' : 'danger') }}">
                        {{ $equipment->status }}
                    </span>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <h6 class="font-weight-bold">Serial Number</h6>
                    <p>{{ $equipment->serial_number ?: 'Not Specified' }}</p>
                </div>
                <div class="col-md-6">
                    <h6 class="font-weight-bold">Model Number</h6>
                    <p>{{ $equipment->model_number ?: 'Not Specified' }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <h6 class="font-weight-bold">Condition</h6>
                    <span class="badge bg-{{ $equipment->condition === 'Good' ? 'success' : ($equipment->condition === 'Fair' ? 'warning' : 'danger') }}">
                        {{ $equipment->condition }}
                    </span>
                </div>
                <div class="col-md-6">
                    <h6 class="font-weight-bold">Acquisition Date</h6>
                    <p>{{ $equipment->acquisition_date ? $equipment->acquisition_date->format('M d, Y') : 'Not Set' }}</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <h6 class="font-weight-bold">Last Maintenance Date</h6>
                    <p>{{ $equipment->last_maintenance_date ? $equipment->last_maintenance_date->format('M d, Y') : 'Not Set' }}</p>
                </div>
                <div class="col-md-6">
                    <h6 class="font-weight-bold">Next Maintenance Date</h6>
                    <p>{{ $equipment->next_maintenance_date ? $equipment->next_maintenance_date->format('M d, Y') : 'Not Set' }}</p>
                </div>
            </div>

            <div class="mb-3">
                <h6 class="font-weight-bold">Description</h6>
                <p>{{ $equipment->description ?: 'No description available' }}</p>
            </div>

            <hr>

            <div class="row mb-3">
                <div class="col-md-6">
                    <h6 class="font-weight-bold">Created By</h6>
                    <p>{{ $equipment->createdBy ? $equipment->createdBy->FullName : 'N/A' }}</p>
                    <small class="text-muted">{{ $equipment->created_at ? $equipment->created_at->format('M d, Y H:i:s') : 'N/A' }}</small>
                </div>
                <div class="col-md-6">
                    <h6 class="font-weight-bold">Last Updated By</h6>
                    <p>{{ $equipment->updatedBy ? $equipment->updatedBy->FullName : 'N/A' }}</p>
                    <small class="text-muted">{{ $equipment->updated_at ? $equipment->updated_at->format('M d, Y H:i:s') : 'N/A' }}</small>
                </div>
            </div>

            @if($equipment->deleted_at)
            <div class="row mb-3">
                <div class="col-md-6">
                    <h6 class="font-weight-bold">Deleted By</h6>
                    <p>{{ $equipment->deletedBy ? $equipment->deletedBy->FullName : 'N/A' }}</p>
                    <small class="text-muted">{{ $equipment->deleted_at->format('M d, Y H:i:s') }}</small>
                </div>
                @if($equipment->DateRestored)
                <div class="col-md-6">
                    <h6 class="font-weight-bold">Restored By</h6>
                    <p>{{ $equipment->restoredBy ? $equipment->restoredBy->FullName : 'N/A' }}</p>
                    <small class="text-muted">{{ $equipment->DateRestored->format('M d, Y H:i:s') }}</small>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.badge {
    font-size: 0.875rem;
    padding: 0.375rem 0.75rem;
}
h6.font-weight-bold {
    margin-bottom: 0.25rem;
    color: #4a5568;
}
p {
    margin-bottom: 0;
}
.text-muted {
    font-size: 0.875rem;
}
</style>
@endpush 