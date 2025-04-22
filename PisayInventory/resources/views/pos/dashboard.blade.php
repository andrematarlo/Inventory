@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h1 class="fs-2 fw-bold mb-0">Orders Dashboard</h1>
                        <p class="mb-0 opacity-75">Monitor and manage orders in real-time</p>
                    </div>
                    <div>
                        <a href="{{ route('pos.index') }}" class="btn btn-light">
                            <i class="bi bi-arrow-left"></i> Back to POS
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Preparing Orders -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-clock-history me-2"></i>Preparing Orders
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Time</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($preparingOrders as $order)
                                <tr>
                                    <td>{{ $order->OrderNumber }}</td>
                                    <td>
                                        {{ $order->customer_name ?? 'Walk-in Customer' }}
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-link view-items" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#orderItemsModal" 
                                                data-order-id="{{ $order->OrderID }}">
                                            View Items ({{ $order->items_count }})
                                        </button>
                                    </td>
                                    <td>₱{{ number_format($order->TotalAmount, 2) }}</td>
                                    <td>{{ $order->created_at->format('h:i A') }}</td>
                                    <td>
                                        <a href="{{ route('pos.process.byid', $order->OrderID) }}" 
                                           class="btn btn-sm btn-success mark-ready" 
                                           data-order-id="{{ $order->OrderID }}">
                                            <i class="bi bi-check-circle"></i> Serve
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-clock-history fs-4 d-block mb-2"></i>
                                            No orders currently being prepared
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ready Orders -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-check-circle me-2"></i>Ready to Serve
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Time</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($readyOrders as $order)
                                <tr>
                                    <td>{{ $order->OrderNumber }}</td>
                                    <td>
                                        {{ $order->customer_name ?? 'Walk-in Customer' }}
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-link view-items" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#orderItemsModal" 
                                                data-order-id="{{ $order->OrderID }}">
                                            View Items ({{ $order->items_count }})
                                        </button>
                                    </td>
                                    <td>₱{{ number_format($order->TotalAmount, 2) }}</td>
                                    <td>{{ $order->created_at->format('h:i A') }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary claim-order" 
                                                data-order-id="{{ $order->OrderID }}">
                                            <i class="bi bi-check-circle"></i> Claim
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bi bi-check-circle fs-4 d-block mb-2"></i>
                                            No orders ready to serve
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order Items Modal -->
<div class="modal fade" id="orderItemsModal" tabindex="-1">
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
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="orderItemsTableBody">
                            <!-- Items will be loaded here via AJAX -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                <td id="orderItemsTotal">₱0.00</td>
                            </tr>
                        </tfoot>
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
    // Auto-refresh the page every 10 seconds
    setInterval(function() {
        window.location.reload();
    }, 10000);

    // Handle view items button click
    $('.view-items').on('click', function() {
        const orderId = $(this).data('order-id');
        
        // Load order items via AJAX
        $.get(`/pos/orders/${orderId}/items`, function(response) {
            const tbody = $('#orderItemsTableBody');
            tbody.empty();
            
            let total = 0;
            
            response.items.forEach(function(item) {
                tbody.append(`
                    <tr>
                        <td>${item.ItemName}</td>
                        <td>${item.Quantity}</td>
                        <td>₱${parseFloat(item.UnitPrice).toFixed(2)}</td>
                        <td>₱${parseFloat(item.Subtotal).toFixed(2)}</td>
                    </tr>
                `);
                total += parseFloat(item.Subtotal);
            });
            
            $('#orderItemsTotal').text(`₱${total.toFixed(2)}`);
        });
    });

    // Handle mark ready button click
    $('.mark-ready').on('click', function(e) {
        e.preventDefault();
        const orderId = $(this).data('order-id');
        const button = $(this);
        
        if (confirm('Are you sure you want to mark this order as ready to serve?')) {
            $.ajax({
                url: `/pos/orders/${orderId}/process`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        alert(response.message);
                        // Reload the page to update the lists
                        window.location.reload();
                    } else {
                        alert(response.message);
                    }
                },
                error: function(xhr) {
                    alert(xhr.responseJSON?.message || 'Error processing order');
                }
            });
        }
    });

    // Handle claim button click
    $('.claim-order').on('click', function(e) {
        e.preventDefault();
        const orderId = $(this).data('order-id');
        const button = $(this);
        
        if (confirm('Are you sure you want to claim this order?')) {
            $.ajax({
                url: `/inventory/pos/orders/${orderId}/claim`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        // Remove the button's parent row
                        button.closest('tr').fadeOut(400, function() {
                            $(this).remove();
                        });
                        
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Order Claimed',
                            text: 'The order has been claimed successfully.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Failed to claim the order'
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Error claiming the order'
                    });
                }
            });
        }
    });
});
</script>
@endpush 