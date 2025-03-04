@extends('layouts.app')

@section('title', 'Edit Role')

@section('content')
<div class="container-fluid px-4">
    <h2 class="mt-4">Edit Role</h2>

    <div class="card mb-4">
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
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="Description" class="form-label">Description</label>
                    <textarea class="form-control" 
                              id="Description" 
                              name="Description" 
                              rows="3">{{ old('Description', $role->Description) }}</textarea>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Module Permissions</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Module</th>
                                        <th class="text-center">View</th>
                                        <th class="text-center">Add</th>
                                        <th class="text-center">Edit</th>
                                        <th class="text-center">Delete</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($role->policies as $policy)
                                    <tr>
                                        <td>{{ $policy->Module }}</td>
                                        <td class="text-center">
                                            <div class="form-check d-flex justify-content-center">
                                                <input type="checkbox" 
                                                       class="form-check-input" 
                                                       name="policies[{{ $policy->RolePolicyId }}][view]" 
                                                       {{ $policy->CanView ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check d-flex justify-content-center">
                                                <input type="checkbox" 
                                                       class="form-check-input" 
                                                       name="policies[{{ $policy->RolePolicyId }}][add]" 
                                                       {{ $policy->CanAdd ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check d-flex justify-content-center">
                                                <input type="checkbox" 
                                                       class="form-check-input" 
                                                       name="policies[{{ $policy->RolePolicyId }}][edit]" 
                                                       {{ $policy->CanEdit ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="form-check d-flex justify-content-center">
                                                <input type="checkbox" 
                                                       class="form-check-input" 
                                                       name="policies[{{ $policy->RolePolicyId }}][delete]" 
                                                       {{ $policy->CanDelete ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Role</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
