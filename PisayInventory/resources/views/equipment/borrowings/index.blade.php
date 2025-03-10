@extends('layouts.app')

@section('title', 'Equipment Borrowings')

@php
    use Carbon\Carbon;
@endphp

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Equipment Borrowings</h1>
        @if($userPermissions->CanAdd)
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
                    <select class="form-select form-select-sm d-inline-block w-auto" id="pageSizeSelect">
                        <option value="10" {{ $borrowings->perPage() == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ $borrowings->perPage() == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ $borrowings->perPage() == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $borrowings->perPage() == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    entries
                </div>
                <div class="d-flex gap-3 align-items-center">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="showDeleted" {{ request()->has('trashed') ? 'checked' : '' }}>
                        <label class="form-check-label" for="showDeleted">Show Deleted Records</label>
                    </div>
                    <div class="search-box">
                        <input type="text" class="form-control" id="searchInput" placeholder="Search..." value="{{ request()->get('search') }}">
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Actions</th>
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
                    <tbody>
                        @forelse($borrowings as $borrowing)
                        <tr>
                            <td>
                                <div class="btn-group">
                                    @if(!$borrowing->deleted_at)
                                        @if($userPermissions->CanEdit)
                                            @if(!$borrowing->actual_return_date)
                                                <button type="button" 
                                                        class="btn btn-sm btn-success returnBorrowingBtn" 
                                                        data-borrowing-id="{{ $borrowing->borrowing_id }}"
                                                        data-equipment-name="{{ $borrowing->equipment->equipment_name ?? '' }}"
                                                        title="Return">
                                                    <i class="bi bi-box-arrow-in-left"></i>
                                                </button>
                                            @endif
                                            <button type="button" 
                                                    class="btn btn-sm btn-primary editBorrowingBtn" 
                                                    data-borrowing-id="{{ $borrowing->borrowing_id }}"
                                                    title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        @endif
                                        @if($userPermissions->CanDelete)
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger deleteBorrowingBtn" 
                                                    data-borrowing-id="{{ $borrowing->borrowing_id }}"
                                                    title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endif
                                    @else
                                        @if($userPermissions->CanEdit)
                                            <button type="button" 
                                                    class="btn btn-sm btn-success restoreBorrowingBtn" 
                                                    data-borrowing-id="{{ $borrowing->borrowing_id }}"
                                                    title="Restore">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td>{{ $borrowing->status }}</td>
                            <td>{{ $borrowing->borrowing_id }}</td>
                            <td>
                                @if($borrowing->equipment)
                                    {{ $borrowing->equipment->equipment_name }}
                                @else
                                    <span class="text-danger">Equipment not found</span>
                                @endif
                            </td>
                            <td>
                                @if($borrowing->borrower)
                                    {{ $borrowing->borrower->FullName }}
                                @else
                                    <span class="text-danger">Borrower not found</span>
                                @endif
                            </td>
                            <td>{{ $borrowing->borrow_date ? Carbon::parse($borrowing->borrow_date)->format('M d, Y') : '-' }}</td>
                            <td>{{ $borrowing->expected_return_date ? Carbon::parse($borrowing->expected_return_date)->format('M d, Y') : '-' }}</td>
                            <td>{{ $borrowing->actual_return_date ? Carbon::parse($borrowing->actual_return_date)->format('M d, Y') : '-' }}</td>
                            <td>{{ $borrowing->purpose ?: '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">No borrowings found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing {{ $borrowings->firstItem() ?? 0 }} to {{ $borrowings->lastItem() ?? 0 }} of {{ $borrowings->total() }} entries
                </div>
                {{ $borrowings->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" 
     id="createModal" 
     data-bs-backdrop="static" 
     data-bs-keyboard="false" 
     tabindex="-1" 
     aria-hidden="true">
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

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Borrowing</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="borrowing_id">
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
                        <input type="date" class="form-control" name="borrow_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Expected Return Date</label>
                        <input type="date" class="form-control" name="expected_return_date" required>
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
                <button type="button" class="btn btn-primary" id="saveEdit">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Return Modal -->
<div class="modal fade" id="returnModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Return Equipment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="returnForm">
                    @csrf
                    <input type="hidden" name="borrowing_id">
                    <div class="mb-3">
                        <label class="form-label">Condition on Return</label>
                        <select class="form-select" name="condition_on_return" required>
                            <option value="">Select Condition</option>
                            <option value="Good">Good</option>
                            <option value="Fair">Fair</option>
                            <option value="Poor">Poor</option>
                            <option value="Damaged">Damaged</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" name="remarks" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveReturn">Return Equipment</button>
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
    // Handle page size change
    $('#pageSizeSelect').change(function() {
        const pageSize = $(this).val();
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('per_page', pageSize);
        window.location.href = currentUrl.toString();
    });

    // Handle show deleted toggle
    $('#showDeleted').change(function() {
        const currentUrl = new URL(window.location.href);
        if (this.checked) {
            currentUrl.searchParams.set('trashed', '1');
        } else {
            currentUrl.searchParams.delete('trashed');
        }
        window.location.href = currentUrl.toString();
    });

    // Handle search input with debounce
    let searchTimeout;
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const searchValue = $(this).val();
            const currentUrl = new URL(window.location.href);
            if (searchValue) {
                currentUrl.searchParams.set('search', searchValue);
            } else {
                currentUrl.searchParams.delete('search');
            }
            window.location.href = currentUrl.toString();
        }, 500);
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
    $(document).on('click', '.returnBorrowingBtn', function() {
        const borrowingId = $(this).data('borrowing-id');
        const equipmentName = $(this).data('equipment-name');
        
        // Reset form and show modal
        $('#returnForm')[0].reset();
        $('#returnForm').find('[name="borrowing_id"]').val(borrowingId);
        $('#returnModal').modal('show');
    });

    // Handle Return Submit
    $('#saveReturn').click(function() {
        const form = $('#returnForm');
        if (!form[0].checkValidity()) {
            form[0].reportValidity();
            return;
        }

        const button = $(this);
        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');

        const borrowingId = form.find('[name="borrowing_id"]').val();

        $.ajax({
            url: `/equipment-borrowings/${borrowingId}/return`,
            type: 'POST',
            data: new FormData(form[0]),
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#returnModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to return equipment'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Failed to return equipment'
                });
            },
            complete: function() {
                button.prop('disabled', false).html('Return Equipment');
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

    // Edit Borrowing
    $(document).on('click', '.editBorrowingBtn', function() {
        const borrowingId = $(this).data('borrowing-id');
        
        // Load borrowing details
        $.ajax({
            url: "{{ url('equipment-borrowings') }}/" + borrowingId + "/edit",
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const borrowing = response.data;
                    $('#editModal').modal('show');
                    // Populate form fields
                    $('#editForm').find('[name="equipment_id"]').val(borrowing.equipment_id);
                    $('#editForm').find('[name="borrow_date"]').val(borrowing.borrow_date);
                    $('#editForm').find('[name="expected_return_date"]').val(borrowing.expected_return_date);
                    $('#editForm').find('[name="purpose"]').val(borrowing.purpose);
                    $('#editForm').find('[name="condition_on_borrow"]').val(borrowing.condition_on_borrow);
                    $('#editForm').find('[name="remarks"]').val(borrowing.remarks);
                    $('#editForm').find('[name="borrowing_id"]').val(borrowing.borrowing_id);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to load borrowing details'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load borrowing details'
                });
            }
        });
    });

    // Delete Borrowing
    $(document).on('click', '.deleteBorrowingBtn', function() {
        const borrowingId = $(this).data('borrowing-id');
        
        Swal.fire({
            title: 'Delete Borrowing?',
            text: 'Are you sure you want to delete this borrowing record?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('equipment-borrowings') }}/" + borrowingId,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to delete borrowing'
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Failed to delete borrowing'
                        });
                    }
                });
            }
        });
    });

    // Restore Borrowing
    $(document).on('click', '.restoreBorrowingBtn', function() {
        const borrowingId = $(this).data('borrowing-id');
        
        Swal.fire({
            title: 'Restore Borrowing?',
            text: "Are you sure you want to restore this borrowing record?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, restore it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/equipment-borrowings/${borrowingId}/restore`,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Restored!',
                                text: response.message
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to restore borrowing'
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Failed to restore borrowing'
                        });
                    }
                });
            }
        });
    });
});
</script>
@endpush 