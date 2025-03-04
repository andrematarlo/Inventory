@extends('layouts.app')

@section('title', 'Role Management')

@section('content')
<div class="container">
    <!-- Header Section -->
    <div class="mb-4">
        <h2 class="mb-3">Role Management</h2>
        <div class="d-flex justify-content-between align-items-center">
            <div class="btn-group" role="group">
                <a href="{{ route('roles.index') }}" 
                   class="btn btn-outline-primary {{ !request('show_deleted') ? 'active' : '' }}">
                    Active Roles
                </a>
                <a href="{{ route('roles.index', ['show_deleted' => 1]) }}" 
                   class="btn btn-danger {{ request('show_deleted') ? 'active' : '' }}">
                    <i class="bi bi-trash"></i> Deleted Roles
                </a>
            </div>
            @if($userPermissions && $userPermissions->CanAdd && !request('show_deleted'))
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                <i class="bi bi-plus-lg"></i> Add Role
            </button>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Roles Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0 {{ request('show_deleted') ? 'text-danger' : 'text-primary' }}">
                {{ request('show_deleted') ? 'Deleted Roles' : 'Active Roles' }}
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="rolesTable">
                    <thead class="bg-light">
                        <tr>
                            @if($userPermissions && (($userPermissions->CanEdit || $userPermissions->CanDelete) && !request('show_deleted') || ($userPermissions->CanEdit && request('show_deleted'))))
                            <th width="150" class="text-center">Actions</th>
                            @endif
                            <th>Role Name</th>
                            <th>Description</th>
                            @if(!request('show_deleted'))
                                <th>Created By</th>
                                <th>Date Created</th>
                                <th>Modified By</th>
                                <th>Date Modified</th>
                            @else
                                <th>Deleted By</th>
                                <th>Date Deleted</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @if(!request('show_deleted'))
                            @forelse($roles as $role)
                                <tr>
                                    @if($userPermissions && ($userPermissions->CanEdit || $userPermissions->CanDelete))
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            @if($userPermissions->CanEdit)
                                            <a href="{{ route('roles.edit', ['id' => $role->RoleId]) }}" 
                                               class="btn btn-primary">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            @endif
                                            @if($userPermissions->CanDelete)
                                            <button type="button" 
                                                    class="btn btn-danger delete-role"
                                                    data-role-id="{{ $role->RoleId }}"
                                                    data-role-name="{{ $role->RoleName }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                    @endif
                                    <td>{{ $role->RoleName }}</td>
                                    <td>{{ $role->Description }}</td>
                                    <td>{{ $role->created_by_user->Username ?? 'N/A' }}</td>
                                    <td>{{ $role->DateCreated ? date('M d, Y h:i A', strtotime($role->DateCreated)) : 'N/A' }}</td>
                                    <td>{{ $role->modified_by_user->Username ?? 'N/A' }}</td>
                                    <td>{{ $role->DateModified ? date('M d, Y h:i A', strtotime($role->DateModified)) : 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ ($userPermissions && ($userPermissions->CanEdit || $userPermissions->CanDelete)) ? '7' : '6' }}" class="text-center py-4">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bi bi-folder text-muted" style="font-size: 2rem;"></i>
                                            <h5 class="mt-2 mb-1">No Roles Found</h5>
                                            <p class="text-muted mb-0">No roles have been created yet</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        @else
                            @forelse($trashedRoles as $role)
                                <tr>
                                    @if($userPermissions && $userPermissions->CanEdit)
                                    <td class="text-center">
                                        @if($userPermissions->CanEdit)
                                        <button type="button" 
                                                class="btn btn-sm btn-success restore-role"
                                                data-role-id="{{ $role->RoleId }}"
                                                data-role-name="{{ $role->RoleName }}">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                        @endif
                                    </td>
                                    @endif
                                    <td>{{ $role->RoleName }}</td>
                                    <td>{{ $role->Description }}</td>
                                    <td>{{ $role->deleted_by_user->Username ?? 'N/A' }}</td>
                                    <td>{{ $role->DateDeleted ? date('M d, Y h:i A', strtotime($role->DateDeleted)) : 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $userPermissions && $userPermissions->CanEdit ? '5' : '4' }}" class="text-center py-4">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bi bi-trash text-muted" style="font-size: 2rem;"></i>
                                            <h5 class="mt-2 mb-1">No Deleted Roles</h5>
                                            <p class="text-muted mb-0">Deleted roles will appear here</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Role Modal -->
@if($userPermissions && $userPermissions->CanAdd && !request('show_deleted'))
<div class="modal fade" 
     id="addRoleModal" 
     data-bs-backdrop="static" 
     data-bs-keyboard="false" 
     tabindex="-1" 
     aria-labelledby="addRoleModalLabel" 
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRoleModalLabel">Add New Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('roles.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="RoleName" class="form-label">Role Name</label>
                        <input type="text" class="form-control" id="RoleName" name="RoleName" required>
                    </div>
                    <div class="mb-3">
                        <label for="Description" class="form-label">Description</label>
                        <textarea class="form-control" id="Description" name="Description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Role</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Delete Modal -->
@foreach($roles as $role)
<div class="modal fade" id="deleteModal{{ $role->RoleId }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="mb-4">
                    <i class="bi bi-exclamation-circle text-warning" style="font-size: 3rem;"></i>
                </div>
                <h4 class="mb-3">Are you sure?</h4>
                <p class="mb-4">You won't be able to revert this!</p>
                <form action="{{ route('roles.destroy', $role->RoleId) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Yes, delete it!</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    if (!$.fn.DataTable.isDataTable('#rolesTable')) {
        $('#rolesTable').DataTable({
            pageLength: 10,
            responsive: true,
            dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center"ip>',
            language: {
                search: "Search:",
                searchPlaceholder: "Search roles..."
            }
        });
    }

    // Success/Error messages
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    @if(Session::has('success'))
        Toast.fire({
            icon: 'success',
            title: @json(Session::get('success'))
        });
    @endif

    @if(Session::has('error'))
        Toast.fire({
            icon: 'error',
            title: @json(Session::get('error'))
        });
    @endif

    // Delete confirmation handler
    $('.delete-role').click(function(e) {
        e.preventDefault();
        const roleId = $(this).data('role-id');
        const roleName = $(this).data('role-name');

        Swal.fire({
            title: 'Delete Role?',
            html: `Are you sure you want to delete role: <strong>${roleName}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/inventory/roles/${roleId}`;
                form.innerHTML = `
                    @csrf
                    @method('DELETE')
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    });

    // Restore confirmation handler
    $('.restore-role').click(function(e) {
        e.preventDefault();
        const roleId = $(this).data('role-id');
        const roleName = $(this).data('role-name');

        Swal.fire({
            title: 'Restore Role?',
            html: `Are you sure you want to restore role: <strong>${roleName}</strong>?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, restore it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/inventory/roles/${roleId}/restore`;
                form.innerHTML = `
                    @csrf
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>
@endsection

@section('additional_styles')
<style>
    .modal-dialog-centered {
        display: flex;
        align-items: center;
        min-height: calc(100% - 1rem);
    }

    .bi-exclamation-circle {
        color: #ffc107;
    }

    .modal-body {
        padding: 2rem;
    }
</style>
@endsection

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection