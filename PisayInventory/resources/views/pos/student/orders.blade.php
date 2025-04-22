@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">My Orders</h4>
        </div>
        <div class="card-body">
            @if($orders->isEmpty())
                <div class="text-center py-4">
                    <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                    <p class="lead">You haven't placed any orders yet.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Payment Method</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td>{{ $order->OrderNumber }}</td>
                                    <td>{{ \Carbon\Carbon::parse($order->created_at)->format('M d, Y h:i A') }}</td>
                                    <td>₱{{ number_format($order->TotalAmount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $order->Status === 'Pending' ? 'warning' : 
                                            ($order->Status === 'Paid' ? 'success' : 
                                            ($order->Status === 'Preparing' ? 'info' : 
                                            ($order->Status === 'Ready to Serve' ? 'primary' : 'danger'))) }}">
                                            {{ $order->Status }}
                                        </span>
                                    </td>
                                    <td>{{ $order->PaymentMethod }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info view-items" data-order-id="{{ $order->OrderID }}">
                                            <i class="fas fa-eye"></i> View Items
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Items Modal -->
<div class="modal fade" id="itemsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Items</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.view-items').click(function() {
        const orderId = $(this).data('order-id');
        const modal = $('#itemsModal');
        const tableBody = $('#itemsTableBody');
        
        // Clear previous items
        tableBody.empty();
        
        // Show loading
        tableBody.html('<tr><td colspan="4" class="text-center"><div class="spinner-border text-primary" role="status"></div></td></tr>');
        modal.modal('show');
        
        // Fetch items
        $.ajax({
            url: `/pos/my-orders/${orderId}/items`,
            method: 'GET',
            success: function(response) {
                tableBody.empty();
                let total = 0;
                
                if (response.items && response.items.length > 0) {
                    response.items.forEach(function(item) {
                        const subtotal = item.quantity * item.price;
                        total += subtotal;
                        
                        tableBody.append(`
                            <tr>
                                <td>${item.ItemName}</td>
                                <td class="text-center">${item.quantity}</td>
                                <td class="text-end">₱${parseFloat(item.price).toFixed(2)}</td>
                                <td class="text-end">₱${subtotal.toFixed(2)}</td>
                            </tr>
                        `);
                    });
                    
                    // Add total row
                    tableBody.append(`
                        <tr class="table-light fw-bold">
                            <td colspan="3" class="text-end">Total:</td>
                            <td class="text-end">₱${total.toFixed(2)}</td>
                        </tr>
                    `);
                } else {
                    tableBody.html(`
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                No items found for this order
                            </td>
                        </tr>
                    `);
                }
            },
            error: function(xhr) {
                let errorMessage = 'Failed to load order items';
                if (xhr.responseJSON) {
                    errorMessage = xhr.responseJSON.error;
                    if (xhr.responseJSON.message) {
                        errorMessage += '<br><small class="text-muted">' + xhr.responseJSON.message + '</small>';
                    }
                }
                tableBody.html(`
                    <tr>
                        <td colspan="4" class="text-center text-danger">
                            ${errorMessage}
                        </td>
                    </tr>
                `);
                console.error('Order items error:', xhr.responseJSON);
            }
        });
    });
});
</script>
@endpush 