@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Order Processing</h5>
                    <div>
                        <a href="{{ route('pos.index') }}" class="btn btn-sm btn-light">
                            <i class="bi bi-list"></i> All Orders
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Pending Orders Column -->
        <div class="col-lg-7 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Pending Orders</h5>
                </div>
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

                    @if(count($pendingOrders) > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingOrders as $order)
                                <tr class="order-row" data-order-id="{{ $order->OrderID }}">
                                    <td>{{ $order->OrderNumber }}</td>
                                    <td>{{ date('M d, Y g:i A', strtotime($order->created_at)) }}</td>
                                    <td>
                                        @if($order->student_id)
                                        <span class="badge bg-info">Student</span>
                                        @else
                                        <span class="badge bg-secondary">Guest</span>
                                        @endif
                                    </td>
                                    <td>₱{{ number_format($order->TotalAmount, 2) }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary view-order-btn" data-order-id="{{ $order->OrderID }}">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                        <button class="btn btn-sm btn-success process-order-btn" data-order-id="{{ $order->OrderID }}" data-bs-toggle="modal" data-bs-target="#paymentModal">
                                            <i class="bi bi-cash"></i> Process
                                        </button>
                                        <a href="{{ route('pos.process.byid', $order->OrderID) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-check-circle"></i> Complete
                                        </a>
                                        <button class="btn btn-sm btn-danger cancel-order-btn" data-order-id="{{ $order->OrderID }}">
                                            <i class="bi bi-x-circle"></i> Cancel
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info">
                        No pending orders to process.
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Order Details Column -->
        <div class="col-lg-5 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Order Details</h5>
                </div>
                <div class="card-body">
                    <div id="orderDetailsPlaceholder">
                        <div class="text-center py-5">
                            <i class="bi bi-cart3" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="mt-3 text-muted">Select an order to view details</p>
                        </div>
                    </div>
                    
                    <div id="orderDetails" class="d-none">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0" id="orderNumber"></h5>
                            <span class="badge bg-warning" id="orderStatus"></span>
                        </div>

                        <div class="mb-3">
                            <span class="small text-muted">Order Date:</span>
                            <span id="orderDate"></span>
                        </div>

                        <div id="studentInfo" class="mb-3 d-none">
                            <span class="small text-muted">Student:</span>
                            <span id="studentName"></span>
                        </div>

                        <div class="table-responsive mb-3">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="orderItems">
                                    <!-- Order items will be inserted here -->
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="3" class="text-end">Total:</th>
                                        <th class="text-end" id="orderTotal"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div id="paymentActions" class="d-grid gap-2">
                            <button class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#paymentModal">
                                <i class="bi bi-cash"></i> Process Payment
                            </button>
                            <a id="completeOrderLink" href="" class="btn btn-info w-100">
                                <i class="bi bi-check-circle"></i> Complete Order
                            </a>
                            <button id="cancelOrderBtn" class="btn btn-danger w-100">
                                <i class="bi bi-x-circle"></i> Cancel Order
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Process Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="paymentForm" method="POST" action="">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="orderNumberDisplay" class="form-label">Order Number</label>
                        <input type="text" class="form-control" id="orderNumberDisplay" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="totalAmountDisplay" class="form-label">Total Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="text" class="form-control" id="totalAmountDisplay" readonly>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_type" class="form-label">Payment Method</label>
                        <select class="form-select" id="payment_type" name="payment_type" required>
                            <option value="cash">Cash</option>
                            <option value="deposit" id="depositOption">Student Deposit</option>
                        </select>
                    </div>
                    
                    <div id="cashPaymentFields">
                        <div class="mb-3">
                            <label for="payment_amount" class="form-label">Amount Tendered</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" min="0" class="form-control" id="payment_amount" name="payment_amount" required>
                            </div>
                        </div>
                        
                        <!-- Quick Cash Amount Buttons -->
                        <div class="mb-3 d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm quick-cash-btn" data-amount="100">₱100</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm quick-cash-btn" data-amount="200">₱200</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm quick-cash-btn" data-amount="500">₱500</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm quick-cash-btn" data-amount="1000">₱1000</button>
                            <button type="button" class="btn btn-outline-primary btn-sm quick-cash-btn" data-amount="exact">Exact</button>
                        </div>
                        
                        <div class="mb-3">
                            <label for="changeAmount" class="form-label">Change</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="text" class="form-control" id="changeAmount" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div id="depositPaymentFields" class="d-none">
                        <div class="alert alert-info mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Current Balance:</span>
                                <span class="fw-bold" id="currentBalance">₱0.00</span>
                            </div>
                        </div>
                        
                        <div id="insufficientFundsAlert" class="alert alert-danger d-none">
                            Insufficient funds in student account.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="processPaymentBtn">Process Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancel Order Confirmation Modal -->
<div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelOrderModalLabel">Cancel Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel order <span id="cancelOrderNumber"></span>?</p>
                <p>This action cannot be undone and will return inventory items to stock.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Keep Order</button>
                <a href="#" id="confirmCancelOrderBtn" class="btn btn-danger">Yes, Cancel Order</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        let currentOrderId = null;
        let selectedOrder = null;
        
        // View order details
        $('.view-order-btn').on('click', function() {
            const orderId = $(this).data('order-id');
            loadOrderDetails(orderId);
        });
        
        // Click on order row to view details
        $('.order-row').on('click', function() {
            const orderId = $(this).data('order-id');
            loadOrderDetails(orderId);
        });
        
        // Load order details via AJAX
        function loadOrderDetails(orderId) {
            currentOrderId = orderId;
            
            $.ajax({
                url: `/inventory/pos/orders/${orderId}/details`,
                type: 'GET',
                success: function(response) {
                    if (!response.error) {
                        displayOrderDetails(response);
                    } else {
                        alert('Error loading order details: ' + response.error);
                    }
                },
                error: function(xhr) {
                    alert('Failed to load order details');
                }
            });
        }
        
        // Display order details in the sidebar
        function displayOrderDetails(data) {
            selectedOrder = data.order;
            
            // Show order details section
            $('#orderDetailsPlaceholder').addClass('d-none');
            $('#orderDetails').removeClass('d-none');
            
            // Set order details
            $('#orderNumber').text(`Order #${selectedOrder.OrderNumber}`);
            $('#orderStatus').text(selectedOrder.Status);
            $('#orderDate').text(new Date(selectedOrder.created_at).toLocaleString());
            
            // Set order items
            $('#orderItems').empty();
            let total = 0;
            
            data.items.forEach(item => {
                $('#orderItems').append(`
                    <tr>
                        <td>${item.ItemName}</td>
                        <td class="text-center">${item.Quantity}</td>
                        <td class="text-end">₱${parseFloat(item.UnitPrice).toFixed(2)}</td>
                        <td class="text-end">₱${parseFloat(item.Subtotal).toFixed(2)}</td>
                    </tr>
                `);
                
                total += parseFloat(item.Subtotal);
            });
            
            $('#orderTotal').text(`₱${total.toFixed(2)}`);
            
            // Student info section
            if (selectedOrder.student) {
                $('#studentInfo').removeClass('d-none');
                $('#studentName').text(selectedOrder.student.name);
                $('#depositOption').prop('disabled', false);
            } else {
                $('#studentInfo').addClass('d-none');
                $('#depositOption').prop('disabled', true);
                // Switch to cash payment if deposit is selected
                if ($('#payment_type').val() === 'deposit') {
                    $('#payment_type').val('cash');
                    $('#payment_type').trigger('change');
                }
            }
            
            // Update action links and buttons
            $('#paymentForm').attr('action', `/inventory/pos/process-payment/${currentOrderId}`);
            $('#completeOrderLink').attr('href', `/inventory/pos/process/${currentOrderId}`);
            
            // Update payment modal info
            $('#orderNumberDisplay').val(selectedOrder.OrderNumber);
            $('#totalAmountDisplay').val(parseFloat(selectedOrder.TotalAmount).toFixed(2));
            
            // Auto-fill payment amount with total amount for cash payment
            if ($('#payment_type').val() === 'cash') {
                $('#payment_amount').val(parseFloat(selectedOrder.TotalAmount).toFixed(2));
                $('#payment_amount').trigger('input'); // Calculate change
            } else {
                // Reset payment form for other payment methods
                $('#payment_amount').val('');
                $('#changeAmount').val('');
            }
            
            // If student order, get balance for deposit option
            if (selectedOrder.student && selectedOrder.student.id) {
                $.ajax({
                    url: `/inventory/pos/student-balance/${selectedOrder.student.id}`,
                    type: 'GET',
                    success: function(response) {
                        const balance = parseFloat(response.balance);
                        $('#currentBalance').text(`₱${balance.toFixed(2)}`);
                        
                        // Check if balance is sufficient
                        if (balance < parseFloat(selectedOrder.TotalAmount)) {
                            $('#insufficientFundsAlert').removeClass('d-none');
                            // Disable deposit option
                            $('#depositOption').attr('disabled', true);
                        } else {
                            $('#insufficientFundsAlert').addClass('d-none');
                            // Enable deposit option
                            $('#depositOption').attr('disabled', false);
                        }
                    },
                    error: function() {
                        $('#currentBalance').text('₱0.00');
                        $('#insufficientFundsAlert').removeClass('d-none');
                        $('#depositOption').attr('disabled', true);
                    }
                });
            }
        }
        
        // Handle payment method change
        $('#payment_type').on('change', function() {
            const method = $(this).val();
            
            if (method === 'cash') {
                $('#cashPaymentFields').removeClass('d-none');
                $('#depositPaymentFields').addClass('d-none');
                
                // Auto-fill the payment amount with the total amount
                const totalAmount = parseFloat($('#totalAmountDisplay').val()) || 0;
                $('#payment_amount').val(totalAmount.toFixed(2));
                $('#payment_amount').trigger('input'); // Trigger calculation of change
                
            } else {
                $('#cashPaymentFields').addClass('d-none');
                $('#depositPaymentFields').removeClass('d-none');
            }
        });
        
        // Handle quick cash amount buttons
        $('.quick-cash-btn').on('click', function() {
            const amount = $(this).data('amount');
            
            if (amount === 'exact') {
                // Set exact amount
                const totalAmount = parseFloat($('#totalAmountDisplay').val()) || 0;
                $('#payment_amount').val(totalAmount.toFixed(2));
            } else {
                // Set predefined amount
                $('#payment_amount').val(amount);
            }
            
            // Trigger change calculation
            $('#payment_amount').trigger('input');
        });
        
        // Calculate change
        $('#payment_amount').on('input', function() {
            const amountTendered = parseFloat($(this).val()) || 0;
            const totalAmount = parseFloat($('#totalAmountDisplay').val()) || 0;
            const change = amountTendered - totalAmount;
            
            $('#changeAmount').val(change.toFixed(2));
            
            // Disable button if amount is less than total
            if (amountTendered < totalAmount) {
                $('#processPaymentBtn').attr('disabled', true);
            } else {
                $('#processPaymentBtn').attr('disabled', false);
            }
        });
        
        // Handle opening the payment modal from the process button
        $('.process-order-btn').on('click', function() {
            const orderId = $(this).data('order-id');
            loadOrderDetails(orderId);
        });
        
        // Handle cancel order button
        $('#cancelOrderBtn, .cancel-order-btn').on('click', function() {
            if (!currentOrderId && $(this).hasClass('cancel-order-btn')) {
                currentOrderId = $(this).data('order-id');
            }
            
            if (currentOrderId) {
                // Get order number to display in confirmation
                const orderNumber = $('#orderNumber').text() || `Order #${currentOrderId}`;
                $('#cancelOrderNumber').text(orderNumber);
                
                // Set the cancel link
                $('#confirmCancelOrderBtn').attr('href', `/inventory/pos/cancel-order/${currentOrderId}`);
                
                // Show modal
                $('#cancelOrderModal').modal('show');
            }
        });
    });
</script>
@endsection 