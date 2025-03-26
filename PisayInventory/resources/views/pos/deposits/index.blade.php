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
                        <select class="form-select" id="student_id" name="student_id" required>
                            <option value="">Search by ID or name</option>
                        </select>
                        <div class="invalid-feedback">Please select a student</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" name="amount" required min="0.01" step="0.01">
                            <div class="invalid-feedback">Please enter a valid amount</div>
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

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
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

/* Fix Select2 sizing */
.select2-container .select2-selection--single {
    height: 38px;
    padding: 6px 12px;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 24px;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
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

    // Initialize Select2 when the modal is shown
    $('#quickDepositModal').on('shown.bs.modal', function() {
        $('#student_id').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#quickDepositModal'),
            width: '100%',
            placeholder: 'Search by ID or name',
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
                        results: $.map(data.students, function(student) {
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
        $('#student_id').val(null).trigger('change');
    });

    // Form validation before submission
    $('#quickDepositForm').on('submit', function(e) {
        e.preventDefault();
        
        // Basic validation
        let isValid = true;
        const studentId = $('#student_id').val();
        const amount = $('input[name="amount"]').val();
        
        if (!studentId) {
            $('#student_id').addClass('is-invalid');
            isValid = false;
        } else {
            $('#student_id').removeClass('is-invalid');
        }
        
        if (!amount || parseFloat(amount) <= 0) {
            $('input[name="amount"]').addClass('is-invalid');
            isValid = false;
        } else {
            $('input[name="amount"]').removeClass('is-invalid');
        }
        
        if (!isValid) {
            return false;
        }
        
        // Show loading state
        const submitBtn = $('button[type="submit"][form="quickDepositForm"]');
        const originalText = submitBtn.html();
        submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
        submitBtn.prop('disabled', true);
        
        // Submit the form
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Deposit added successfully!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    // Close modal and reload page
                    $('#quickDepositModal').modal('hide');
                    location.reload();
                });
            },
            error: function(xhr) {
                console.error('Deposit error:', xhr);
                const errorMessage = xhr.responseJSON?.message || 'Something went wrong. Please try again.';
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage
                });
                
                // Reset button state
                submitBtn.html(originalText);
                submitBtn.prop('disabled', false);
            }
        });
    });

    // Table filters
    $('#typeFilter').on('change', function() {
        filterTable();
    });
    
    $('#startDate, #endDate').on('change', function() {
        filterTable();
    });
    
    $('#studentSearch').on('keyup', function() {
        filterTable();
    });
    
    function filterTable() {
        const type = $('#typeFilter').val().toUpperCase();
        const searchTerm = $('#studentSearch').val().toLowerCase();
        const startDate = $('#startDate').val() ? new Date($('#startDate').val()) : null;
        const endDate = $('#endDate').val() ? new Date($('#endDate').val()) : null;
        
        $('#depositsTable tbody tr').each(function() {
            let show = true;
            const row = $(this);
            
            // Filter by type
            if (type && row.find('td:nth-child(4)').text().toUpperCase().indexOf(type) === -1) {
                show = false;
            }
            
            // Filter by student
            if (searchTerm && row.find('td:nth-child(2)').text().toLowerCase().indexOf(searchTerm) === -1) {
                show = false;
            }
            
            // Filter by date range
            if (startDate || endDate) {
                const dateText = row.find('td:first-child').text();
                const rowDate = new Date(dateText);
                
                if (startDate && rowDate < startDate) {
                    show = false;
                }
                
                if (endDate && rowDate > endDate) {
                    show = false;
                }
            }
            
            row.toggle(show);
        });
    }

    // Reset Filters
    $('#resetFilters').on('click', function() {
        $('#typeFilter').val('');
        $('#startDate, #endDate').val('');
        $('#studentSearch').val('');
        filterTable();
    });
});
</script>
@endpush
@endsection 