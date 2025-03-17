@extends('layouts.app')

@section('title', 'Cashiering')

@section('styles')
<style>
    .cashier-container {
        display: flex;
        height: calc(100vh - 60px);
        overflow: hidden;
    }
    
    .orders-panel {
        width: 400px;
        background: #f8f9fa;
        border-right: 1px solid #dee2e6;
        display: flex;
        flex-direction: column;
    }
    
    .orders-header {
        padding: 1rem;
        background: #e9ecef;
        border-bottom: 1px solid #dee2e6;
    }
    
    .orders-list {
        flex: 1;
        overflow-y: auto;
        padding: 1rem;
    }
    
    .order-card {
        background: white;
        border-radius: 0.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        margin-bottom: 1rem;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .order-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .order-card.active {
        border: 2px solid #007bff;
    }
    
    .order-header {
        padding: 1rem;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .order-body {
        padding: 1rem;
    }
    
    .order-detail-panel {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    
    .order-details {
        flex: 1;
        padding: 1.5rem;
        overflow-y: auto;
    }
    
    .order-items {
        margin-top: 1.5rem;
    }
    
    .payment-panel {
        padding: 1.5rem;
        background: #f8f9fa;
        border-top: 1px solid #dee2e6;
    }
    
    .payment-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
    }
    
    .payment-label {
        font-weight: 500;
    }
    
    .payment-value {
        font-weight: 700;
    }
    
    .payment-total {
        font-size: 1.5rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #dee2e6;
    }
    
    .no-order-selected {
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 2rem;
        color: #6c757d;
    }
    
    .no-order-selected i {
        font-size: 4rem;
        margin-bottom: 1rem;
    }
    
    /* Responsive adjustments */
    @media (max-width: 992px) {
        .cashier-container {
            flex-direction: column;
        }
        
        .orders-panel {
            width: 100%;
            height: 40%;
        }
    }
</style>
@endsection

@section('content')
<div class="cashier-container">
    <!-- Orders Panel -->
    <div class="orders-panel">
        <div class="orders-header">
            <h4>Pending Orders</h4>
            <div class="d-flex justify-content-between align-items-center mt-2">
                <span class="text-muted">{{ count($pendingOrders) }} pending orders</span>
                <button id="refreshOrdersBtn" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>
        </div>
        <div class="orders-list" id="ordersList">
            @forelse($pendingOrders as $order)
                <div class="order-card" data-order-id="{{ $order->OrderID }}">
                    <div class="order-header">
                        <div>
                            <strong>Order #{{ str_pad($order->OrderID, 6, '0', STR_PAD_LEFT) }}</strong>
                            <div class="text-muted">{{ $order->OrderDate->format('M d, Y h:i A') }}</div>
                        </div>
                        <div class="text-primary fw-bold">₱{{ number_format($order->TotalAmount, 2) }}</div>
                    </div>
                    <div class="order-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div><strong>Items:</strong> {{ $order->items->count() }}</div>
                                <div><strong>Payment:</strong> {{ ucfirst($order->PaymentMethod) }}</div>
                            </div>
                            @if($order->student)
                                <div class="text-end">
                                    <div><strong>Student:</strong></div>
                                    <div>{{ $order->student->FirstName }} {{ $order->student->LastName }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="bi bi-clipboard-check" style="font-size: 3rem; color: #6c757d;"></i>
                    <p class="mt-3">No pending orders</p>
                </div>
            @endforelse
        </div>
    </div>
    
    <!-- Order Detail Panel -->
    <div class="order-detail-panel">
        <!-- Initially show empty state -->
        <div id="noOrderSelected" class="no-order-selected">
            <i class="bi bi-basket"></i>
            <h4>No Order Selected</h4>
            <p class="text-muted">Select an order from the list to view details and process payment</p>
        </div>
        
        <!-- Order details will be shown here -->
        <div id="orderDetailsPanel" style="display: none; height: 100%;">
            <div class="order-details">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3>Order #<span id="orderNumber"></span></h3>
                        <p class="text-muted">Placed on <span id="orderDate"></span></p>
                    </div>
                    <span class="badge bg-warning px-3 py-2" id="orderStatus">Pending</span>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <h5>Payment Method</h5>
                        <p id="paymentMethod"></p>
                    </div>
                    <div class="col-md-6" id="studentInfoSection">
                        <h5>Student Information</h5>
                        <p id="studentInfo">-</p>
                    </div>
                </div>
                
                <div class="order-items">
                    <h5>Order Items</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="orderItemsList">
                                <!-- Order items will be inserted here -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Total:</th>
                                    <th class="text-end" id="orderTotal"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="payment-panel">
                <h4>Process Payment</h4>
                
                <div id="cashPaymentSection">
                    <div class="mb-3">
                        <label for="amountTendered" class="form-label">Amount Tendered (₱)</label>
                        <input type="number" class="form-control form-control-lg" id="amountTendered" placeholder="Enter amount">
                    </div>
                    
                    <div class="payment-row">
                        <span class="payment-label">Total Amount:</span>
                        <span class="payment-value" id="paymentTotal"></span>
                    </div>
                    
                    <div class="payment-row">
                        <span class="payment-label">Amount Tendered:</span>
                        <span class="payment-value" id="paymentTendered">₱0.00</span>
                    </div>
                    
                    <div class="payment-row payment-total">
                        <span class="payment-label">Change:</span>
                        <span class="payment-value" id="paymentChange">₱0.00</span>
                    </div>
                </div>
                
                <div id="depositPaymentSection" style="display: none;">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <span id="depositBalanceInfo"></span>
                    </div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <button class="btn btn-outline-secondary me-md-2" id="cancelOrderBtn">Cancel Order</button>
                    <button class="btn btn-primary" id="completePaymentBtn">Complete Payment</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment Successful</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                <h4 class="mt-3">Payment Completed</h4>
                <p>Order #<span id="receiptOrderNumber"></span> has been processed successfully.</p>
                
                <div class="mt-4">
                    <a href="#" id="viewReceiptBtn" class="btn btn-outline-primary" target="_blank">
                        <i class="bi bi-printer"></i> View Receipt
                    </a>
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
    document.addEventListener('DOMContentLoaded', function() {
        const ordersList = document.getElementById('ordersList');
        const noOrderSelected = document.getElementById('noOrderSelected');
        const orderDetailsPanel = document.getElementById('orderDetailsPanel');
        const orderNumber = document.getElementById('orderNumber');
        const orderDate = document.getElementById('orderDate');
        const orderStatus = document.getElementById('orderStatus');
        const paymentMethod = document.getElementById('paymentMethod');
        const studentInfoSection = document.getElementById('studentInfoSection');
        const studentInfo = document.getElementById('studentInfo');
        const orderItemsList = document.getElementById('orderItemsList');
        const orderTotal = document.getElementById('orderTotal');
        const paymentTotal = document.getElementById('paymentTotal');
        const amountTendered = document.getElementById('amountTendered');
        const paymentTendered = document.getElementById('paymentTendered');
        const paymentChange = document.getElementById('paymentChange');
        const cashPaymentSection = document.getElementById('cashPaymentSection');
        const depositPaymentSection = document.getElementById('depositPaymentSection');
        const depositBalanceInfo = document.getElementById('depositBalanceInfo');
        const completePaymentBtn = document.getElementById('completePaymentBtn');
        const refreshOrdersBtn = document.getElementById('refreshOrdersBtn');
        
        let selectedOrderId = null;
        let selectedOrder = null;
        
        // Initialize order cards click events
        initializeOrderCards();
        
        function initializeOrderCards() {
            const orderCards = document.querySelectorAll('.order-card');
            
            orderCards.forEach(card => {
                card.addEventListener('click', function() {
                    // Remove active class from all cards
                    orderCards.forEach(c => c.classList.remove('active'));
                    
                    // Add active class to clicked card
                    this.classList.add('active');
                    
                    // Get order ID and load details
                    const orderId = this.dataset.orderId;
                    loadOrderDetails(orderId);
                });
            });
        }
        
        // Load order details
        async function loadOrderDetails(orderId) {
            selectedOrderId = orderId;
            
            // For now, we'll just get the order from the DOM data
            // In a real app, you might want to fetch fresh data from the server
            const orderCard = document.querySelector(`.order-card[data-order-id="${orderId}"]`);
            
            if (!orderCard) return;
            
            // Show order details panel, hide empty state
            noOrderSelected.style.display = 'none';
            orderDetailsPanel.style.display = 'flex';
            
            // Get order data from the server
            try {
                const response = await fetch(`/inventory/pos/cashier/orders?order_id=${orderId}`);
                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.message || 'Failed to load order details');
                }
                
                selectedOrder = data.order;
                displayOrderDetails(selectedOrder);
                
            } catch (error) {
                console.error('Error loading order details:', error);
                alert('Failed to load order details. Please try again.');
            }
        }
        
        // Display order details
        function displayOrderDetails(order) {
            // Set basic order info
            orderNumber.textContent = order.order_number;
            orderDate.textContent = new Date(order.OrderDate).toLocaleString();
            paymentMethod.textContent = order.PaymentMethod === 'cash' ? 'Cash' : 'Cash Deposit';
            
            // Show/hide student info
            if (order.student) {
                studentInfoSection.style.display = 'block';
                studentInfo.textContent = `${order.student.FirstName} ${order.student.LastName}`;
            } else {
                studentInfoSection.style.display = 'none';
            }
            
            // Display order items
            let itemsHtml = '';
            
            order.items.forEach(item => {
                itemsHtml += `
                    <tr>
                        <td>${item.item.ItemName}</td>
                        <td class="text-center">${item.Quantity}</td>
                        <td class="text-end">₱${parseFloat(item.UnitPrice).toFixed(2)}</td>
                        <td class="text-end">₱${parseFloat(item.Subtotal).toFixed(2)}</td>
                    </tr>
                `;
            });
            
            orderItemsList.innerHTML = itemsHtml;
            orderTotal.textContent = `₱${parseFloat(order.TotalAmount).toFixed(2)}`;
            paymentTotal.textContent = `₱${parseFloat(order.TotalAmount).toFixed(2)}`;
            
            // Set up payment sections based on payment method
            if (order.PaymentMethod === 'cash') {
                cashPaymentSection.style.display = 'block';
                depositPaymentSection.style.display = 'none';
            } else {
                cashPaymentSection.style.display = 'none';
                depositPaymentSection.style.display = 'block';
                
                // Display student deposit info
                if (order.student) {
                    fetchStudentBalance(order.student.StudentID);
                }
            }
            
            // Clear amount tendered
            amountTendered.value = '';
            paymentTendered.textContent = '₱0.00';
            paymentChange.textContent = '₱0.00';
        }
        
        // Fetch student balance for deposit payment
        async function fetchStudentBalance(studentId) {
            try {
                const response = await fetch(`/inventory/pos/deposit/balance/${studentId}`);
                const data = await response.json();
                
                if (data.success) {
                    const balance = parseFloat(data.balance);
                    const orderAmount = parseFloat(selectedOrder.TotalAmount);
                    
                    if (balance >= orderAmount) {
                        depositBalanceInfo.innerHTML = `
                            Student has sufficient funds.<br>
                            Current Balance: <strong>₱${balance.toFixed(2)}</strong><br>
                            Order Amount: <strong>₱${orderAmount.toFixed(2)}</strong><br>
                            Remaining Balance after payment: <strong>₱${(balance - orderAmount).toFixed(2)}</strong>
                        `;
                        completePaymentBtn.disabled = false;
                    } else {
                        depositBalanceInfo.innerHTML = `
                            <strong>Insufficient funds!</strong><br>
                            Current Balance: <strong>₱${balance.toFixed(2)}</strong><br>
                            Order Amount: <strong>₱${orderAmount.toFixed(2)}</strong><br>
                            Shortfall: <strong>₱${(orderAmount - balance).toFixed(2)}</strong>
                        `;
                        completePaymentBtn.disabled = true;
                    }
                } else {
                    throw new Error(data.message || 'Failed to fetch student balance');
                }
                
            } catch (error) {
                console.error('Error fetching student balance:', error);
                depositBalanceInfo.innerHTML = 'Error fetching student balance. Please try again.';
                completePaymentBtn.disabled = true;
            }
        }
        
        // Handle amount tendered input
        amountTendered.addEventListener('input', function() {
            const amount = parseFloat(this.value) || 0;
            const total = parseFloat(selectedOrder?.TotalAmount) || 0;
            const change = amount - total;
            
            paymentTendered.textContent = `₱${amount.toFixed(2)}`;
            paymentChange.textContent = `₱${Math.max(0, change).toFixed(2)}`;
            
            // Enable/disable complete payment button
            completePaymentBtn.disabled = amount < total;
        });
        
        // Handle complete payment button
        completePaymentBtn.addEventListener('click', async function() {
            if (!selectedOrderId) return;
            
            const paymentData = {
                order_id: selectedOrderId,
                payment_method: selectedOrder.PaymentMethod,
                amount_tendered: selectedOrder.PaymentMethod === 'cash' ? parseFloat(amountTendered.value) : null
            };
            
            try {
                const response = await fetch('/inventory/pos/cashier/process-payment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(paymentData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Show receipt modal
                    document.getElementById('receiptOrderNumber').textContent = orderNumber.textContent;
                    document.getElementById('viewReceiptBtn').href = result.receipt_url;
                    
                    const modal = new bootstrap.Modal(document.getElementById('receiptModal'));
                    modal.show();
                    
                    // Remove the order from the list
                    const orderCard = document.querySelector(`.order-card[data-order-id="${selectedOrderId}"]`);
                    if (orderCard) {
                        orderCard.remove();
                    }
                    
                    // Reset selected order
                    selectedOrderId = null;
                    selectedOrder = null;
                    
                    // Show empty state if no more orders
                    if (document.querySelectorAll('.order-card').length === 0) {
                        orderDetailsPanel.style.display = 'none';
                        noOrderSelected.style.display = 'flex';
                    } else {
                        // Select first order if available
                        const firstOrder = document.querySelector('.order-card');
                        if (firstOrder) {
                            firstOrder.click();
                        }
                    }
                } else {
                    throw new Error(result.message || 'Failed to process payment');
                }
                
            } catch (error) {
                console.error('Error processing payment:', error);
                alert('Failed to process payment: ' + error.message);
            }
        });
        
        // Handle refresh orders button
        refreshOrdersBtn.addEventListener('click', async function() {
            try {
                const response = await fetch('/inventory/pos/cashier/orders');
                const data = await response.json();
                
                // Update orders list
                let html = '';
                
                if (data.length === 0) {
                    html = `
                        <div class="text-center py-5">
                            <i class="bi bi-clipboard-check" style="font-size: 3rem; color: #6c757d;"></i>
                            <p class="mt-3">No pending orders</p>
                        </div>
                    `;
                    
                    // Reset selected order if any
                    selectedOrderId = null;
                    selectedOrder = null;
                    
                    // Show empty state
                    orderDetailsPanel.style.display = 'none';
                    noOrderSelected.style.display = 'flex';
                } else {
                    data.forEach(order => {
                        html += `
                            <div class="order-card ${order.OrderID === selectedOrderId ? 'active' : ''}" data-order-id="${order.OrderID}">
                                <div class="order-header">
                                    <div>
                                        <strong>Order #${order.order_number}</strong>
                                        <div class="text-muted">${new Date(order.OrderDate).toLocaleString()}</div>
                                    </div>
                                    <div class="text-primary fw-bold">₱${parseFloat(order.TotalAmount).toFixed(2)}</div>
                                </div>
                                <div class="order-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <div><strong>Items:</strong> ${order.items.length}</div>
                                            <div><strong>Payment:</strong> ${order.PaymentMethod === 'cash' ? 'Cash' : 'Cash Deposit'}</div>
                                        </div>
                                        ${order.student ? `
                                            <div class="text-end">
                                                <div><strong>Student:</strong></div>
                                                <div>${order.student.FirstName} ${order.student.LastName}</div>
                                            </div>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                }
                
                ordersList.innerHTML = html;
                
                // Re-initialize order cards
                initializeOrderCards();
                
                // If we had a selected order, try to select it again
                if (selectedOrderId) {
                    const orderCard = document.querySelector(`.order-card[data-order-id="${selectedOrderId}"]`);
                    if (orderCard) {
                        orderCard.click();
                    } else {
                        // If the order is no longer available, select the first one
                        const firstOrder = document.querySelector('.order-card');
                        if (firstOrder) {
                            firstOrder.click();
                        }
                    }
                }
                
            } catch (error) {
                console.error('Error refreshing orders:', error);
                alert('Failed to refresh orders. Please try again.');
            }
        });
    });
</script>
@endsection 