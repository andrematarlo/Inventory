@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Laboratories</h1>
        @if($userPermissions->CanAdd)
        <button type="button" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addLaboratoryModal">
            <i class="bi bi-plus"></i> Add Laboratory
        </button>
        @endif
    </div>

    <!-- Add Laboratory Modal -->
    <div class="modal fade" 
     id="addLaboratoryModal" 
     data-bs-backdrop="static" 
     data-bs-keyboard="false" 
     tabindex="-1" 
     aria-labelledby="addLaboratoryModalLabel" 
     aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addLaboratoryModalLabel">Add Laboratory</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                </div>
                <form method="POST" action="{{ route('laboratories.store') }}" id="addLaboratoryForm">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="laboratory_id">Laboratory ID <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('laboratory_id') is-invalid @enderror" 
                                       id="laboratory_id" 
                                       name="laboratory_id" 
                                       required>
                                <div class="invalid-feedback" id="laboratory_id_error"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="laboratory_name">Laboratory Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('laboratory_name') is-invalid @enderror" 
                                       id="laboratory_name" 
                                       name="laboratory_name" 
                                       required>
                                <div class="invalid-feedback" id="laboratory_name_error"></div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="location">Location <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('location') is-invalid @enderror" 
                                       id="location" 
                                       name="location" 
                                       value="{{ old('location') }}" 
                                       required>
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="capacity">Capacity <span class="text-danger">*</span></label>
                                <input type="number" 
                                       class="form-control @error('capacity') is-invalid @enderror" 
                                       id="capacity" 
                                       name="capacity" 
                                       value="{{ old('capacity') }}" 
                                       min="1" 
                                       required>
                                @error('capacity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6">
                                <label for="status">Status <span class="text-danger">*</span></label>
                                <select class="form-control @error('status') is-invalid @enderror" 
                                        id="status" 
                                        name="status" 
                                        required>
                                    <option value="">Select Status</option>
                                    <option value="Available" {{ old('status') === 'Available' ? 'selected' : '' }}>Available</option>
                                    <option value="Occupied" {{ old('status') === 'Occupied' ? 'selected' : '' }}>Occupied</option>
                                    <option value="Under Maintenance" {{ old('status') === 'Under Maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Laboratory</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Laboratory List</h6>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="showDeletedRecords">
                <label class="form-check-label" for="showDeletedRecords">Show Deleted Records</label>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="laboratoriesTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th style="width: 150px;">Actions</th>
                            <th>Laboratory ID</th>
                            <th>Name</th>
                            <th>Location</th>
                            <th>Capacity</th>
                            <th>Status</th>
                            <th>Deleted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($laboratories as $laboratory)
                        <tr class="{{ $laboratory->deleted_at ? 'table-secondary deleted-record d-none' : 'active-record' }}">
                            <td>
                                <div class="btn-group">
                                    @if(!$laboratory->deleted_at)
                                        @if($userPermissions->CanView)
                                        <a href="{{ route('laboratories.show', $laboratory->laboratory_id) }}" 
                                           class="btn btn-sm btn-info" 
                                           data-bs-toggle="tooltip" 
                                           title="View Details">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                        @endif

                                        @if($userPermissions->CanEdit)
                                        <button type="button" 
                                                class="btn btn-sm btn-primary editLaboratoryBtn"
                                                data-bs-toggle="tooltip"
                                                data-bs-title="Edit"
                                                data-laboratory-id="{{ $laboratory->laboratory_id }}"
                                                data-laboratory-name="{{ $laboratory->laboratory_name }}"
                                                data-location="{{ $laboratory->location }}"
                                                data-capacity="{{ $laboratory->capacity }}"
                                                data-status="{{ $laboratory->status }}"
                                                data-description="{{ $laboratory->description }}">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        @endif

                                        @if($userPermissions->CanDelete)
                                        <button type="button" 
                                                class="btn btn-sm btn-danger deleteLaboratoryBtn"
                                                data-bs-toggle="tooltip"
                                                data-bs-title="Delete"
                                                data-laboratory-id="{{ $laboratory->laboratory_id }}"
                                                data-laboratory-name="{{ $laboratory->laboratory_name }}">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                        @endif

                                    @else
                                        @if($userPermissions->CanEdit)
                                        <button type="button" 
                                                class="btn btn-sm btn-success restoreLaboratoryBtn" 
                                                data-bs-toggle="tooltip"
                                                data-laboratory-id="{{ $laboratory->laboratory_id }}"
                                                data-laboratory-name="{{ $laboratory->laboratory_name }}"
                                                title="Restore">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td>{{ $laboratory->laboratory_id }}</td>
                            <td>{{ $laboratory->laboratory_name }}</td>
                            <td>{{ $laboratory->location }}</td>
                            <td>{{ $laboratory->capacity }}</td>
                            <td>
                                <span class="badge rounded-pill bg-{{ $laboratory->status === 'Available' ? 'success' : ($laboratory->status === 'Occupied' ? 'warning' : 'danger') }} text-white">
                                    {{ $laboratory->status }}
                                </span>
                            </td>
                            <td>{{ $laboratory->deleted_at ? $laboratory->deleted_at->format('M d, Y H:i:s') : '' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush



<!-- Edit Laboratory Modal -->
<div class="modal fade" 
     id="editLaboratoryModal" 
     data-bs-backdrop="static" 
     data-bs-keyboard="false" 
     tabindex="-1" 
     aria-labelledby="editLaboratoryModalLabel" 
     aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editLaboratoryModalLabel">Edit Laboratory</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            <form id="editLaboratoryForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="edit_laboratory_id">Laboratory ID <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="edit_laboratory_id" 
                                   name="laboratory_id" 
                                   required 
                                   readonly>
                        </div>

                        <div class="col-md-6">
                            <label for="edit_laboratory_name">Laboratory Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="edit_laboratory_name" 
                                   name="laboratory_name" 
                                   required>
                        </div>
                    </div>

                    <div class="form-group row mt-3">
                        <div class="col-md-6">
                            <label for="edit_location">Location <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="edit_location" 
                                   name="location" 
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label for="edit_capacity">Capacity <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control" 
                                   id="edit_capacity" 
                                   name="capacity" 
                                   min="1" 
                                   required>
                        </div>
                    </div>

                    <div class="form-group row mt-3">
                        <div class="col-md-6">
                            <label for="edit_status">Status <span class="text-danger">*</span></label>
                            <select class="form-control" 
                                    id="edit_status" 
                                    name="status" 
                                    required>
                                <option value="Available">Available</option>
                                <option value="Occupied">Occupied</option>
                                <option value="Under Maintenance">Under Maintenance</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group mt-3">
                        <label for="edit_description">Description</label>
                        <textarea class="form-control" 
                                  id="edit_description" 
                                  name="description" 
                                  rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Laboratory</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Laboratory Modal -->
<div class="modal fade" 
     id="deleteLaboratoryModal" 
     data-bs-backdrop="static" 
     data-bs-keyboard="false" 
     tabindex="-1" 
     aria-labelledby="deleteLaboratoryModalLabel" 
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteLaboratoryModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete laboratory "<span id="deleteLaboratoryName"></span>"?
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteLaboratoryForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Restore Laboratory Modal -->
<div class="modal fade" 
     id="restoreLaboratoryModal" 
     data-bs-backdrop="static" 
     data-bs-keyboard="false" 
     tabindex="-1" 
     aria-labelledby="restoreLaboratoryModalLabel" 
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="restoreLaboratoryModalLabel">Confirm Restore</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to restore laboratory "<span id="restoreLaboratoryName"></span>"?
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="restoreLaboratoryForm" method="POST">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-success">Restore</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Initialize DataTable
        var table = $('#laboratoriesTable').DataTable({
            "order": [[2, "asc"]],
            "pageLength": 25,
            "language": {
                "emptyTable": "No laboratories found"
            },
            "columnDefs": [
                {
                    "targets": [6], // Deleted At column
                    "visible": false
                }
            ]
        });

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Edit Laboratory Button Click
        $(document).on('click', '.editLaboratoryBtn', function() {
            const btn = $(this);
            const laboratoryId = btn.data('laboratory-id');
            const laboratoryName = btn.data('laboratory-name');
            const location = btn.data('location');
            const capacity = btn.data('capacity');
            const status = btn.data('status');
            const description = btn.data('description');

            // Populate the edit form
            $('#edit_laboratory_id').val(laboratoryId);
            $('#edit_laboratory_name').val(laboratoryName);
            $('#edit_location').val(location);
            $('#edit_capacity').val(capacity);
            $('#edit_status').val(status);
            $('#edit_description').val(description || '');

            // Show the modal
            const editModal = new bootstrap.Modal(document.getElementById('editLaboratoryModal'));
            editModal.show();
        });
        

        // Initialize Bootstrap Modals
        const addLaboratoryModal = document.getElementById('addLaboratoryModal');
        if (addLaboratoryModal) {
            new bootstrap.Modal(addLaboratoryModal);
        }

        const editLaboratoryModal = document.getElementById('editLaboratoryModal');
        if (editLaboratoryModal) {
            new bootstrap.Modal(editLaboratoryModal);
        }

        const deleteLaboratoryModal = document.getElementById('deleteLaboratoryModal');
        if (deleteLaboratoryModal) {
            new bootstrap.Modal(deleteLaboratoryModal);
        }

        const restoreLaboratoryModal = document.getElementById('restoreLaboratoryModal');
        if (restoreLaboratoryModal) {
            new bootstrap.Modal(restoreLaboratoryModal);
        }

        // Toggle deleted records
        $('#showDeletedRecords').change(function() {
            if ($(this).is(':checked')) {
                $('.deleted-record').removeClass('d-none');
                table.column(6).visible(true);
            } else {
                $('.deleted-record').addClass('d-none');
                table.column(6).visible(false);
            }
            table.columns.adjust().draw();
        });

        // Show modal if there are validation errors
        @if($errors->any())
            const modal = new bootstrap.Modal(addLaboratoryModal);
            modal.show();
        @endif

        // Add Laboratory Modal
        $('#addLaboratoryBtn').on('click', function() {
            const modal = new bootstrap.Modal(addLaboratoryModal);
            modal.show();
        });

 
        // Delete Laboratory Button Click
        $(document).on('click', '.deleteLaboratoryBtn', function() {
            const btn = $(this);
            const laboratoryId = btn.data('laboratory-id');
            const laboratoryName = btn.data('laboratory-name');
            
            Swal.fire({
                title: 'Delete Laboratory?',
                text: `Are you sure you want to delete "${laboratoryName}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                allowOutsideClick: () => {
                    const popup = Swal.getPopup()
                    popup.classList.remove('swal2-show')
                    popup.classList.add('swal2-shake')
                    setTimeout(() => {
                        popup.classList.remove('swal2-shake')
                        popup.classList.add('swal2-show')
                    }, 100)
                    return false
                },
                allowEscapeKey: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/inventory/laboratories/${laboratoryId}`,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Success!',
                                text: 'Laboratory deleted successfully.',
                                icon: 'success'
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: 'Error!',
                                text: 'An error occurred while deleting the laboratory.',
                                icon: 'error'
                            });
                        }
                    });
                }
            });
        });

        // Handle edit form submission
        $('#editLaboratoryForm').on('submit', function(e) {
            e.preventDefault();
            
            const laboratoryId = $('#edit_laboratory_id').val();
            const formData = {
                laboratory_id: laboratoryId,
                laboratory_name: $('#edit_laboratory_name').val(),
                location: $('#edit_location').val(),
                capacity: $('#edit_capacity').val(),
                status: $('#edit_status').val(),
                description: $('#edit_description').val()
            };

            // Validate required fields
            let errors = [];
            if (!formData.laboratory_name.trim()) errors.push('Laboratory Name is required');
            if (!formData.location.trim()) errors.push('Location is required');
            if (!formData.capacity || parseInt(formData.capacity) < 1) errors.push('Capacity must be at least 1');
            if (!formData.status) errors.push('Status is required');

            if (errors.length > 0) {
                Swal.fire({
                    title: 'Validation Error',
                    html: errors.join('<br>'),
                    icon: 'error'
                });
                return;
            }

            $.ajax({
                url: `/laboratories/${laboratoryId}`,
                type: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#editLaboratoryModal').modal('hide');
                        Swal.fire({
                            title: 'Success!',
                            text: response.message,
                            icon: 'success'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: response.message || 'Failed to update laboratory.',
                            icon: 'error'
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'An error occurred while updating the laboratory.';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.errors) {
                            errorMessage = Object.values(xhr.responseJSON.errors).flat().join('\n');
                        } else if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                    }
                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error'
                    });
                }
            });
        });

 

        // Handle restore laboratory
        $(document).on('click', '.restoreLaboratoryBtn', function() {
            const laboratoryId = $(this).data('laboratory-id');
            const laboratoryName = $(this).data('laboratory-name');
            
            Swal.fire({
                title: 'Restore Laboratory?',
                text: `Are you sure you want to restore "${laboratoryName}"?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, restore it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/inventory/laboratories/${laboratoryId}/restore`,
                        type: 'PUT',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    title: 'Restored!',
                                    text: response.message,
                                    icon: 'success'
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: response.message,
                                    icon: 'error'
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: 'Error!',
                                text: 'An error occurred while restoring the laboratory.',
                                icon: 'error'
                            });
                        }
                    });
                }
            });
        });

        // Reset forms when modals are closed
        $('#addLaboratoryModal').on('hidden.bs.modal', function () {
            $("#addLaboratoryForm").trigger('reset');
            $(".is-invalid").removeClass('is-invalid');
            $(".invalid-feedback").remove();
        });

        $('#editLaboratoryModal').on('hidden.bs.modal', function () {
            $("#editLaboratoryForm").trigger('reset');
            $(".is-invalid").removeClass('is-invalid');
            $(".invalid-feedback").remove();
        });

        // Handle Add Laboratory Form Submission
        $('#addLaboratoryForm').on('submit', function(e) {
            e.preventDefault();
            
            // Reset any previous error states
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').empty();
            
            const formData = new FormData(this);
            
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    // Close the modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addLaboratoryModal'));
                    modal.hide();
                    
                    // Show success message
                    Swal.fire({
                        title: 'Success!',
                        text: 'Laboratory created successfully.',
                        icon: 'success'
                    }).then(() => {
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        // Validation errors
                        const errors = xhr.responseJSON.errors;
                        Object.keys(errors).forEach(field => {
                            const input = $(`#${field}`);
                            const feedback = $(`#${field}_error`);
                            input.addClass('is-invalid');
                            feedback.text(errors[field][0]);
                        });
                    } else {
                        // Other errors
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while creating the laboratory.',
                            icon: 'error'
                        });
                    }
                }
            });
        });
    });
</script>
@endpush