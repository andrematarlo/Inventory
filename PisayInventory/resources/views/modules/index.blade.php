@extends('layouts.app')

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
                            <th>Actions</th>
                            @endif
                            <th>Module Name</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($modules as $module)
                        <tr>
                            @if($userPermissions && ($userPermissions->CanEdit || $userPermissions->CanDelete))
                            <td>
                                <div class="btn-group" role="group">
                                    @if($userPermissions->CanEdit)
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModuleModal{{ $module->ModuleId }}">
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
<div class="modal fade" id="addModuleModal" tabindex="-1">
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
<div class="modal fade" id="editModuleModal{{ $module->ModuleId }}" tabindex="-1">
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
<div class="modal fade" id="deleteModuleModal{{ $module->ModuleId }}" tabindex="-1">
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