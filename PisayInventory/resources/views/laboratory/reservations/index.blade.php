@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h2>Laboratory Reservations</h2>
        </div>
        <div class="col text-end">
            @if($userPermissions->CanAdd)
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                    <i class="bi bi-plus"></i> Add Reservation
                </button>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    Show 
                    <select class="form-select form-select-sm d-inline-block w-auto">
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    entries
                </div>
                <div class="d-flex gap-3 align-items-center">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="showDeleted">
                        <label class="form-check-label" for="showDeleted">Show Deleted Records</label>
                    </div>
                    <div class="search-box">
                        <input type="text" class="form-control" placeholder="Search...">
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 100px">Actions</th>
                            <th>Status</th>
                            <th>Reservation ID</th>
                            <th>Laboratory</th>
                            <th>Reserved By</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Purpose</th>
                        </tr>
                    </thead>
                    <tbody id="activeRecords">
                        @include('laboratory.reservations.table-rows', ['reservations' => $activeReservations])
                    </tbody>
                    <tbody id="deletedRecords" style="display: none;">
                        @include('laboratory.reservations.table-rows', ['reservations' => $deletedReservations, 'isDeleted' => true])
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing <span id="recordsShowing">1 to {{ $activeReservations->count() }}</span> of {{ $activeReservations->total() }} entries
                </div>
                {{ $activeReservations->links() }}
            </div>
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reservation Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Reservation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Form fields will be added here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveEdit">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg"> <!-- Changed from modal-lg to modal-xl -->
        <div class="modal-content">
            <div class="modal-header text-center d-block border-bottom-0">
                <div class="mt-3 mb-4 text-center"> <!-- Increased margin-bottom -->
                    <h5 class="fw-bold mb-2">PHILIPPINE SCIENCE HIGH SCHOOL SYSTEM</h5> <!-- Added margin-bottom -->
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <label class="form-label mb-0">CAMPUS:</label>
                        <input type="text" class="form-control form-control-sm w-auto" name="campus" required>
                    </div>
                </div>
                <h5 class="modal-title mb-3">LABORATORY RESERVATION FORM</h5> <!-- Added margin-bottom -->
                <button type="button" class="btn-close position-absolute top-0 end-0 mt-2 me-2" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-5"> <!-- Added horizontal padding -->
                <form id="createForm">
                    @csrf
                    <div class="mb-3">
                        <div class="d-flex justify-content-end gap-4"> <!-- Aligned to right with gap -->
                            <div class="d-flex align-items-center gap-2 mb-4"> <!-- Inline layout -->
                                <label for="control_no" class="form-label mb-0">Control No.:</label>
                                <input type="text" class="form-control form-control-sm w-auto" style="width: 85px !important;" id="control_no" name="control_no" readonly>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-4"> <!-- Inline layout -->
                                <label for="school_year" class="form-label mb-0">SY:</label>
                                <input type="text" class="form-control form-control-sm w-auto" style="width: 100px !important;" id="school_year" name="school_year" required>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="grade_section" class="form-label">Grade Level and Section</label>
                            <input type="text" class="form-control" id="grade_section" name="grade_section" required>
                        </div>
                        <div class="col-md-6">
                            <label for="num_students" class="form-label">Number of Students</label>
                            <input type="number" class="form-control" id="num_students" name="num_students" min="1" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="col-md-6">
                            <label for="teacher_in_charge" class="form-label">Teacher In-Charge</label>
                            <input type="text" class="form-control" id="teacher_in_charge" name="teacher_in_charge" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="reservation_date" class="form-label">Date/Inclusive Dates</label>
                            <input type="date" class="form-control" id="reservation_date" name="reservation_date" 
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label for="start_time" class="form-label">Start Time</label>
                            <input type="time" class="form-control" id="start_time" name="start_time" required>
                        </div>
                        <div class="col-md-3">
                            <label for="end_time" class="form-label">End Time</label>
                            <input type="time" class="form-control" id="end_time" name="end_time" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="laboratory_id" class="form-label">Preferred Lab Room</label>
                        <select name="laboratory_id" id="laboratory_id" class="form-control" required>
                            <option value="">Select Laboratory</option>
                            @foreach($laboratories as $lab)
                                <option value="{{ $lab->laboratory_id }}">{{ $lab->laboratory_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="requested_by" class="form-label">Requested by</label>
                            <div class="d-flex flex-column">
                                <input type="text" class="form-control" id="requested_by" name="requested_by" required>
                                <small class="text-muted text-center mt-1">Teacher/Student</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="date_requested" class="form-label">Date Requested</label>
                            <input type="date" class="form-control" id="date_requested" name="date_requested" 
                                   value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">If user of the lab is a group, list down the names of students.</label>
                        <div id="group_members">
                            @for($i = 1; $i <= 5; $i++)
                                <div class="input-group mb-2">
                                    <span class="input-group-text">{{ $i }}.</span>
                                    <input type="text" class="form-control" name="group_members[]">
                                </div>
                            @endfor
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="endorsed_by" class="form-label">Endorsed by</label>
                            <div class="d-flex flex-column">
                                <input type="text" class="form-control" id="endorsed_by" name="endorsed_by">
                                <small class="text-muted text-center mt-1">Subject Teacher/Unit Head</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="approved_by" class="form-label">Approved by</label>
                            <div class="d-flex flex-column">
                                <input type="text" class="form-control" id="approved_by" name="approved_by">
                                <small class="text-muted text-center mt-1">SRS/SRA</small>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer flex-column px-5">
                <div class="d-flex w-100 justify-content-end"> <!-- Changed to end alignment -->
                    <button type="button" class="btn btn-primary" id="saveCreate">Submit Reservation</button>
                </div>
                <div class="w-100 text-start">
                    <small class="text-muted">PSHS-00-F-CID-05-Ver02-Rev1-10/18/20</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .btn-group {
        display: flex;
        gap: 5px;
    }
    .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        border: none;
        transition: all 0.2s;
    }
    .btn-icon:hover {
        opacity: 0.8;
        transform: translateY(-1px);
    }
    .badge {
        font-size: 0.875rem;
        padding: 0.375rem 0.65rem;
    }
    .search-box {
        width: 250px;
    }
    .form-select {
        min-width: 70px;
    }
    #createModal .modal-header {
        border-bottom: 2px solid #dee2e6;
        padding-bottom: 0;
    }
    
    #createModal .modal-title {
        width: 100%;
        text-align: center;
    }
    
    #createModal .form-control-sm {
        height: 30px;
        padding: 0.25rem 0.5rem;
    }
    #createModal .modal-content {
        padding: 1rem;
    }

    #createModal .form-label {
        margin-bottom: 0.25rem;
    }

    #createModal small.text-muted {
        font-size: 0.8rem;
    }
    #createModal .modal-body,
    #createModal .modal-footer {
        padding-left: 3rem !important;  /* Increased padding */
        padding-right: 3rem !important; /* Increased padding */
    }
        /* Adjust close button position */
        #createModal .btn-close {
        padding: 1rem;
    }

