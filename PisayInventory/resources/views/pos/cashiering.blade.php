@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 text-success fw-bold">Cashiering</h1>
        <a href="{{ route('pos.create') }}" class="btn btn-success">
            New Order
        </a>
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

    <!-- Pending Orders -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="card-title text-success mb-0">Pending Orders</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr class="table-light">
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Payment Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingOrders as $order)
                            <tr>
                                <td>{{ $order->OrderNumber }}</td>
                                <td>{{ date('M d, Y h:i A', strtotime($order->created_at)) }}</td>
                                <td>₱{{ number_format($order->TotalAmount, 2) }}</td>
                                <td>{{ ucfirst($order->PaymentMethod) }}</td>
                                <td>
                                    <span class="badge bg-warning">Pending</span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-success process-payment-btn" 
                                            data-order-id="{{ $order->OrderID }}"
                                            data-order-number="{{ $order->OrderNumber }}"
                                            data-total="{{ $order->TotalAmount }}">
                                        Process Payment
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No pending orders found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recently Completed Payments -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="card-title text-success mb-0">Recent Transactions</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr class="table-light">
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Payment Type</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $completedOrders = DB::table('pos_orders')
                                ->where('Status', 'completed')
                                ->orderBy('updated_at', 'desc')
                                ->limit(5)
                                ->get(['OrderNumber', 'updated_at as DateModified', 'TotalAmount as Amount', 'PaymentMethod as PaymentType', 'Status']);
                        @endphp
                        
                        @forelse($completedOrders as $order)
                            <tr>
                                <td>{{ $order->OrderNumber }}</td>
                                <td>{{ date('M d, Y h:i A', strtotime($order->DateModified)) }}</td>
                                <td>₱{{ number_format($order->Amount, 2) }}</td>
                                <td>{{ ucfirst($order->PaymentType) }}</td>
                                <td><span class="badge bg-success">{{ ucfirst($order->Status) }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No completed transactions found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-success" id="paymentModalLabel">Process Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="paymentForm" action="" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="orderNumber" class="form-label">Order Number</label>
                        <input type="text" id="orderNumber" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="totalAmount" class="form-label">Total Amount</label>
                        <input type="text" id="totalAmount" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="paymentAmount" class="form-label">Payment Amount</label>
                        <input type="number" step="0.01" id="paymentAmount" name="payment_amount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Type</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_type" id="cashPayment" value="cash" checked>
                                <label class="form-check-label" for="cashPayment">Cash</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_type" id="depositPayment" value="deposit">
                                <label class="form-check-label" for="depositPayment">Deposit</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="paymentForm" class="btn btn-success">Process Payment</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
        const paymentForm = document.getElementById('paymentForm');
        const orderNumberInput = document.getElementById('orderNumber');
        const totalAmountInput = document.getElementById('totalAmount');
        const paymentAmountInput = document.getElementById('paymentAmount');
        
        // Process Payment button click handlers
        document.querySelectorAll('.process-payment-btn').forEach(button => {
            button.addEventListener('click', function() {
                const orderId = this.getAttribute('data-order-id');
                const orderNumber = this.getAttribute('data-order-number');
                const total = this.getAttribute('data-total');
                
                // Set form action
                paymentForm.action = `{{ route('pos.process-payment', '') }}/${orderId}`;
                
                // Set values
                orderNumberInput.value = orderNumber;
                totalAmountInput.value = `₱${parseFloat(total).toFixed(2)}`;
                paymentAmountInput.value = parseFloat(total).toFixed(2);
                
                // Show modal
                paymentModal.show();
            });
        });
    });
</script>
@endpush
@endsection 