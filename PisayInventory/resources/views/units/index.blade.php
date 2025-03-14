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
                                        <button type="button" 
                                                class="btn btn-danger delete-unit"
                                                data-unit-id="{{ $unit->UnitOfMeasureId }}"
                                                data-unit-name="{{ $unit->UnitName }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
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

<!-- Delete Modal Template -->
<div class="modal fade" 
     id="deleteUnitModal" 
     data-bs-backdrop="static" 
     data-bs-keyboard="false" 
     tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this unit?</p>
                <p class="text-danger mt-3"><small>This action can be undone later.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Restore Modal -->
<div class="modal fade" id="restoreUnitModal" tabindex="-1" aria-labelledby="restoreUnitModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="restoreUnitModalLabel">Restore Unit</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to restore this unit?</p>
                <p id="unitNameToRestore" class="fw-bold text-success"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmRestoreBtn">
                    <i class="bi bi-arrow-counterclockwise"></i> Restore
                </button>
            </div>
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
        // Initialize DataTable
        if (!$.fn.DataTable.isDataTable('#unitsTable')) {
            $('#unitsTable').DataTable({
                pageLength: 10,
                responsive: true,
                dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center"ip>',
                language: {
                    search: "Search:",
                    searchPlaceholder: "Search units..."
                }
            });
        }

        // Initialize all modals with static backdrop
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            const bsModal = new bootstrap.Modal(modal, {
                backdrop: 'static',
                keyboard: false
            });

            // Prevent modal from closing when clicking outside
            $(modal).on('mousedown', function(e) {
                if ($(e.target).is('.modal')) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            });
        });

        // Delete confirmation handler
        $('.delete-unit').click(function(e) {
            e.preventDefault();
            const unitId = $(this).data('unit-id');
            const unitName = $(this).data('unit-name');

            // Update modal content
            $('#deleteUnitModal .modal-body p:first').html(
                `Are you sure you want to delete unit: <strong>${unitName}</strong>?`
            );
            
            // Store the ID for use in confirmation
            $('#confirmDeleteBtn').data('unit-id', unitId);
            
            // Show the modal
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteUnitModal'));
            deleteModal.show();
        });

        // Handle delete confirmation
        $('#confirmDeleteBtn').click(function() {
            const unitId = $(this).data('unit-id');
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/inventory/units/${unitId}`;
            form.innerHTML = `
                @csrf
                @method('DELETE')
            `;
            document.body.appendChild(form);
            form.submit();
        });

        // Restore unit handling
        $('.restore-unit').click(function(e) {
            e.preventDefault();
            const form = $(this).closest('form');
            const unitName = $(this).closest('tr').find('td:eq(1)').text(); // Get unit name from the table
            
            // Update modal content
            $('#unitNameToRestore').text(unitName);
            
            // Store the form for use in confirmation
            $('#confirmRestoreBtn').data('form', form);
            
            // Show the modal
            $('#restoreUnitModal').modal('show');
        });

        // Handle restore confirmation
        $('#confirmRestoreBtn').click(function() {
            const form = $(this).data('form');
            
            // Hide the modal
            $('#restoreUnitModal').modal('hide');
            
            // Show loading state
            Swal.fire({
                title: 'Restoring...',
                text: 'Please wait while we process your request',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Submit the form using AJAX
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Unit has been restored successfully.',
                        confirmButtonColor: '#198754',
                        showConfirmButton: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.reload();
                        }
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Restore Error:', xhr.responseText);
                    let errorMessage = 'Something went wrong while restoring the unit.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMessage,
                        confirmButtonColor: '#dc3545'
                    });
                }
            });
        });
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const activeRecordsBtn = document.getElementById('activeRecordsBtn');
    const showDeletedBtn = document.getElementById('showDeletedBtn');
    const activeUnits = document.getElementById('activeUnits');
    const deletedUnits = document.getElementById('deletedUnits');
    const activeSearchInput = document.getElementById('activeSearchInput');
    const deletedSearchInput = document.getElementById('deletedSearchInput');

    function filterTable(tableBody, searchTerm) {
        const rows = tableBody.getElementsByTagName('tr');
        
        for (let row of rows) {
            const cells = row.getElementsByTagName('td');
            let shouldShow = false;
            
            // Skip header row or empty message row
            if (cells.length <= 1) continue;

            for (let cell of cells) {
                const text = cell.textContent.toLowerCase();
                if (text.includes(searchTerm.toLowerCase())) {
                    shouldShow = true;
                    break;
                }
            }
            
            row.style.display = shouldShow ? '' : 'none';
        }
    }

    activeSearchInput.addEventListener('input', (e) => {
        const activeTableBody = activeUnits.querySelector('tbody');
        filterTable(activeTableBody, e.target.value);
    });

    deletedSearchInput.addEventListener('input', (e) => {
        const deletedTableBody = deletedUnits.querySelector('tbody');
        filterTable(deletedTableBody, e.target.value);
    });

    function toggleRecords(showActive) {
        if (showActive) {
            activeUnits.style.display = 'block';
            deletedUnits.style.display = 'none';
            activeRecordsBtn.classList.add('active');
            activeRecordsBtn.classList.remove('btn-outline-primary');
            activeRecordsBtn.classList.add('btn-primary');
            showDeletedBtn.classList.remove('active');
            showDeletedBtn.classList.add('btn-outline-danger');
            showDeletedBtn.classList.remove('btn-danger');
            // Clear deleted search when switching
            deletedSearchInput.value = '';
        } else {
            activeUnits.style.display = 'none';
            deletedUnits.style.display = 'block';
            showDeletedBtn.classList.add('active');
            showDeletedBtn.classList.remove('btn-outline-danger');
            showDeletedBtn.classList.add('btn-danger');
            activeRecordsBtn.classList.remove('active');
            activeRecordsBtn.classList.add('btn-outline-primary');
            activeRecordsBtn.classList.remove('btn-primary');
            // Clear active search when switching
            activeSearchInput.value = '';
        }
    }

    activeRecordsBtn.addEventListener('click', () => toggleRecords(true));
    showDeletedBtn.addEventListener('click', () => toggleRecords(false));

    // Initialize view
    toggleRecords(true);
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

    /* Restore Modal Styles */
    #restoreUnitModal .modal-header {
        background-color: #198754;
        color: white;
    }

    #restoreUnitModal .btn-close-white {
        filter: brightness(0) invert(1);
    }

    #restoreUnitModal .modal-body {
        padding: 1.5rem;
    }

    #restoreUnitModal #unitNameToRestore {
        color: #198754;
        font-size: 1.1rem;
        margin-top: 0.5rem;
        padding: 0.5rem;
        background-color: #f8f9fa;
        border-radius: 4px;
        text-align: center;
    }

    #restoreUnitModal .modal-footer {
        padding: 1rem;
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

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

<!-- Add error message display if exists in session -->
@if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: '{{ session('error') }}',
        showConfirmButton: true
    });
@endif 