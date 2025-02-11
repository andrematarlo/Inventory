@extends('layouts.app')

@section('title', 'Employee Management')

@section('styles')
<style>
    /* Table container */
    .table-responsive {
        overflow: hidden;
        margin-bottom: 1rem;
        padding: 0;
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
        overflow-y: auto !important;
        overflow-x: auto !important;
        max-height: 60vh;
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
        background: #f1f1f1;
    }

    .dataTables_scrollBody::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 3px;
    }

    .dataTables_scrollBody::-webkit-scrollbar-thumb:hover {
        background: #a0aec0;
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

    /* Adjust other column widths */
    .actions-column { width: 120px !important; min-width: 120px !important; }
    .name-column { width: 200px !important; min-width: 200px !important; }
    .username-column { width: 180px !important; min-width: 180px !important; }
    .email-column { width: 220px !important; min-width: 220px !important; }
    .gender-column { width: 100px !important; min-width: 100px !important; }
    .status-column { width: 100px !important; min-width: 100px !important; }
    .created-by-column { width: 150px !important; min-width: 150px !important; }
    .modified-by-column { width: 150px !important; min-width: 150px !important; }
    .deleted-date-column { width: 150px !important; min-width: 150px !important; }

    /* Ensure table cells don't wrap by default except for role column */
    .table td:not(.role-column) {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* DataTables body container */
    .dataTables_scrollBody {
        min-height: 400px;
        max-height: 60vh;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Employee Management</h2>
        <div>
            <button class="btn btn-outline-secondary" type="button" id="toggleButton">
                <i class="bi bi-archive"></i> <span id="buttonText">Show Deleted</span>
            </button>
            @if(Auth::user()->role === 'Admin' || Auth::user()->role === 'Inventory Manager')
                <a href="{{ route('employees.create') }}" class="btn btn-primary">
                    <i class="bi bi-person-plus me-2"></i>Add Employee
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
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
                                <th>Actions</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Gender</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th>Modified By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activeEmployees as $employee)
                                <tr>
                                    <td>
                                        <div class="action-buttons">
                                            @if(Auth::user()->role === 'Admin' || Auth::user()->role === 'Inventory Manager')
                                                <a href="{{ route('employees.edit', $employee->EmployeeID) }}" 
                                                   class="btn btn-sm btn-primary me-2">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <form action="{{ route('employees.destroy', $employee->EmployeeID) }}" 
                                                      method="POST" 
                                                      class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" 
                                                            onclick="return confirm('Are you sure you want to delete this employee?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $employee->FirstName }} {{ $employee->LastName }}</td>
                                    <td>{{ $employee->Username }}</td>
                                    <td>{{ $employee->Email }}</td>
                                    <td>{{ $employee->Role }}</td>
                                    <td>{{ $employee->Gender }}</td>
                                    <td>
                                        <span class="badge bg-success">Active</span>
                                    </td>
                                    <td>{{ $employee->CreatedByFirstName }} {{ $employee->CreatedByLastName }}</td>
                                    <td>{{ $employee->ModifiedByFirstName }} {{ $employee->ModifiedByLastName }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">No active employees found</td>
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
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Gender</th>
                                <th>Deleted Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($deletedEmployees as $employee)
                                <tr>
                                    <td>{{ $employee->FirstName }} {{ $employee->LastName }}</td>
                                    <td>{{ $employee->Username }}</td>
                                    <td>{{ $employee->Email }}</td>
                                    <td>{{ $employee->Role }}</td>
                                    <td>{{ $employee->Gender }}</td>
                                    <td>{{ $employee->DateDeleted }}</td>
                                    <td>
                                        <form action="{{ route('employees.restore', ['employeeId' => $employee->EmployeeID]) }}" 
                                              method="POST" 
                                              class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No deleted employees found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let commonConfig = {
        pageLength: 10,
        scrollY: '60vh',
        scrollX: true,
        scrollCollapse: true,
        autoWidth: false,
        fixedHeader: {
            header: true,
            headerOffset: 0
        },
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        language: {
            search: "Search:",
            searchPlaceholder: "Search records..."
        }
    };

    // Active employees table
    let activeTable = $('#employeesTable').DataTable({
        ...commonConfig,
        columnDefs: [
            { className: "actions-column", targets: 0, width: "120px" },
            { className: "name-column", targets: 1, width: "200px" },
            { className: "username-column", targets: 2, width: "180px" },
            { className: "email-column", targets: 3, width: "220px" },
            { className: "role-column", targets: 4, width: "250px", render: function(data, type, row) {
                if (type === 'display') {
                    return '<div class="role-content">' + data + '</div>';
                }
                return data;
            }},
            { className: "gender-column", targets: 5, width: "100px" },
            { className: "status-column", targets: 6, width: "100px" },
            { className: "created-by-column", targets: 7, width: "150px" },
            { className: "modified-by-column", targets: 8, width: "150px" }
        ],
        order: [[1, 'asc']],
        createdRow: function(row, data, dataIndex) {
            // Add max-width to role column cells
            $(row).find('.role-column').css('max-width', '250px');
        }
    });

    // Deleted employees table
    let deletedTable = $('#deletedEmployeesTable').DataTable({
        ...commonConfig,
        columnDefs: [
            { className: "name-column", targets: 0, width: "200px" },
            { className: "username-column", targets: 1, width: "180px" },
            { className: "email-column", targets: 2, width: "220px" },
            { className: "role-column", targets: 3, width: "250px", render: function(data, type, row) {
                if (type === 'display') {
                    return '<div class="role-content">' + data + '</div>';
                }
                return data;
            }},
            { className: "gender-column", targets: 4, width: "100px" },
            { className: "deleted-date-column", targets: 5, width: "150px" },
            { className: "actions-column", targets: 6, width: "120px", orderable: false }
        ],
        order: [[0, 'asc']],
        createdRow: function(row, data, dataIndex) {
            // Add max-width to role column cells
            $(row).find('.role-column').css('max-width', '250px');
        }
    });

    // Function to adjust columns and redraw tables
    function adjustTables() {
        activeTable.columns.adjust();
        deletedTable.columns.adjust();
    }

    // Adjust on window resize
    window.addEventListener('resize', adjustTables);

    // Toggle functionality
    const toggleButton = document.getElementById('toggleButton');
    const activeDiv = document.getElementById('activeEmployees');
    const deletedDiv = document.getElementById('deletedEmployees');
    const buttonText = document.getElementById('buttonText');

    function showActiveTable() {
        deletedDiv.style.display = 'none';
        activeDiv.style.display = 'block';
        buttonText.textContent = 'Show Deleted';
        toggleButton.classList.remove('btn-outline-primary');
        toggleButton.classList.add('btn-outline-secondary');
        setTimeout(adjustTables, 0);
    }

    function showDeletedTable() {
        activeDiv.style.display = 'none';
        deletedDiv.style.display = 'block';
        buttonText.textContent = 'Show Active';
        toggleButton.classList.remove('btn-outline-secondary');
        toggleButton.classList.add('btn-outline-primary');
        setTimeout(adjustTables, 0);
    }

    toggleButton.addEventListener('click', function() {
        if (activeDiv.style.display !== 'none') {
            showDeletedTable();
        } else {
            showActiveTable();
        }
    });

    // Initial setup
    showActiveTable();
    
    // Initial column adjustment
    setTimeout(adjustTables, 100);
});
</script>
@endsection