</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Toggle Deleted Records
    $('#showDeleted').change(function() {
        if ($(this).is(':checked')) {
            $('#activeRecords').hide();
            $('#deletedRecords').show();
            $('#recordsShowing').text('1 to {{ $deletedReservations->count() }} of {{ $deletedReservations->total() }}');
            $('.pagination').hide(); // Hide active records pagination
        } else {
            $('#deletedRecords').hide();
            $('#activeRecords').show();
            $('#recordsShowing').text('1 to {{ $activeReservations->count() }} of {{ $activeReservations->total() }}');
            $('.pagination').show(); // Show active records pagination
        }
    });

    // View Reservation
    $('.view-reservation').click(function() {
        const id = $(this).data('id');
        $.get("{{ route('laboratory.reservations.show', '_id_') }}".replace('_id_', id), function(data) {
            $('#viewModal .modal-body').html(data);
            $('#viewModal').modal('show');
        });
    });

    // Edit Reservation
    $('.edit-reservation').click(function() {
        const id = $(this).data('id');
        $.get("{{ route('laboratory.reservations.edit', '_id_') }}".replace('_id_', id), function(data) {
            $('#editModal .modal-body').html(data);
            $('#editModal').modal('show');
        });
    });

    // Save Edit
    $('#saveEdit').click(function() {
        const form = $('#editModal form');
        const id = form.data('id');
        
        $.ajax({
            url: "{{ route('laboratory.reservations.update', '_id_') }}".replace('_id_', id),
            type: 'PUT',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: form.serialize(),
            success: function(response) {
                $('#editModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Something went wrong.'
                });
            }
        });
    });

    // Delete Reservation
    $('.delete-reservation').click(function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "This reservation will be moved to trash. You can restore it later.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('laboratory.reservations.destroy', '_id_') }}".replace('_id_', id),
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire(
                            'Deleted!',
                            'The reservation has been moved to trash.',
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            xhr.responseJSON?.message || 'Something went wrong.',
                            'error'
                        );
                    }
                });
            }
        });
    });

    // Restore Reservation
    $('.restore-reservation').click(function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: 'Restore Reservation?',
            text: "This will restore the reservation from trash.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, restore it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('laboratory.reservations.restore', '_id_') }}".replace('_id_', id),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire(
                            'Restored!',
                            'The reservation has been restored.',
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Error!',
                            xhr.responseJSON?.message || 'Something went wrong.',
                            'error'
                        );
                    }
                });
            }
        });
    });

    // Create Reservation
    $('#saveCreate').click(function() {
        const form = $('#createForm');
        
        $.ajax({
            url: "{{ route('laboratory.reservations.store') }}",
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: form.serialize(),
            success: function(response) {
                $('#createModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Reservation created successfully.'
                }).then(() => {
                    location.reload();
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

    // Reset form when modal is closed
    $('#createModal').on('hidden.bs.modal', function () {
        $('#createForm')[0].reset();
    });

    // Validate end time is after start time
    $('#start_time, #end_time').change(function() {
        const startTime = $('#start_time').val();
        const endTime = $('#end_time').val();
        
        if (startTime && endTime && endTime <= startTime) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Time',
                text: 'End time must be after start time'
            });
            $('#end_time').val('');
        }
    });
});
</script>
@endpush 