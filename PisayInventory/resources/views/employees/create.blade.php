@extends('layouts.app')

@section('title', 'Add Employee')

@section('content')
<div class="container-fluid px-4">
    <div class="card mt-4">
        <div class="card-header">
            <h4 class="mb-0">Add New Employee</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('employees.store') }}">
                @csrf
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="FirstName" class="form-label">First Name</label>
                        <input type="text" class="form-control @error('FirstName') is-invalid @enderror" 
                               id="FirstName" name="FirstName" value="{{ old('FirstName') }}" required>
                        @error('FirstName')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="LastName" class="form-label">Last Name</label>
                        <input type="text" class="form-control @error('LastName') is-invalid @enderror" 
                               id="LastName" name="LastName" value="{{ old('LastName') }}" required>
                        @error('LastName')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="Email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('Email') is-invalid @enderror" 
                               id="Email" name="Email" value="{{ old('Email') }}" required>
                        @error('Email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="Gender" class="form-label">Gender</label>
                        <select class="form-select @error('Gender') is-invalid @enderror" 
                                id="Gender" name="Gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male" {{ old('Gender') == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('Gender') == 'Female' ? 'selected' : '' }}>Female</option>
                        </select>
                        @error('Gender')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="Address" class="form-label">Address</label>
                    <textarea class="form-control @error('Address') is-invalid @enderror" 
                              id="Address" name="Address" rows="3" required>{{ old('Address') }}</textarea>
                    @error('Address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="Username" class="form-label">Username</label>
                        <input type="text" class="form-control @error('Username') is-invalid @enderror" 
                               id="Username" name="Username" value="{{ old('Username') }}" required>
                        @error('Username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="Role" class="form-label">Role</label>
                        <select name="Role" id="Role" class="form-control @error('Role') is-invalid @enderror" required>
                            <option value="">Select Role</option>
                            @foreach($roles as $value => $label)
                                <option value="{{ $value }}" {{ old('Role') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('Role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="Password" class="form-label">Password</label>
                        <input type="password" class="form-control @error('Password') is-invalid @enderror" 
                               id="Password" name="Password" required>
                        @error('Password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="Password_confirmation" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" 
                               id="Password_confirmation" name="Password_confirmation" required>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('employees.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Add Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 