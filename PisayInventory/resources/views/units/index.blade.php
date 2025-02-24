@extends('layouts.app')

@section('title', 'Units')

@section('content')
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
    <div class="btn-group mb-4" role="group">
        <button class="btn btn-primary active" type="button" id="activeRecordsBtn">
            Active Records
        </button>
        <button class="btn btn-danger" type="button" id="showDeletedBtn">
            <i class="bi bi-archive"></i> Show Deleted Records
        </button>
    </div>

    <!-- Active Units Section -->
    <div id="activeUnits">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Active Units</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
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
                            <tr>
                                @if($userPermissions && ($userPermissions->CanEdit || $userPermissions->CanDelete))
                                <td>
                                    <div class="btn-group">
                                        @if($userPermissions->CanEdit)
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editUnitModal{{ $unit->UnitOfMeasureId }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        @endif
                                        @if($userPermissions->CanDelete)
                                        <form action="{{ route('units.destroy', $unit->UnitOfMeasureId) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this unit?')">
                                                <i class="bi bi-trash"></i>
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
                                <td colspan="{{ ($userPermissions && ($userPermissions->CanEdit || $userPermissions->CanDelete)) ? '6' : '5' }}" class="text-center">No units found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Deleted Units Section -->
    <div id="deletedUnits" style="display: none;">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Deleted Units</h5>
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
                                    <form action="{{ route('units.restore', $unit->UnitOfMeasureId) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Restore">
                                            <i class="bi bi-arrow-counterclockwise"></i>
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
                                <td colspan="4" class="text-center">No deleted units found</td>
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
                    <div class="mb-3">
                        <label for="UnitName" class="form-label">Unit Name</label>
                        <input type="text" class="form-control" id="UnitName" name="UnitName" required>
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

<!-- Delete Unit Modal -->
<div class="modal fade" 
     id="deleteUnitModal{{ $unit->UnitOfMeasureId }}" 
     data-bs-backdrop="static" 
     data-bs-keyboard="false" 
     tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('units.destroy', ['id' => $unit->UnitOfMeasureId]) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Are you sure you want to delete this unit: <strong>{{ $unit->UnitName }}</strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize all unit modals
        const unitModals = document.querySelectorAll('[id^="addUnitModal"], [id^="editUnitModal"], [id^="deleteUnitModal"]');
        unitModals.forEach(modal => {
            // Initialize with Bootstrap's options
            const bsModal = new bootstrap.Modal(modal, {
                backdrop: 'static',
                keyboard: false
            });

            // Add click handler to prevent closing
            $(modal).on('click mousedown', function(e) {
                if ($(e.target).hasClass('modal')) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            });

            // Also prevent Esc key
            $(modal).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    e.preventDefault();
                    return false;
                }
            });
        });

        // Destroy existing DataTable if it exists
        if ($.fn.DataTable.isDataTable('#unitsTable')) {
            $('#unitsTable').DataTable().destroy();
        }
        
        // Initialize DataTable
        const table = $('#unitsTable').DataTable({
            pageLength: 10,
            ordering: true,
            order: [[3, 'desc']], // Sort by date created by default
            responsive: true,
            destroy: true, // Allow table to be destroyed and recreated
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search units..."
            },
            columnDefs: [
                { orderable: false, targets: 0 } // Disable sorting on actions column
            ]
        });

        // Handle modal events to prevent DataTable issues
        $('.modal').on('hidden.bs.modal', function () {
            if ($.fn.DataTable.isDataTable('#unitsTable')) {
                table.draw();
            }
        });

        // Tab switching functionality
        $('#activeRecordsBtn').click(function() {
            $(this).addClass('active');
            $('#showDeletedBtn').removeClass('active');
            $('#activeUnits').show();
            $('#deletedUnits').hide();
        });

        $('#showDeletedBtn').click(function() {
            $(this).addClass('active');
            $('#activeRecordsBtn').removeClass('active');
            $('#activeUnits').hide();
            $('#deletedUnits').show();
        });

        // Initialize DataTable for deleted records
        if (!$.fn.DataTable.isDataTable('#deletedUnitsTable')) {
            $('#deletedUnitsTable').DataTable({
                pageLength: 10,
                ordering: true,
                order: [[3, 'desc']], // Sort by date deleted by default
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search deleted units..."
                },
                columnDefs: [
                    { orderable: false, targets: 0 } // Disable sorting on actions column
                ]
            });
        }
    });
</script>
@endsection

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
</style>
@endsection 