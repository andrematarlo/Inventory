@extends('layouts.app')

@section('title', 'Role Management')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Role Management</h2>
        <a href="{{ route('roles.create') }}" class="btn btn-primary">
            <i class="bi bi-shield-plus me-2"></i>Add Role
        </a>
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
                <table class="table table-hover" id="rolesTable">
                    <thead>
                        <tr>
                            <th>Role Name</th>
                            <th>Description</th>
                            <th>Created Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr>
                                <td>{{ $role->RoleName }}</td>
                                <td>{{ $role->Description }}</td>
                                <td>{{ $role->DateCreated ? date('M d, Y', strtotime($role->DateCreated)) : 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-{{ $role->IsDeleted ? 'danger' : 'success' }}">
                                        {{ $role->IsDeleted ? 'Inactive' : 'Active' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('roles.edit', $role->RoleId) }}" 
                                           class="btn btn-sm btn-primary" 
                                           title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('roles.destroy', $role->RoleId) }}" 
                                              method="POST" 
                                              class="d-inline" 
                                              onsubmit="return confirm('Are you sure you want to delete this role?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-danger" 
                                                    title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No roles found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Check if DataTable is already initialized
        if (!$.fn.DataTable.isDataTable('#rolesTable')) {
            $('#rolesTable').DataTable({
                pageLength: 10,
                responsive: true,
                dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center"ip>',
                language: {
                    search: "Search:",
                    searchPlaceholder: "Search roles..."
                },
                destroy: true // Allow table to be reinitialized
            });
        }
    });
</script>
@endsection 