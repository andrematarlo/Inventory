@extends('layouts.app')

@section('title', 'Deleted Students')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Deleted Students</h2>
        <div>
            <a href="{{ route('students.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Students
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="deletedStudentsTable">
                    <thead>
                        <tr>
                            @if($userPermissions && $userPermissions->CanEdit)
                            <th style="width: 120px">Actions</th>
                            @endif
                            <th>Student Number</th>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Year & Section</th>
                            <th>Contact</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Deleted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deletedStudents as $student)
                        <tr>
                            @if($userPermissions && $userPermissions->CanEdit)
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#restoreModal{{ $student->student_id }}" title="Restore">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#forceDeleteModal{{ $student->student_id }}" title="Delete Permanently">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                            @endif
                            <td>{{ $student->student_id }}</td>
                            <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                            <td>{{ $student->gender }}</td>
                            <td>{{ $student->grade_level }} {{ $student->section }}</td>
                            <td>{{ $student->contact_number }}</td>
                            <td>{{ $student->email }}</td>
                            <td>{{ $student->status }}</td>
                            <td>{{ $student->deleted_at->format('M d, Y h:i A') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">No deleted students found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Restore Modals -->
@if($userPermissions && $userPermissions->CanEdit)
@foreach($deletedStudents as $student)
<div class="modal fade" id="restoreModal{{ $student->student_id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Restore Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to restore the student "{{ $student->first_name }} {{ $student->last_name }}"?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('students.restore', $student->student_id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">Restore</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="forceDeleteModal{{ $student->student_id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Student Permanently</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to permanently delete the student "{{ $student->first_name }} {{ $student->last_name }}"? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('students.force-delete', $student->student_id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Permanently</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@endif

@endsection

@section('additional_scripts')
<script>
    $(document).ready(function() {
        $('#deletedStudentsTable').DataTable({
            responsive: true,
            order: [[1, 'asc']],
            language: {
                emptyTable: "No deleted students found"
            }
        });
    });
</script>
@endsection