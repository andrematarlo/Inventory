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

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
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
                        @forelse($employees as $employee)
                            <tr>
                                <td>
                                    <div class="action-buttons">
                                        @if(Auth::user()->role === 'Admin' || Auth::user()->role === 'Inventory Manager')
                                            @php
                                                \Log::info('Employee ID:', [
                                                    'EmployeeId' => $employee->EmployeeID,
                                                    'Raw Employee' => $employee
                                                ]);
                                            @endphp
                                            <a href="{{ route('employees.edit', $employee->EmployeeID) }}" class="btn btn-sm btn-primary me-2">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            @if(!$employee->IsDeleted)
                                                <form action="{{ route('employees.destroy', $employee->EmployeeID) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to deactivate this employee?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </td>
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
                                        <span class="badge bg-danger">Inactive</span>
                                    @else
                                        <span class="badge bg-success">Active</span>
                                    @endif
                                </td>
                                <td>{{ $employee->created_by_name }}</td>
                                <td>{{ $employee->modified_by_name }}</td>
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