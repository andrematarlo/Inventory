@extends('layouts.app')

@section('title', 'Student Details')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Student Details</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Students</a></li>
        <li class="breadcrumb-item active">Student Details</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-user me-1"></i>
                Student Information
            </div>
            <div>
                @if($userPermissions && $userPermissions->CanEdit)
                <a href="{{ route('students.edit', $student->StudentID) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-edit"></i> Edit
                </a>
                @endif
                <a href="{{ route('students.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Student Number:</th>
                            <td>{{ $student->StudentNumber }}</td>
                        </tr>
                        <tr>
                            <th>Name:</th>
                            <td>{{ $student->getFullNameWithMiddleAttribute() }}</td>
                        </tr>
                        <tr>
                            <th>Gender:</th>
                            <td>{{ $student->Gender }}</td>
                        </tr>
                        <tr>
                            <th>Contact Number:</th>
                            <td>{{ $student->ContactNumber ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>{{ $student->Email ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="30%">Year Level:</th>
                            <td>{{ $student->YearLevel }}</td>
                        </tr>
                        <tr>
                            <th>Section:</th>
                            <td>{{ $student->Section }}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge bg-{{ $student->Status == 'Active' ? 'success' : 'danger' }}">
                                    {{ $student->Status }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Created At:</th>
                            <td>{{ $student->created_at->format('M d, Y h:i A') }}</td>
                        </tr>
                        <tr>
                            <th>Last Updated:</th>
                            <td>{{ $student->updated_at->format('M d, Y h:i A') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 