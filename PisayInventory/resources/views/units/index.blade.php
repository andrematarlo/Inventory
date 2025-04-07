@extends('layouts.app')

@section('title', 'Units')

@section('content')
<div id="pageData" 
     data-has-errors="{{ $errors->any() ? 'true' : 'false' }}"
     data-show-deleted="{{ request()->has('show_deleted') || Str::contains(request()->url(), '#deleted') ? 'true' : 'false' }}">

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Units Management</h2>
        @if($userPermissions && $userPermissions->CanAdd)
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUnitModal">
            <i class="bi bi-plus-lg"></i> Add Unit
        </button>
        @endif
    </div>

    <!-- Tab buttons -->
    <div class="mb-4">
        <a href="{{ route('units.index') }}" class="btn {{ !$showDeleted ? 'btn-primary' : 'btn-outline-primary' }} me-2">
            <i class="bi bi-list-check me-1"></i> Active Units ({{ count($units) }})
        </a>
        <a href="{{ route('units.trash') }}" class="btn {{ $showDeleted ? 'btn-danger' : 'btn-outline-danger' }}">
            <i class="bi bi-archive me-1"></i> Deleted Units ({{ count($trashedUnits) }})
        </a>
    </div>

    <!-- Active Units Section -->
    <div id="activeUnits" @if($showDeleted) style="display: none;" @endif>
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Active Units</h5>
                    <div class="d-flex gap-2 align-items-center">
                        <div class="input-group">
                            <input type="text" 
                                   class="form-control" 
                                   id="activeSearchInput" 
                                   placeholder="Search..."
                                   aria-label="Search">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="activeUnitsTable">
                        <thead>
                            <tr>
                                @if($userPermissions && ($userPermissions->CanEdit || $userPermissions->CanDelete))
                                <th>Actions</th>
                                @endif
                                <th>Name</th>
                                <th>Created By</th>
                                <th>Date Created</th>
                                <th>Modified By</th>
                                <th>Date Modified</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($units as $unit)
                            <tr data-unit-id="{{ $unit->UnitOfMeasureId }}">
                                @if($userPermissions && ($userPermissions->CanEdit || $userPermissions->CanDelete))
                                <td>
                                    <div class="btn-group">
                                        @if($userPermissions->CanEdit)
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editUnitModal{{ $unit->UnitOfMeasureId }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        @endif
                                        @if($userPermissions->CanDelete)
                                        <form action="{{ route('units.destroy', $unit->UnitOfMeasureId) }}" 
                                              method="POST" 
                                              style="display:inline-block"
                                              class="delete-unit-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-danger"
                                                    data-name="{{ $unit->UnitName }}">
                                                <i class="bi bi-trash"></i> Move to Trash
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                                @endif
                                <td>{{ $unit->UnitName }}</td>
                                <td>{{ $unit->created_by_user->Username ?? 'N/A' }}</td>
                                <td>{{ $unit->DateCreated ? date('M d, Y h:i A', strtotime($unit->DateCreated)) : 'N/A' }}</td>
                                <td>{{ $unit->modified_by_user->Username ?? 'N/A' }}</td>
                                <td>{{ $unit->DateModified ? date('M d, Y h:i A', strtotime($unit->DateModified)) : 'N/A' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ ($userPermissions && ($userPermissions->CanEdit || $userPermissions->CanDelete)) ? '6' : '5' }}" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bi bi-clipboard-x text-muted mb-2" style="font-size: 2rem;"></i>
                                        <p class="mb-0 text-muted">No active units found</p>
                                        @if($userPermissions && $userPermissions->CanAdd)
                                        <button type="button" class="btn btn-sm btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addUnitModal">
                                            <i class="bi bi-plus-lg"></i> Add New Unit
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Deleted Units Section -->
    <div id="deletedUnits" @if(!$showDeleted) style="display: none;" @endif>
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Deleted Units</h5>
                    <div class="d-flex gap-2 align-items-center">
                        <div class="input-group">
                            <input type="text" 
                                   class="form-control" 
                                   id="deletedSearchInput" 
                                   placeholder="Search..."
                                   aria-label="Search">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="deletedUnitsTable">
                        <thead>
                            <tr>
                                <th>Actions</th>
                                <th>Unit Name</th>
                                <th>Deleted By</th>
                                <th>Date Deleted</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($trashedUnits as $unit)
                            <tr>
                                <td>
                                    @if($userPermissions->CanEdit)
                                    <form action="{{ route('units.restore', $unit->UnitOfMeasureId) }}" method="POST" class="d-inline restore-unit-form">
                                        @csrf
                                        <input type="hidden" name="redirect_hash" value="#deleted">
                                        <button type="submit" class="btn btn-sm btn-success" title="Restore" data-name="{{ $unit->UnitName }}">
                                            <i class="bi bi-arrow-counterclockwise"></i> Restore
                                        </button>
                                    </form>
                                    @endif
                                </td>
                                <td>{{ $unit->UnitName }}</td>
                                <td>{{ $unit->deletedBy->Username ?? 'N/A' }}</td>
                                <td>{{ $unit->DateDeleted ? date('M d, Y h:i A', strtotime($unit->DateDeleted)) : 'N/A' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bi bi-archive text-muted mb-2" style="font-size: 2rem;"></i>
                                        <p class="mb-0 text-muted">No deleted units found</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Unit Modal -->
<div class="modal fade" 
     id="addUnitModal" 
     data-bs-backdrop="static" 
     data-bs-keyboard="false" 
     tabindex="-1" 
     aria-labelledby="addUnitModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUnitModalLabel">Add New Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('units.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    <div class="mb-3">
                        <label for="UnitName" class="form-label">Unit Name</label>
                        <input type="text" class="form-control" id="UnitName" name="UnitName" value="{{ old('UnitName') }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Unit Modal -->
@foreach($units as $unit)
<div class="modal fade" 
     id="editUnitModal{{ $unit->UnitOfMeasureId }}" 
     data-bs-backdrop="static" 
     data-bs-keyboard="false" 
     tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('units.update', ['id' => $unit->UnitOfMeasureId]) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="UnitName{{ $unit->UnitOfMeasureId }}" class="form-label">Unit Name</label>
                        <input type="text" class="form-control" 
                               id="UnitName{{ $unit->UnitOfMeasureId }}" 
                               name="UnitName" 
                               value="{{ $unit->UnitName }}" 
                               required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@section('styles')
<style>
    /* Your existing styles plus: */
    .btn-group .btn {
        border-radius: 0;
    }
    
    .btn-group .btn:first-child {
        border-top-left-radius: 4px;
        border-bottom-left-radius: 4px;
    }
    
    .btn-group .btn:last-child {
        border-top-right-radius: 4px;
        border-bottom-right-radius: 4px;
    }

    .btn-group .btn.active {
        opacity: 1;
    }

    .btn-group .btn:not(.active) {
        opacity: 0.8;
    }

    .btn-group .btn:hover:not(.active) {
        opacity: 0.9;
    }

    .input-group {
        width: 250px;
    }

    .input-group-text {
        background-color: white;
        border-left: none;
    }

    .form-control:focus + .input-group-text {
        border-color: #86b7fe;
    }

    .input-group .form-control:focus {
        border-right: none;
        box-shadow: none;
    }

    /* Add styles for restore modal */
    #restoreUnitModal .modal-header {
        background-color: #198754;
        color: white;
    }

    #restoreUnitModal .btn-close-white {
        filter: brightness(0) invert(1);
    }

    #restoreUnitModal .alert-success {
        background-color: #f8f9fa;
        border-color: #198754;
        color: #0f5132;
    }

    #restoreUnitModal .modal-footer {
        border-top: 1px solid #dee2e6;
    }

    #restoreUnitModal .btn-success {
        background-color: #198754;
        border-color: #198754;
    }

    #restoreUnitModal .btn-success:hover {
        background-color: #157347;
        border-color: #146c43;
    }

    /* SweetAlert2 Custom Styles */
    .swal2-popup {
        font-size: 0.9rem;
    }

    .swal2-title {
        font-size: 1.5rem;
    }

    .swal2-content {
        font-size: 1rem;
    }

    .swal2-confirm {
        padding: 0.5rem 1.5rem !important;
    }
