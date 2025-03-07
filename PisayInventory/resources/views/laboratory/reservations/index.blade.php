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
                    Add Reservation
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
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Reservation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createForm">
                    @csrf
                    <div class="mb-3">
                        <label for="laboratory_id" class="form-label">Laboratory</label>
                        <select name="laboratory_id" id="laboratory_id" class="form-control" required>
                            <option value="">Select Laboratory</option>
                            @foreach($laboratories as $lab)
                                <option value="{{ $lab->laboratory_id }}">{{ $lab->laboratory_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="reservation_date" class="form-label">Reservation Date</label>
                        <input type="date" 
                               class="form-control" 
                               id="reservation_date" 
                               name="reservation_date"
                               min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                               required>
                    </div>

                    <div class="mb-3">
                        <label for="start_time" class="form-label">Start Time</label>
                        <input type="time" 
                               class="form-control" 
                               id="start_time" 
                               name="start_time"
                               required>
                    </div>

                    <div class="mb-3">
                        <label for="end_time" class="form-label">End Time</label>
                        <input type="time" 
                               class="form-control" 
                               id="end_time" 
                               name="end_time"
                               required>
                    </div>

                    <div class="mb-3">
                        <label for="purpose" class="form-label">Purpose</label>
                        <textarea class="form-control" 
                                 id="purpose" 
                                 name="purpose" 
                                 rows="3" 
                                 required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="num_students" class="form-label">Number of Students</label>
                        <input type="number" 
                               class="form-control" 
                               id="num_students" 
                               name="num_students"
                               min="1">
                    </div>

                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remarks</label>
                        <textarea class="form-control" 
                                 id="remarks" 
                                 name="remarks" 
                                 rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveCreate">Create Reservation</button>
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