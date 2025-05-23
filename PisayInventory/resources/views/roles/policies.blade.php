@extends('layouts.app')

@section('title', 'Role Policies')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Role Policies</h2>
        @if(isset($userPermissions) && $userPermissions->CanAdd)
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPolicyModal">
                <i class="bi bi-plus-circle me-2"></i>Create Policy
            </button>
        @endif
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

<!-- Create Policy Modal -->
@if(isset($userPermissions) && $userPermissions->CanAdd)
<div class="modal fade" id="createPolicyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Policy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('roles.policies.create') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="RoleId" class="form-label">Role</label>
                        <select class="form-select" id="RoleId" name="RoleId" required>
                            <option value="">Select Role</option>
                            @foreach(DB::table('roles')->where('IsDeleted', false)->get() as $role)
                                <option value="{{ $role->RoleId }}">{{ $role->RoleName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="Module" class="form-label">Module</label>
                        <select class="form-select" id="Module" name="Module" required>
                            <option value="">Select Module</option>
                            @foreach(DB::table('modules')->get() as $module)
                                <option value="{{ $module->ModuleName }}">{{ $module->ModuleName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Permissions</label>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="CanView" name="CanView" value="1">
                            <label class="form-check-label" for="CanView">Can View</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="CanAdd" name="CanAdd" value="1">
                            <label class="form-check-label" for="CanAdd">Can Add</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="CanEdit" name="CanEdit" value="1">
                            <label class="form-check-label" for="CanEdit">Can Edit</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="CanDelete" name="CanDelete" value="1">
                            <label class="form-check-label" for="CanDelete">Can Delete</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Policy</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

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
                            <label class="form-label">Permissions</label>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="view{{ $policy->RolePolicyId }}" 
                                       name="permissions[view]" {{ $policy->CanView ? 'checked' : '' }}>
                                <label class="form-check-label" for="view{{ $policy->RolePolicyId }}">Can View</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="add{{ $policy->RolePolicyId }}" 
                                       name="permissions[add]" {{ $policy->CanAdd ? 'checked' : '' }}>
                                <label class="form-check-label" for="add{{ $policy->RolePolicyId }}">Can Add</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="edit{{ $policy->RolePolicyId }}" 
                                       name="permissions[edit]" {{ $policy->CanEdit ? 'checked' : '' }}>
                                <label class="form-check-label" for="edit{{ $policy->RolePolicyId }}">Can Edit</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="delete{{ $policy->RolePolicyId }}" 
                                       name="permissions[delete]" {{ $policy->CanDelete ? 'checked' : '' }}>
                                <label class="form-check-label" for="delete{{ $policy->RolePolicyId }}">Can Delete</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Policy</button>
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
        $('#editPolicyModal' + policyId).modal('show');
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