@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h2>Edit Role</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Roles</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Role</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('roles.update', $role->RoleId) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="RoleName" class="form-label">Role Name</label>
                            <input type="text" 
                                   class="form-control @error('RoleName') is-invalid @enderror" 
                                   id="RoleName" 
                                   name="RoleName" 
                                   value="{{ old('RoleName', $role->RoleName) }}" 
                                   required>
                            @error('RoleName')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="Description" class="form-label">Description</label>
                            <textarea class="form-control @error('Description') is-invalid @enderror" 
                                      id="Description" 
                                      name="Description" 
                                      rows="3">{{ old('Description', $role->Description) }}</textarea>
                            @error('Description')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="IsActive" class="form-label">Status</label>
                            <select class="form-select @error('IsActive') is-invalid @enderror" 
                                    id="IsActive" 
                                    name="IsActive">
                                <option value="1" {{ old('IsActive', $role->IsActive) == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('IsActive', $role->IsActive) == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('IsActive')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Role</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Role Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Created:</strong><br>
                        {{ $role->DateCreated ? date('M d, Y h:i A', strtotime($role->DateCreated)) : 'N/A' }}
                    </p>
                    <p><strong>Last Modified:</strong><br>
                        {{ $role->DateModified ? date('M d, Y h:i A', strtotime($role->DateModified)) : 'N/A' }}
                    </p>
                    <p><strong>Created By:</strong><br>
                        {{ $role->created_by_user ? $role->created_by_user->Username : 'N/A' }}
                    </p>
                    <p class="mb-0"><strong>Modified By:</strong><br>
                        {{ $role->modified_by_user ? $role->modified_by_user->Username : 'N/A' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
