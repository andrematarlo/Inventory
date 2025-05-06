@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Preparing Orders Section -->
        <div class="col-md-6 border-right">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-clock"></i> Preparing Orders</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Student</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Time</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($preparingOrders->isEmpty())
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fas fa-coffee fa-2x text-muted mb-2"></i>
                                            <p class="mb-0">No orders being prepared</p>
                                        </td>
                                    </tr>
                                @else
                                    @foreach($preparingOrders as $order)
                                    <tr>
                                        <td>{{ $order->OrderNumber }}</td>
                                        <td>{{ $order->first_name && $order->last_name ? $order->first_name . ' ' . $order->last_name : 'Walk-in' }}</td>
                                        <td>
                                            <button type="button" class="btn btn-link btn-sm p-0 view-items" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#itemsModal" 
                                                    data-order-id="{{ $order->OrderID }}">
                                                View Items ({{ $order->items_count }})
                                            </button>
                                        </td>
                                        <td>₱{{ number_format($order->TotalAmount, 2) }}</td>
                                        <td>{{ \Carbon\Carbon::parse($order->created_at)->format('h:i A') }}</td>
                                        <td>
                                            <button class="btn btn-success btn-sm mark-ready" data-order-id="{{ $order->OrderID }}">
                                                Serve
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ready to Serve Orders Section -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-check-circle"></i> Ready to Claim</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Student</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Time</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($readyOrders->isEmpty())
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fas fa-check-circle fa-2x text-muted mb-2"></i>
                                            <p class="mb-0">No orders ready to serve</p>
                                        </td>
                                    </tr>
                                @else
                                    @foreach($readyOrders as $order)
                                    <tr>
                                        <td>{{ $order->OrderNumber }}</td>
                                        <td>{{ $order->first_name && $order->last_name ? $order->first_name . ' ' . $order->last_name : 'Walk-in' }}</td>
                                        <td>
                                            <button type="button" class="btn btn-link btn-sm p-0 view-items" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#itemsModal" 
                                                    data-order-id="{{ $order->OrderID }}">
                                                View Items ({{ $order->items_count }})
                                            </button>
                                        </td>
                                        <td>₱{{ number_format($order->TotalAmount, 2) }}</td>
                                        <td>{{ \Carbon\Carbon::parse($order->created_at)->format('h:i A') }}</td>
                                        <td>
                                            <button class="btn btn-primary btn-sm mark-served" data-order-id="{{ $order->OrderID }}">
                                                Claimed
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
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

@push('scripts')
<script>
$(document).ready(function() {
    // View items modal handler
    $('.view-items').click(function() {
        const orderId = $(this).data('order-id');
        const tableBody = $('#itemsTableBody');
        tableBody.empty();
        
        // Fetch items via AJAX
        $.ajax({
            url: "{{ route('pos.orders.items', '') }}/" + orderId,
            method: 'GET',
            success: function(response) {
                let total = 0;
                response.items.forEach(function(item) {
                    const subtotal = item.Quantity * item.UnitPrice;
                    total += subtotal;
                    
                    tableBody.append(`
                        <tr>
                            <td>${item.ItemName}</td>
                            <td class="text-center">${item.Quantity}</td>
                            <td class="text-end">₱${parseFloat(item.UnitPrice).toFixed(2)}</td>
                            <td class="text-end">₱${subtotal.toFixed(2)}</td>
                        </tr>
                    `);
                });
                
                tableBody.append(`
                    <tr class="table-light fw-bold">
                        <td colspan="3" class="text-end">Total:</td>
                        <td class="text-end">₱${total.toFixed(2)}</td>
                    </tr>
                `);
            },
            error: function(xhr) {
                tableBody.append(`
                    <tr>
                        <td colspan="4" class="text-center text-danger">
                            Error loading items: ${xhr.responseJSON?.message || 'Unknown error'}
                        </td>
                    </tr>
                `);
            }
        });
    });

    // Mark order as ready
    $('.mark-ready').click(function() {
        const orderId = $(this).data('order-id');
        $.ajax({
            url: `/pos/orders/${orderId}/process`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Order has been marked as ready',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to update order status'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Failed to update order status'
                });
            }
        });
    });

    // Mark order as served
    $('.mark-served').click(function() {
        const orderId = $(this).data('order-id');
        $.ajax({
            url: `/pos/orders/${orderId}/claim`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Order has been marked as served',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to update order status'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Failed to update order status'
                });
            }
        });
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
.table {
    margin-bottom: 0;
}

.table > :not(caption) > * > * {
    padding: 0.75rem;
}

.btn-link {
    text-decoration: none;
}

.btn-link:hover {
    text-decoration: underline;
}

.card {
    border: none;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.card-header {
    padding: 1rem;
}

.card-header i {
    margin-right: 0.5rem;
}

/* Status colors */
.bg-warning {
    background-color: #ffc107 !important;
}

.bg-success {
    background-color: #28a745 !important;
}
</style>
@endpush
@endsection 