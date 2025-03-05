@extends('layouts.app')

@section('title', 'Role Policies')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Role Policies</h2>
    </div>

    <div class="card">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover" id="rolePoliciesTable">
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Module</th>
                            <th>Permissions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($policies as $policy)
                            <tr>
                                <td>{{ $policy->role->RoleName }}</td>
                                <td>{{ $policy->Module }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <span class="badge bg-{{ $policy->CanView ? 'success' : 'secondary' }}">View</span>
                                        <span class="badge bg-{{ $policy->CanAdd ? 'success' : 'secondary' }}">Add</span>
                                        <span class="badge bg-{{ $policy->CanEdit ? 'success' : 'secondary' }}">Edit</span>
                                        <span class="badge bg-{{ $policy->CanDelete ? 'success' : 'secondary' }}">Delete</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        @if(isset($userPermissions) && $userPermissions->CanEdit)
                                        <button type="button" 
                                                class="btn btn-sm btn-primary" 
                                                onclick="openPolicyModal('{{ $policy->RolePolicyId }}')"
                                                title="Edit">
                                            <i class="bi bi-pencil"></i>  Edit
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No role policies found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Policy Modals -->
@if(isset($userPermissions) && $userPermissions->CanEdit)
@foreach($policies as $policy)
    <div class="modal fade" id="editPolicyModal{{ $policy->RolePolicyId }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Policy: {{ $policy->role->RoleName }} - {{ $policy->Module }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('roles.policies.update', $policy->RolePolicyId) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="policy_id" value="{{ $policy->RolePolicyId }}">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label d-block">Permissions</label>
                            
                            <!-- VIEW PERMISSION -->
                            <div class="form-check form-check-inline">
                                <input type="hidden" name="view" value="0">
                                <input type="checkbox" class="form-check-input" 
                                       id="canView{{ $policy->RolePolicyId }}" 
                                       name="view" 
                                       value="1"
                                       {{ $policy->CanView ? 'checked' : '' }}>
                                <label class="form-check-label" for="canView{{ $policy->RolePolicyId }}">View</label>
                            </div>
                            
                            <!-- ADD PERMISSION -->
                            <div class="form-check form-check-inline">
                                <input type="hidden" name="add" value="0">
                                <input type="checkbox" class="form-check-input" 
                                       id="canAdd{{ $policy->RolePolicyId }}" 
                                       name="add" 
                                       value="1"
                                       {{ $policy->CanAdd ? 'checked' : '' }}>
                                <label class="form-check-label" for="canAdd{{ $policy->RolePolicyId }}">Add</label>
                            </div>
                            
                            <!-- EDIT PERMISSION -->
                            <div class="form-check form-check-inline">
                                <input type="hidden" name="edit" value="0">
                                <input type="checkbox" class="form-check-input" 
                                       id="canEdit{{ $policy->RolePolicyId }}" 
                                       name="edit" 
                                       value="1"
                                       {{ $policy->CanEdit ? 'checked' : '' }}>
                                <label class="form-check-label" for="canEdit{{ $policy->RolePolicyId }}">Edit</label>
                            </div>
                            
                            <!-- DELETE PERMISSION -->
                            <div class="form-check form-check-inline">
                                <input type="hidden" name="delete" value="0">
                                <input type="checkbox" class="form-check-input" 
                                       id="canDelete{{ $policy->RolePolicyId }}" 
                                       name="delete" 
                                       value="1"
                                       {{ $policy->CanDelete ? 'checked' : '' }}>
                                <label class="form-check-label" for="canDelete{{ $policy->RolePolicyId }}">Delete</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endif
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        if (!$.fn.DataTable.isDataTable('#rolePoliciesTable')) {
            $('#rolePoliciesTable').DataTable({
                pageLength: 10,
                responsive: true,
                dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center"ip>',
                language: {
                    search: "Search:",
                    searchPlaceholder: "Search policies..."
                },
                destroy: true
            });
        }
    });

    function openPolicyModal(policyId) {
        // Check if user has edit permission (handled server-side)
        // If the button is visible, user has permission
        try {
            var modalId = '#editPolicyModal' + policyId;
            var modalElement = document.querySelector(modalId);
            
            if (!modalElement) {
                console.error('Modal not found:', modalId);
                alert('Error: Could not find the edit modal. Please refresh the page and try again.');
                return;
            }
            
            var modal = new bootstrap.Modal(modalElement);
            modal.show();
        } catch (error) {
            console.error('Error opening modal:', error);
            alert('An error occurred while opening the modal. Please try again.');
        }
    }
</script>

@if(!isset($userPermissions) || !$userPermissions->CanEdit)
<script>
    // Override the openPolicyModal function for users without permission
    function openPolicyModal(policyId) {
        Swal.fire({
            icon: 'error',
            title: 'Access Denied',
            text: 'You do not have permission to edit role policies.'
        });
    }
</script>
@endif

@endsection 