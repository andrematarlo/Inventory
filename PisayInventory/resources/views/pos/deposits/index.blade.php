@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Student Deposits</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#addDepositModal">
                            <i class="bi bi-plus-circle"></i> Add Deposit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Transaction Date</th>
                                    <th>Reference #</th>
                                    <th>Student</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Balance Before</th>
                                    <th>Balance After</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deposits as $deposit)
                                <tr>
                                    <td>{{ $deposit->TransactionDate->format('M d, Y g:i A') }}</td>
                                    <td>{{ $deposit->ReferenceNumber }}</td>
                                    <td>
                                        @if($deposit->student)
                                            {{ $deposit->student->first_name }} {{ $deposit->student->last_name }}
                                            <br>
                                            <small class="text-muted">{{ $deposit->student_id }}</small>
                                        @else
                                            <span class="text-muted">Unknown Student</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($deposit->TransactionType == 'DEPOSIT')
                                            <span class="badge bg-success">Deposit</span>
                                        @elseif($deposit->TransactionType == 'WITHDRAWAL')
                                            <span class="badge bg-warning">Withdrawal</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $deposit->TransactionType }}</span>
                                        @endif
                                    </td>
                                    <td>₱{{ number_format($deposit->Amount, 2) }}</td>
                                    <td>₱{{ number_format($deposit->BalanceBefore, 2) }}</td>
                                    <td>₱{{ number_format($deposit->BalanceAfter, 2) }}</td>
                                    <td>
                                        @if(strtolower($deposit->Status) == 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif(strtolower($deposit->Status) == 'active' || strtolower($deposit->Status) == 'completed')
                                            <span class="badge bg-success">Active</span>
                                        @elseif(strtolower($deposit->Status) == 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $deposit->Status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(strtolower($deposit->Status) == 'pending')
                                            <a href="{{ route('pos.deposits.approve', $deposit->DepositID) }}" class="btn btn-sm btn-success">
                                                <i class="bi bi-check-circle"></i> Approve
                                            </a>
                                            <a href="{{ route('pos.deposits.reject', $deposit->DepositID) }}" class="btn btn-sm btn-danger">
                                                <i class="bi bi-x-circle"></i> Reject
                                            </a>
                                        @endif
                                        <button class="btn btn-sm btn-info view-deposit" 
                                                data-deposit-id="{{ $deposit->DepositID }}"
                                                data-reference="{{ $deposit->ReferenceNumber }}"
                                                data-date="{{ $deposit->TransactionDate->format('M d, Y g:i A') }}"
                                                data-type="{{ $deposit->TransactionType }}"
                                                data-amount="{{ number_format($deposit->Amount, 2) }}"
                                                data-before="{{ number_format($deposit->BalanceBefore, 2) }}"
                                                data-after="{{ number_format($deposit->BalanceAfter, 2) }}"
                                                data-notes="{{ $deposit->Notes }}"
                                                data-status="{{ $deposit->Status }}">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">No deposit transactions found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-end mt-3">
                        {{ $deposits->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Deposit Modal -->
<div class="modal fade" id="addDepositModal" tabindex="-1" aria-labelledby="addDepositModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addDepositModalLabel">Add New Deposit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('pos.deposits.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="student_id" class="form-label">Student ID</label>
                        <select class="form-select student-select" id="student_id" name="student_id" required>
                            <option value="">Select Student</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="amount" class="form-label">Deposit Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" id="amount" name="amount" min="0.01" step="0.01" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Deposit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Deposit Modal -->
<div class="modal fade" id="viewDepositModal" tabindex="-1" aria-labelledby="viewDepositModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewDepositModalLabel">Deposit Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-5 text-end fw-bold">Reference Number:</div>
                    <div class="col-7" id="viewReference"></div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-5 text-end fw-bold">Transaction Date:</div>
                    <div class="col-7" id="viewDate"></div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-5 text-end fw-bold">Transaction Type:</div>
                    <div class="col-7" id="viewType"></div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-5 text-end fw-bold">Amount:</div>
                    <div class="col-7" id="viewAmount"></div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-5 text-end fw-bold">Balance Before:</div>
                    <div class="col-7" id="viewBefore"></div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-5 text-end fw-bold">Balance After:</div>
                    <div class="col-7" id="viewAfter"></div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-5 text-end fw-bold">Status:</div>
                    <div class="col-7" id="viewStatus"></div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-5 text-end fw-bold">Notes:</div>
                    <div class="col-7" id="viewNotes"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize student select2
        $('.student-select').select2({
            theme: 'bootstrap-5',
            width: '100%',
            ajax: {
                url: '{{ route("pos.students.select2") }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        term: params.term || '',
                        page: params.page || 1
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    
                    return {
                        results: data.items,
                        pagination: {
                            more: data.pagination.more
                        }
                    };
                },
                cache: true
            },
            placeholder: 'Search for a student by ID or name',
            allowClear: true,
            minimumInputLength: 3
        });
        
        // View deposit details
        $('.view-deposit').on('click', function() {
            const data = $(this).data();
            
            $('#viewReference').text(data.reference);
            $('#viewDate').text(data.date);
            
            let typeDisplay = data.type;
            if (data.type === 'DEPOSIT') {
                typeDisplay = '<span class="badge bg-success">Deposit</span>';
            } else if (data.type === 'WITHDRAWAL') {
                typeDisplay = '<span class="badge bg-warning">Withdrawal</span>';
            }
            $('#viewType').html(typeDisplay);
            
            $('#viewAmount').text('₱' + data.amount);
            $('#viewBefore').text('₱' + data.before);
            $('#viewAfter').text('₱' + data.after);
            
            let statusDisplay = data.status;
            if (data.status.toLowerCase() === 'pending') {
                statusDisplay = '<span class="badge bg-warning">Pending</span>';
            } else if (data.status.toLowerCase() === 'active' || data.status.toLowerCase() === 'completed') {
                statusDisplay = '<span class="badge bg-success">Active</span>';
            } else if (data.status.toLowerCase() === 'rejected') {
                statusDisplay = '<span class="badge bg-danger">Rejected</span>';
            }
            $('#viewStatus').html(statusDisplay);
            
            $('#viewNotes').text(data.notes || 'N/A');
            
            $('#viewDepositModal').modal('show');
        });
    });
</script>
@endsection 