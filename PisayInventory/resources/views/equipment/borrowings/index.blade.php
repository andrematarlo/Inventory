@extends('layouts.app')

@section('title', 'Equipment Borrowings')

@php
    use Carbon\Carbon;
@endphp

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Equipment Borrowings</h2>
        @if($userPermissions && $userPermissions->CanAdd)
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="bi bi-plus-lg"></i> Borrow Equipment
        </button>
        @endif
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
                            <th>Borrowing ID</th>
                            <th>Equipment</th>
                            <th>Borrower</th>
                            <th>Borrow Date</th>
                            <th>Due Date</th>
                            <th>Return Date</th>
                            <th>Purpose</th>
                        </tr>
                    </thead>
                    <tbody id="activeRecords">
                        @include('equipment.borrowings.table-rows', ['borrowings' => $activeBorrowings])
                    </tbody>
                    <tbody id="deletedRecords" style="display: none;">
                        @include('equipment.borrowings.table-rows', ['borrowings' => $deletedBorrowings, 'isDeleted' => true])
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing <span id="recordsShowing">1 to {{ $activeBorrowings->count() }}</span> of {{ $activeBorrowings->total() }} entries
                </div>
                {{ $activeBorrowings->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Borrow Equipment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Equipment</label>
                        <select class="form-select" name="equipment_id" required>
                            <option value="">Select Equipment</option>
                            @foreach($equipment as $item)
                                <option value="{{ $item->equipment_id }}">
                                    {{ $item->equipment_name }}
                                    @if($item->serial_number || $item->model_number)
                                        ({{ $item->serial_number ?: $item->model_number }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Borrow Date</label>
                        <input type="date" class="form-control" name="borrow_date" id="borrow_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Expected Return Date</label>
                        <input type="date" class="form-control" name="expected_return_date" id="due_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Purpose</label>
                        <textarea class="form-control" name="purpose" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Condition on Borrow</label>
                        <select class="form-select" name="condition_on_borrow" required>
                            <option value="">Select Condition</option>
                            <option value="Good">Good</option>
                            <option value="Fair">Fair</option>
                            <option value="Poor">Poor</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" name="remarks" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveCreate">Borrow Equipment</button>
            </div>
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Borrowing Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Content will be loaded dynamically -->
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
.search-box {
    width: 250px;
}
.form-select {
    min-width: 70px;
}
</style>
@endpush

@push('scripts')
<script>
console.log('Available routes:', {
    show: "{{ route('equipment.borrowings.show', ['borrowing' => '_id_']) }}",
    return: "{{ route('equipment.borrowings.return', '_id_') }}",
    restore: "{{ route('equipment.borrowings.restore', '_id_') }}"
});

$(document).ready(function() {
    // Toggle Deleted Records
    $('#showDeleted').change(function() {
        if ($(this).is(':checked')) {
            $('#activeRecords').hide();
            $('#deletedRecords').show();
            $('#recordsShowing').text('1 to {{ $deletedBorrowings->count() }} of {{ $deletedBorrowings->total() }}');
        } else {
            $('#deletedRecords').hide();
            $('#activeRecords').show();
            $('#recordsShowing').text('1 to {{ $activeBorrowings->count() }} of {{ $activeBorrowings->total() }}');
        }
    });

    // Set default borrow date to today and initialize dates
    const today = new Date().toISOString().split('T')[0];
    $('#borrow_date').val(today).attr('min', today);
    $('#due_date').attr('min', today);

    // Format dates for display
    function formatDate(date) {
        return date ? new Date(date).toISOString().split('T')[0] : '';
    }

    // Create Borrowing
    $('#saveCreate').click(function() {
        const form = $('#createForm');
        const borrowDate = $('#borrow_date').val();
        const dueDate = $('#due_date').val();

        // Validate dates
        if (new Date(dueDate) <= new Date(borrowDate)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Date',
                text: 'Due date must be after borrow date'
            });
            return;
        }

        // Add loading state
        const button = $(this);
        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');

        const formData = new FormData(form[0]);
        formData.set('borrow_date', borrowDate);
        formData.set('expected_return_date', dueDate);

        $.ajax({
            url: "{{ route('equipment.borrowings.store') }}",
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: Object.fromEntries(formData),
            success: function(response) {
                $('#createModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message
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
            },
            complete: function() {
                // Reset button state
                button.prop('disabled', false).html('Borrow Equipment');
            }
        });
    });

    // View Borrowing
    $('.view-borrowing').click(function() {
        const id = $(this).data('id');
        
        $.get("{{ route('equipment.borrowings.show', ['borrowing' => '_id_']) }}".replace('_id_', id))
            .done(function(response) {
                $('#viewModal .modal-body').html(response);
                $('#viewModal').modal('show');
            })
            .fail(function(xhr) {
                console.error('Error:', xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Failed to load borrowing details.'
                });
            });
    });

    // Return Equipment
    $('.return-equipment').click(function() {
        const id = $(this).data('id');
        console.log('Returning equipment:', id);
        
        Swal.fire({
            title: 'Return Equipment?',
            text: "Are you sure you want to mark this equipment as returned?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, return it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('equipment.borrowings.return', ['borrowing' => '_id_']) }}".replace('_id_', id),
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Something went wrong.'
                        });
                    }
                });
            }
        });
    });

    // Reset form when modal is closed
    $('#createModal').on('hidden.bs.modal', function () {
        $('#createForm')[0].reset();
        $('#borrow_date').val(today);
    });

    // Update due_date min when borrow_date changes
    $('#borrow_date').change(function() {
        const selectedDate = $(this).val();
        const nextDay = new Date(selectedDate);
        nextDay.setDate(nextDay.getDate() + 1);
        
        $('#due_date').attr('min', formatDate(nextDay));
        if ($('#due_date').val() && new Date($('#due_date').val()) <= new Date(selectedDate)) {
            $('#due_date').val('');
        }
    });

    // Delete Borrowing
    $('.delete-borrowing').click(function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: 'Delete Borrowing?',
            text: "This will mark the equipment as available. Are you sure?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('equipment.borrowings.destroy', ['borrowing' => '_id_']) }}".replace('_id_', id),
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Something went wrong.'
                        });
                    }
                });
            }
        });
    });

    // Restore Borrowing
    $('.restore-borrowing').click(function() {
        const id = $(this).data('id');
        
        Swal.fire({
            title: 'Restore Borrowing?',
            text: "This will restore the borrowing record. Are you sure?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, restore it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('equipment.borrowings.restore', ['borrowing' => '_id_']) }}".replace('_id_', id),
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Something went wrong.'
                        });
                    }
                });
            }
        });
    });
});
</script>
@endpush 