</style>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTables
    var activeTable = $('#activeUnitsTable').DataTable({
        pageLength: 10,
        responsive: true
    });

    var deletedTable = $('#deletedUnitsTable').DataTable({
        pageLength: 10,
        responsive: true
    });

    // Make sure DataTables are properly sized
    $('.table-responsive table').each(function() {
        $(this).DataTable().columns.adjust().draw();
    });

    // Delete confirmation
    $('.delete-unit-form').submit(function(e) {
        e.preventDefault();
        var form = $(this);
        var unitName = form.find('button').data('name');
        
        Swal.fire({
            title: 'Move Unit to Trash?',
            html: `Are you sure you want to move <strong>${unitName}</strong> to trash?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, move to trash',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                form.off('submit').submit();
            }
        });
    });

    // Restore confirmation
    $('.restore-unit-form').submit(function(e) {
        e.preventDefault();
        var form = $(this);
        var unitName = form.find('button').data('name');
        
        Swal.fire({
            title: 'Restore Unit?',
            html: `Are you sure you want to restore <strong>${unitName}</strong>?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, restore it',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                form.off('submit').submit();
            }
        });
    });
});
</script>

@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: "{{ session('success') }}",
        showConfirmButton: true
    });
</script>
@endif

@if(session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: "{{ session('error') }}",
        showConfirmButton: true
    });
</script>
@endif
@endsection 