@extends('layouts.app')

@section('content')
<div class="laboratory-reservation-form">
<div class="container-fluid py-4 px-4">
    <div class="row justify-content-center">
        <div class="col-md-15">
            <div class="card">
                <div class="position-absolute top-0 end-0 mt-4 me-4">
                    <a href="{{ route('laboratory.reservations') }}" class="btn btn-link text-dark">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
                <div class="card-header text-center border-bottom-0 bg-white pt-4">
                    <h4 class="fw-bold mb-2">PHILIPPINE SCIENCE HIGH SCHOOL SYSTEM</h4>

                    <h5 class="mb-0">LABORATORY RESERVATION FORM</h5>
                </div>

                <div class="card-body px-4">
                    <form id="reservationForm" method="POST" action="{{ route('laboratory.reservations.store') }}">
                        @csrf

                    <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                        <label class="form-label mb-0">CAMPUS:</label>
                        <input type="text" class="form-control form-control-sm w-auto" name="campus" required>
                    </div>
                        
                        <!-- Control Number and School Year -->
                        <div class="row mb-3">
                            <div class="col-md-6 offset-md-6">
                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label">Control No.:</label>
                                        <input type="text" class="form-control form-control-sm" name="control_no" readonly>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">SY:</label>
                                        <input type="text" class="form-control form-control-sm" name="school_year" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Basic Information -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Grade Level and Section:</label>
                                @if(Auth::user()->student)
                                    <input type="text" class="form-control" name="grade_section" 
                                        value="{{ Auth::user()->student->grade_level }} - {{ Auth::user()->student->section }}" 
                                        readonly>
                                @elseif(Auth::user()->role === 'Teacher')
                                    <input type="text" class="form-control" name="grade_section" 
                                        placeholder="Not applicable for teacher reservations" 
                                        readonly>
                                    <!-- Add a hidden input to ensure the form submits even without grade_section -->
                                    <input type="hidden" name="grade_section" value="N/A">
                                @else
                                    <input type="text" class="form-control" name="grade_section" required>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Number of Students:</label>
                                <input type="number" class="form-control" name="num_students" 
                                    min="1" 
                                    @if(Auth::user()->role === 'Teacher') value="1" @endif
                                    required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Subject:</label>
                                <input type="text" class="form-control" name="subject" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teacher In-Charge:</label>
                                <select class="form-control" name="teacher_id" required 
                                        {{ Auth::user()->role !== 'Students' ? 'disabled' : '' }}>
                                    @if(Auth::user()->role !== 'Students')
                                        <option value="{{ Auth::user()->employee->EmployeeID }}" selected>
                                            {{ Auth::user()->employee->FirstName }} {{ Auth::user()->employee->LastName }}
                                        </option>
                                    @else
                                        <option value="">Select Teacher</option>
                                    @endif
                                </select>
                            </div>
                        </div>

                        <!-- Reservation Details -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Date/Inclusive Dates:</label>
                                <input type="date" class="form-control" name="reservation_date" 
                                       min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Inclusive Time of Use:</label>
                                <div class="d-flex gap-2">
                                    <input type="time" class="form-control" name="start_time" required>
                                    <span class="align-self-center">to</span>
                                    <input type="time" class="form-control" name="end_time" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Preferred Lab Room:</label>
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
            @if(Auth::user()->student)
                <input type="text" class="form-control" name="requested_by" 
                    value="{{ Auth::user()->student->FirstName }} {{ Auth::user()->student->LastName }}"
                    readonly>
                <input type="hidden" name="requested_by_type" value="student">
            @elseif(Auth::user()->employee)
                <input type="text" class="form-control" name="requested_by" 
                    value="{{ Auth::user()->employee->FirstName }} {{ Auth::user()->employee->LastName }}"
                    readonly>
                <input type="hidden" name="requested_by_type" value="teacher">
            @endif
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

                        <!-- Endorsement -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Endorsed by:</label>
                                <div class="d-flex flex-column">
                                    <input type="text" class="form-control" name="endorsed_by" 
                                        disabled 
                                        placeholder="{{ Auth::user()->role === 'Teacher' ? 'Will be endorsed by Unit Head' : 'Will be endorsed by Subject Teacher' }}">
                                    <small class="text-muted text-center mt-1">
                                        {{ Auth::user()->role === 'Teacher' ? 'Unit Head' : 'Subject Teacher' }}
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Approved by:</label>
                                <div class="d-flex flex-column">
                                    <input type="text" class="form-control" name="approved_by" 
                                        disabled 
                                        placeholder="Will be approved by SRS/SRA">
                                    <small class="text-muted text-center mt-1">SRS/SRA</small>
                                </div>
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
</div>
@endsection

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
<style>
    /* Core Layout */
    .laboratory-reservation-form .container-fluid {
        padding: 1rem !important;
        max-width: 1200px;  /* Adjusted for centered, narrower layout */
        margin: 0 auto;
    }

    .laboratory-reservation-form .card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        padding: 3rem;
    }

    /* Header */
    .laboratory-reservation-form .card-header {
        text-align: center;
        border: none;
        background: none;
        padding: 1rem 0 2rem !important;
    }
        /* Campus field specific styling */
    .laboratory-reservation-form .d-flex.align-items-center.justify-content-center.gap-2.mb-2 {
        margin-top: -1.5rem !important;  /* Pull campus field up */
        margin-bottom: 2rem !important;  /* Add space below campus field */
    }

    /* Campus input width */
    .laboratory-reservation-form input[name="campus"] {
        width: 250px !important;  /* Adjust width as needed */
    }

    /* Close Button */
    .laboratory-reservation-form .card .btn-link {        position: absolute;
        right: 1rem;
        top: 1rem;
    }
    .laboratory-reservation-form .card .btn-link .bi-x-lg {
        font-size: 1.5rem;
        font-weight: 600;
    }

    .laboratory-reservation-form .card .btn-link:hover {
        background-color: rgba(0, 0, 0, 0.05);
        transform: scale(1.1);
    }

    /* Form Layout */
    .laboratory-reservation-form .form-group {
        margin-bottom: 1.5rem !important;
    }

    .laboratory-reservation-form .form-label {
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
</style>
@endsection

@push('styles')
<style>
    /* Form Elements */
    .laboratory-reservation-form.form-control {
        border: 1px solid #dee2e6;
        padding: 0.5rem 0.75rem;
    }

    /* Time Input Group */
    .time-group {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Select2 Customization */
    .select2-container .select2-selection--single {
        height: 38px;
        border-color: #dee2e6;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .container-fluid {
            padding: 1rem !important;
        }
        
        .card {
            padding: 1rem;
        }
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
    allowClear: true,
    ajax: {
        url: '{{ route("laboratory.reservations.getTeachers") }}',
        dataType: 'json',
        delay: 250,
        data: function(params) {
            return {
                search: params.term || ''
            };
        },
        processResults: function(data) {
            return {
                results: data
            };
        },
        cache: true
    }
});

    // Auto-populate student info if user is a student
    @if(Auth::user()->student)
        $.get('{{ route("laboratory.reservations.getStudentInfo") }}', function(response) {
            if (response.success) {
                $('input[name="grade_section"]').val(response.data.grade_level + ' - ' + response.data.section);
                $('input[name="requested_by"]').val(response.data.full_name);
                $('input[name="requested_by_type"]').val('student');
            }
        });
    @endif

    // If user is a teacher, auto-select themselves
    @if(Auth::user()->employee)
        var teacherData = {
            id: '{{ Auth::user()->employee->EmployeeID }}',
            text: '{{ Auth::user()->employee->FirstName }} {{ Auth::user()->employee->LastName }}'
        };
        
        var option = new Option(teacherData.text, teacherData.id, true, true);
        $('select[name="teacher_id"]').append(option).trigger('change');
        $('select[name="teacher_id"]').prop('disabled', true);
        $('input[name="requested_by"]').val(teacherData.text);
        $('input[name="requested_by_type"]').val('teacher');
    @endif



    // Generate control number on page load
    $.get('{{ route("laboratory.reservations.generateControlNo") }}', function(response) {
        $('input[name="control_no"]').val(' ');
    });

    // Set current school year
    const currentDate = new Date();
    const currentYear = currentDate.getFullYear();
    const nextYear = currentYear + 1;
    const schoolYear = currentDate.getMonth() >= 5  // June onwards is new school year
        ? `${currentYear}-${nextYear}`
        : `${currentYear-1}-${currentYear}`;
    $('input[name="school_year"]').val(schoolYear);

    // Form submission
$('#reservationForm').submit(function(e) {
    e.preventDefault();
    
    // Validate required fields
    const requiredFields = ['subject', 'teacher_id', 'reservation_date', 
                          'start_time', 'end_time', 'num_students'];
    
    // Only add grade_section to required fields if user is not a teacher
    @if(Auth::user()->role !== 'Teacher')
        requiredFields.push('grade_section');
    @endif

    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!$(`[name="${field}"]`).val()) {
            isValid = false;
            $(`[name="${field}"]`).addClass('is-invalid');
        } else {
            $(`[name="${field}"]`).removeClass('is-invalid');
        }
    });

    if (!isValid) {
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Please fill in all required fields'
        });
        return;
    }

    // Show loading state while processing
    Swal.fire({
        title: 'Submitting Reservation',
        text: 'Please wait...',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });
        
    $.ajax({
        url: '{{ route("laboratory.reservations.store") }}',
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

    // Initialize datepicker with minimum date
    $('input[name="reservation_date"]').attr('min', new Date().toISOString().split('T')[0]);
});
</script>
@endpush

