@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Equipment</h1>
        @if($userPermissions->CanAdd)
        <button type="button" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" id="addEquipmentBtn">
            <i class="bi bi-plus"></i> Add Equipment
        </button>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Equipment List</h6>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="showDeleted">
                <label class="form-check-label" for="showDeleted">Show Deleted Records</label>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Actions</th>
                            <th>Equipment ID</th>
                            <th>Name</th>
                            <th>Laboratory</th>
                            <th>Serial Number</th>
                            <th>Model Number</th>
                            <th>Status</th>
                            <th>Condition</th>
                            <th>Last Maintenance</th>
                            <th>Next Maintenance</th>
                            <th>Deleted At</th>
                        </tr>
                    </thead>
                    <tbody id="activeRecords">
                        @foreach($equipment->where('IsDeleted', false) as $item)
                        <tr class="{{ $item->deleted_at ? 'table-secondary deleted-record' : 'active-record' }}">
                            <td>
                                <div class="btn-group">
                                    @if(!$item->deleted_at)
                                        @if($userPermissions->CanView)
                                        <button type="button" 
                                                class="btn btn-sm btn-info viewEquipmentBtn" 
                                                data-bs-toggle="tooltip"
                                                data-equipment-id="{{ $item->equipment_id }}"
                                                title="View">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        @endif

                                        @if($userPermissions->CanEdit)
                                        <button type="button" 
                                                class="btn btn-sm btn-warning editEquipmentBtn" 
                                                data-bs-toggle="tooltip"
                                                data-equipment-id="{{ $item->equipment_id }}"
                                                title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        @endif

                                        @if($userPermissions->CanDelete)
                                        <button type="button" 
                                                class="btn btn-sm btn-danger deleteEquipmentBtn" 
                                                data-bs-toggle="tooltip"
                                                data-equipment-id="{{ $item->equipment_id }}"
                                                data-name="{{ $item->equipment_name }}"
                                                title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        @endif
                                    @else
                                        @if($userPermissions->CanEdit)
                                        <button type="button" 
                                                class="btn btn-sm btn-success restoreEquipmentBtn" 
                                                data-bs-toggle="tooltip"
                                                data-equipment-id="{{ $item->equipment_id }}"
                                                data-name="{{ $item->equipment_name }}"
                                                title="Restore">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td>{{ $item->equipment_id }}</td>
                            <td>{{ $item->equipment_name }}</td>
                            <td>{{ $item->laboratory ? $item->laboratory->laboratory_name : 'Unassigned' }}</td>
                            <td>{{ $item->serial_number ?: 'Not Specified' }}</td>
                            <td>{{ $item->model_number ?: 'Not Specified' }}</td>
                            <td>
                                <span class="badge rounded-pill bg-{{ $item->status === 'Available' ? 'success' : ($item->status === 'In Use' ? 'warning' : 'danger') }}">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-{{ $item->condition === 'Good' ? 'success' : ($item->condition === 'Fair' ? 'warning' : 'danger') }}">
                                    {{ $item->condition }}
                                </span>
                            </td>
                            <td>{{ $item->last_maintenance_date ? date('M d, Y', strtotime($item->last_maintenance_date)) : 'Not Set' }}</td>
                            <td>{{ $item->next_maintenance_date ? date('M d, Y', strtotime($item->next_maintenance_date)) : 'Not Set' }}</td>
                            <td>{{ $item->deleted_at ? $item->deleted_at->format('M d, Y H:i:s') : '' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tbody id="deletedRecords" style="display: none;">
                        @foreach($equipment->where('IsDeleted', true) as $item)
                        <tr class="deleted-record">
                            <td>
                                <div class="btn-group">
                                    @if($item->deleted_at)
                                        @if($userPermissions->CanEdit)
                                        <button type="button" 
                                                class="btn btn-sm btn-success restoreEquipmentBtn" 
                                                data-bs-toggle="tooltip"
                                                data-equipment-id="{{ $item->equipment_id }}"
                                                data-name="{{ $item->equipment_name }}"
                                                title="Restore">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td>{{ $item->equipment_id }}</td>
                            <td>{{ $item->equipment_name }}</td>
                            <td>
                                @if($item->laboratory)
                                    {{ $item->laboratory->laboratory_name }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>{{ $item->serial_number ?? 'N/A' }}</td>
                            <td>{{ $item->model_number ?? 'N/A' }}</td>
                            <td>
                                <span class="badge rounded-pill bg-{{ $item->status === 'Available' ? 'success' : ($item->status === 'In Use' ? 'warning' : 'danger') }} text-white">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-{{ $item->condition === 'Good' ? 'success' : ($item->condition === 'Fair' ? 'warning' : 'danger') }} text-white">
                                    {{ $item->condition }}
                                </span>
                            </td>
                            <td>{{ $item->last_maintenance_date ? date('M d, Y', strtotime($item->last_maintenance_date)) : 'N/A' }}</td>
                            <td>{{ $item->next_maintenance_date ? date('M d, Y', strtotime($item->next_maintenance_date)) : 'N/A' }}</td>
                            <td>{{ $item->deleted_at ? $item->deleted_at->format('M d, Y H:i:s') : '' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create Equipment Modal -->
<div class="modal fade" 
     id="createEquipmentModal" 
     data-bs-backdrop="static" 
     data-bs-keyboard="false" 
     tabindex="-1" 
     aria-labelledby="createEquipmentModalLabel" 
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createEquipmentModalLabel">Add New Equipment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createEquipmentForm">
                    @csrf
                    <div class="mb-3">
                        <label for="equipment_name" class="form-label">Equipment Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="equipment_name" name="equipment_name" required>
                    </div>

                    <div class="mb-3">
                        <label for="laboratory_id" class="form-label">Laboratory</label>
                        <select class="form-control" id="laboratory_id" name="laboratory_id">
                            <option value="">Select Laboratory</option>
                            @foreach($laboratories as $lab)
                                <option value="{{ $lab->laboratory_id }}">{{ $lab->laboratory_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="serial_number" class="form-label">Serial Number</label>
                                <input type="text" class="form-control" id="serial_number" name="serial_number">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="model_number" class="form-label">Model Number</label>
                                <input type="text" class="form-control" id="model_number" name="model_number">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="Available">Available</option>
                                    <option value="In Use">In Use</option>
                                    <option value="Under Maintenance">Under Maintenance</option>
                                    <option value="Damaged">Damaged</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="condition" class="form-label">Condition <span class="text-danger">*</span></label>
                                <select class="form-control" id="condition" name="condition" required>
                                    <option value="Good">Good</option>
                                    <option value="Fair">Fair</option>
                                    <option value="Poor">Poor</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="acquisition_date" class="form-label">Acquisition Date</label>
                                <input type="date" class="form-control" id="acquisition_date" name="acquisition_date">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="last_maintenance_date" class="form-label">Last Maintenance</label>
                                <input type="date" class="form-control" id="last_maintenance_date" name="last_maintenance_date">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="next_maintenance_date" class="form-label">Next Maintenance</label>
                                <input type="date" class="form-control" id="next_maintenance_date" name="next_maintenance_date">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveEquipment">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Equipment Modal -->
<div class="modal fade" id="editEquipmentModal" tabindex="-1" aria-labelledby="editEquipmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEquipmentModalLabel">Edit Equipment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editEquipmentForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="edit_equipment_id">Equipment ID <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="edit_equipment_id" 
                                   name="equipment_id" 
                                   required 
                                   readonly>
                        </div>

                        <div class="col-md-6">
                            <label for="edit_equipment_name">Equipment Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="edit_equipment_name" 
                                   name="equipment_name" 
                                   required>
                        </div>
                    </div>

                    <div class="form-group row mt-3">
                        <div class="col-md-6">
                            <label for="edit_laboratory_id">Laboratory <span class="text-danger">*</span></label>
                            <select class="form-control" 
                                    id="edit_laboratory_id" 
                                    name="laboratory_id" 
                                    required>
                                <option value="">Select Laboratory</option>
                                @foreach($laboratories as $laboratory)
                                    <option value="{{ $laboratory->laboratory_id }}">
                                        {{ $laboratory->laboratory_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="edit_status">Status <span class="text-danger">*</span></label>
                            <select class="form-control" 
                                    id="edit_status" 
                                    name="status" 
                                    required>
                                <option value="Available">Available</option>
                                <option value="In Use">In Use</option>
                                <option value="Under Maintenance">Under Maintenance</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row mt-3">
                        <div class="col-md-6">
                            <label for="edit_condition">Condition <span class="text-danger">*</span></label>
                            <select class="form-control" 
                                    id="edit_condition" 
                                    name="condition" 
                                    required>
                                <option value="Good">Good</option>
                                <option value="Fair">Fair</option>
                                <option value="Poor">Poor</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="edit_acquisition_date">Acquisition Date</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="edit_acquisition_date" 
                                   name="acquisition_date">
                        </div>
                    </div>

                    <div class="form-group row mt-3">
                        <div class="col-md-6">
                            <label for="edit_last_maintenance_date">Last Maintenance Date</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="edit_last_maintenance_date" 
                                   name="last_maintenance_date">
                        </div>

                        <div class="col-md-6">
                            <label for="edit_next_maintenance_date">Next Maintenance Date</label>
                            <input type="date" 
                                   class="form-control" 
                                   id="edit_next_maintenance_date" 
                                   name="next_maintenance_date">
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
                    <button type="submit" class="btn btn-primary">Update Equipment</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Show/hide deleted records
        $('#showDeleted').change(function() {
            if ($(this).is(':checked')) {
                $('#activeRecords').hide();
                $('#deletedRecords').show();
            } else {
                $('#activeRecords').show();
                $('#deletedRecords').hide();
            }
        });

        // Initialize DataTable with custom row rendering
        var table = $('#equipmentTable').DataTable({
            "order": [[2, "asc"]],
            "pageLength": 25,
            "language": {
                "emptyTable": "No equipment found"
            },
            "createdRow": function(row, data, dataIndex) {
                if ($(row).hasClass('deleted-record')) {
                    $(row).hide();
                }
            }
        });

        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();

        // Initialize all modals
        var modals = document.querySelectorAll('.modal');
        modals.forEach(function(modal) {
            new bootstrap.Modal(modal);
        });

        // Add Equipment button click
        $('#addEquipmentBtn').click(function() {
            $('#createEquipmentModal').modal('show');
        });

        // Create Equipment
        $('#saveEquipment').click(function() {
            var form = $('#createEquipmentForm')[0];
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            var formData = new FormData(form);

            $.ajax({
                url: "{{ route('equipment.store') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        $('#createEquipmentModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message
                        }).then((result) => {
                            location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Something went wrong.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
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
        $('#createEquipmentModal').on('hidden.bs.modal', function() {
            $('#createEquipmentForm')[0].reset();
        });

        // Edit Equipment
        $(document).on('click', '.editEquipmentBtn', function() {
            const equipmentId = $(this).data('equipment-id');
            const equipmentName = $(this).data('equipment-name');
            const laboratoryId = $(this).data('laboratory-id');
            const serialNumber = $(this).data('serial-number');
            const modelNumber = $(this).data('model-number');
            const status = $(this).data('status');
            const condition = $(this).data('condition');
            const acquisitionDate = $(this).data('acquisition-date');
            const lastMaintenanceDate = $(this).data('last-maintenance-date');
            const nextMaintenanceDate = $(this).data('next-maintenance-date');
            const description = $(this).data('description');

            $('#edit_equipment_id').val(equipmentId);
            $('#edit_equipment_name').val(equipmentName);
            $('#edit_laboratory_id').val(laboratoryId);
            $('#edit_serial_number').val(serialNumber);
            $('#edit_model_number').val(modelNumber);
            $('#edit_status').val(status);
            $('#edit_condition').val(condition);
            $('#edit_acquisition_date').val(acquisitionDate);
            $('#edit_last_maintenance_date').val(lastMaintenanceDate);
            $('#edit_next_maintenance_date').val(nextMaintenanceDate);
            $('#edit_description').val(description || '');

            const modal = new bootstrap.Modal(editEquipmentModal);
            modal.show();
        });

        // Handle edit form submission
        $('#editEquipmentForm').on('submit', function(e) {
            e.preventDefault();
            
            const equipmentId = $('#edit_equipment_id').val();
            const formData = {
                equipment_id: equipmentId,
                equipment_name: $('#edit_equipment_name').val(),
                laboratory_id: $('#edit_laboratory_id').val(),
                serial_number: $('#edit_serial_number').val(),
                model_number: $('#edit_model_number').val(),
                status: $('#edit_status').val(),
                condition: $('#edit_condition').val(),
                acquisition_date: $('#edit_acquisition_date').val(),
                last_maintenance_date: $('#edit_last_maintenance_date').val(),
                next_maintenance_date: $('#edit_next_maintenance_date').val(),
                description: $('#edit_description').val()
            };

            // Validate required fields
            let errors = [];
            if (!formData.equipment_name.trim()) errors.push('Equipment Name is required');
            if (!formData.laboratory_id) errors.push('Laboratory is required');
            if (!formData.status) errors.push('Status is required');
            if (!formData.condition) errors.push('Condition is required');

            if (errors.length > 0) {
                Swal.fire({
                    title: 'Validation Error',
                    html: errors.join('<br>'),
                    icon: 'error'
                });
                return;
            }

            $.ajax({
                url: `/equipment/${equipmentId}`,
                type: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: formData,
                success: function(response) {
                    if (response.success) {
                        const editModal = bootstrap.Modal.getInstance(editEquipmentModal);
                        editModal.hide();
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
                            text: response.message || 'Failed to update equipment.',
                            icon: 'error'
                        });
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'An error occurred while updating the equipment.';
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

        // Delete Equipment
        $(document).on('click', '.deleteEquipmentBtn', function() {
            const equipmentId = $(this).data('equipment-id');
            const name = $(this).data('name');
            
            Swal.fire({
                title: 'Delete Equipment?',
                text: `Are you sure you want to delete "${name}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('equipment.destroy', '') }}/" + equipmentId,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: response.message,
                                    icon: 'success'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: response.message || 'An error occurred while deleting the equipment.',
                                    icon: 'error'
                                });
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = 'An error occurred while deleting the equipment.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                title: 'Error!',
                                text: errorMessage,
                                icon: 'error'
                            });
                        }
                    });
                }
            });
        });

        // Restore Equipment
        $(document).on('click', '.restoreEquipmentBtn', function() {
            const equipmentId = $(this).data('equipment-id');
            const name = $(this).data('name');
            
            Swal.fire({
                title: 'Restore Equipment?',
                text: `Are you sure you want to restore "${name}"?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, restore it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('equipment') }}/" + equipmentId + "/restore",
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    title: 'Restored!',
                                    text: response.message,
                                    icon: 'success'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: response.message || 'An error occurred while restoring the equipment.',
                                    icon: 'error'
                                });
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = 'An error occurred while restoring the equipment.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                title: 'Error!',
                                text: errorMessage,
                                icon: 'error'
                            });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
@endsection 