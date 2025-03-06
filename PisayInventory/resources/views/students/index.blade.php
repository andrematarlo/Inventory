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
                                    <a href="{{ route('students.edit', $student->id) }}" class="btn btn-sm btn-blue" title="Edit">
                                        <i class="bi bi-pencil"></i>
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
<div class="modal fade" id="deleteModal{{ $student->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the student "{{ $student->first_name }} {{ $student->last_name }}"?</p>
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
<!-- Import Modal -->
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
                        <button type="button" id="readExcelBtn" class="btn btn-primary">Preview Columns</button>
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

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
    // Update the readExcelBtn click handler to include the new fields in auto-mapping
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
                                title: 'Import Successful',
                                text: response.message
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Import Failed',
                                text: response.error || 'Unknown error occurred'
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
    });
});
</script>
@endsection 