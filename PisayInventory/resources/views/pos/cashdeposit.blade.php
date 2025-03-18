@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 fw-bold">Cash Deposit</h1>
        <button id="new-deposit-btn" class="btn btn-primary">
            New Deposit
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger mb-4">
            {{ session('error') }}
        </div>
    @endif

    @if(isset($isStudent) && $isStudent)
    <!-- STUDENT VIEW -->
    <!-- Student Current Balance -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">Your Current Balance</h5>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-1 text-muted">Available Balance:</p>
                    <h2 class="mb-0 text-primary fw-bold">₱{{ number_format($currentBalance, 2) }}</h2>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-1 text-muted">Student ID:</p>
                    <h5 class="mb-0">{{ $studentId }}</h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Deposits -->
    @if(count($pendingDeposits) > 0)
    <div class="card mb-4">
        <div class="card-header bg-warning bg-opacity-10">
            <h5 class="card-title mb-0 text-warning">Pending Deposits</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr class="table-light">
                            <th>Date</th>
                            <th>Reference</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingDeposits as $deposit)
                        <tr>
                            <td>{{ date('M d, Y h:i A', strtotime($deposit->transaction_date)) }}</td>
                            <td>{{ $deposit->reference_number }}</td>
                            <td class="fw-bold">₱{{ number_format($deposit->amount, 2) }}</td>
                            <td><span class="badge bg-warning text-dark">Pending</span></td>
                            <td>{{ $deposit->notes }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Approved Deposits History -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">Deposit History</h5>
        </div>
        <div class="card-body">
            @if(count($approvedDeposits) > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr class="table-light">
                                <th>Date</th>
                                <th>Reference</th>
                                <th>Amount</th>
                                <th>Balance After</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($approvedDeposits as $deposit)
                            <tr>
                                <td>{{ date('M d, Y h:i A', strtotime($deposit->transaction_date)) }}</td>
                                <td>{{ $deposit->reference_number }}</td>
                                <td class="fw-bold">₱{{ number_format($deposit->amount, 2) }}</td>
                                <td>₱{{ number_format($deposit->balance_after, 2) }}</td>
                                <td>{{ $deposit->notes }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4 text-muted">
                    <p>No deposit history found. Make your first deposit by clicking the "New Deposit" button.</p>
                </div>
            @endif
        </div>
    </div>
    
    @else
    <!-- CASHIER/ADMIN VIEW -->
    <!-- Deposits Pending Approval -->
    <div class="card mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Deposits Pending Approval</h5>
            <span class="badge bg-warning text-dark fs-6">{{ count($pendingDeposits ?? []) }} Pending</span>
        </div>
        <div class="card-body">
            @if(isset($pendingDeposits) && count($pendingDeposits) > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr class="table-light">
                                <th>Student</th>
                                <th>Date</th>
                                <th>Reference</th>
                                <th>Amount</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingDeposits as $deposit)
                            <tr>
                                <td>{{ $deposit->FirstName }} {{ $deposit->LastName }}<br><small class="text-muted">{{ $deposit->student_id }}</small></td>
                                <td>{{ date('M d, Y h:i A', strtotime($deposit->transaction_date)) }}</td>
                                <td>{{ $deposit->reference_number }}</td>
                                <td class="fw-bold">₱{{ number_format($deposit->amount, 2) }}</td>
                                <td>{{ $deposit->notes }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <form action="{{ route('pos.approve-deposit', $deposit->deposit_id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#rejectModal" 
                                                data-deposit-id="{{ $deposit->deposit_id }}">
                                            Reject
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4 text-muted">
                    <p>No pending deposits to approve at this time.</p>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Student Search and Manual Deposit (for admin/cashier) -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">Student Search</h5>
        </div>
        <div class="card-body">
            <div class="mb-4">
                <div class="input-group">
                    <input type="text" id="student-search" class="form-control" placeholder="Search by student ID or name...">
                    <button id="search-btn" class="btn btn-primary">
                        Search
                    </button>
                </div>
            </div>

            <div id="student-info" class="d-none p-4 bg-light rounded mb-4">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <div class="bg-secondary bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center mx-auto" style="height: 128px; width: 128px;">
                            <span class="text-muted fs-1">👤</span>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <h3 class="h4 fw-bold mb-2" id="student-name">Student Name</h3>
                        <p class="mb-1"><span class="fw-medium">ID:</span> <span id="student-id">12345</span></p>
                        <p class="mb-1"><span class="fw-medium">Grade & Section:</span> <span id="student-section">Grade 10 - Einstein</span></p>
                        <p class="mb-4"><span class="fw-medium">Contact:</span> <span id="student-contact">09123456789</span></p>
                        
                        <div class="alert alert-primary">
                            <p class="text-primary fw-medium mb-1">Current Balance:</p>
                            <p class="text-primary fs-4 fw-bold mb-0" id="student-balance">₱0.00</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- New Deposit Modal -->
<div class="modal fade" id="depositModal" tabindex="-1" aria-labelledby="depositModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="depositModalLabel">Add New Deposit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="depositForm" action="{{ route('pos.store-deposit') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="deposit-student-id" class="form-label">Student ID</label>
                        @if(isset($isStudent) && $isStudent)
                            <input type="text" class="form-control" value="{{ $studentId }}" readonly>
                            <input type="hidden" name="student_id" value="{{ $studentId }}">
                        @else
                            <select id="deposit-student-id" name="student_id" class="form-select student-select" style="width: 100%;" required>
                                <option value="">Search for a student...</option>
                            </select>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label for="deposit-amount" class="form-label">Deposit Amount</label>
                        <input type="number" step="0.01" id="deposit-amount" name="amount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="deposit-notes" class="form-label">Notes (Optional)</label>
                        <textarea id="deposit-notes" name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="depositForm" class="btn btn-primary">Submit Deposit</button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Deposit Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">Reject Deposit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="rejectForm" action="" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="rejection-reason" class="form-label">Reason for Rejection (Optional)</label>
                        <textarea id="rejection-reason" name="rejection_reason" class="form-control" rows="3" placeholder="Enter reason for rejecting this deposit..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="rejectForm" class="btn btn-danger">Confirm Rejection</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- Add Select2 CSS and JS files -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const studentSearch = document.getElementById('student-search');
        const searchBtn = document.getElementById('search-btn');
        const studentInfo = document.getElementById('student-info');
        
        const depositModal = new bootstrap.Modal(document.getElementById('depositModal'));
        const newDepositBtn = document.getElementById('new-deposit-btn');
        const depositStudentIdSelect = document.getElementById('deposit-student-id');
        
        // Initialize modals
        const rejectModal = document.getElementById('rejectModal');
        if (rejectModal) {
            rejectModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const depositId = button.getAttribute('data-deposit-id');
                const form = this.querySelector('#rejectForm');
                form.action = '{{ url("pos/reject-deposit") }}/' + depositId;
            });
        }
        
        // Initialize Select2 if student select exists
        if (depositStudentIdSelect) {
            $('.student-select').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#depositModal'),
                placeholder: 'Search by student ID or name',
                allowClear: true,
                minimumInputLength: 1,
                ajax: {
                    url: '{{ route("pos.search-students") }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            term: params.term || '',
                            page: params.page || 1
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data.results,
                            pagination: data.pagination
                        };
                    },
                    cache: true
                }
            }).on('select2:select', function (e) {
                console.log('Student selected:', e.params.data);
                
                const studentId = e.params.data.id;
                
                // Fetch student balance
                fetch(`{{ url('pos/student-balance') }}/${studentId}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Student balance:', data);
                        // Show student balance if needed (for the admin/cashier view)
                        if (studentInfo && !studentInfo.classList.contains('d-none')) {
                            document.getElementById('student-balance').textContent = `₱${parseFloat(data.balance).toFixed(2)}`;
                        }
                    })
                    .catch(error => console.error('Error fetching student balance:', error));
            });
        }
        
        // Mock student data - in a real app this would come from your database
        const students = [
            {
                id: '2023001',
                name: 'Juan Dela Cruz',
                section: 'Grade 10 - Einstein',
                contact: '09123456789',
                balance: 500.00,
                history: [
                    { date: '2023-05-15', reference: 'DEP-001', type: 'Deposit', amount: 200.00, balance: 200.00 },
                    { date: '2023-05-20', reference: 'DEP-002', type: 'Deposit', amount: 300.00, balance: 500.00 }
                ]
            },
            {
                id: '2023002',
                name: 'Maria Santos',
                section: 'Grade 11 - Newton',
                contact: '09987654321',
                balance: 750.00,
                history: [
                    { date: '2023-05-10', reference: 'DEP-003', type: 'Deposit', amount: 500.00, balance: 500.00 },
                    { date: '2023-05-18', reference: 'DEP-004', type: 'Deposit', amount: 250.00, balance: 750.00 }
                ]
            }
        ];
        
        // If admin/cashier view and student select exists, populate with mock data
        if (depositStudentIdSelect) {
            students.forEach(student => {
                const option = new Option(`${student.id} - ${student.name} (${student.section})`, student.id, false, false);
                depositStudentIdSelect.appendChild(option);
            });
        }
        
        // Search button click handler (for admin/cashier view)
        if (searchBtn) {
            searchBtn.addEventListener('click', function() {
                const searchTerm = studentSearch.value.trim().toLowerCase();
                if (!searchTerm) return;
                
                // Find student in mock data
                const student = students.find(s => 
                    s.id.toLowerCase().includes(searchTerm) || 
                    s.name.toLowerCase().includes(searchTerm)
                );
                
                if (student) {
                    // Show student info
                    document.getElementById('student-name').textContent = student.name;
                    document.getElementById('student-id').textContent = student.id;
                    document.getElementById('student-section').textContent = student.section;
                    document.getElementById('student-contact').textContent = student.contact;
                    document.getElementById('student-balance').textContent = `₱${student.balance.toFixed(2)}`;
                    
                    studentInfo.classList.remove('d-none');
                } else {
                    // No student found
                    studentInfo.classList.add('d-none');
                    alert('No student found with that ID or name.');
                }
            });
            
            // Handle Enter key in search input
            studentSearch.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    searchBtn.click();
                }
            });
        }
        
        // New Deposit button click handler
        newDepositBtn.addEventListener('click', function() {
            depositModal.show();
        });
    });
</script>
@endpush
@endsection 