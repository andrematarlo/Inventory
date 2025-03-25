@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card bg-primary mb-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <h1 class="text-white h2 mb-0">Order Details:</h1>
                    <a href="{{ route('pos.index') }}" class="btn btn-light">
                        <i class="bi bi-arrow-left me-1"></i> Back to Orders
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Order Information -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Order Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Order Number:</div>
                        <div class="col-md-8" id="orderNumber">Loading...</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Date Created:</div>
                        <div class="col-md-8" id="dateCreated">Loading...</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Customer:</div>
                        <div class="col-md-8" id="customer">Loading...</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Status:</div>
                        <div class="col-md-8" id="status">Loading...</div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Payment Method:</div>
                        <div class="col-md-8" id="paymentMethod">Loading...</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Order Items -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Order Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="orderItems">
                                <tr>
                                    <td colspan="5" class="text-center">Loading order items...</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">Total Amount:</th>
                                    <th class="text-end" id="totalAmount">₱0.00</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="row mb-4" id="actionButtons">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="btn-group" id="orderActions">
                        <button class="btn btn-success" id="processOrderBtn">
                            <i class="bi bi-check-circle"></i> Process Order
                        </button>
                        <button class="btn btn-danger" id="cancelOrderBtn">
                            <i class="bi bi-x-circle"></i> Cancel Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Confirmation Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelModalLabel">Cancel Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to cancel this order? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Keep Order</button>
                <button type="button" class="btn btn-danger" id="confirmCancelBtn">Yes, Cancel Order</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        const orderId = window.location.pathname.split('/').pop();
        
        // Load order details
        $.ajax({
            url: `/inventory/pos/orders/${orderId}/details`,
            type: 'GET',
            success: function(response) {
                if (response.error) {
                    console.error('Error loading order details:', response.error);
                    return;
                }
                
                const order = response.order;
                const items = response.items;
                
                // Update order information
                $('#orderNumber').text(order.OrderNumber);
                $('#dateCreated').text(new Date(order.created_at).toLocaleString());
                $('#customer').html(order.student ? `${order.student.name} <span class="badge bg-info">Student</span>` : 'Walk-in Customer');
                
                // Set status with appropriate badge color
                const statusBadgeClass = order.Status === 'completed' ? 'bg-success' : 
                                        (order.Status === 'pending' ? 'bg-warning text-dark' : 'bg-danger');
                $('#status').html(`<span class="badge ${statusBadgeClass}">${order.Status.charAt(0).toUpperCase() + order.Status.slice(1)}</span>`);
                
                // Set payment method with appropriate badge color
                const paymentBadgeClass = order.PaymentMethod === 'cash' ? 'bg-success' : 'bg-info';
                $('#paymentMethod').html(`<span class="badge ${paymentBadgeClass}">${order.PaymentMethod.charAt(0).toUpperCase() + order.PaymentMethod.slice(1)}</span>`);
                
                // Update order items
                let itemsHtml = '';
                let total = 0;
                
                if (items.length === 0) {
                    itemsHtml = '<tr><td colspan="5" class="text-center">No items found for this order</td></tr>';
                } else {
                    items.forEach((item, index) => {
                        const subtotal = parseFloat(item.Subtotal);
                        total += subtotal;
                        
                        itemsHtml += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${item.ItemName} ${item.IsCustomItem ? '<span class="badge bg-secondary">Custom</span>' : ''}</td>
                                <td class="text-center">${item.Quantity}</td>
                                <td class="text-end">₱${parseFloat(item.UnitPrice).toFixed(2)}</td>
                                <td class="text-end">₱${subtotal.toFixed(2)}</td>
                            </tr>
                        `;
                    });
                }
                
                $('#orderItems').html(itemsHtml);
                $('#totalAmount').text(`₱${total.toFixed(2)}`);
                
                // Update action buttons based on order status
                if (order.Status !== 'pending') {
                    $('#actionButtons').hide();
                } else {
                    // Set up process button
                    $('#processOrderBtn').on('click', function() {
                        window.location.href = `/inventory/pos/process/${orderId}`;
                    });
                    
                    // Set up cancel button
                    $('#cancelOrderBtn').on('click', function() {
                        $('#cancelModal').modal('show');
                    });
                    
                    // Set up cancel confirmation
                    $('#confirmCancelBtn').on('click', function() {
                        window.location.href = `/inventory/pos/cancel-order/${orderId}`;
                    });
                }
            },
            error: function(xhr) {
                console.error('Failed to load order details', xhr);
                alert('Failed to load order details. Please try refreshing the page.');
            }
        });
    });
</script>
@endsection 