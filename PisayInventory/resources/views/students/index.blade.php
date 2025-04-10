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
                                    <a href="{{ route('students.edit', $student->student_id) }}" class="btn btn-sm btn-primary" title="Edit">
                                        <i class="bi bi-pencil" style="color: white;"></i>
                                    </a>
                                    @endif
                                    @if($userPermissions->CanDelete)
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $student->student_id }}" title="Delete">
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
<div class="modal fade" id="deleteModal{{ $student->student_id }}" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
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
                <form action="{{ route('students.destroy', $student->student_id) }}" method="POST" class="d-inline">
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
                    <form id="uploadForm" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="excelFileInput" class="form-label">Select Excel File</label>
                            <input type="file" class="form-control" id="excelFileInput" accept=".xlsx, .xls">
                        </div>
                        <button type="button" id="readExcelBtn" class="btn btn-primary">
                            <span class="normal-text">Preview Columns</span>
                            <span class="loading-text d-none">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                Loading...
                            </span>
                        </button>
                    </form>
                </div>
                
                <!-- Step 2: Column Mapping -->
                <div id="importStep2" style="display: none;">
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
                                            <select name="default_gender" class="form-control form-control-sm">
                                                <option value="">Select Default</option>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Date of Birth</td>
                                        <td>
                                            <select name="column_mapping[date_of_birth]" class="form-select form-select-sm">
                                                <option value="">Select Column</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="date" name="default_date_of_birth" class="form-control form-control-sm" placeholder="Optional">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Address</td>
                                        <td>
                                            <select name="column_mapping[address]" class="form-select form-select-sm">
                                                <option value="">Select Column</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="default_address" class="form-control form-control-sm" placeholder="Optional">
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
                                        <td>Contact Number</td>
                                        <td>
                                            <select name="column_mapping[contact_number]" class="form-select form-select-sm">
                                                <option value="">Select Column</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="default_contact_number" class="form-control form-control-sm" placeholder="Optional">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Grade Level</td>
                                        <td>
                                            <select name="column_mapping[grade_level]" class="form-select form-select-sm">
                                                <option value="">Select Column</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="default_grade_level" class="form-control form-control-sm">
                                                <option value="">Select Default</option>
                                                <option value="7">Grade 7</option>
                                                <option value="8">Grade 8</option>
                                                <option value="9">Grade 9</option>
                                                <option value="10">Grade 10</option>
                                            </select>
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
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveChangesBtn">Save changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">Preview Selected Columns</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Preview content will be inserted here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {
    // Function to toggle loading state of preview button
    function togglePreviewLoading(isLoading) {
        const btn = $('#readExcelBtn');
        if (isLoading) {
            btn.prop('disabled', true);
            btn.find('.normal-text').addClass('d-none');
            btn.find('.loading-text').removeClass('d-none');
        } else {
            btn.prop('disabled', false);
            btn.find('.loading-text').addClass('d-none');
            btn.find('.normal-text').removeClass('d-none');
        }
    }

    // Initialize DataTables
    $('#studentsTable').DataTable({
        responsive: true,
        order: [[1, 'asc']],
        language: {
            emptyTable: "No students found"
        }
    });

    // Handle file selection and preview
    $('#readExcelBtn').on('click', function(e) {
        e.preventDefault();
        console.log('Button clicked');
        
        var fileInput = document.getElementById('excelFileInput');
        var file = fileInput.files[0];
        
        if (!file) {
            Swal.fire({
                icon: 'warning',
                title: 'No File Selected',
                text: 'Please select an Excel file first'
            });
            return;
        }

        // Show loading state
        togglePreviewLoading(true);

        var formData = new FormData();
        formData.append('excel_file', file);

        $.ajax({
            url: "/inventory/students/preview-columns",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('Raw server response:', response);
                
                if (response.success && response.data && response.data.headers) {
                    console.log('Headers found:', response.data.headers);
                    
                    // Define common column name mappings
                    const commonMappings = {
                        'student_id': ['student id', 'student number', 'id number', 'learner reference number', 'lrn'],
                        'first_name': ['first name', 'firstname', 'given name'],
                        'last_name': ['last name', 'lastname', 'surname', 'family name'],
                        'middle_name': ['middle name', 'middlename'],
                        'email': ['email', 'email address', 'e-mail'],
                        'contact_number': ['contact', 'contact number', 'phone', 'mobile', 'telephone'],
                        'gender': ['gender', 'sex'],
                        'date_of_birth': ['birth date', 'birthdate', 'date of birth', 'dob'],
                        'address': ['address', 'residence', 'location'],
                        'grade_level': ['grade', 'grade level', 'year level', 'level'],
                        'section': ['section', 'class', 'block']
                    };

                    // Populate all select elements
                    $('select[name^="column_mapping"]').each(function() {
                        var select = $(this);
                        var fieldName = select.attr('name').match(/\[(.*?)\]/)[1];
                        
                        // Clear existing options except the first one
                        select.find('option:not(:first)').remove();
                        
                        // Add new options
                        response.data.headers.forEach(function(header) {
                            select.append(new Option(header, header));
                            
                            // Auto-select if header matches common mappings
                            if (commonMappings[fieldName]) {
                                const matchesMapping = commonMappings[fieldName].some(mapping => 
                                    header.toLowerCase().includes(mapping.toLowerCase())
                                );
                                if (matchesMapping) {
                                    select.val(header);
                                }
                            }
                        });
                    });
                    
                    $('#importStep1').hide();
                    $('#importStep2').show();
                } else {
                    console.error('Invalid response structure:', response);
                    Swal.fire({
                        icon: 'error',
                        title: 'Preview Failed',
                        text: 'Invalid response format from server'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {
                    status: status,
                    error: error,
                    response: xhr.responseText
                });
                Swal.fire({
                    icon: 'error',
                    title: 'Preview Failed',
                    text: 'Failed to preview columns: ' + error
                });
            },
            complete: function() {
                // Hide loading state
                togglePreviewLoading(false);
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
            Swal.fire({
                icon: 'warning',
                title: 'Required Fields Missing',
                text: 'Please map the following required fields: ' + missingFields.join(', ')
            });
            return;
        }

        // Show confirmation dialog
        Swal.fire({
            title: 'Confirm Import',
            text: 'Are you sure you want to proceed with the import?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, import',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                var formData = new FormData();
                formData.append('excel_file', $('#excelFileInput')[0].files[0]);
                
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

                // Show loading state
                Swal.fire({
                    title: 'Importing...',
                    text: 'Please wait while we process your file',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

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
    if (response.success) {
        Swal.fire({
            icon: 'success',
            title: response.title,
            text: response.message,
            showCancelButton: response.showDetailsButton,
            cancelButtonText: 'See Skipped Records',
            confirmButtonText: 'OK',
            cancelButtonColor: '#6c757d',
            reverseButtons: true
        }).then((result) => {
            if (result.dismiss === Swal.DismissReason.cancel) {
                // Show details modal
                showSkippedRecordsModal(response.details);
            } else {
                location.reload();
            }
        });
    } else {
        Swal.fire({
            icon: 'error',
            title: response.title,
            text: response.message,
            showCancelButton: response.showDetailsButton,
            cancelButtonText: 'See Skipped Records',
            confirmButtonText: 'OK',
            cancelButtonColor: '#6c757d',
            reverseButtons: true
        }).then((result) => {
            if (result.dismiss === Swal.DismissReason.cancel && response.details) {
                showSkippedRecordsModal(response.details);
            }
        });
    }
},
                    error: function(xhr, status, error) {
                        let errorMessage = 'An error occurred during import';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMessage = response.error || response.message || errorMessage;
                        } catch (e) {
                            errorMessage = error || errorMessage;
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Import Failed',
                            text: errorMessage
                        });
                    }
                });
            }
        });

        // Populate the modal with student data
        $('#editStudentModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var studentId = button.data('student-id');
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
});

function showSkippedRecordsModal(details) {
    let duplicateRows = details.duplicates;
    let content = `
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Row</th>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
    `;

    duplicateRows.forEach(row => {
        content += `
            <tr>
                <td>${row.row}</td>
                <td>${row.student_id}</td>
                <td>${row.name}</td>
                <td><span class="badge bg-warning">Already Exists</span></td>
            </tr>
        `;
    });

    content += `
                </tbody>
            </table>
        </div>
    `;

    Swal.fire({
        title: 'Skipped Records',
        html: content,
        width: '800px',
        confirmButtonText: 'Close',
        confirmButtonColor: '#6c757d'
    });
}

function previewColumns() {
    const selectedColumns = [];
    // Make sure we're selecting the correct checkboxes
    $('input[name="columns[]"]:checked').each(function() {
        selectedColumns.push($(this).val());
    });

    console.log('Selected columns:', selectedColumns);

    if (selectedColumns.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Columns Selected',
            text: 'Please select at least one column to preview'
        });
        return;
    }

    $.ajax({
        url: '{{ route("students.preview-columns") }}',
        method: 'POST',
        data: {
            columns: selectedColumns,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            console.log('Server response:', response);
            
            if (!response || typeof response !== 'object') {
                console.error('Invalid response format:', response);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Invalid response format received from server'
                });
                return;
            }

            if (response.success && response.data) {
                console.log('Response data:', response.data);
                const previewHtml = generatePreviewHtml(response.data);
                $('#previewModal .modal-body').html(previewHtml);
                $('#previewModal').modal('show');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Failed to load preview'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', {
                status: status,
                error: error,
                response: xhr.responseText
            });
            
            let errorMessage = 'An error occurred while loading the preview.';
            try {
                const response = JSON.parse(xhr.responseText);
                errorMessage = response.message || errorMessage;
            } catch (e) {
                console.error('Error parsing response:', e);
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMessage
            });
        }
    });
}

function generatePreviewHtml(data) {
    console.log('Generating preview HTML with data:', data);
    
    if (!data || !data.headers || !data.rows) {
        console.error('Invalid data format:', data);
        return '<div class="alert alert-danger">Invalid data format received from server</div>';
    }

    let html = `
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        ${data.headers.map(header => `<th>${header}</th>`).join('')}
                    </tr>
                </thead>
                <tbody>
    `;

    data.rows.forEach(row => {
        html += '<tr>';
        data.headers.forEach(header => {
            const value = row[header] !== undefined ? row[header] : '';
            html += `<td>${value}</td>`;
        });
        html += '</tr>';
    });

    html += `
                </tbody>
            </table>
        </div>
    `;

    return html;
}
</script>
@endsection 