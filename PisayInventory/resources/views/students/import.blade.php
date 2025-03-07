@extends('layouts.app')

@section('title', 'Import Students')

@section('content')
<div class="container-fluid px-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Import Students</h4>
            <a href="{{ route('students.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Students
            </a>
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

                    <form id="importForm" action="{{ route('students.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label for="file" class="form-label">Excel File</label>
                            <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls" required>
                        </div>

                        <div id="mappingSection" style="display: none;">
                            <h5 class="mb-3">Column Mapping</h5>
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Excel Column</th>
                                            <th>Map to Field</th>
                                            <th>Required</th>
                                            <th>Preview</th>
                                        </tr>
                                    </thead>
                                    <tbody id="mappingTable">
                                        <!-- Will be populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>

                            <div id="previewSection" class="mb-4" style="display: none;">
                                <h5>Data Preview</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead id="previewHeader">
                                            <!-- Will be populated by JavaScript -->
                                        </thead>
                                        <tbody id="previewBody">
                                            <!-- Will be populated by JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" onclick="window.history.back()">Cancel</button>
                            <div>
                                <button type="button" id="previewBtn" class="btn btn-info me-2" style="display: none;">
                                    Preview Data
                                </button>
                                <button type="submit" id="importBtn" class="btn btn-primary" style="display: none;">
                                    Import Students
                                </button>
                            </div>
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
const availableFields = {
    'student_id': { label: 'Student ID', required: true },
    'first_name': { label: 'First Name', required: true },
    'last_name': { label: 'Last Name', required: true },
    'middle_name': { label: 'Middle Name', required: false },
    'email': { label: 'Email', required: false },
    'contact_number': { label: 'Contact Number', required: false },
    'gender': { label: 'Gender', required: false },
    'grade_level': { label: 'Grade Level', required: false },
    'section': { label: 'Section', required: false }
};

let excelData = null;

document.getElementById('file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('file', file);
    formData.append('_token', '{{ csrf_token() }}');

    // Show loading state
    Swal.fire({
        title: 'Reading file...',
        html: 'Please wait while we process your file.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Send file for preview
    fetch('{{ route("students.preview-columns") }}', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            excelData = data;
            showMapping(data.headers, data.preview_data);
            Swal.close();
        } else {
            throw new Error(data.message || 'Error processing file');
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Failed to process the file. Please try again.'
        });
    });
});

function showMapping(headers, previewData) {
    const mappingTable = document.getElementById('mappingTable');
    mappingTable.innerHTML = '';

    headers.forEach((header, index) => {
        const row = document.createElement('tr');
        
        // Excel Column
        const excelCol = document.createElement('td');
        excelCol.textContent = header;
        row.appendChild(excelCol);

        // Field Mapping
        const mappingCol = document.createElement('td');
        const select = document.createElement('select');
        select.className = 'form-select';
        select.name = `column_mapping[${index}]`;
        
        // Add empty option
        select.innerHTML = '<option value="">-- Select Field --</option>';
        
        // Add options for each available field
        Object.entries(availableFields).forEach(([value, field]) => {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = field.label;
            // Try to auto-match columns
            if (header.toLowerCase().includes(value.toLowerCase())) {
                option.selected = true;
            }
            select.appendChild(option);
        });
        
        mappingCol.appendChild(select);
        row.appendChild(mappingCol);

        // Required Field
        const requiredCol = document.createElement('td');
        requiredCol.className = 'text-center';
        requiredCol.innerHTML = '<i class="bi bi-x text-muted"></i>';
        row.appendChild(requiredCol);

        // Preview Data
        const previewCol = document.createElement('td');
        previewCol.textContent = previewData[0] ? previewData[0][index] || '' : '';
        row.appendChild(previewCol);

        mappingTable.appendChild(row);
    });

    // Show mapping section and buttons
    document.getElementById('mappingSection').style.display = 'block';
    document.getElementById('previewBtn').style.display = 'inline-block';
    document.getElementById('importBtn').style.display = 'inline-block';

    // Update required indicators when mapping changes
    const selects = document.querySelectorAll('select[name^="column_mapping"]');
    selects.forEach(select => {
        select.addEventListener('change', updateRequiredIndicators);
    });
    updateRequiredIndicators();
}

function updateRequiredIndicators() {
    const selects = document.querySelectorAll('select[name^="column_mapping"]');
    selects.forEach(select => {
        const row = select.closest('tr');
        const requiredCol = row.querySelector('td:nth-child(3)');
        const selectedField = select.value;
        
        if (selectedField && availableFields[selectedField]) {
            requiredCol.innerHTML = availableFields[selectedField].required ? 
                '<i class="bi bi-check-circle-fill text-success"></i>' :
                '<i class="bi bi-dash text-muted"></i>';
        } else {
            requiredCol.innerHTML = '<i class="bi bi-x text-muted"></i>';
        }
    });
}

document.getElementById('importForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Validate required fields
    const mappings = {};
    let hasAllRequired = true;
    const selects = document.querySelectorAll('select[name^="column_mapping"]');
    
    selects.forEach(select => {
        if (select.value) {
            mappings[select.value] = true;
        }
    });

    // Check if all required fields are mapped
    Object.entries(availableFields).forEach(([field, info]) => {
        if (info.required && !mappings[field]) {
            hasAllRequired = false;
        }
    });

    if (!hasAllRequired) {
        Swal.fire({
            icon: 'error',
            title: 'Missing Required Fields',
            text: 'Please map all required fields (Student ID, First Name, Last Name) before importing.'
        });
        return;
    }

    // Show confirmation dialog
    Swal.fire({
        title: 'Confirm Import',
        text: 'Are you sure you want to import these students?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, import',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Importing...',
                html: 'Please wait while we import the students.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Submit the form
            const formData = new FormData(this);
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: data.sweet_alert.type,
                        title: data.sweet_alert.title,
                        text: data.sweet_alert.message,
                        showCancelButton: true,
                        confirmButtonText: 'View Students',
                        cancelButtonText: 'Import More'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '{{ route("students.index") }}';
                        } else {
                            window.location.reload();
                        }
                    });
                } else {
                    throw new Error(data.sweet_alert.message || 'Import failed');
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Import Failed',
                    text: error.message || 'Failed to import students. Please try again.'
                });
            });
        }
    });
});

document.getElementById('previewBtn').addEventListener('click', function() {
    if (!excelData || !excelData.preview_data) return;

    const previewSection = document.getElementById('previewSection');
    const previewHeader = document.getElementById('previewHeader');
    const previewBody = document.getElementById('previewBody');
    
    // Clear previous preview
    previewHeader.innerHTML = '';
    previewBody.innerHTML = '';

    // Get current mappings
    const mappings = {};
    const selects = document.querySelectorAll('select[name^="column_mapping"]');
    selects.forEach((select, index) => {
        if (select.value) {
            mappings[index] = {
                field: select.value,
                label: availableFields[select.value].label
            };
        }
    });

    // Create header row
    const headerRow = document.createElement('tr');
    Object.values(mappings).forEach(mapping => {
        const th = document.createElement('th');
        th.textContent = mapping.label;
        headerRow.appendChild(th);
    });
    previewHeader.appendChild(headerRow);

    // Create data rows
    excelData.preview_data.forEach(row => {
        const dataRow = document.createElement('tr');
        Object.entries(mappings).forEach(([index, mapping]) => {
            const td = document.createElement('td');
            td.textContent = row[index] || '';
            dataRow.appendChild(td);
        });
        previewBody.appendChild(dataRow);
    });

    previewSection.style.display = 'block';
});
</script>
@endsection 