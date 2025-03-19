@extends('layouts.app')

@php
use Illuminate\Support\Carbon;
@endphp

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h1 class="fs-2 fw-bold mb-0">Student Deposits</h1>
                        <p class="mb-0 opacity-75">Manage student deposit accounts and transactions</p>
                    </div>
                    <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addDepositModal">
                        <i class="bi bi-plus-circle me-1"></i> Add Deposit
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts Section -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm border-0" role="alert">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm border-0" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Search and Filter Section -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="{{ route('pos.deposits.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search Student</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Enter student name or ID...">
                </div>
                <div class="col-md-3">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label for="date_to" class="form-label">Date To</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Deposits Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Current Balance</th>
                            <th>Last Deposit</th>
                            <th>Last Transaction</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deposits as $deposit)
                            <tr>
                                <td>{{ $deposit->student_id }}</td>
                                <td>{{ $deposit->student_name }}</td>
                                <td class="fw-bold text-primary">₱{{ number_format($deposit->balance, 2) }}</td>
                                <td>
                                    @if($deposit->last_deposit)
                                        <span class="text-success">
                                            ₱{{ number_format($deposit->last_deposit_amount, 2) }}
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            {{ Carbon::parse($deposit->last_deposit)->format('M d, Y h:ia') }}
                                        </small>
                                    @else
                                        <span class="text-muted">No deposits yet</span>
                                    @endif
                                </td>
                                <td>
                                    @if($deposit->last_transaction)
                                        {{ Carbon::parse($deposit->last_transaction)->format('M d, Y h:ia') }}
                                    @else
                                        <span class="text-muted">No transactions yet</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary view-history"
                                            data-student-id="{{ $deposit->student_id }}"
                                            data-student-name="{{ $deposit->student_name }}">
                                        <i class="bi bi-clock-history"></i> History
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="bi bi-inbox text-muted d-block" style="font-size: 2rem;"></i>
                                    <p class="text-muted mb-0 mt-2">No deposits found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-end mt-4">
                {{ $deposits->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Add Deposit Modal -->
<div class="modal fade" id="addDepositModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('pos.deposits.store') }}" method="POST" id="addDepositForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add New Deposit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="student_select" class="form-label">Student</label>
                        <select class="form-select" id="student_select" name="student_id" required>
                            <option value="">Search student...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" id="amount" name="amount" 
                                   min="0.01" step="0.01" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> Add Deposit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Transaction History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Transaction History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="transactionHistory">
                    <!-- Transaction history will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.table th {
    background-color: #f8f9fa;
}
.pagination {
    margin-bottom: 0;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2 for student selection
    $('#student_select').select2({
        theme: 'bootstrap-5',
        ajax: {
            url: '{{ route("pos.students.select2") }}',
            dataType: 'json',
            delay: 250,
            processResults: function(data) {
                return {
                    results: data.results
                };
            },
            cache: true
        },
        placeholder: 'Search student by ID or name...',
        minimumInputLength: 3,
        dropdownParent: $('#addDepositModal')
    });

    // Handle view history button click
    $('.view-history').click(function() {
        const studentId = $(this).data('student-id');
        const studentName = $(this).data('student-name');
        
        // Show loading state
        $('#transactionHistory').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mt-3 mb-0">Loading transaction history...</p>
            </div>
        `);
        
        // Show modal
        $('#historyModal').modal('show');
        
        // Load transaction history
        $.get("{{ route('pos.deposits.history', '') }}/" + studentId, function(response) {
            $('#transactionHistory').html(response);
        }).fail(function() {
            $('#transactionHistory').html(`
                <div class="text-center py-5">
                    <i class="bi bi-exclamation-circle text-danger" style="font-size: 2rem;"></i>
                    <p class="text-danger mt-3 mb-0">Failed to load transaction history</p>
                </div>
            `);
        });
    });

    // Handle deposit form submission
    $('#addDepositForm').submit(function(e) {
        e.preventDefault();
        
        // Show loading state
        Swal.fire({
            title: 'Processing Deposit',
            text: 'Please wait...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Submit form via AJAX
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        showConfirmButton: true
                    }).then((result) => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to add deposit',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Failed to add deposit',
                    confirmButtonText: 'OK'
                });
            }
        });
    });
});
</script>
@endpush
@endsection 