@extends('layouts.app')

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

    <div class="card">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover" id="employeesTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Gender</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $employee)
                            <tr>
                                <td>{{ $employee->FirstName }} {{ $employee->LastName }}</td>
                                <td>
                                    @php
                                        \Log::info('User Account Debug:', [
                                            'Employee' => $employee->toArray(),
                                            'UserAccount' => $employee->userAccount ? $employee->userAccount->toArray() : 'null'
                                        ]);
                                    @endphp
                                    
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
                                        <span class="badge bg-danger">Deleted</span>
                                    @else
                                        <span class="badge bg-success">Active</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        @if(Auth::user()->role === 'Admin' || Auth::user()->role === 'Inventory Manager')
                                            @php
                                                \Log::info('Employee ID:', [
                                                    'EmployeeId' => $employee->EmployeeID,
                                                    'Raw Employee' => $employee->toArray()
                                                ]);
                                            @endphp

                                            @if($employee->IsDeleted)
                                                <form action="{{ route('employees.restore', ['employeeId' => $employee->EmployeeID]) }}" 
                                                      method="POST" 
                                                      class="d-inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-success" 
                                                            title="Restore">
                                                        <i class="bi bi-arrow-counterclockwise"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <span class="d-none">Debug ID: {{ $employee->EmployeeID }}</span>
                                                
                                                <a href="{{ route('employees.edit', ['employeeId' => $employee->EmployeeID]) }}" 
                                                   class="btn btn-sm btn-primary" 
                                                   title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                
                                                <form action="{{ route('employees.destroy', ['employeeId' => $employee->EmployeeID]) }}" 
                                                      method="POST" 
                                                      class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-danger" 
                                                            title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this employee?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No employees found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Employee Modal -->
<!-- <div class="modal fade" id="addEmployeeModal" tabindex="-1"> ... </div> -->
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Check if DataTable is already initialized
        if (!$.fn.DataTable.isDataTable('#employeesTable')) {
            $('#employeesTable').DataTable({
                pageLength: 10,
                responsive: true,
                dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center"ip>',
                language: {
                    search: "Search:",
                    searchPlaceholder: "Search employees..."
                },
                destroy: true // Allow table to be reinitialized
            });
        }
    });
</script>
@endsection 