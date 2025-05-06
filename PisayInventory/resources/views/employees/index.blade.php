@extends('layouts.app')

@section('title', 'Employee Management')

@section('styles')
<style>

    h2.m-0 {
        color: #2d3748;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }

    /* Button container styling */
    .d-flex.gap-2.flex-nowrap {
        white-space: nowrap;
        min-width: fit-content;
    }

    /* Ensure buttons don't shrink */
    .btn {
        flex-shrink: 0;
    }
    /* Table container */
    .table-responsive {
        overflow: hidden; /* Change from auto to visible */
        margin-bottom: 1rem;
        padding: 0;
        width: 100%;
    }

    /* Table styles */
    .table {
        width: 100%;
        margin-bottom: 0;
        white-space: nowrap;
    }

    /* Header styles */
    .table thead th {
        position: sticky;
        top: 0;
        background: #f8f9fa;
        z-index: 1000;
        padding: 12px 16px;
        vertical-align: middle;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
    }

    /* Fix header background when scrolling */
    .dataTables_scrollHead {
        background: #f8f9fa;
        position: sticky;
        top: 0;
        z-index: 1000;
    }

    .dataTables_scrollBody {
    position: relative;
    max-height: 60vh !important;
    overflow-y: scroll !important; /* change from auto to scroll */
    overflow-x: scroll !important; /* change from auto to scroll */
    width: 100%;
}

    /* Ensure header cells align with body cells */
    .dataTables_scrollHead .table,
    .dataTables_scrollBody .table {
        margin: 0;
        table-layout: fixed;
    }

    /* Specific column widths */
    .table th.actions-column { min-width: 120px; }
    .table th.name-column { min-width: 200px; }
    .table th.username-column { min-width: 150px; }
    .table th.email-column { min-width: 200px; }
    .table th.role-column { min-width: 150px; }
    .table th.gender-column { min-width: 100px; }
    .table th.status-column { min-width: 100px; }
    .table th.created-by-column { min-width: 150px; }
    .table th.modified-by-column { min-width: 150px; }

    /* DataTables Scrollbar Styling */
    .dataTables_scrollBody::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .dataTables_scrollBody::-webkit-scrollbar-track {
        background:rgb(209, 209, 209);
    }

    .dataTables_scrollBody::-webkit-scrollbar-thumb {
        background:rgb(172, 181, 190);
        border-radius: 6px;
    }

    .dataTables_scrollBody::-webkit-scrollbar-thumb:hover {
        background:rgb(123, 133, 146);
    }

    /* DataTables Styling */
    .dataTables_wrapper {
        padding: 0;
        margin-top: 1rem;
       
       
     
       
    }

    .dataTables_length,
    .dataTables_filter {
        margin-bottom: 1rem;
        padding: 0 1rem;
    }

    .dataTables_info,
    .dataTables_paginate {
        margin-top: 1rem;
        padding: 1rem;
    }

    .dataTables_paginate .paginate_button {
        padding: 0.375rem 0.75rem !important;
        margin: 0 0.2rem !important;
        border-radius: 10px !important;
        background: white !important;
        color: #0d6efd !important;
    }

    .dataTables_paginate .paginate_button:hover {
        background: #e9ecef !important;
        border-color: #dee2e6 !important;
        color: #0a58ca !important;
    }

    .dataTables_paginate .paginate_button.current {
        background-color: #0d6efd !important;
        border-color: #0d6efd !important;
        color: white !important;
    }

    .dataTables_paginate .paginate_button.disabled {
        color: #6c757d !important;
        cursor: not-allowed;
        opacity: 0.6;
    }

    /* Card Styling */
    .card {
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        margin-bottom: 2rem;
    }

    .card-header {
        background-color: #fff;
        border-bottom: 1px solid #dee2e6;
        padding: 1rem 1.5rem;
    }

    .card-body {
        padding: 0;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
        min-width: 100px;
    }

    /* Status Badge */
    .badge {
        padding: 0.5em 0.75em;
        font-size: 0.85rem;
        font-weight: 500;
    }

    /* Form Controls */
    .dataTables_length select {
        min-width: 80px;
        padding: 0.375rem 1.75rem 0.375rem 0.75rem;
    }

    .dataTables_filter input {
        min-width: 200px;
        padding: 0.375rem 0.75rem;
    }

    /* Responsive */
    @media screen and (max-width: 1200px) {
        .table-responsive {
            overflow-x: auto;
        }
    }

    /* Role column specific styling */
    .role-column {
        white-space: normal !important; /* Allow text wrapping */
        min-width: 250px !important; /* Increase min-width */
        max-width: 250px !important;
    }

    .table td.role-column {
        white-space: normal;
        word-wrap: break-word;
        padding: 8px 12px;
    }

    

    /* Ensure table cells don't wrap by default except for role column */
    .table td:not(.role-column) {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    
    /* Ensure the table header stays fixed */
    .dataTables_scrollHead {
        overflow: hidden !important;
        position: sticky !important;
        top: 0;
        z-index: 10;
    }
    
    /* Make sure the table takes full width */
    table.dataTable {
        width: 100% !important;
        min-width: 1200px; /* Add a minimum width to prevent squishing */
}

    /* Add these styles for the delete modal */
    .modal-dialog-centered {
        display: flex;
        align-items: center;
        min-height: calc(100% - 1rem);
    }

    .bi-exclamation-circle {
        color: #ffc107;
    }

    .modal-body {
        padding: 2rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="m-0">Employee Management</h2>
            </div>
            <div class="d-flex justify-content-end gap-2 flex-nowrap">
                <button class="btn btn-outline-secondary" type="button" id="toggleButton">
                    <i class="bi bi-archive"></i> <span id="buttonText">Show Deleted</span>
                </button>
                @if($userPermissions && $userPermissions->CanAdd)
                    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#exportModal">
                        <i class="bi bi-download"></i> Export Employees
                    </button>
                    <button type="button" class="btn btn-success" id="openExcelImportBtn">
                        <i class="bi bi-upload"></i> Import Employees
                    </button>
                    <a href="{{ route('employees.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Add Employee
                    </a>
                @endif
            </div>
        </div>

    @if(session('success'))
        <!-- Success alert removed as requested -->
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Active Employees Table -->
    <div id="activeEmployees">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Active Employees</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="employeesTable">
                        <thead>
                            <tr>
                                <th class="text-center">Actions</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Gender</th>
                                <th>Roles</th>
                                <th>Created By</th>
                                <th>Date Created</th>
                                <th>Modified By</th>
                                <th>Date Modified</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activeEmployees as $employee)
                                <tr>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            {{-- Only show edit button if user has edit permission --}}
                                            @if($userPermissions && $userPermissions->CanEdit)
                                                <a href="{{ route('employees.edit', $employee->EmployeeID) }}" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endif

                                            {{-- Only show delete button if user has delete permission --}}
                                            @if($userPermissions && $userPermissions->CanDelete)
                                                <button type="button" 
                                                        class="btn btn-sm btn-danger" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteModal{{ $employee->EmployeeID }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $employee->FirstName }} {{ $employee->LastName }}</td>
                                    <td>{{ $employee->Email }}</td>
                                    <td>{{ $employee->Gender }}</td>
                                    <td>{{ $employee->Role ?: 'No Role Assigned' }}</td>
                                    <td>
                                        @php
                                            Log::info('Employee Creator Debug:', [
                                                'employee_id' => $employee->EmployeeID,
                                                'created_by_id' => $employee->CreatedByID,
                                                'created_by' => $employee->createdBy,
                                                'created_by_name' => $employee->CreatedByName
                                            ]);
                                        @endphp
                                        {{ $employee->CreatedByName }}
                                    </td>
                                    <td>{{ $employee->DateCreated->format('M d, Y') }}</td>
                                    <td>{{ $employee->modifiedBy ? $employee->modifiedBy->FirstName . ' ' . $employee->modifiedBy->LastName : '-' }}</td>
                                    <td>{{ $employee->DateModified ? $employee->DateModified->format('M d, Y') : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">No employees found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Deleted Employees Table -->
    <div id="deletedEmployees" style="display: none;">
        <div class="card mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Deleted Employees</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="deletedEmployeesTable">
                        <thead>
                            <tr>
                                <th class="text-center">Actions</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Gender</th>
                                <th>Deleted Date</th>
                                <th>Created By</th>
                                <th>Modified By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($deletedEmployees as $employee)
                                <tr>
                                    <td class="text-center">
                                        @if($userPermissions && $userPermissions->CanEdit)
                                        <button type="button" class="btn btn-sm btn-success restore-employee" 
                                               data-employee-id="{{ $employee->EmployeeID }}"
                                               data-employee-name="{{ $employee->FirstName }} {{ $employee->LastName }}">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                        @endif
                                    </td>
                                    <td>{{ $employee->FirstName }} {{ $employee->LastName }}</td>
                                    <td>{{ $employee->userAccount ? $employee->userAccount->Username : 'N/A' }}</td>
                                    <td>{{ $employee->Email }}</td>
                                    <td>{{ $employee->Role }}</td>
                                    <td>{{ $employee->Gender }}</td>
                                    <td>{{ $employee->DateDeleted }}</td>
                                    <td>{{ $employee->createdBy ? $employee->createdBy->FirstName . ' ' . $employee->createdBy->LastName : 'System User' }}</td>
                                    <td>{{ $employee->modifiedBy ? $employee->modifiedBy->FirstName . ' ' . $employee->modifiedBy->LastName : 'System User' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">No deleted employees found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Standalone Import Modal -->
<div class="modal fade" 
     id="standaloneImportModal" 
     data-bs-backdrop="static" 
     data-bs-keyboard="false" 
     tabindex="-1" 
     role="dialog" 
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Employees</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Step 1: File Selection -->
                <div id="importStep1">
                    <div class="mb-3">
                        <label for="excelFileInput" class="form-label">Select Excel File</label>
                        <input type="file" class="form-control" id="excelFileInput" accept=".xlsx, .xls">
                    </div>
                    @if($userPermissions && $userPermissions->CanAdd)
                    <button type="button" id="readExcelBtn" class="btn btn-primary">Preview Columns</button>
                    @endif
                </div>
                
                <!-- Step 2: Column Mapping -->
                <div id="importStep2" style="display: none;">
                    <h6 class="mb-3">Map Excel Columns to Employee Fields</h6>
                    <form id="standaloneImportForm">
                        <div class="mb-3">
                            <label for="firstNameColumn" class="form-label">First Name</label>
                            <select id="firstNameColumn" name="column_mapping[FirstName]" class="form-select" required></select>
                        </div>
                        <div class="mb-3">
                            <label for="lastNameColumn" class="form-label">Last Name</label>
                            <select id="lastNameColumn" name="column_mapping[LastName]" class="form-select" required></select>
                        </div>
                        <div class="mb-3">
                            <label for="emailColumn" class="form-label">Email</label>
                            <select id="emailColumn" name="column_mapping[Email]" class="form-select" ></select>
                        </div>
                        <div class="mb-3">
                            <label for="addressColumn" class="form-label">Address</label>
                            <select id="addressColumn" name="column_mapping[Address]" class="form-select" ></select>
                        </div>
                        <div class="mb-3">
                            <label for="genderColumn" class="form-label">Gender</label>
                            <select id="genderColumn" name="column_mapping[Gender]" class="form-select" ></select>
                        </div>
                        <div class="mb-3">
                            <label for="roleColumn" class="form-label">Role</label>
                            <select id="roleColumn" name="column_mapping[Role]" class="form-select" ></select>
                        </div>
                        @if($userPermissions && $userPermissions->CanAdd)
                        <button type="submit" class="btn btn-success">Import Data</button>
                        @endif
                        <button type="button" class="btn btn-secondary" id="backToStep1Btn">Back</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" 
     id="exportModal" 
     data-bs-backdrop="static" 
     data-bs-keyboard="false" 
     tabindex="-1" 
     aria-labelledby="exportModalLabel" 
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel">Export Employees</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('employees.export') }}" method="POST" id="exportForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Format</label>
                        <select name="format" class="form-select" required>
                            <option value="xlsx">Excel (XLSX)</option>
                            <option value="csv">CSV</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select Records to Export</label>
                        <select name="employees_status" class="form-select" required>
                            <option value="active">Active Employees Only</option>
                            <option value="deleted">Deleted Employees Only</option>
                            <option value="all">All Employees</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select Fields to Export</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fields[]" value="FirstName" checked>
                            <label class="form-check-label">First Name</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fields[]" value="LastName" checked>
                            <label class="form-check-label">Last Name</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fields[]" value="Email" checked>
                            <label class="form-check-label">Email</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fields[]" value="Gender" checked>
                            <label class="form-check-label">Gender</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fields[]" value="Role" checked>
                            <label class="form-check-label">Role</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fields[]" value="Address" checked>
                            <label class="form-check-label">Address</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    @if($userPermissions && $userPermissions->CanAdd)
                    <button type="submit" class="btn btn-primary">Export</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Employee Modal -->
<div class="modal fade" 
     id="addEmployeeModal" 
     data-bs-backdrop="static" 
     data-bs-keyboard="false" 
     tabindex="-1" 
     aria-labelledby="addEmployeeModalLabel" 
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEmployeeModalLabel">Add New Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('employees.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="FirstName" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="FirstName" name="FirstName" required>
                    </div>
                    <div class="mb-3">
                        <label for="LastName" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="LastName" name="LastName" required>
                    </div>
                    <div class="mb-3">
                        <label for="Email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="Email" name="Email" required>
                    </div>
                    <div class="mb-3">
                        <label for="Gender" class="form-label">Gender</label>
                        <select class="form-select" id="Gender" name="Gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="Address" class="form-label">Address</label>
                        <textarea class="form-control" id="Address" name="Address" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($activeEmployees as $employee)
<div class="modal fade" 
     id="deleteModal{{ $employee->EmployeeID }}" 
     data-bs-backdrop="static" 
     data-bs-keyboard="false" 
     tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="mb-4">
                    <i class="bi bi-exclamation-circle text-warning" style="font-size: 3rem;"></i>
                </div>
                <h4 class="mb-3">Are you sure?</h4>
                <p class="mb-4">You won't be able to revert this!</p>
                <form action="{{ route('employees.destroy', $employee->EmployeeID) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                    @if($userPermissions && $userPermissions->CanDelete)
                    <button type="submit" class="btn btn-danger">Yes, delete it!</button>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
// Use an immediately invoked function expression (IIFE) to avoid conflicts
(function() {
    // Wait until DOM is fully loaded
    window.addEventListener('DOMContentLoaded', function() {
        // Get elements
        const openModalBtn = document.getElementById('openExcelImportBtn');
        const modal = document.getElementById('standaloneImportModal');
        const fileInput = document.getElementById('excelFileInput');
        const readExcelBtn = document.getElementById('readExcelBtn');
        const step1 = document.getElementById('importStep1');
        const step2 = document.getElementById('importStep2');
        const backBtn = document.getElementById('backToStep1Btn');
        const importForm = document.getElementById('standaloneImportForm');
        
        // Initialize Bootstrap modal
        let bootstrapModal = null;
        
        // Open modal button click handler
        if (openModalBtn) {
            openModalBtn.addEventListener('click', function() {
                if (typeof bootstrap !== 'undefined') {
                    bootstrapModal = new bootstrap.Modal(modal);
                    bootstrapModal.show();
                } else {
                    // Fallback if Bootstrap JS is not available
                    modal.style.display = 'block';
                }
            });
        }
        
        // Preview button click handler
        if (readExcelBtn) {
            readExcelBtn.addEventListener('click', function() {
                if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                    alert('Please select an Excel file first');
                    return;
                }
                
                const file = fileInput.files[0];
                readExcelBtn.textContent = 'Loading...';
                readExcelBtn.disabled = true;
                
                // Read the Excel file
                const reader = new FileReader();
                reader.onload = function(e) {
                    try {
                        const data = new Uint8Array(e.target.result);
                        const workbook = XLSX.read(data, { type: 'array' });
                        
                        // Get first sheet
                        const firstSheetName = workbook.SheetNames[0];
                        const worksheet = workbook.Sheets[firstSheetName];
                        
                        // Get headers (first row)
                        const headers = [];
                        const range = XLSX.utils.decode_range(worksheet['!ref']);
                        
                        for (let col = range.s.c; col <= range.e.c; col++) {
                            const cellAddress = XLSX.utils.encode_cell({ r: range.s.r, c: col });
                            const cell = worksheet[cellAddress];
                            
                            if (cell && cell.v) {
                                headers.push(cell.v.toString().trim());
                            }
                        }
                        
                        // Populate all select elements
                        const selects = document.querySelectorAll('#standaloneImportForm select');
                        
                        selects.forEach(select => {
                            // Clear existing options
                            select.innerHTML = '<option value="">Select a column</option>';
                            
                            // Add header options
                            headers.forEach(header => {
                                const option = document.createElement('option');
                                option.value = header;
                                option.textContent = header;
                                select.appendChild(option);
                            });
                            
                            // Try to auto-map based on field name
                            const fieldName = select.name.match(/\[(.*?)\]/)[1].toLowerCase();
                            
                            for (const header of headers) {
                                if (header.toLowerCase().includes(fieldName.toLowerCase())) {
                                    select.value = header;
                                    break;
                                }
                            }
                        });
                        
                        // Show mapping form
                        step1.style.display = 'none';
                        step2.style.display = 'block';
                        
                    } catch (error) {
                        console.error('Error processing Excel file:', error);
                        alert('Error processing Excel file: ' + error.message);
                    }
                    
                    // Reset button state
                    readExcelBtn.textContent = 'Preview Columns';
                    readExcelBtn.disabled = false;
                };
                
                reader.onerror = function() {
                    alert('Error reading file');
                    readExcelBtn.textContent = 'Preview Columns';
                    readExcelBtn.disabled = false;
                };
                
                reader.readAsArrayBuffer(file);
            });
        }
        
        // Back button click handler
        if (backBtn) {
            backBtn.addEventListener('click', function() {
                step1.style.display = 'block';
                step2.style.display = 'none';
            });
        }
        
        // Form submission handler
        if (importForm) {
            importForm.addEventListener('submit', function(e) {
                e.preventDefault();

                // Check if file is selected
                const fileInput = document.getElementById('excelFileInput');
                if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                    alert('Please select a file first');
                    return;
                }

                // Check if all required fields are mapped
                const selects = importForm.querySelectorAll('select');
                const columnMapping = {};
                const unmappedFields = [];

                const requiredFields = ['FirstName', 'LastName'];
selects.forEach(select => {
    const fieldName = select.name.match(/\[(.*?)\]/)[1];
    if (requiredFields.includes(fieldName) && !select.value) {
        unmappedFields.push(fieldName);
    }
    if (select.value) {
        columnMapping[fieldName] = select.value;
    }
});

                if (unmappedFields.length > 0) {
                    alert(`Please map the following required fields: ${unmappedFields.join(', ')}`);
                    return;
                }

                // Create FormData object
                const formData = new FormData();
                formData.append('file', fileInput.files[0]);
                
                // Add column mappings
                Object.entries(columnMapping).forEach(([key, value]) => {
                    formData.append(`column_mapping[${key}]`, value);
                });

                // Show loading state
                const submitBtn = importForm.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Importing...';

                // Send data to server
                fetch('{{ route("employees.import") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message || 'Import successful!');
                        window.location.reload();
                    } else {
                        throw new Error(data.error || 'Import failed');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert(error.message || 'Import failed. Please try again.');
                })
                .finally(() => {
                    // Reset button state
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalBtnText;
                });
            });
        }

        // Initialize modals with static backdrop
        const importModal = document.getElementById('standaloneImportModal');
        const addModal = document.getElementById('addEmployeeModal');

        if (importModal) {
            // Prevent closing when clicking outside
            $(importModal).on('click mousedown', function(e) {
                if ($(e.target).hasClass('modal')) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            });

            // Prevent Esc key from closing the modal
            $(importModal).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    e.preventDefault();
                    return false;
                }
            });
        }

        if (addModal) {
            // Prevent closing when clicking outside
            $(addModal).on('click mousedown', function(e) {
                if ($(e.target).hasClass('modal')) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            });

            // Prevent Esc key from closing the modal
            $(addModal).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    e.preventDefault();
                    return false;
                }
            });
        }
    });
})();

