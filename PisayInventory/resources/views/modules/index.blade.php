@extends('layouts.app')

@section('title', 'Modules')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Module Management</h2>
        @if($userPermissions && $userPermissions->CanAdd)
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModuleModal">
            <i class="bi bi-plus-lg"></i> New Module
        </button>
        @endif
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            @if($userPermissions && ($userPermissions->CanEdit || $userPermissions->CanDelete))
                            <th style="width: 120px">Actions</th>
                            @endif
                            <th>Module Name <i class="bi bi-arrow-down-up small-icon"></i></th>
                            <th>Created At <i class="bi bi-arrow-down-up small-icon"></i></th>
                            <th>Updated At <i class="bi bi-arrow-down-up small-icon"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($modules as $module)
                        <tr>
                            @if($userPermissions && ($userPermissions->CanEdit || $userPermissions->CanDelete))
                            <td>
                                <div class="btn-group" role="group">
                                    @if($userPermissions->CanEdit)
                                    <button type="button" class="btn btn-sm btn-blue" data-bs-toggle="modal" data-bs-target="#editModuleModal{{ $module->ModuleId }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    @endif
                                    @if($userPermissions->CanDelete)
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModuleModal{{ $module->ModuleId }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                            @endif
                            <td>{{ $module->ModuleName }}</td>
                            <td>{{ $module->CreatedAt }}</td>
                            <td>{{ $module->UpdatedAt }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">No modules found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Module Modal -->
@if($userPermissions && $userPermissions->CanAdd)
<div class="modal fade" 
     id="addModuleModal" 
     data-bs-backdrop="static" 
     data-bs-keyboard="false" 
     tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Module</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('modules.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="ModuleName" class="form-label">Module Name</label>
                        <input type="text" class="form-control" id="ModuleName" name="ModuleName" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Module</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Edit Module Modals -->
@if($userPermissions && $userPermissions->CanEdit)
@foreach($modules as $module)
<div class="modal fade" 
     id="editModuleModal{{ $module->ModuleId }}" 
     data-bs-backdrop="static" 
     data-bs-keyboard="false" 
     tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Module</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('modules.update', $module->ModuleId) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="ModuleName{{ $module->ModuleId }}" class="form-label">Module Name</label>
                        <input type="text" class="form-control" id="ModuleName{{ $module->ModuleId }}" name="ModuleName" value="{{ $module->ModuleName }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Module</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endif

<!-- Delete Module Modals -->
@if($userPermissions && $userPermissions->CanDelete)
@foreach($modules as $module)
<div class="modal fade" 
     id="deleteModuleModal{{ $module->ModuleId }}" 
     data-bs-backdrop="static" 
     data-bs-keyboard="false" 
     tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Module</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the module "{{ $module->ModuleName }}"?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('modules.destroy', $module->ModuleId) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@endif

@endsection

@section('additional_styles')
<style>
    /* Hide default scrolling buttons */
    .table-responsive::-webkit-scrollbar-button {
        display: none;
    }

    /* Custom scrollbar styling */
    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    .table th {
        padding: 12px 16px;
        white-space: nowrap;
    }

    .table th .small-icon {
        font-size: 10px;
        color: #6c757d;
        margin-left: 3px;
    }

    .table td {
        padding: 12px 16px;
        vertical-align: middle;
    }

    /* Action buttons styling */
    .btn-group {
        white-space: nowrap;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    /* Consistent button styles */
    .btn {
        transition: all 0.3s ease;
        font-size: 0.875rem;
    }
    
    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* Header styles */
    h2 {
        color: #2c3e50;
        font-weight: 600;
    }

    .btn-blue {
        background-color: #0d6efd;
        color: white;
    }
    
    .btn-blue:hover {
        background-color: #0b5ed7;
        color: white;
    }
</style>
@endsection     