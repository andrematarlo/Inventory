@extends('layouts.app')

@section('title', 'Edit Student')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Edit Student</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Students</a></li>
        <li class="breadcrumb-item active">Edit Student</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-user-edit me-1"></i>
            Edit Student Information
        </div>
        <div class="card-body">
            <form action="{{ route('students.update', $student->StudentID) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control @error('StudentNumber') is-invalid @enderror" id="StudentNumber" name="StudentNumber" placeholder="Student Number" value="{{ old('StudentNumber', $student->StudentNumber) }}" required>
                            <label for="StudentNumber">Student Number</label>
                            @error('StudentNumber')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control @error('FirstName') is-invalid @enderror" id="FirstName" name="FirstName" placeholder="First Name" value="{{ old('FirstName', $student->FirstName) }}" required>
                            <label for="FirstName">First Name</label>
                            @error('FirstName')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control @error('MiddleName') is-invalid @enderror" id="MiddleName" name="MiddleName" placeholder="Middle Name" value="{{ old('MiddleName', $student->MiddleName) }}">
                            <label for="MiddleName">Middle Name</label>
                            @error('MiddleName')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control @error('LastName') is-invalid @enderror" id="LastName" name="LastName" placeholder="Last Name" value="{{ old('LastName', $student->LastName) }}" required>
                            <label for="LastName">Last Name</label>
                            @error('LastName')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <select class="form-select @error('Gender') is-invalid @enderror" id="Gender" name="Gender" required>
                                <option value="" disabled>Select Gender</option>
                                <option value="Male" {{ (old('Gender', $student->Gender) == 'Male') ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ (old('Gender', $student->Gender) == 'Female') ? 'selected' : '' }}>Female</option>
                            </select>
                            <label for="Gender">Gender</label>
                            @error('Gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="tel" class="form-control @error('ContactNumber') is-invalid @enderror" id="ContactNumber" name="ContactNumber" placeholder="Contact Number" value="{{ old('ContactNumber', $student->ContactNumber) }}">
                            <label for="ContactNumber">Contact Number</label>
                            @error('ContactNumber')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control @error('Email') is-invalid @enderror" id="Email" name="Email" placeholder="Email Address" value="{{ old('Email', $student->Email) }}">
                            <label for="Email">Email Address</label>
                            @error('Email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <select class="form-select @error('YearLevel') is-invalid @enderror" id="YearLevel" name="YearLevel" required>
                                <option value="" disabled>Select Year Level</option>
                                <option value="Grade 7" {{ (old('YearLevel', $student->YearLevel) == 'Grade 7') ? 'selected' : '' }}>Grade 7</option>
                                <option value="Grade 8" {{ (old('YearLevel', $student->YearLevel) == 'Grade 8') ? 'selected' : '' }}>Grade 8</option>
                                <option value="Grade 9" {{ (old('YearLevel', $student->YearLevel) == 'Grade 9') ? 'selected' : '' }}>Grade 9</option>
                                <option value="Grade 10" {{ (old('YearLevel', $student->YearLevel) == 'Grade 10') ? 'selected' : '' }}>Grade 10</option>
                                <option value="Grade 11" {{ (old('YearLevel', $student->YearLevel) == 'Grade 11') ? 'selected' : '' }}>Grade 11</option>
                                <option value="Grade 12" {{ (old('YearLevel', $student->YearLevel) == 'Grade 12') ? 'selected' : '' }}>Grade 12</option>
                            </select>
                            <label for="YearLevel">Year Level</label>
                            @error('YearLevel')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control @error('Section') is-invalid @enderror" id="Section" name="Section" placeholder="Section" value="{{ old('Section', $student->Section) }}" required>
                            <label for="Section">Section</label>
                            @error('Section')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <select class="form-select @error('Status') is-invalid @enderror" id="Status" name="Status" required>
                                <option value="" disabled>Select Status</option>
                                <option value="Active" {{ (old('Status', $student->Status) == 'Active') ? 'selected' : '' }}>Active</option>
                                <option value="Inactive" {{ (old('Status', $student->Status) == 'Inactive') ? 'selected' : '' }}>Inactive</option>
                            </select>
                            <label for="Status">Status</label>
                            @error('Status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-4 mb-0">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-block">Update Student</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 