// Add this script to fix table scrolling
(function() {
    document.addEventListener('DOMContentLoaded', function() {
        // Find your employees table
        const employeesTable = document.querySelector('#employeesTable'); // Replace with your actual table ID
        
        if (!employeesTable) {
            console.warn('Employees table not found');
            return;
        }
        
        // Check if DataTable is already initialized
        if ($.fn.DataTable.isDataTable(employeesTable)) {
            // Destroy existing DataTable
            $(employeesTable).DataTable().destroy();
        }
        
        // Initialize DataTable with proper scrolling options
        $(employeesTable).DataTable({
            scrollY: '60vh',        // Set a fixed height for vertical scrolling
            scrollX: true,          // Enable horizontal scrolling
            scrollCollapse: true,   // Enable scroll collapse
            paging: true,          // Enable pagination
            responsive: false,      // Disable responsive to prevent column wrapping
            autoWidth: false,       // Disable auto width calculation
            fixedHeader: {
                header: true,       // Fix the header
                headerOffset: 0     // Header offset
            },
            columnDefs: [          // Define column widths
                { targets: 0, width: '100px' },  // Actions
                { targets: 1, width: '200px' },  // Name
                { targets: 2, width: '200px' },  // Email
                { targets: 3, width: '100px' },  // Gender
                { targets: 4, width: '150px' },  // Roles
                { targets: 5, width: '150px' },  // Created By
                { targets: 6, width: '120px' },  // Date Created
                { targets: 7, width: '150px' },  // Modified By
                { targets: 8, width: '120px' }   // Date Modified
            ],
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            language: {
                search: "Search:",
                searchPlaceholder: "Search records..."
            }
        });
        


        // Toggle button functionality
        const toggleButton = document.getElementById('toggleButton');
        const buttonText = document.getElementById('buttonText');
        const activeEmployees = document.getElementById('activeEmployees');
        const deletedEmployees = document.getElementById('deletedEmployees');
        
        if (toggleButton) {
            toggleButton.addEventListener('click', function() {
                // Toggle visibility
                if (activeEmployees.style.display !== 'none') {
                    activeEmployees.style.display = 'none';
                    deletedEmployees.style.display = 'block';
                    buttonText.textContent = 'Show Active';
                    toggleButton.classList.remove('btn-outline-secondary');
                    toggleButton.classList.add('btn-outline-primary');
                } else {
                    activeEmployees.style.display = 'block';
                    deletedEmployees.style.display = 'none';
                    buttonText.textContent = 'Show Deleted';
                    toggleButton.classList.remove('btn-outline-primary');
                    toggleButton.classList.add('btn-outline-secondary');
                }
            
            // Initialize DataTable for deleted employees table if not already initialized
            const deletedTable = document.getElementById('deletedEmployeesTable');
            if (deletedTable && !$.fn.DataTable.isDataTable(deletedTable)) {
                $(deletedTable).DataTable({
                    scrollY: '60vh',
                    scrollX: true,
                    scrollCollapse: true,
                    paging: true,
                    responsive: false,
                    autoWidth: false,
                    fixedHeader: {
                        header: true,
                        headerOffset: 0
                    },
                    columnDefs: [
                        { targets: 0, width: '100px' },
                        { targets: 1, width: '200px' },
                        { targets: 2, width: '150px' },
                        { targets: 3, width: '200px' },
                        { targets: 4, width: '150px' },
                        { targets: 5, width: '100px' },
                        { targets: 6, width: '150px' },
                        { targets: 7, width: '150px' },
                        { targets: 8, width: '150px' }
                    ],
                    dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                         "<'row'<'col-sm-12'tr>>" +
                         "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                    language: {
                        search: "Search:",
                        searchPlaceholder: "Search records..."
                    }
                });
            }
        });
    }
});




})();

