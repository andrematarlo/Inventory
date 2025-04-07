@extends('layouts.app')

@section('title', 'Classifications')

@section('content')
<style>
    #activeRecordsCard {
        display: block;
    }
    #deletedRecordsCard {
        display: none;
    }
</style>

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
        <button type="button" class="btn btn-primary active" id="activeRecords" onclick="document.getElementById('activeRecordsCard').style.display='block'; document.getElementById('deletedRecordsCard').style.display='none'; this.classList.add('active'); this.classList.remove('btn-outline-primary'); this.classList.add('btn-primary'); document.getElementById('deletedRecords').classList.remove('active'); document.getElementById('deletedRecords').classList.add('btn-outline-danger'); document.getElementById('deletedRecords').classList.remove('btn-danger');">Active Records</button>
        <button type="button" class="btn btn-outline-danger" id="deletedRecords" onclick="document.getElementById('activeRecordsCard').style.display='none'; document.getElementById('deletedRecordsCard').style.display='block'; this.classList.add('active'); this.classList.remove('btn-outline-danger'); this.classList.add('btn-danger'); document.getElementById('activeRecords').classList.remove('active'); document.getElementById('activeRecords').classList.add('btn-outline-primary'); document.getElementById('activeRecords').classList.remove('btn-primary');">
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
                                    <form action="{{ route('classifications.destroy', $classification->ClassificationId) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this classification?');">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
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
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to restore this classification?');">
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

@endsection

@section('scripts')
<script>
// Move confirmDelete function to global scope
window.confirmDelete = function(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to delete this classification?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Create and submit the form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/inventory/classifications/${id}`;
            
            // Add CSRF token
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            // Add method spoofing for DELETE
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            form.appendChild(methodField);
            
            // Add form to body and submit
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// Add debugging to see if elements exist
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded');
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

    // Add event listeners for delete buttons
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            
            Swal.fire({
                title: 'Are you sure?',
                text: "You want to delete this classification?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create and submit the form
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/inventory/classifications/${id}`;
                    
                    // Add CSRF token
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';
                    form.appendChild(csrfToken);
                    
                    // Add method spoofing for DELETE
                    const methodField = document.createElement('input');
                    methodField.type = 'hidden';
                    methodField.name = '_method';
                    methodField.value = 'DELETE';
                    form.appendChild(methodField);
                    
                    // Add form to body and submit
                    document.body.appendChild(form);
                    form.submit();
                }
            });
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