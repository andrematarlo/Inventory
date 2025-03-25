@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Deposits Report</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('reports') }}">Reports</a></li>
        <li class="breadcrumb-item active">Deposits</li>
    </ol>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter me-1"></i>
            Filter Options
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('reports.deposits') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="date_range" class="form-label">Date Range</label>
                    <select name="date_range" id="date_range" class="form-select" onchange="toggleCustomDateInputs()">
                        <option value="today" {{ $dateRange == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="yesterday" {{ $dateRange == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option value="last7days" {{ $dateRange == 'last7days' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="last30days" {{ $dateRange == 'last30days' ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="thisMonth" {{ $dateRange == 'thisMonth' ? 'selected' : '' }}>This Month</option>
                        <option value="lastMonth" {{ $dateRange == 'lastMonth' ? 'selected' : '' }}>Last Month</option>
                        <option value="custom" {{ $dateRange == 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>
                <div id="custom_date_container" class="row g-3 mt-1" style="{{ $dateRange == 'custom' ? '' : 'display: none;' }}">
                    <div class="col-md-6">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                            value="{{ $startDate ? $startDate->format('Y-m-d') : '' }}">
                    </div>
                    <div class="col-md-6">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                            value="{{ $endDate ? $endDate->format('Y-m-d') : '' }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="transaction_type" class="form-label">Transaction Type</label>
                    <select name="transaction_type" id="transaction_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="DEPOSIT" {{ request()->transaction_type == 'DEPOSIT' ? 'selected' : '' }}>Deposits</option>
                        <option value="WITHDRAWAL" {{ request()->transaction_type == 'WITHDRAWAL' ? 'selected' : '' }}>Withdrawals</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="student_id" class="form-label">Student</label>
                    <input type="text" class="form-control" id="student_id" name="student_id" 
                        value="{{ request()->student_id }}" placeholder="Search by Student ID">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="{{ route('reports.deposits') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Total Deposits</div>
                            @php
                                $totalDeposits = $deposits->where('TransactionType', 'DEPOSIT')->sum('Amount');
                            @endphp
                            <div class="fs-4 fw-bold">₱{{ number_format($totalDeposits, 2) }}</div>
                        </div>
                        <i class="fas fa-wallet fa-2x text-white-50"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span class="small text-white">Period Total</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Total Withdrawals</div>
                            @php
                                $totalWithdrawals = $deposits->where('TransactionType', 'WITHDRAWAL')->sum('Amount');
                            @endphp
                            <div class="fs-4 fw-bold">₱{{ number_format($totalWithdrawals, 2) }}</div>
                        </div>
                        <i class="fas fa-money-bill-wave fa-2x text-white-50"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span class="small text-white">Period Total</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Net Balance</div>
                            <div class="fs-4 fw-bold">₱{{ number_format($totalDeposits - $totalWithdrawals, 2) }}</div>
                        </div>
                        <i class="fas fa-balance-scale fa-2x text-white-50"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span class="small text-white">Deposits - Withdrawals</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Total Transactions</div>
                            <div class="fs-4 fw-bold">{{ $deposits->count() }}</div>
                        </div>
                        <i class="fas fa-exchange-alt fa-2x text-white-50"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span class="small text-white">Period Count</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Deposit & Withdrawal Trends
                </div>
                <div class="card-body">
                    <canvas id="depositChart" height="225"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Transaction Types
                </div>
                <div class="card-body">
                    <canvas id="transactionTypesChart" height="225"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Student Balances -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-users me-1"></i>
            Top Student Balances
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Total Deposits</th>
                            <th>Total Withdrawals</th>
                            <th>Current Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topStudents ?? [] as $student)
                        <tr>
                            <td>{{ $student->student_id }}</td>
                            <td>{{ $student->StudentName ?? 'Unknown' }}</td>
                            <td class="text-success">+₱{{ number_format($student->total_deposits, 2) }}</td>
                            <td class="text-danger">-₱{{ number_format($student->total_withdrawals, 2) }}</td>
                            <td class="fw-bold">₱{{ number_format($student->current_balance, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">No student data available for the selected period.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Deposits Table -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Deposit Data
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Student</th>
                            <th>Transaction Type</th>
                            <th>Amount</th>
                            <th>Balance</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deposits as $deposit)
                        <tr>
                            <td>{{ $deposit->DepositID }}</td>
                            <td>{{ \Carbon\Carbon::parse($deposit->TransactionDate)->format('M d, Y g:i A') }}</td>
                            <td>
                                @if($deposit->student)
                                    {{ $deposit->student->StudentName }}
                                @else
                                    <span class="text-muted">Unknown</span>
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
                            <td class="text-end">
                                @if($deposit->TransactionType == 'DEPOSIT')
                                    <span class="text-success">+₱{{ number_format($deposit->Amount, 2) }}</span>
                                @elseif($deposit->TransactionType == 'WITHDRAWAL')
                                    <span class="text-danger">-₱{{ number_format($deposit->Amount, 2) }}</span>
                                @else
                                    ₱{{ number_format($deposit->Amount, 2) }}
                                @endif
                            </td>
                            <td class="text-end">₱{{ number_format($deposit->BalanceAfter ?? 0, 2) }}</td>
                            <td>{{ $deposit->Notes ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No deposit data available for the selected period.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing {{ $deposits->firstItem() ?? 0 }} to {{ $deposits->lastItem() ?? 0 }} of {{ $deposits->total() }} entries
                </div>
                <div>
                    {{ $deposits->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function toggleCustomDateInputs() {
        const dateRange = document.getElementById('date_range').value;
        const customDateContainer = document.getElementById('custom_date_container');
        
        if (dateRange === 'custom') {
            customDateContainer.style.display = 'flex';
        } else {
            customDateContainer.style.display = 'none';
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Deposits and Withdrawals Chart
        const chartLabels = JSON.parse('{{ json_encode($chartLabels ?? []) }}'.replace(/&quot;/g, '"'));
        const depositData = JSON.parse('{{ json_encode($depositData ?? []) }}'.replace(/&quot;/g, '"'));
        const withdrawalData = JSON.parse('{{ json_encode($withdrawalData ?? []) }}'.replace(/&quot;/g, '"'));
        
        const depositCtx = document.getElementById('depositChart').getContext('2d');
        new Chart(depositCtx, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [
                    {
                        label: 'Deposits (₱)',
                        data: depositData,
                        backgroundColor: 'rgba(40, 167, 69, 0.5)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Withdrawals (₱)',
                        data: withdrawalData,
                        backgroundColor: 'rgba(255, 193, 7, 0.5)',
                        borderColor: 'rgba(255, 193, 7, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
        
        // Transaction Types Chart
        const typeLabels = JSON.parse('{{ json_encode($typeLabels ?? []) }}'.replace(/&quot;/g, '"'));
        const typeValues = JSON.parse('{{ json_encode($typeValues ?? []) }}'.replace(/&quot;/g, '"'));
        
        const typesCtx = document.getElementById('transactionTypesChart').getContext('2d');
        new Chart(typesCtx, {
            type: 'doughnut',
            data: {
                labels: typeLabels,
                datasets: [{
                    data: typeValues,
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.7)',
                        'rgba(255, 193, 7, 0.7)',
                        'rgba(108, 117, 125, 0.7)'
                    ],
                    borderColor: [
                        'rgba(40, 167, 69, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(108, 117, 125, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
    });
</script>
@endsection 