// Update the column mappings in the JavaScript
const columnMappings = {
    'Name': ['name', 'full name', 'fullname', 'complete name'], // Add this line
    'FirstName': ['first name', 'firstname', 'given name', 'first'],
    'LastName': ['last name', 'lastname', 'surname', 'family name', 'last'],
    'Email': ['email', 'e-mail', 'mail', 'email address'],
    'Address': ['address', 'addr', 'location'],
    'Gender': ['gender', 'sex'],
    'Role': ['role', 'roles', 'position', 'designation']
};

document.addEventListener('DOMContentLoaded', function() {
    // Add this to your existing script
    const deleteModals = document.querySelectorAll('[id^="deleteModal"]');
    deleteModals.forEach(modal => {
        // Prevent closing when clicking outside
        $(modal).on('click mousedown', function(e) {
            if ($(e.target).hasClass('modal')) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });

        // Prevent Esc key from closing the modal
        $(modal).on('keydown', function(e) {
            if (e.key === 'Escape') {
                e.preventDefault();
                return false;
            }
        });
    });

    // Rest of your existing script...
});

// Add restore confirmation handler
$('.restore-employee').click(function(e) {
    e.preventDefault();
    
    const employeeId = $(this).data('employee-id');
    const employeeName = $(this).data('employee-name');
    
    Swal.fire({
        title: 'Restore Employee?',
        html: `Are you sure you want to restore employee: <strong>${employeeName}</strong>?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, restore it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create and submit the form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/inventory/employees/${employeeId}/restore`;
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            
            form.appendChild(csrfInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
});

$(document).ready(function() {
    // Initialize export modal with static backdrop
    const exportModal = document.getElementById('exportModal');
    if (exportModal) {
        const bsModal = new bootstrap.Modal(exportModal, {
            backdrop: 'static',
            keyboard: false
        });

        // Prevent modal from closing when clicking outside
        $(exportModal).on('mousedown', function(e) {
            if ($(e.target).is('.modal')) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });

        // Handle export form submission
        $('#exportForm').on('submit', function() {
            // Close the modal
            $('#exportModal').modal('hide');
        });
    }

    // ... rest of your existing code ...
});

</script>
@endsection

