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
                                            <a href="{{ route('roles.edit', $role->RoleId) }}" 
                                               class="btn btn-primary">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            @endif
                                            @if($userPermissions->CanDelete)
                                            <form action="{{ route('roles.destroy', $role->RoleId) }}" 
                                                  method="POST" 
                                                  class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-danger"
                                                        onclick="return confirm('Are you sure you want to delete this role?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
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
                                        <form action="{{ route('roles.restore', $role->RoleId) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        </form>
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
    @include('roles.partials.add-modal')
@endif

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
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
    });
</script>
@endsection