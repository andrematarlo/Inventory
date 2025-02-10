@extends('layouts.app')

<style>
    /* Global container */
    .container-fluid {
        padding: 20px 30px;
        position: relative;
    }

    /* Title styling */
    h2 {
        color: #2d3748;
        font-weight: 600 !important;
        font-size: 1.75rem !important;
        margin-bottom: 1.5rem !important;
        padding-top: 1rem !important;
        font-family: inherit !important;
        line-height: 1.2 !important;
    }

    /* Spacing for header */
    .d-flex.justify-content-between.align-items-center.mb-4 {
        margin-top: 0.5rem;
    }

    /* Card Styling */
    .card {
        background-color: #ffffff;
        border: none;
        border-radius: 15px !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .card-body {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    
    .table thead,
    .table tfoot {
        position: sticky;
        background: white;
        z-index: 15;
    }

    /* Sticky header */
    .table thead {
        top: 0;
    }

    /* Sticky footer */
    .table tfoot {
        bottom: 0;
    }

    /* Table Styling */
    .table th,
    .table td {
        text-align: center !important;
        vertical-align: middle !important;
        padding: 0.5rem !important;
        
    }

    .table th:nth-child(1), .table td:nth-child(1) { width: 150px !important; min-width: 150px !important; }
    .table th:nth-child(2), .table td:nth-child(2) { width: 200px !important; min-width: 200px !important; }
    .table th:nth-child(3), .table td:nth-child(3) { width: 150px !important; min-width: 150px !important; }
    .table th:nth-child(4), .table td:nth-child(4) { width: 250px !important; min-width: 250px !important; }
    .table th:nth-child(5), .table td:nth-child(5) { width: 100px !important; min-width: 100px !important; }
    .table th:nth-child(6), .table td:nth-child(6) { width: 100px !important; min-width: 100px !important; }
    .table th:nth-child(7), .table td:nth-child(7) { width: 100px !important; min-width: 100px !important; }
    .table th:nth-child(8), .table td:nth-child(8) { width: 150px !important; min-width: 150px !important; }
    .table th:nth-child(9), .table td:nth-child(9) { width: 150px !important; min-width: 150px !important; }
    .table td:nth-child(5) .badge {
        background: none !important;
        padding: 0 !important;
    }
    .table td:nth-child(2), .table td:nth-child(4), .table td:nth-child(3) {
        text-align: left !important;
    }
    .table th:nth-child(3) {
        text-align: left !important;
    }
    .table td:nth-child(5) .badge.bg-primary {
        color: rgb(201, 1, 1) !important;
    }

    .table td:nth-child(5) .badge.bg-info {
        color: #0d6efd !important;
    }
    .table td:first-child .d-flex.gap-2 {
        justify-content: center !important;
        align-items: center !important;
        
    }

    .table td:first-child .d-flex.gap-2 form {
        margin: 0 !important;
        display: flex !important;
        align-items: center !important;
    }

    .btn-primary {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        background-color: #198754 !important;  /* Bootstrap success green */
        border-color: #198754 !important;
    }
    .btn-primary:hover {
        background-color: #157347 !important;  /* Darker shade for hover */
        border-color: #157347 !important;
    }
    .btn-primary.btn-sm {
        background-color: #0366d6 !important;  /* Bootstrap primary blue */
        border-color: #0d6efd !important;
    }

    .btn-primary.btn-sm:hover {
        background-color:rgb(11, 88, 204) !important;  /* Darker shade for hover */
        border-color: #0b5ed7 !important;
    }
    .btn-danger.btn-sm {
        background-color:rgb(220, 53, 53) !important;  /* Bootstrap danger red */
        border-color: rgb(220, 53, 53)!important;
    }

    .btn-danger.btn-sm:hover {
        background-color: rgb(211, 46, 46) !important;  /* Darker shade for hover */
        border-color: rgb(211, 46, 46) !important;
    }

    
    .btn-sm {
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        height: 28px;  
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 2px;
        font-size: 14px !important;
    }

    /* Spacing between buttons */
    .d-flex.gap-2 {
        gap: 0.5rem !important;
    }

    /* Status Badges */
    .badge {
        padding: 0.5rem 0.8rem;
        border-radius: 6px;
        font-weight: 500;
    }

    /* Sticky DataTable Controls */
    .datatable-wrapper {
        position: relative;
    }

    .dataTables_wrapper {
        padding-top: 20px;
        padding-bottom: 20px;
    }

    .dataTables_length, .dataTables_filter {
        position: absolute;
        top: 0;
        background: white;
        padding: 10px;
        z-index: 20;
    }

    /* Stick the search bar and "Show entries" dropdown to the top corners */
    .dataTables_length {
        left: 0;
    }

    .dataTables_filter {
        right: 0;
    }

    .dataTables_info, .dataTables_paginate {
        position: absolute;
        bottom: 0;
        background: white;
        padding: 10px;
        z-index: 20;
    }

    /* Stick the pagination and "Showing X to X" info at bottom corners */
    .dataTables_info {
        left: 0;
        white-space: nowrap;
    }

    .dataTables_paginate {
        right: 0;
    }

    /* Alert Styling */
    .alert {
        border-radius: 10px;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
    }
    /* Table responsiveness */
    .table-responsive {
        overflow-x: auto;
        overflow-y: auto;
        max-height: 600px;
        position: relative;
        padding-top: 50px; /* Space for top controls */
        padding-bottom: 50px; /* Space for bottom controls */
    }


    /* Scrollbar Styling */
    .table-responsive::-webkit-scrollbar,
    .dataTables_scrollBody::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .table-responsive::-webkit-scrollbar-track,
    .dataTables_scrollBody::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .table-responsive::-webkit-scrollbar-thumb,
    .dataTables_scrollBody::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 3px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover,
    .dataTables_scrollBody::-webkit-scrollbar-thumb:hover {
        background: #a0aec0;
    }
    /* Pagination Buttons Styling */
    .dataTables_paginate .paginate_button {
        padding: 0.375rem 0.75rem !important;
        margin: 0 0.2rem !important;
        border-radius: 10px !important;
        background: white !important;
        color: #0d6efd !important;
    }

    /* Hover state */
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

</style>

@section('title', 'Employee Management')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Employee Management</h2>
        @if(Auth::user()->role === 'Admin' || Auth::user()->role === 'Inventory Manager')
            <a href="{{ route('employees.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus me-2"></i>Add Employee
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="datatable-wrapper">
                <div class="datatable-header"></div>
                
                <div class="datatable-container">
                    <div class="table-responsive">
                        <table class="table table-hover" id="employeesTable">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Full Name</th>
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
                                @forelse($employees as $employee)
                                    <tr>
                                        <td>
                                            <div class="d-flex gap-2">
                                                @if(Auth::user()->role === 'Admin' || Auth::user()->role === 'Inventory Manager')
                                                    @if($employee->IsDeleted)
                                                        <form action="{{ route('employees.restore', ['employeeId' => $employee->EmployeeID]) }}" 
                                                              method="POST">
                                                            @csrf
                                                            <button type="submit" 
                                                                    class="btn btn-sm btn-success" 
                                                                    title="Restore">
                                                                <i class="bi bi-arrow-counterclockwise"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <a href="{{ route('employees.edit', ['employeeId' => $employee->EmployeeID]) }}" 
                                                           class="btn btn-sm btn-primary d-flex align-items-center" 
                                                           title="Edit">
                                                            <i class="bi bi-pencil me-1"></i> Edit
                                                        </a>

                                                        <form action="{{ route('employees.destroy', ['employeeId' => $employee->EmployeeID]) }}" 
                                                                method="POST" 
                                                                class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" 
                                                                    class="btn btn-sm btn-danger d-flex align-items-center" 
                                                                    title="Delete"
                                                                    onclick="return confirm('Are you sure you want to delete this employee?')">
                                                                <i class="bi bi-trash me-1"></i> Delete
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ $employee->FirstName }} {{ $employee->LastName }}</td>
                                        <td>
                                            @if($employee->userAccount)
                                                {{ $employee->userAccount->Username }}
                                            @elseif($employee->UserAccountID)
                                                <span class="text-muted">Account ID: {{ $employee->UserAccountID }}</span>
                                            @else
                                                <span class="text-muted">No Account</span>
                                            @endif
                                        </td>
                                        <td>{{ $employee->Email }}</td>
                                        <td>
                                            <span class="badge bg-{{ $employee->Role === 'Admin' ? 'primary' : 'info' }}">
                                                {{ $employee->Role }}
                                            </span>
                                        </td>
                                        <td>{{ $employee->Gender }}</td>
                                        <td>
                                            @if($employee->IsDeleted)
                                                <span class="badge bg-danger">Inactive</span>
                                            @else
                                                <span class="badge bg-success">Active</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ optional($employee->createdBy)->Username ?? 'System' }}
                                        </td>
                                        <td>
                                            {{ optional($employee->modifiedBy)->Username ?? 'System' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No employees found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="9">
                                        <!-- Add footer content if needed -->
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="datatable-footer"></div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        if (!$.fn.DataTable.isDataTable('#employeesTable')) {
            $('#employeesTable').DataTable({
                pageLength: 10,
                responsive: false,
                scrollX: true,
                dom: '<"datatable-header"<"dataTables_length"l><"dataTables_filter"f>>' +
                     't' +
                     '<"datatable-footer"<"dataTables_info"i><"dataTables_paginate"p>>',
                language: {
                    search: "Search:",
                    searchPlaceholder: "Search employees..."
                },
                columnDefs: [{
                    targets: 0,
                    orderable: false
                }],
                destroy: true
            });
        }
    });
</script>
@endsection
