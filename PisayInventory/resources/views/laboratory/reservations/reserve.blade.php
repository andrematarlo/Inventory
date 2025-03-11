@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header text-center border-bottom-0 bg-white pt-4">
                    <h4 class="fw-bold mb-2">PHILIPPINE SCIENCE HIGH SCHOOL SYSTEM</h4>
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                        <label class="form-label mb-0">CAMPUS:</label>
                        <input type="text" class="form-control form-control-sm w-auto" name="campus" required>
                    </div>
                    <h5 class="mb-0">LABORATORY RESERVATION FORM</h5>
                </div>

                <div class="card-body px-4">
                    <form id="reservationForm" method="POST" action="{{ route('laboratory.reserve.store') }}">
                        @csrf
                        
                        <!-- Control Number and School Year -->
                        <div class="row mb-3">
                            <div class="col-md-6 offset-md-6">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label">Control No.:</label>
                                        <input type="text" class="form-control form-control-sm" name="control_no" readonly>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">School Year:</label>
                                        <input type="text" class="form-control form-control-sm" name="school_year" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Basic Information -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Grade Level and Section:</label>
                                <input type="text" class="form-control" name="grade_section" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Number of Students:</label>
                                <input type="number" class="form-control" name="num_students" min="1" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Subject:</label>
                                <input type="text" class="form-control" name="subject" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teacher:</label>
                                <select class="form-control" name="teacher_id" required>
                                    <option value="">Select Teacher</option>
                                </select>
                            </div>
                        </div>

                        <!-- Reservation Details -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Date:</label>
                                <input type="date" class="form-control" name="reservation_date" 
                                       min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Start Time:</label>
                                <input type="time" class="form-control" name="start_time" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">End Time:</label>
                                <input type="time" class="form-control" name="end_time" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Preferred Laboratory:</label>
                            <select class="form-control" name="laboratory_id" required>
                                <option value="">Select Laboratory</option>
                                @foreach($laboratories as $lab)
                                    <option value="{{ $lab->laboratory_id }}">{{ $lab->laboratory_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Requestor Information -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Requested by:</label>
                                <div class="d-flex flex-column">
                                    <input type="text" class="form-control" name="requested_by" 
                                           value="{{ Auth::user()->employee->FirstName ?? Auth::user()->student->FirstName }} {{ Auth::user()->employee->LastName ?? Auth::user()->student->LastName }}" 
                                           readonly>
                                    <small class="text-muted text-center mt-1">Teacher/Student</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date Requested:</label>
                                <input type="date" class="form-control" name="date_requested" 
                                       value="{{ date('Y-m-d') }}" readonly>
                            </div>
                        </div>

                        <!-- Group Members -->
                        <div class="mb-4">
                            <label class="form-label">If user of the lab is a group, list down the names of students:</label>
                            <div id="group_members">
                                @for($i = 1; $i <= 5; $i++)
                                    <div class="input-group mb-2">
                                        <span class="input-group-text">{{ $i }}.</span>
                                        <input type="text" class="form-control" name="group_members[]">
                                    </div>
                                @endfor
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-end mb-3">
                            <button type="submit" class="btn btn-primary">Submit Reservation</button>
                        </div>
                    </form>

                    <!-- Form Number -->
                    <div class="text-start">
                        <small class="text-muted">PSHS-00-F-CID-05-Ver02-Rev1-10/18/20</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.card-header {
    background-color: white;
    border-bottom: 2px solid #dee2e6;
}

.form-label {
    font-weight: 500;
    margin-bottom: 0.3rem;
}

.input-group-text {
    background-color: #f8f9fa;
}

.select2-container .select2-selection--single {
    height: 38px;
    border-color: #ced4da;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 for teacher selection
    $('select[name="teacher_id"]').select2({
        placeholder: 'Select Teacher',
        ajax: {
            url: '{{ route("laboratory.reservations.getTeachers") }}',
            dataType: 'json',
            delay: 250,
            processResults: function (data) {
                return {
                    results: data.map(function(teacher) {
                        return {
                            id: teacher.EmployeeID,
                            text: teacher.full_name
                        };
                    })
                };
            },
            cache: true
        }
    });

    // Generate control number on page load
    $.get('{{ route("laboratory.reservations.generateControlNo") }}', function(response) {
        $('input[name="control_no"]').val(response.control_no);
    });

    // Form submission
    $('#reservationForm').submit(function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '{{ route("laboratory.reserve.store") }}',
        type: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Your reservation has been submitted successfully.'
            }).then(() => {
                window.location.href = '{{ route("laboratory.reservations") }}';
            });
        },
        error: function(xhr) {
                let errorMessage = 'Something went wrong.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMessage = Object.values(xhr.responseJSON.errors).flat().join('\n');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: errorMessage
                });
            }
        });
    });

    // Validate time inputs
    $('input[name="start_time"], input[name="end_time"]').change(function() {
        const startTime = $('input[name="start_time"]').val();
        const endTime = $('input[name="end_time"]').val();
        
        if (startTime && endTime && endTime <= startTime) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Time',
                text: 'End time must be after start time'
            });
            $('input[name="end_time"]').val('');
        }
    });
});
</script>
@endpush