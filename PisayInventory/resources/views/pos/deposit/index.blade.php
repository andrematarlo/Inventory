@extends('layouts.app')

@section('title', 'Cash Deposit Management')

@section('styles')
<style>
    .deposit-container {
        padding: 2rem 0;
    }
    
    .deposit-header {
        margin-bottom: 2rem;
    }
    
    .deposit-form-container {
        background: white;
        border-radius: 0.5rem;
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.05);
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .deposit-form {
        max-width: 800px;
        margin: 0 auto;
    }
    
    .deposit-history-container {
        background: white;
        border-radius: 0.5rem;
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.05);
        padding: 1.5rem;
    }
    
    .table-header {
        background: #f8f9fa;
        font-weight: 600;
    }
    
    .amount-positive {
        color: #28a745;
        font-weight: 600;
    }
    
    .amount-negative {
        color: #dc3545;
        font-weight: 600;
    }
    
    .search-student {
        position: relative;
    }
    
    .search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1);
        display: none;
    }
    
    .search-results.show {
        display: block;
    }
    
    .search-result-item {
        padding: 0.5rem 1rem;
        cursor: pointer;
        border-bottom: 1px solid #f8f9fa;
    }
    
    .search-result-item:hover {
        background: #f8f9fa;
    }
    
    .search-result-item:last-child {
        border-bottom: none;
    }
    
    .student-info {
        background: #f8f9fa;
        border-radius: 0.25rem;
        padding: 0.75rem;
        margin-top: 1rem;
        display: none;
    }
    
    .student-info.show {
        display: block;
    }
    
    .balance-display {
        font-size: 1.25rem;
        font-weight: 600;
        margin-top: 0.5rem;
    }
    
    .balance-positive {
        color: #28a745;
    }
    
    .balance-negative {
        color: #dc3545;
    }
</style>
@endsection

@section('content')
<div class="container deposit-container">
    <div class="deposit-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1>Cash Deposit Management</h1>
            <a href="{{ route('pos.deposit.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="deposit-form-container">
                <h3 class="mb-4">Add New Deposit</h3>
                
                <form action="{{ route('pos.deposit.add') }}" method="POST" class="deposit-form">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="student_id" class="form-label">Select Student</label>
                        <select class="form-select" id="student_id" name="student_id" required>
                            <option value="">Select a student...</option>
                            @foreach($students as $student)
                                <option value="{{ $student->StudentID }}">
                                    {{ $student->LastName }}, {{ $student->FirstName }} {{ $student->MiddleName ? substr($student->MiddleName, 0, 1) . '.' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="student-info mb-3" id="studentInfo">
                        <div class="fw-bold mb-1">Student Information</div>
                        <div id="studentDetails"></div>
                        <div class="mt-2">
                            Current Balance: <span id="currentBalance" class="balance-display"></span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="amount" class="form-label">Deposit Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" id="amount" name="amount" required min="0.01" step="0.01" placeholder="Enter amount">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description (Optional)</label>
                        <input type="text" class="form-control" id="description" name="description" placeholder="Enter description">
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Add Deposit</button>
                    </div>
                </form>
            </div>
            
            <div class="deposit-form-container">
                <h3 class="mb-4">View Student History</h3>
                
                <form action="{{ route('pos.deposit.history', 0) }}" method="GET" id="historyForm">
                    <div class="mb-3">
                        <label for="student_history_id" class="form-label">Select Student</label>
                        <select class="form-select" id="student_history_id" required>
                            <option value="">Select a student...</option>
                            @foreach($students as $student)
                                <option value="{{ $student->StudentID }}">
                                    {{ $student->LastName }}, {{ $student->FirstName }} {{ $student->MiddleName ? substr($student->MiddleName, 0, 1) . '.' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-outline-primary">View History</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="col-lg-7">
            <div class="deposit-history-container">
                <h3 class="mb-4">Recent Transactions</h3>
                
                <div class="table-responsive">
                    <table class="table">
                        <thead class="table-header">
                            <tr>
                                <th>Student</th>
                                <th>Transaction</th>
                                <th>Amount</th>
                                <th>Date/Time</th>
                                <th>Processed By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentDeposits as $deposit)
                                <tr>
                                    <td>
                                        <a href="{{ route('pos.deposit.history', $deposit->StudentID) }}">
                                            {{ $deposit->student->LastName }}, {{ $deposit->student->FirstName }}
                                        </a>
                                    </td>
                                    <td>{{ $deposit->TransactionType }}</td>
                                    <td class="{{ $deposit->Amount > 0 ? 'amount-positive' : 'amount-negative' }}">
                                        {{ $deposit->Amount > 0 ? '+' : '' }}₱{{ number_format($deposit->Amount, 2) }}
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($deposit->TransactionDate)->format('M d, Y h:i A') }}</td>
                                    <td>{{ $deposit->processedBy ? $deposit->processedBy->name : 'System' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">No transactions found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Balance Modal -->
<div class="modal fade" id="balanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Student Balance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modalStudentInfo"></div>
                <hr>
                <div class="text-center">
                    <div class="mb-2">Current Balance:</div>
                    <div class="fs-2 fw-bold" id="modalBalance"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="viewHistoryBtn" class="btn btn-primary">View Transaction History</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const studentSelect = document.getElementById('student_id');
        const studentInfo = document.getElementById('studentInfo');
        const studentDetails = document.getElementById('studentDetails');
        const currentBalance = document.getElementById('currentBalance');
        const studentHistorySelect = document.getElementById('student_history_id');
        const historyForm = document.getElementById('historyForm');
        
        // Handle student selection for deposit
        studentSelect.addEventListener('change', async function() {
            const studentId = this.value;
            
            if (!studentId) {
                studentInfo.classList.remove('show');
                return;
            }
            
            try {
                const response = await fetch(`/inventory/pos/deposit/balance/${studentId}`);
                const data = await response.json();
                
                if (data.success) {
                    const student = data.student;
                    const balance = parseFloat(data.balance);
                    
                    // Show student info
                    studentDetails.innerHTML = `
                        <div><strong>Name:</strong> ${student.LastName}, ${student.FirstName} ${student.MiddleName ? student.MiddleName.charAt(0) + '.' : ''}</div>
                        <div><strong>Student ID:</strong> ${student.StudentNumber || 'N/A'}</div>
                    `;
                    
                    // Show balance with appropriate color
                    currentBalance.textContent = `₱${balance.toFixed(2)}`;
                    
                    if (balance >= 0) {
                        currentBalance.classList.add('balance-positive');
                        currentBalance.classList.remove('balance-negative');
                    } else {
                        currentBalance.classList.add('balance-negative');
                        currentBalance.classList.remove('balance-positive');
                    }
                    
                    studentInfo.classList.add('show');
                } else {
                    throw new Error(data.message || 'Failed to get student balance');
                }
                
            } catch (error) {
                console.error('Error getting student balance:', error);
                alert('Failed to get student balance. Please try again.');
                studentInfo.classList.remove('show');
            }
        });
        
        // Handle history form submission
        historyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const studentId = studentHistorySelect.value;
            
            if (studentId) {
                window.location.href = `/inventory/pos/deposit/history/${studentId}`;
            }
        });
        
        // Initialize select elements with Select2 if available
        if (typeof $.fn.select2 !== 'undefined') {
            $('#student_id, #student_history_id').select2({
                theme: 'bootstrap-5',
                placeholder: 'Select a student...',
                allowClear: true
            });
        }
    });
</script>
@endsection 