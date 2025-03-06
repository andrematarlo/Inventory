@extends('layouts.app')

@section('title', 'Students')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Student Management</h2>
        <div>
            @if($userPermissions && $userPermissions->CanView)
            <a href="{{ route('students.trash') }}" class="btn btn-secondary me-2">
                <i class="bi bi-trash"></i> Deleted Records <span class="badge bg-danger">{{ $deletedCount }}</span>
            </a>
            @endif
            @if($userPermissions && $userPermissions->CanAdd)
            <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="bi bi-file-earmark-excel"></i> Import Students
            </button>
            <a href="{{ route('students.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> New Student
            </a>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="studentsTable">
                    <thead>
                        <tr>
                            @if($userPermissions && ($userPermissions->CanEdit || $userPermissions->CanDelete))
                            <th style="width: 120px">Actions</th>
                            @endif
                            <th>Student Number</th>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Year & Section</th>
                            <th>Contact</th>
                            <th>Email</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                        <tr>
                            @if($userPermissions && ($userPermissions->CanEdit || $userPermissions->CanDelete))
                            <td>
                                <div class="btn-group" role="group">
                                    @if($userPermissions->CanEdit)
                                    <a href="{{ route('students.edit', $student->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                        <i class="bi bi-pencil" style="color: white;"></i>
                                    </a>
                                    @endif
                                    @if($userPermissions->CanDelete)
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $student->id }}" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
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
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No students found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modals -->
@if($userPermissions && $userPermissions->CanDelete)
@foreach($students as $student)
<div class="modal fade" id="deleteModal{{ $student->id }}" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Delete Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this student?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('students.destroy', $student->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@endif

<!-- Import Modal -->
@if($userPermissions && $userPermissions->CanAdd)
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Students</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Step 1: File Selection -->
                <div id="importStep1">
                    <div class="mb-3">
                        <label for="excelFileInput" class="form-label">Select Excel File</label>
                        <input type="file" class="form-control" id="excelFileInput" accept=".xlsx, .xls">
                    </div>
                    <button type="button" id="readExcelBtn" class="btn btn-primary">Preview Columns</button>
                </div>
                
                <!-- Step 2: Column Mapping -->
                <div id="importStep2" style="display: none;">
                    <h5>Map Excel Columns to Student Fields</h5>
                    <form id="importForm">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Field</th>
                                        <th>Excel Column</th>
                                        <th>Default Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Student Number <span class="text-danger">*</span></td>
                                        <td>
                                            <select name="column_mapping[student_id]" class="form-select form-select-sm" required>
                                                <option value="">Select Column</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm" disabled placeholder="Required">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>First Name <span class="text-danger">*</span></td>
                                        <td>
                                            <select name="column_mapping[first_name]" class="form-select form-select-sm" required>
                                                <option value="">Select Column</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm" disabled placeholder="Required">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Last Name <span class="text-danger">*</span></td>
                                        <td>
                                            <select name="column_mapping[last_name]" class="form-select form-select-sm" required>
                                                <option value="">Select Column</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm" disabled placeholder="Required">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Middle Name</td>
                                        <td>
                                            <select name="column_mapping[middle_name]" class="form-select form-select-sm">
                                                <option value="">Select Column</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="default_middle_name" class="form-control form-control-sm" placeholder="Optional">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Gender</td>
                                        <td>
                                            <select name="column_mapping[gender]" class="form-select form-select-sm">
                                                <option value="">Select Column</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="default_gender" class="form-select form-select-sm">
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Email</td>
                                        <td>
                                            <select name="column_mapping[email]" class="form-select form-select-sm">
                                                <option value="">Select Column</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="email" name="default_email" class="form-control form-control-sm" placeholder="Optional">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Year Level</td>
                                        <td>
                                            <select name="column_mapping[grade_level]" class="form-select form-select-sm">
                                                <option value="">Select Column</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="default_year" class="form-control form-control-sm" placeholder="Optional">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Section</td>
                                        <td>
                                            <select name="column_mapping[section]" class="form-select form-select-sm">
                                                <option value="">Select Column</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="default_section" class="form-control form-control-sm" placeholder="Optional">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" id="backToStep1Btn" class="btn btn-secondary">Back</button>
                        <button type="submit" class="btn btn-primary">Import Students</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Edit Student Modal -->
<div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editStudentModalLabel">Edit Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('students.update', 'student_id') }}" method="POST" id="editStudentForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editStudentId" name="student_id">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="editFirstName" name="first_name" placeholder="First Name" required>
                                <label for="editFirstName">First Name</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="editLastName" name="last_name" placeholder="Last Name" required>
                                <label for="editLastName">Last Name</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="editEmail" name="email">
                    </div>
                    <!-- Add other fields as necessary -->
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveChangesBtn">Save changes</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('additional_scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTables
        $('#studentsTable').DataTable({
            responsive: true,
            order: [[1, 'asc']],
            language: {
                emptyTable: "No students found"
            }
        });

        // Handle file selection and preview
        $('#readExcelBtn').on('click', function() {
            var fileInput = document.getElementById('excelFileInput');
            var file = fileInput.files[0];
            
            if (!file) {
                alert('Please select an Excel file first');
                return;
            }

            // Show loading state
            var $btn = $(this);
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Loading...');

            var formData = new FormData();
            formData.append('excel_file', file);
            
            // Get CSRF token
            var token = $('meta[name="csrf-token"]').attr('content');
            if (!token) {
                console.error('CSRF token not found');
                alert('Error: CSRF token not found. Please refresh the page and try again.');
                $btn.prop('disabled', false).text('Preview Columns');
                return;
            }

            console.log('Sending preview request with CSRF token:', token);
            console.log('File being sent:', file);
            
            // Send request to preview columns
            $.ajax({
                url: "/inventory/students/preview-columns",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': token
                },
                success: function(response) {
                    console.log('Preview response:', response);
                    if (!response.columns || !Array.isArray(response.columns)) {
                        console.error('Invalid response format:', response);
                        alert('Error: Invalid response format from server');
                        return;
                    }

                    // Reset all dropdowns first
                    $('select[name^="column_mapping"]').each(function() {
                        var select = $(this);
                        select.find('option:not(:first)').remove();
                        
                        // Add the columns to all dropdowns
                        response.columns.forEach(function(column) {
                            select.append(new Option(column, column));
                        });

                        // Try to auto-match based on field name
                        var fieldName = select.attr('name').match(/\[([^\]]+)\]/)[1].toLowerCase();
                        var matchingColumn = response.columns.find(function(column) {
                            return column.toLowerCase().includes(fieldName.toLowerCase());
                        });

                        if (matchingColumn) {
                            select.val(matchingColumn);
                        }
                    });

                    // Show mapping form
                    $('#importStep1').hide();
                    $('#importStep2').show();
                },
                error: function(xhr, status, error) {
                    console.error('Preview error:', {
                        status: status,
                        error: error,
                        response: xhr.responseText,
                        headers: xhr.getAllResponseHeaders()
                    });
                    
                    var errorMessage;
                    try {
                        var response = JSON.parse(xhr.responseText);
                        errorMessage = response.error || response.message || 'Unknown error occurred';
                    } catch (e) {
                        errorMessage = 'Failed to preview columns. Please try again.';
                    }
                    
                    alert('Preview failed: ' + errorMessage);
                },
                complete: function() {
                    // Reset button state
                    $btn.prop('disabled', false).text('Preview Columns');
                }
            });
        });

        // Handle back button
        $('#backToStep1Btn').on('click', function() {
            $('#importStep2').hide();
            $('#importStep1').show();
        });

        // Handle form submission
        $('#importForm').on('submit', function(e) {
            e.preventDefault();
            
            // Validate required fields
            var requiredFields = ['student_id', 'first_name', 'last_name'];
            var missingFields = [];
            
            requiredFields.forEach(function(field) {
                if (!$('select[name="column_mapping[' + field + ']"]').val()) {
                    missingFields.push(field);
                }
            });
            
            if (missingFields.length > 0) {
                alert('Please map the following required fields: ' + missingFields.join(', '));
                return;
            }

            var formData = new FormData();
            formData.append('file', $('#excelFileInput')[0].files[0]);
            
            // Add mappings
            $('select[name^="column_mapping"]').each(function() {
                if ($(this).val()) {
                    formData.append($(this).attr('name'), $(this).val());
                }
            });
            
            // Add defaults
            $('[name^="default_"]').each(function() {
                if ($(this).val()) {
                    formData.append($(this).attr('name'), $(this).val());
                }
            });

            console.log('Sending import request with data:', {
                file: $('#excelFileInput')[0].files[0],
                mappings: Object.fromEntries(formData.entries())
            });

            // Show loading state
            var submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Importing...');

            // Send import request
            $.ajax({
                url: "/inventory/students/process-import",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log('Import response:', response);
                    if (response.success) {
                        alert(response.message);
                        location.reload();
                    } else {
                        console.error('Import failed:', response);
                        alert('Import failed: ' + (response.error || 'Unknown error occurred'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Import error:', {
                        status: status,
                        error: error,
                        response: xhr.responseText,
                        headers: xhr.getAllResponseHeaders()
                    });
                    try {
                        var response = JSON.parse(xhr.responseText);
                        alert('Import failed: ' + (response.error || response.message || 'Unknown error occurred'));
                    } catch (e) {
                        alert('Import failed: ' + error);
                    }
                },
                complete: function() {
                    submitBtn.prop('disabled', false).text('Import Students');
                }
            });
        });

        // Populate the modal with student data
        $('#editStudentModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var studentId = button.data('id');
            var firstName = button.data('first-name');
            var lastName = button.data('last-name');
            var email = button.data('email');

            // Update the modal's content
            var modal = $(this);
            modal.find('#editStudentId').val(studentId);
            modal.find('#editFirstName').val(firstName);
            modal.find('#editLastName').val(lastName);
            modal.find('#editEmail').val(email);
        });

        // Handle form submission
        $('#saveChangesBtn').on('click', function () {
            var form = $('#editStudentForm');
            var actionUrl = "{{ url('inventory/students') }}/" + $('#editStudentId').val();

            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: form.serialize(),
                success: function (response) {
                    // Handle success (e.g., reload the page or update the table)
                    location.reload(); // Reload the page to see the changes
                },
                error: function (xhr) {
                    // Handle error
                    console.error(xhr.responseText);
                }
            });
        });
    });
</script>
@endsection 