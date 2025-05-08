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
                        <input type="text" class="form-control form-control-sm w-auto" name="campus" value="CVisC" required>
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
                                @php
                                    $student = \App\Models\Student::where('UserAccountID', Auth::id())->first();
                                @endphp
                                @if($student)
                                    <input type="text" class="form-control" name="grade_section" 
                                        value="{{ $student->grade_level }} - {{ $student->section }}" 
                                        readonly>
                                @elseif(Auth::user()->role === 'Teacher')
                                    <input type="text" class="form-control" name="grade_section" 
                                        placeholder="Not applicable for teacher reservations" 
                                        readonly>
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
                                <div class="d-flex gap-2">
                                    <input type="date" class="form-control" name="reservation_date_from" 
                                        min="{{ date('Y-m-d', strtotime('+1 day')) }}" 
                                        required>
                                    <span class="align-self-center">to</span>
                                    <input type="date" class="form-control" name="reservation_date_to" 
                                        min="{{ date('Y-m-d', strtotime('+1 day')) }}" 
                                        required>
                                </div>
                                <small class="text-muted">Select both start and end dates</small>
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
                                    @php
                                        $student = \App\Models\Student::where('UserAccountID', Auth::id())->first();
                                        $employee = DB::table('employee')
                                            ->where('UserAccountID', Auth::id())
                                            ->first();
                                    @endphp
                                    
                                    @if($student)
                                        <input type="text" class="form-control" name="requested_by" 
                                            value="{{ $student->first_name }} {{ $student->last_name }}"
                                            readonly>
                                        <input type="hidden" name="requested_by_type" value="student">
                                    @elseif($employee)
                                        <input type="text" class="form-control" name="requested_by" 
                                            value="{{ $employee->FirstName }} {{ $employee->LastName }}"
                                            readonly>
                                        <input type="hidden" name="requested_by_type" value="teacher">
                                    @else
                                        <input type="text" class="form-control" name="requested_by" required>
                                        <input type="hidden" name="requested_by_type" value="other">
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
    // Auto-generate control number on page load
    $.get('{{ route("laboratory.reservations.generateControlNo") }}', function(response) {
        $('input[name="control_no"]').val(response.control_no);
    });

    // Auto-populate current school year
    const currentDate = new Date();
    const currentYear = currentDate.getFullYear();
    const nextYear = currentYear + 1;
    const schoolYear = currentDate.getMonth() >= 5  // If current month is June or later
        ? `${currentYear}-${nextYear}`
        : `${currentYear-1}-${currentYear}`;
    $('input[name="school_year"]').val(schoolYear);

    // Auto-populate campus and grade-section
    $.get('{{ route("laboratory.reservations.getStudentInfo") }}', function(response) {
        if (response.success) {
            // Auto-populate campus
            $('input[name="campus"]').val('CVisC');
            
            // Auto-populate grade and section for students
            if (response.data.isStudent) {
                const gradeSection = `${response.data.grade_level || ''} - ${response.data.section || ''}`.trim();
                $('input[name="grade_section"]').val(gradeSection);
            } else if (response.data.isTeacher) {
                $('input[name="grade_section"]')
                    .val('N/A')
                    .prop('readonly', true)
                    .attr('placeholder', 'Not applicable for teacher reservations');
            }
        }
    }).fail(function(xhr) {
        console.error('Error fetching user info:', xhr.responseText);
    });

    // Initialize Select2 for teacher selection with search
    $('select[name="teacher_id"]').select2({
        placeholder: 'Select Teacher',
        allowClear: true,
        ajax: {
            url: '/inventory/laboratory/reservations/teachers',
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

    // Initialize Select2 for laboratory selection
    $('select[name="laboratory_id"]').select2({
        placeholder: 'Select Laboratory'
    });

    // Set minimum date for reservation
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    $('input[name="reservation_date_from"], input[name="reservation_date_to"]').attr('min', tomorrow.toISOString().split('T')[0]);

    // When reservation_date_from changes, update reservation_date_to if needed
    $('input[name="reservation_date_from"]').on('change', function() {
        var dateFrom = $(this).val();
        var dateTo = $('input[name="reservation_date_to"]').val();
        
        // If to date is empty or before from date, set it to from date
        if (!dateTo || new Date(dateTo) < new Date(dateFrom)) {
            $('input[name="reservation_date_to"]').val(dateFrom);
        }
    });

    // When reservation_date_to changes, validate it's not before from date
    $('input[name="reservation_date_to"]').on('change', function() {
        var dateFrom = $('input[name="reservation_date_from"]').val();
        var dateTo = $(this).val();
        
        if (dateTo && new Date(dateTo) < new Date(dateFrom)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Date Range',
                text: 'End date cannot be before start date'
            });
            $(this).val(dateFrom);
        }
    });

    // Set default time range (8 AM to 5 PM)
    $('input[name="start_time"]').val('08:00');
    $('input[name="end_time"]').val('17:00');

    // Validate time inputs
    $('input[name="start_time"], input[name="end_time"]').change(function() {
        const startTime = $('input[name="start_time"]').val();
        const endTime = $('input[name="end_time"]').val();
        
        if (startTime && endTime) {
            const start = new Date(`2000-01-01T${startTime}`);
            const end = new Date(`2000-01-01T${endTime}`);
            
            // Check if end time is before start time
            if (end <= start) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Time',
                    text: 'End time must be after start time'
                });
                $(this).val('');
                return;
            }

            // Check if reservation is within operating hours (8 AM to 5 PM)
            const openingTime = new Date(`2000-01-01T08:00`);
            const closingTime = new Date(`2000-01-01T17:00`);
            
            if (start < openingTime || end > closingTime) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Time',
                    text: 'Reservations must be between 8:00 AM and 5:00 PM'
                });
                $(this).val('');
            }
        }
    });

    // Before submitting the form
    $('#reservationForm').off('submit').on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission immediately!
        // Cutoff: block submission after 3pm PH time (UTC+8)
        const now = new Date();
        // Convert to PH time (UTC+8)
        const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
        const phTime = new Date(utc + (3600000 * 8));
        if (phTime.getHours() > 15 || (phTime.getHours() === 15 && phTime.getMinutes() > 0)) {
            Swal.fire({
                icon: 'warning',
                title: 'Cutoff Time Reached',
                text: 'Reservation requests can only be filed until 3:00 PM (PH Time). Please try again tomorrow.'
            });
            return;
        }
        
        var dateFrom = $('input[name="reservation_date_from"]').val();
        var dateTo = $('input[name="reservation_date_to"]').val();
        
        // Log the form data before submission
        console.log('Form data before submission:', {
            reservation_date_from: dateFrom,
            reservation_date_to: dateTo
        });

        // Validate required fields
        const requiredFields = ['campus', 'school_year', 'subject', 'teacher_id', 
                              'reservation_date_from', 'reservation_date_to', 'start_time', 'end_time', 
                              'laboratory_id', 'num_students'];
        
        // Check user role and add grade_section conditionally
        const userRole = '{{ Auth::user()->role }}';
        if (userRole !== 'Teacher') {
            requiredFields.push('grade_section');
        }

        let isValid = true;
        let firstInvalidField = null;
        
        requiredFields.forEach(field => {
            const element = $(`[name="${field}"]`);
            if (!element.val()) {
                isValid = false;
                element.addClass('is-invalid');
                if (!firstInvalidField) {
                    firstInvalidField = element;
                }
            } else {
                element.removeClass('is-invalid');
            }
        });

        if (!isValid) {
            firstInvalidField.focus();
            Swal.fire({
                icon: 'error',
                title: 'Required Fields Missing',
                text: 'Please fill in all required fields'
            });
            return;
        }

        // Show loading state
        Swal.fire({
            title: 'Submitting Reservation',
            text: 'Please wait...',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        // Submit form
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Your reservation has been submitted successfully.',
                        showConfirmButton: true
                    }).then(() => {
                        window.location.href = '{{ route("laboratory.reservations") }}';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Something went wrong.'
                    });
                }
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

    // Check for conflicting reservations when laboratory and date/time are selected
    function checkConflicts() {
        const labId = $('select[name="laboratory_id"]').val();
        const reservationDate = $('input[name="reservation_date_from"]').val() + ' to ' + $('input[name="reservation_date_to"]').val();
        const startTime = $('input[name="start_time"]').val();
        const endTime = $('input[name="end_time"]').val();

        if (labId && reservationDate && startTime && endTime) {
            $.get('{{ route("laboratory.reservations.checkConflicts") }}', {
                laboratory_id: labId,
                reservation_date: reservationDate,
                start_time: startTime,
                end_time: endTime
            }, function(response) {
                if (response.conflict) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Time Slot Conflict',
                        text: 'This time slot is already reserved. Please choose a different time.'
                    });
                    $('input[name="start_time"]').val('');
                    $('input[name="end_time"]').val('');
                }
            });
        }
    }

    // Check conflicts when relevant fields change
    $('select[name="laboratory_id"], input[name="reservation_date_from"], input[name="reservation_date_to"], input[name="start_time"], input[name="end_time"]')
        .change(checkConflicts);
});
</script>
@endpush

