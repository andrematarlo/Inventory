@extends('layouts.app')

@section('title', 'Classifications')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Classification Management</h2>
        @if($userPermissions && $userPermissions->CanAdd)
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClassificationModal">
            <i class="bi bi-plus-lg"></i> Add Classification
        </button>
        @endif
    </div>

    <div class="d-flex gap-2 mb-3">
        <button type="button" class="btn btn-primary active" id="activeRecords">Active Records</button>
        <button type="button" class="btn btn-outline-danger" id="deletedRecords">
            <i class="bi bi-trash"></i> Show Deleted Records
        </button>
    </div>

    <!-- Active Classifications Card -->
    <div class="card mb-4" id="activeRecordsCard">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Active Classifications</h5>
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
                        @forelse($classifications as $classification)
                        <tr>
                            @if($userPermissions && ($userPermissions->CanEdit || $userPermissions->CanDelete))
                            <td>
                                <div class="btn-group">
                                    @if($userPermissions->CanEdit)
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editClassificationModal{{ $classification->ClassificationId }}">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    @endif
                                    @if($userPermissions->CanDelete)
                                    <button type="button" 
                                            class="btn btn-danger" 
                                            onclick="confirmDelete('{{ $classification->ClassificationId }}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                            @endif
                            <td>{{ $classification->ClassificationName }}</td>
                            <td>{{ $classification->created_by_user->Username ?? 'N/A' }}</td>
                            <td>{{ $classification->DateCreated ? date('M d, Y h:i A', strtotime($classification->DateCreated)) : 'N/A' }}</td>
                            <td>{{ $classification->modified_by_user->Username ?? 'N/A' }}</td>
                            <td>{{ $classification->DateModified ? date('M d, Y h:i A', strtotime($classification->DateModified)) : 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ ($userPermissions && ($userPermissions->CanEdit || $userPermissions->CanDelete)) ? '6' : '5' }}" class="text-center">No classifications found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end align-items-center mt-3">
                {{ $classifications->appends(request()->except('page'))->links() }}
            </div>
        </div>
    </div>

    <!-- Deleted Classifications Card -->
    <div class="card mb-4" id="deletedRecordsCard" style="display: none;">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Deleted Classifications</h5>
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
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Actions</th>
                            <th>Name</th>
                            <th>Deleted By</th>
                            <th>Date Deleted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trashedClassifications as $classification)
                        <tr>
                            <td>
                                @if($userPermissions && $userPermissions->CanDelete)
                                <div class="btn-group btn-group-sm">
                                    <form action="{{ route('classifications.restore', $classification->ClassificationId) }}" method="POST">
                                        @csrf
                                        <button type="button" class="btn btn-sm btn-success restore-btn">
                                            <i class="bi bi-arrow-counterclockwise"></i> Restore
                                        </button>
                                    </form>
                                </div>
                                @endif
                            </td>
                            <td>{{ $classification->ClassificationName }}</td>
                            <td>{{ $classification->deleted_by_user->Username ?? 'N/A' }}</td>
                            <td>{{ $classification->DateDeleted ? date('M d, Y h:i A', strtotime($classification->DateDeleted)) : 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">No deleted classifications found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end align-items-center mt-3">
                {{ $trashedClassifications->appends(request()->except('page'))->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Add Classification Modal -->
@if($userPermissions && $userPermissions->CanAdd)
    @include('classifications.partials.add-modal')
@endif

<!-- Edit Classification Modals -->
@if($userPermissions && $userPermissions->CanEdit)
    @foreach($classifications as $classification)
        @include('classifications.partials.edit-modal', ['classification' => $classification])
    @endforeach
@endif

<!-- Delete Modal -->
<div class="modal fade" 
     id="deleteClassificationModal" 
     data-bs-backdrop="static" 
     data-bs-keyboard="false" 
     tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Classification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this classification?</p>
                <p class="text-danger mt-3"><small>This action can be undone later.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Restore Confirmation Modal -->
<div class="modal fade" id="restoreClassificationModal" tabindex="-1" aria-labelledby="restoreClassificationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="restoreClassificationModalLabel">
                    <i class="bi bi-arrow-counterclockwise"></i> Restore Classification
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to restore this classification?</p>
                <p class="text-success mt-3"><small>This classification will be available in active records again.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmRestoreBtn">Restore</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const activeRecordsBtn = document.getElementById('activeRecords');
    const deletedRecordsBtn = document.getElementById('deletedRecords');
    const activeRecordsCard = document.getElementById('activeRecordsCard');
    const deletedRecordsCard = document.getElementById('deletedRecordsCard');

    // Search functionality
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
        const activeTableBody = activeRecordsCard.querySelector('tbody');
        filterTable(activeTableBody, e.target.value);
    });

    deletedSearchInput.addEventListener('input', (e) => {
        const deletedTableBody = deletedRecordsCard.querySelector('tbody');
        filterTable(deletedTableBody, e.target.value);
    });

    function toggleRecords(showActive) {
        if (showActive) {
            activeRecordsCard.style.display = 'block';
            deletedRecordsCard.style.display = 'none';
            activeRecordsBtn.classList.add('active');
            activeRecordsBtn.classList.remove('btn-outline-primary');
            activeRecordsBtn.classList.add('btn-primary');
            deletedRecordsBtn.classList.remove('active');
            deletedRecordsBtn.classList.add('btn-outline-danger');
            deletedRecordsBtn.classList.remove('btn-danger');
            // Clear deleted search when switching
            deletedSearchInput.value = '';
        } else {
            activeRecordsCard.style.display = 'none';
            deletedRecordsCard.style.display = 'block';
            deletedRecordsBtn.classList.add('active');
            deletedRecordsBtn.classList.remove('btn-outline-danger');
            deletedRecordsBtn.classList.add('btn-danger');
            activeRecordsBtn.classList.remove('active');
            activeRecordsBtn.classList.add('btn-outline-primary');
            activeRecordsBtn.classList.remove('btn-primary');
            // Clear active search when switching
            activeSearchInput.value = '';
        }
    }

    activeRecordsBtn.addEventListener('click', () => toggleRecords(true));
    deletedRecordsBtn.addEventListener('click', () => toggleRecords(false));

    // Initialize view
    toggleRecords(true);

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

    // Add SweetAlert2 delete confirmation
    window.confirmDelete = function(id) {
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteClassificationModal'));
        
        // Update modal content with classification ID
        document.getElementById('confirmDeleteBtn').setAttribute('data-id', id);
        
        // Show the modal
        deleteModal.show();
    }

    // Handle delete confirmation
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/inventory/classifications/${id}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        form.appendChild(methodField);
        
        document.body.appendChild(form);
        form.submit();
    });

    // Handle restore button click
    const restoreButtons = document.querySelectorAll('.restore-btn');
    
    restoreButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            const restoreModal = new bootstrap.Modal(document.getElementById('restoreClassificationModal'));
            
            // Store the form for later use
            document.getElementById('confirmRestoreBtn').setAttribute('data-form', form.outerHTML);
            
            // Show the modal
            restoreModal.show();
        });
    });

    // Handle restore confirmation
    document.getElementById('confirmRestoreBtn').addEventListener('click', function() {
        const formHTML = this.getAttribute('data-form');
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = formHTML;
        const form = tempDiv.firstChild;
        document.body.appendChild(form);
        form.submit();
        
        // Close the modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('restoreClassificationModal'));
        modal.hide();
        
        // Show success message
        Swal.fire({
            icon: 'success',
            title: 'Restored!',
            text: 'Classification has been restored successfully.',
            showConfirmButton: false,
            timer: 1500
        });
    });
});

function updatePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    window.location.href = url.toString();
}
</script>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush