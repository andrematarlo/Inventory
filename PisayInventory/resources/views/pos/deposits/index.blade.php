@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Top Cards Section -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">Total Deposits Today</h6>
                    <h3 class="mb-0">₱{{ number_format($todayDeposits, 2) }}</h3>
                    <small>{{ $todayDepositCount }} transactions</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">Active Deposits</h6>
                    <h3 class="mb-0">₱{{ number_format($activeDeposits, 2) }}</h3>
                    <small>{{ $activeDepositCount }} approved deposits</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">Pending Deposits</h6>
                    <h3 class="mb-0">₱{{ number_format($pendingDeposits, 2) }}</h3>
                    <small>{{ $pendingDepositCount }} pending approval</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Section -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0">Student Deposits</h5>
            <div class="d-flex gap-2">
                <div class="input-group">
                    <input type="text" id="studentSearch" class="form-control" placeholder="Search student...">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#quickDepositModal">
                    <i class="bi bi-plus-circle"></i> Quick Deposit
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <!-- Filters Row -->
            <div class="bg-light border-bottom p-3">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select class="form-select" id="typeFilter">
                            <option value="">All Types</option>
                            <option value="DEPOSIT">Deposits</option>
                            <option value="WITHDRAWAL">Withdrawals</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="date" class="form-control" id="startDate">
                            <span class="input-group-text">to</span>
                            <input type="date" class="form-control" id="endDate">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-secondary w-100" id="resetFilters">
                            Reset Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="depositsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Date & Time</th>
                            <th>Student</th>
                            <th>Reference #</th>
                            <th>Type</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Balance After</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deposits as $deposit)
                        <tr>
                            <td>{{ $deposit->TransactionDate->format('M d, Y g:i A') }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-light rounded-circle me-2 d-flex align-items-center justify-content-center">
                                        <span class="text-primary">{{ substr($deposit->student->first_name ?? 'U', 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $deposit->student->first_name ?? 'Unknown' }} {{ $deposit->student->last_name ?? '' }}</div>
                                        <small class="text-muted">{{ $deposit->student_id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="font-monospace">{{ $deposit->ReferenceNumber }}</span></td>
                            <td>
                                @if($deposit->TransactionType == 'DEPOSIT')
                                    <span class="badge bg-success-subtle text-success">Deposit</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning">Withdrawal</span>
                                @endif
                            </td>
                            <td class="text-end">₱{{ number_format($deposit->Amount, 2) }}</td>
                            <td class="text-end">₱{{ number_format($deposit->BalanceAfter, 2) }}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-light view-deposit" 
                                        data-deposit-id="{{ $deposit->DepositID }}"
                                        data-bs-toggle="tooltip" 
                                        title="View Details">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light set-limit" 
                                        data-student-id="{{ $deposit->student_id }}"
                                        data-student-name="{{ $deposit->student->first_name ?? 'Unknown' }} {{ $deposit->student->last_name ?? '' }}"
                                        data-bs-toggle="tooltip" 
                                        title="Set Balance Limit">
                                    <i class="bi bi-sliders"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox-fill fs-2 d-block mb-2"></i>
                                No deposit transactions found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($deposits->hasPages())
            <div class="p-3 border-top">
                {{ $deposits->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Quick Deposit Modal -->
<div class="modal fade" id="quickDepositModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Quick Deposit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quickDepositForm" action="{{ route('pos.deposits.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label">Student</label>
                        <select class="form-select select2-student" name="student_id" required>
                            <option value="">Search by ID or name</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" name="amount" required min="0.01" step="0.01">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="quickDepositForm" class="btn btn-primary px-4">
                    Add Deposit
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Set Balance Limit Modal -->
<div class="modal fade" id="setLimitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">Set Negative Balance Limit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="student-info mb-4"></p>
                <form id="setLimitForm">
                    <div class="mb-3">
                        <label class="form-label">Negative Balance Limit</label>
                        <div class="input-group">
                            <span class="input-group-text">-₱</span>
                            <input type="number" class="form-control" name="limit" id="limitAmount" min="0" step="100" required>
                            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                Quick Set
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><button class="dropdown-item quick-limit" type="button" data-amount="500">₱500</button></li>
                                <li><button class="dropdown-item quick-limit" type="button" data-amount="1000">₱1,000</button></li>
                                <li><button class="dropdown-item quick-limit" type="button" data-amount="2000">₱2,000</button></li>
                                <li><button class="dropdown-item quick-limit" type="button" data-amount="5000">₱5,000</button></li>
                            </ul>
                        </div>
                        <div class="form-text">Enter the maximum negative balance allowed for this student (without the negative sign).</div>
                    </div>
                    <input type="hidden" name="student_id" id="limitStudentId">
                </form>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="setLimitForm" class="btn btn-primary px-4">Save Limit</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.avatar-sm {
    width: 32px;
    height: 32px;
}

.select2-container--bootstrap-5 {
    width: 100% !important;
}

.modal-open .select2-container--open {
    z-index: 1056 !important;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize Select2 when modal is shown
    $('#quickDepositModal').on('shown.bs.modal', function() {
        $('.select2-student').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#quickDepositModal'),
            placeholder: 'Search student by ID or name',
            allowClear: true,
            minimumInputLength: 1,
            ajax: {
                url: '{{ route("pos.search-students") }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        term: params.term || '',
                        page: params.page || 1
                    };
                },
                processResults: function(data) {
                    return {
                        results: $.map(data.students || [], function(student) {
                            return {
                                id: student.student_id,
                                text: student.student_id + ' - ' + student.first_name + ' ' + student.last_name
                            };
                        })
                    };
                },
                cache: true
            }
        });
    });

    // Reset form and select2 when modal is hidden
    $('#quickDepositModal').on('hidden.bs.modal', function() {
        $('#quickDepositForm').trigger('reset');
        $('.select2-student').val(null).trigger('change');
    });

    // Handle form submission
    $('#quickDepositForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validate form data
        const studentId = $('.select2-student').val();
        const amount = $('input[name="amount"]').val();
        
        if (!studentId) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Please select a student'
            });
            return false;
        }
        
        if (!amount || parseFloat(amount) <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Please enter a valid amount greater than 0'
            });
            return false;
        }
        
        // Submit the form via AJAX
        $.ajax({
            url: this.action,
            method: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#quickDepositModal').modal('hide');
                
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Deposit added successfully',
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    location.reload();
                });
            },
            error: function(xhr) {
                let errorMessage = 'Something went wrong!';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: errorMessage
                });
            }
        });
    });

    // Reset Filters
    $('#resetFilters').on('click', function() {
        $('#typeFilter').val('');
        $('#startDate, #endDate').val('');
        $('#studentSearch').val('');
        location.reload();
    });

    // Handle Set Limit button click
    $('.set-limit').on('click', function() {
        const studentId = $(this).data('student-id');
        const studentName = $(this).data('student-name');
        
        $('#limitStudentId').val(studentId);
        $('.student-info').text(`Setting limit for: ${studentName} (${studentId})`);
        
        // Get current limit if exists
        $.get(`{{ url('pos/student-limit') }}/${studentId}`)
            .done(function(response) {
                $('#limitAmount').val(response.limit || '');
            })
            .fail(function() {
                $('#limitAmount').val('');
            });
        
        $('#setLimitModal').modal('show');
    });

    // Handle quick limit buttons
    $('.quick-limit').on('click', function() {
        $('#limitAmount').val($(this).data('amount'));
    });

    // Handle limit form submission
    $('#setLimitForm').on('submit', function(e) {
        e.preventDefault();
        
        const studentId = $('#limitStudentId').val();
        const limit = $('#limitAmount').val();
        
        $.ajax({
            url: `{{ url('pos/students') }}/${studentId}/limit`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                limit: limit
            },
            success: function(response) {
                $('#setLimitModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Balance limit has been set successfully',
                    showConfirmButton: false,
                    timer: 1500
                });
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Failed to set balance limit'
                });
            }
        });
    });
});
</script>
@endpush
@endsection 