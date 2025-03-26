@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Process Payment - Order #{{ $order->OrderNumber }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('pos.post-payment', $order->OrderID) }}" method="POST" id="paymentForm">
                        @csrf
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Order Total</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="text" class="form-control" value="{{ number_format($order->TotalAmount, 2) }}" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" name="payment_type" id="paymentType" required>
                                <option value="cash">Cash</option>
                                @if($order->student_id)
                                    <option value="deposit">Student Deposit</option>
                                @endif
                            </select>
                        </div>

                        <div id="cashPaymentFields">
                            <div class="mb-3">
                                <label class="form-label">Amount Tendered</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" step="0.01" class="form-control" name="amount_tendered" id="amountTendered" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Change</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="text" class="form-control" id="changeAmount" readonly>
                                </div>
                            </div>
                        </div>

                        <div id="depositPaymentFields" style="display: none;">
                            @if($order->student_id)
                                <div class="alert alert-info">
                                    <strong>Current Balance:</strong> ₱<span id="currentBalance">0.00</span>
                                </div>
                                <div class="alert alert-danger" id="insufficientFundsAlert" style="display: none;">
                                    Insufficient funds in student account.
                                </div>
                            @endif
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary" id="submitPayment">
                                Process Payment
                            </button>
                            <a href="{{ route('pos.orders.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const totalAmount = {{ $order->TotalAmount }};
    
    // Get the amount tendered from session storage if available
    const lastAmountTendered = sessionStorage.getItem('lastAmountTendered');
    if (lastAmountTendered) {
        $('#amountTendered').val(parseFloat(lastAmountTendered).toFixed(2));
        // Clear the stored amount after using it
        sessionStorage.removeItem('lastAmountTendered');
    } else {
        // Fall back to total amount if no stored amount exists
        $('#amountTendered').val(totalAmount.toFixed(2));
    }
    
    // Trigger input event to calculate change
    $('#amountTendered').trigger('input');
    
    // Handle payment type change
    $('#paymentType').on('change', function() {
        const paymentType = $(this).val();
        if (paymentType === 'cash') {
            $('#cashPaymentFields').show();
            $('#depositPaymentFields').hide();
            $('#amountTendered').prop('required', true);
        } else {
            $('#cashPaymentFields').hide();
            $('#depositPaymentFields').show();
            $('#amountTendered').prop('required', false);
        }
        
        if (paymentType === 'deposit') {
            checkStudentBalance();
        }
    });
    
    // Calculate change
    $('#amountTendered').on('input', function() {
        const amountTendered = parseFloat($(this).val()) || 0;
        const change = amountTendered - totalAmount;
        $('#changeAmount').val(change.toFixed(2));
        
        // Enable/disable submit button based on amount
        $('#submitPayment').prop('disabled', amountTendered < totalAmount);
    });
    
    // Check student balance for deposit payment
    function checkStudentBalance() {
        @if($order->student_id)
            $.get('{{ route("pos.student.balance", $order->student_id) }}')
                .done(function(response) {
                    const balance = parseFloat(response.balance);
                    $('#currentBalance').text(balance.toFixed(2));
                    
                    if (balance < totalAmount) {
                        $('#insufficientFundsAlert').show();
                        $('#submitPayment').prop('disabled', true);
                    } else {
                        $('#insufficientFundsAlert').hide();
                        $('#submitPayment').prop('disabled', false);
                    }
                })
                .fail(function() {
                    $('#currentBalance').text('0.00');
                    $('#insufficientFundsAlert').show();
                    $('#submitPayment').prop('disabled', true);
                });
        @endif
    }

    // Add form submission handler
    $('#paymentForm').on('submit', function(e) {
        e.preventDefault();
        
        const paymentType = $('#paymentType').val();
        const amountTendered = parseFloat($('#amountTendered').val()) || 0;
        const change = amountTendered - totalAmount;

        if (paymentType === 'cash' && amountTendered < totalAmount) {
            Swal.fire({
                icon: 'error',
                title: 'Insufficient Amount',
                text: 'The amount tendered is less than the total amount.'
            });
            return;
        }

        // Show confirmation dialog
        Swal.fire({
            title: 'Confirm Payment',
            html: `
                <div class="text-start">
                    <p>Order Total: ₱${totalAmount.toFixed(2)}</p>
                    ${paymentType === 'cash' ? `
                        <p>Amount Tendered: ₱${amountTendered.toFixed(2)}</p>
                        <p>Change: ₱${change.toFixed(2)}</p>
                    ` : '<p>Payment Method: Student Deposit</p>'}
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Process Payment',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Processing Payment',
                    text: 'Please wait...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Submit form via AJAX
                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            // Show success message with payment details
                            Swal.fire({
                                icon: 'success',
                                title: 'Payment Processed',
                                html: `
                                    <div class="text-center">
                                        <h6 class="mb-3">Order #${response.order_number}</h6>
                                        <p class="mb-2">Total Amount: ₱${totalAmount.toFixed(2)}</p>
                                        ${paymentType === 'cash' ? `
                                            <p class="mb-2">Amount Tendered: ₱${amountTendered.toFixed(2)}</p>
                                            <p class="mb-4">Change: ₱${change.toFixed(2)}</p>
                                        ` : '<p class="mb-4">Paid from student deposit</p>'}
                                    </div>
                                `,
                                confirmButtonText: 'Done'
                            }).then((result) => {
                                window.location.href = '{{ route("pos.orders.index") }}';
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Payment Failed',
                                text: response.message || 'Failed to process payment'
                            });
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON || {};
                        Swal.fire({
                            icon: 'error',
                            title: 'Payment Failed',
                            text: response.message || 'An error occurred while processing the payment'
                        });
                    }
                });
            }
        });
    });
});
</script>
@endpush 