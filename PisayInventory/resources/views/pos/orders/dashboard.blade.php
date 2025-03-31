@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Preparing Orders Section -->
        <div class="col-md-6 border-right">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0">Preparing Orders</h5>
                </div>
                <div class="card-body">
                    <div class="preparing-orders-list">
                        @foreach($preparingOrders as $order)
                        <div class="order-card mb-3 p-3 border rounded">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">Order #{{ $order->OrderNumber }}</h6>
                                <span class="badge bg-warning">Preparing</span>
                            </div>
                            <div class="order-details">
                                <p class="mb-1"><strong>Customer:</strong> {{ $order->customer_name ?? 'Walk-in Customer' }}</p>
                                <p class="mb-1"><strong>Total:</strong> ₱{{ number_format($order->TotalAmount, 2) }}</p>
                                <p class="mb-1"><strong>Time:</strong> {{ $order->created_at->format('h:i A') }}</p>
                            </div>
                            <div class="order-items mt-2">
                                <h6 class="mb-2">Items:</h6>
                                <ul class="list-unstyled mb-0">
                                    @foreach($order->items as $item)
                                    <li>{{ $item->ItemName }} x {{ $item->Quantity }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="mt-3">
                                <button class="btn btn-success btn-sm mark-ready" data-order-id="{{ $order->OrderID }}">
                                    Mark as Ready
                                </button>
                                <button class="btn btn-danger btn-sm mark-cancelled" data-order-id="{{ $order->OrderID }}">
                                    Cancel Order
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Ready to Serve Orders Section -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Ready to Serve</h5>
                </div>
                <div class="card-body">
                    <div class="ready-orders-list">
                        @foreach($readyOrders as $order)
                        <div class="order-card mb-3 p-3 border rounded">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">Order #{{ $order->OrderNumber }}</h6>
                                <span class="badge bg-success">Ready to Serve</span>
                            </div>
                            <div class="order-details">
                                <p class="mb-1"><strong>Customer:</strong> {{ $order->customer_name ?? 'Walk-in Customer' }}</p>
                                <p class="mb-1"><strong>Total:</strong> ₱{{ number_format($order->TotalAmount, 2) }}</p>
                                <p class="mb-1"><strong>Time:</strong> {{ $order->created_at->format('h:i A') }}</p>
                            </div>
                            <div class="order-items mt-2">
                                <h6 class="mb-2">Items:</h6>
                                <ul class="list-unstyled mb-0">
                                    @foreach($order->items as $item)
                                    <li>{{ $item->ItemName }} x {{ $item->Quantity }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="mt-3">
                                <button class="btn btn-primary btn-sm mark-served" data-order-id="{{ $order->OrderID }}">
                                    Mark as Served
                                </button>
                                <button class="btn btn-danger btn-sm mark-cancelled" data-order-id="{{ $order->OrderID }}">
                                    Cancel Order
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Mark order as ready
    $('.mark-ready').click(function() {
        const orderId = $(this).data('order-id');
        $.ajax({
            url: `/inventory/pos/process-by-id/${orderId}`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error updating order status: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error updating order status: ' + (xhr.responseJSON?.message || 'Unknown error'));
            }
        });
    });

    // Mark order as served
    $('.mark-served').click(function() {
        const orderId = $(this).data('order-id');
        $.ajax({
            url: `/inventory/pos/orders/${orderId}/claim`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error updating order status: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error updating order status: ' + (xhr.responseJSON?.message || 'Unknown error'));
            }
        });
    });

    // Mark order as cancelled
    $('.mark-cancelled').click(function() {
        const orderId = $(this).data('order-id');
        if (confirm('Are you sure you want to cancel this order?')) {
            $.ajax({
                url: `/inventory/pos/orders/${orderId}/status`,
                method: 'PUT',
                data: {
                    status: 'cancelled',
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error updating order status: ' + response.message);
                    }
                },
                error: function(xhr) {
                    alert('Error updating order status: ' + (xhr.responseJSON?.message || 'Unknown error'));
                }
            });
        }
    });

    // Auto-refresh every 30 seconds
    setInterval(function() {
        location.reload();
    }, 30000);
});
</script>
@endpush

@push('styles')
<style>
.order-card {
    background-color: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.order-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    transform: translateY(-2px);
}

.order-items ul li {
    font-size: 0.9rem;
    color: #666;
}

.badge {
    font-size: 0.8rem;
    padding: 0.5em 0.8em;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

/* Status badge colors */
.badge.bg-warning {
    background-color: #ffc107 !important;
    color: #000;
}

.badge.bg-success {
    background-color: #28a745 !important;
    color: #fff;
}
</style>
@endpush
@endsection 