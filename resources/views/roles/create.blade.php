@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col">
            <h2>Create New Role</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Roles</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create Role</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('roles.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="RoleName" class="form-label">Role Name</label>
                            <input type="text" 
                                   class="form-control @error('RoleName') is-invalid @enderror" 
                                   id="RoleName" 
                                   name="RoleName" 
                                   value="{{ old('RoleName') }}" 
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
                                      rows="3">{{ old('Description') }}</textarea>
                            @error('Description')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <h4>Initial Permissions</h4>
                            <p class="text-muted">You can modify these permissions after creating the role.</p>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Module</th>
                                            <th class="text-center">View</th>
                                            <th class="text-center">Add</th>
                                            <th class="text-center">Edit</th>
                                            <th class="text-center">Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($modules as $moduleKey => $moduleName)
                                            <tr>
                                                <td>{{ $moduleName }}</td>
                                                <td class="text-center">
                                                    <div class="form-check d-flex justify-content-center">
                                                        <input type="checkbox" 
                                                               class="form-check-input" 
                                                               name="permissions[{{ $moduleKey }}][view]">
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="form-check d-flex justify-content-center">
                                                        <input type="checkbox" 
                                                               class="form-check-input" 
                                                               name="permissions[{{ $moduleKey }}][add]">
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="form-check d-flex justify-content-center">
                                                        <input type="checkbox" 
                                                               class="form-check-input" 
                                                               name="permissions[{{ $moduleKey }}][edit]">
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="form-check d-flex justify-content-center">
                                                        <input type="checkbox" 
                                                               class="form-check-input" 
                                                               name="permissions[{{ $moduleKey }}][delete]">
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create Role</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Tips</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-info-circle text-info"></i>
                            Choose a clear, descriptive name for the role.
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-info-circle text-info"></i>
                            Set initial permissions based on the role's responsibilities.
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-info-circle text-info"></i>
                            You can modify permissions later if needed.
                        </li>
                        <li>
                            <i class="fas fa-info-circle text-info"></i>
                            Consider the principle of least privilege when assigning permissions.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add click handlers for "Select All" functionality if needed
});
</script>
@endpush
@endsection
