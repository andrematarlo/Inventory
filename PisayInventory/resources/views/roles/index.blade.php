@extends('layouts.app')

@section('title', 'Role Management')

@section('content')
<div class="container">
    <div class="d-flex justify-content-end mb-3">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">Add Role</button>
    </div>

    <!-- Active Roles Card -->
    <div class="card mb-4">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th class="text-center">Actions</th>
                            <th>Role Name</th>
                            <th>Description</th>
                            <th>Created Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr>
                                <td class="text-center">
                                    <a href="{{ route('roles.edit', $role->RoleId) }}" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <form action="{{ route('roles.destroy', $role->RoleId) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this role?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                                <td>{{ $role->RoleName }}</td>
                                <td>{{ $role->Description }}</td>
                                <td>{{ $role->DateCreated ? date('M d, Y', strtotime($role->DateCreated)) : 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">No roles found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Deleted Roles Card -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th class="text-center">Actions</th>
                            <th>Role Name</th>
                            <th>Description</th>
                            <th>Deleted By</th>
                            <th>Date Deleted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trashedRoles as $role)
                            <tr>
                                <td class="text-center">
                                    <form action="{{ route('roles.restore', $role->RoleId) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </form>
                                </td>
                                <td>{{ $role->RoleName }}</td>
                                <td>{{ $role->Description }}</td>
                                <td>{{ $role->deleted_by_user->Username ?? 'N/A' }}</td>
                                <td>{{ $role->DateDeleted ? date('M d, Y h:i A', strtotime($role->DateDeleted)) : 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bi bi-trash text-muted" style="font-size: 2rem;"></i>
                                        <h5 class="mt-2 mb-1">No Deleted Roles</h5>
                                        <p class="text-muted mb-0">Deleted roles will appear here</p>
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

<!-- Add Role Modal -->
@include('roles.partials.add-modal')
@endsection