@extends('layouts.app')

@section('title', 'Import Students')

@section('content')
<div class="container-fluid px-4">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">Import Students</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <h5><i class="bi bi-info-circle me-2"></i>Instructions:</h5>
                        <ol class="mb-0">
                            <li>Prepare your Excel file (.xlsx or .xls) with student data</li>
                            <li>The first row should contain column headers</li>
                            <li>Required fields: Student ID, First Name, Last Name</li>
                            <li>Optional fields: Middle Name, Email, Contact Number, Gender, Grade Level, Section</li>
                            <li>Upload your file and map the columns to the correct fields</li>
                        </ol>
                    </div>

                    <form id="importForm" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label for="file" class="form-label">Excel File</label>
                            <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls" required>
                        </div>

                        <div id="mappingSection" style="display: none;">
                            <h5 class="mb-3">Column Mapping</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Field</th>
                                            <th>Excel Column</th>
                                            <th>Preview</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Student ID <span class="text-danger">*</span></td>
                                            <td>
                                                <select name="column_mapping[student_id]" class="form-select mapping-select" required>
                                                    <option value="">Select Column</option>
                                                </select>
                                            </td>
                                            <td class="preview-cell"></td>
                                        </tr>
                                        <tr>
                                            <td>First Name <span class="text-danger">*</span></td>
                                            <td>
                                                <select name="column_mapping[first_name]" class="form-select mapping-select" required>
                                                    <option value="">Select Column</option>
                                                </select>
                                            </td>
                                            <td class="preview-cell"></td>
                                        </tr>
                                        <tr>
                                            <td>Last Name <span class="text-danger">*</span></td>
                                            <td>
                                                <select name="column_mapping[last_name]" class="form-select mapping-select" required>
                                                    <option value="">Select Column</option>
                                                </select>
                                            </td>
                                            <td class="preview-cell"></td>
                                        </tr>
                                        <tr>
                                            <td>Middle Name</td>
                                            <td>
                                                <select name="column_mapping[middle_name]" class="form-select mapping-select">
                                                    <option value="">Select Column</option>
                                                </select>
                                            </td>
                                            <td class="preview-cell"></td>
                                        </tr>
                                        <tr>
                                            <td>Email</td>
                                            <td>
                                                <select name="column_mapping[email]" class="form-select mapping-select">
                                                    <option value="">Select Column</option>
                                                </select>
                                            </td>
                                            <td class="preview-cell"></td>
                                        </tr>
                                        <tr>
                                            <td>Contact Number</td>
                                            <td>
                                                <select name="column_mapping[contact_number]" class="form-select mapping-select">
                                                    <option value="">Select Column</option>
                                                </select>
                                            </td>
                                            <td class="preview-cell"></td>
                                        </tr>
                                        <tr>
                                            <td>Gender</td>
                                            <td>
                                                <select name="column_mapping[gender]" class="form-select mapping-select">
                                                    <option value="">Select Column</option>
                                                </select>
                                            </td>
                                            <td class="preview-cell"></td>
                                        </tr>
                                        <tr>
                                            <td>Grade Level</td>
                                            <td>
                                                <select name="column_mapping[grade_level]" class="form-select mapping-select">
                                                    <option value="">Select Column</option>
                                                </select>
                                            </td>
                                            <td class="preview-cell"></td>
                                        </tr>
                                        <tr>
                                            <td>Section</td>
                                            <td>
                                                <select name="column_mapping[section]" class="form-select mapping-select">
                                                    <option value="">Select Column</option>
                                                </select>
                                            </td>
                                            <td class="preview-cell"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="preview-section mt-4" style="display: none;">
                                <h5>Data Preview</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered" id="previewTable">
                                        <thead>
                                            <tr></tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="button" class="btn btn-secondary me-2" onclick="window.history.back()">
                                <i class="bi bi-arrow-left me-1"></i> Back
                            </button>
                            <button type="submit" class="btn btn-primary" id="importButton" style="display: none;">
                                <i class="bi bi-upload me-1"></i> Import Students
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let previewData = [];
    let headers = [];

    $('#file').change(function() {
        const file = this.files[0];
        if (file) {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

            // Show loading state
            Swal.fire({
                title: 'Reading File',
                text: 'Please wait while we process your file...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '{{ route("students.preview-columns") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    Swal.close();
                    
                    if (response.success) {
                        headers = response.data.headers;
                        previewData = response.data.preview_data;

                        // Populate column mapping dropdowns
                        $('.mapping-select').each(function() {
                            const select = $(this);
                            select.find('option:not(:first)').remove();
                            
                            headers.forEach((header, index) => {
                                select.append($('<option>', {
                                    value: String.fromCharCode(65 + index), // Convert to A, B, C, etc.
                                    text: header
                                }));
                            });
                        });

                        // Show mapping section and import button
                        $('#mappingSection').slideDown();
                        $('#importButton').show();

                        // Update preview table
                        updatePreviewTable();
                    } else if (response.sweet_alert) {
                        Swal.fire({
                            icon: response.sweet_alert.type,
                            title: response.sweet_alert.title,
                            text: response.sweet_alert.message
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to process the file. Please try again.'
                    });
                }
            });
        }
    });

    // Handle column mapping changes
    $('.mapping-select').change(function() {
        updatePreviewTable();
    });

    // Handle form submission
    $('#importForm').submit(function(e) {
        e.preventDefault();

        // Show confirmation dialog
        Swal.fire({
            title: 'Confirm Import',
            text: 'Are you sure you want to import these students?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, import',
            cancelButtonText: 'No, cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData(this);

                // Show loading state
                Swal.fire({
                    title: 'Importing Students',
                    text: 'Please wait while we import the data...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '{{ route("students.import") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.sweet_alert) {
                            Swal.fire({
                                icon: response.sweet_alert.type,
                                title: response.sweet_alert.title,
                                text: response.sweet_alert.message,
                                showConfirmButton: true
                            }).then((result) => {
                                if (response.success) {
                                    window.location.href = '{{ route("students.index") }}';
                                }
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Import Failed',
                            text: 'An error occurred while importing students. Please try again.'
                        });
                    }
                });
            }
        });
    });

    function updatePreviewTable() {
        const previewSection = $('.preview-section');
        const previewTable = $('#previewTable');
        
        // Clear existing preview
        previewTable.find('thead tr').empty();
        previewTable.find('tbody').empty();

        // Get selected columns
        const selectedColumns = {};
        $('.mapping-select').each(function() {
            const field = $(this).attr('name').match(/\[(.*?)\]/)[1];
            const column = $(this).val();
            if (column) {
                selectedColumns[field] = headers[column.charCodeAt(0) - 65];
            }
        });

        // If no columns selected, hide preview
        if (Object.keys(selectedColumns).length === 0) {
            previewSection.hide();
            return;
        }

        // Show preview section
        previewSection.show();

        // Add headers
        Object.values(selectedColumns).forEach(header => {
            previewTable.find('thead tr').append($('<th>', { text: header }));
        });

        // Add preview data
        previewData.forEach(row => {
            const tr = $('<tr>');
            Object.keys(selectedColumns).forEach(field => {
                const columnIndex = headers.indexOf(selectedColumns[field]);
                tr.append($('<td>', { text: row[columnIndex] || '' }));
            });
            previewTable.find('tbody').append(tr);
        });
    }
});
</script>
@endsection 