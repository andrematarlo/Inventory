@extends('layouts.app')

@section('title', 'Edit Student')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mt-4">Edit Student</h1>
        <a href="{{ route('students.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('students.update', $student->student_id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control @error('student_id') is-invalid @enderror" id="student_id" name="student_id" placeholder="Student ID" value="{{ old('student_id', $student->student_id) }}" required>
                            <label for="student_id">Student ID</label>
                            @error('student_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" placeholder="First Name" value="{{ old('first_name', $student->first_name) }}" required>
                            <label for="first_name">First Name</label>
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control @error('middle_name') is-invalid @enderror" id="middle_name" name="middle_name" placeholder="Middle Name" value="{{ old('middle_name', $student->middle_name) }}">
                            <label for="middle_name">Middle Name</label>
                            @error('middle_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" placeholder="Last Name" value="{{ old('last_name', $student->last_name) }}" required>
                            <label for="last_name">Last Name</label>
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                                <option value="" disabled>Select Gender</option>
                                <option value="Male" {{ (old('gender', $student->gender) == 'Male') ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ (old('gender', $student->gender) == 'Female') ? 'selected' : '' }}>Female</option>
                                <option value="Other" {{ (old('gender', $student->gender) == 'Other') ? 'selected' : '' }}>Other</option>
                            </select>
                            <label for="gender">Gender</label>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="tel" class="form-control @error('contact_number') is-invalid @enderror" id="contact_number" name="contact_number" placeholder="Contact Number" value="{{ old('contact_number', $student->contact_number) }}">
                            <label for="contact_number">Contact Number</label>
                            @error('contact_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="Email Address" value="{{ old('email', $student->email) }}">
                            <label for="email">Email Address</label>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <select class="form-select @error('grade_level') is-invalid @enderror" id="grade_level" name="grade_level" required>
                                <option value="" disabled>Select Year Level</option>
                                <option value="Grade 7" {{ (old('grade_level', $student->grade_level) == 'Grade 7') ? 'selected' : '' }}>Grade 7</option>
                                <option value="Grade 8" {{ (old('grade_level', $student->grade_level) == 'Grade 8') ? 'selected' : '' }}>Grade 8</option>
                                <option value="Grade 9" {{ (old('grade_level', $student->grade_level) == 'Grade 9') ? 'selected' : '' }}>Grade 9</option>
                                <option value="Grade 10" {{ (old('grade_level', $student->grade_level) == 'Grade 10') ? 'selected' : '' }}>Grade 10</option>
                                <option value="Grade 11" {{ (old('grade_level', $student->grade_level) == 'Grade 11') ? 'selected' : '' }}>Grade 11</option>
                                <option value="Grade 12" {{ (old('grade_level', $student->grade_level) == 'Grade 12') ? 'selected' : '' }}>Grade 12</option>
                            </select>
                            <label for="grade_level">Year Level</label>
                            @error('grade_level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control @error('section') is-invalid @enderror" id="section" name="section" placeholder="Section" value="{{ old('section', $student->section) }}" required>
                            <label for="section">Section</label>
                            @error('section')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="" disabled>Select Status</option>
                                <option value="Active" {{ (old('status', $student->status) == 'Active') ? 'selected' : '' }}>Active</option>
                                <option value="Inactive" {{ (old('status', $student->status) == 'Inactive') ? 'selected' : '' }}>Inactive</option>
                            </select>
                            <label for="status">Status</label>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-4 mb-0">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Update Student</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 