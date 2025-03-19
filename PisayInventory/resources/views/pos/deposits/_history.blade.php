<!-- Student Info -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-1">{{ $student->name }}</h5>
        <p class="text-muted mb-0">Student ID: {{ $student->student_id }}</p>
    </div>
    <div class="text-end">
        <h6 class="mb-1">Current Balance</h6>
        <h4 class="text-primary mb-0">₱{{ number_format($balance, 2) }}</h4>
    </div>
</div>

<!-- Transactions Table -->
<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Date & Time</th>
                <th>Reference</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Balance</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $transaction)
                <tr>
                    <td>{{ Carbon\Carbon::parse($transaction->TransactionDate)->format('M d, Y h:ia') }}</td>
                    <td>{{ $transaction->ReferenceNumber }}</td>
                    <td>
                        @if($transaction->TransactionType === 'DEPOSIT')
                            <span class="badge bg-success">Deposit</span>
                        @else
                            <span class="badge bg-primary">Purchase</span>
                        @endif
                    </td>
                    <td>
                        @if($transaction->TransactionType === 'DEPOSIT')
                            <span class="text-success">+₱{{ number_format($transaction->Amount, 2) }}</span>
                        @else
                            <span class="text-danger">-₱{{ number_format($transaction->Amount, 2) }}</span>
                        @endif
                    </td>
                    <td>₱{{ number_format($transaction->BalanceAfter, 2) }}</td>
                    <td>{{ $transaction->Notes ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <i class="bi bi-inbox text-muted d-block" style="font-size: 2rem;"></i>
                        <p class="text-muted mb-0 mt-2">No transactions found</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
@if($transactions->hasPages())
    <div class="d-flex justify-content-end mt-4">
        {{ $transactions->links() }}
    </div>
@endif 