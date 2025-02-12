@extends('layouts.app')

@section('title', 'Edit Employee')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Employee</h1>
        <a href="{{ route('employees.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('employees.update', $employee->EmployeeID) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="FirstName" class="form-label">First Name</label>
                            <input type="text" 
                                   class="form-control @error('FirstName') is-invalid @enderror" 
                                   id="FirstName" 
                                   name="FirstName" 
                                   value="{{ old('FirstName', $employee->FirstName) }}" 
                                   required>
                            @error('FirstName')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="LastName" class="form-label">Last Name</label>
                            <input type="text" 
                                   class="form-control @error('LastName') is-invalid @enderror" 
                                   id="LastName" 
                                   name="LastName" 
                                   value="{{ old('LastName', $employee->LastName) }}" 
                                   required>
                            @error('LastName')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="Username" class="form-label">Username</label>
                            <input type="text" 
                                   class="form-control @error('Username') is-invalid @enderror" 
                                   id="Username" 
                                   name="Username" 
                                   value="{{ old('Username', $employee->userAccount->Username) }}" 
                                   required>
                            @error('Username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="Email" class="form-label">Email</label>
                            <input type="email" 
                                   class="form-control @error('Email') is-invalid @enderror" 
                                   id="Email" 
                                   name="Email" 
                                   value="{{ old('Email', $employee->Email) }}" 
                                   required>
                            @error('Email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="Password" class="form-label">New Password</label>
                        <input type="password" 
                               class="form-control @error('Password') is-invalid @enderror" 
                               id="Password" 
                               name="Password" 
                               placeholder="Leave blank to keep current password">
                        @error('Password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Only fill this if you want to change the password</div>
                    </div>

                    <div class="col-md-6">
                        <label for="Password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" 
                               class="form-control" 
                               id="Password_confirmation" 
                               name="Password_confirmation" 
                               placeholder="Confirm new password">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="Gender" class="form-label">Gender</label>
                            <select class="form-select @error('Gender') is-invalid @enderror" 
                                    id="Gender" 
                                    name="Gender" 
                                    required>
                                <option value="">Select Gender</option>
                                <option value="Male" {{ old('Gender', $employee->Gender) === 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('Gender', $employee->Gender) === 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('Gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Roles</label>
                            <div class="border rounded p-3 @error('roles') border-danger @enderror">
                                @foreach($roles as $role)
                                    <div class="form-check">
                                        <input type="checkbox" 
                                               class="form-check-input" 
                                               id="role_{{ $role->RoleId }}" 
                                               name="roles[]" 
                                               value="{{ $role->RoleId }}"
                                               {{ in_array($role->RoleId, old('roles', $employee->roles->pluck('RoleId')->toArray())) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="role_{{ $role->RoleId }}">
                                            {{ $role->RoleName }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('roles')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Select at least one role</div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="Address" class="form-label">Address</label>
                    <textarea class="form-control @error('Address') is-invalid @enderror" 
                              id="Address" 
                              name="Address" 
                              rows="3" 
                              required>{{ old('Address', $employee->Address) }}</textarea>
                    @error('Address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Update Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 