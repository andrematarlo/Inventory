@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fs-2 fw-bold">Orders Management</h1>
        <a href="{{ route('pos.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> New Order
        </a>
    </div>

    @if(isset($isStudent) && $isStudent)
    <!-- Student Balance Card -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="card-title fw-bold">Your Balance</h5>
                    <p class="text-muted mb-0">Student ID: {{ $studentId }}</p>
                </div>
                <div class="col-md-6 text-end">
                    <h2 class="text-primary fw-bold mb-0">₱{{ number_format($studentBalance, 2) }}</h2>
                    <p class="text-muted mb-0">Available Balance</p>
                    <a href="{{ route('pos.cashdeposit') }}" class="btn btn-sm btn-outline-primary mt-2">
                        <i class="bi bi-wallet2 me-1"></i> Manage Deposits
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Orders List -->
    <div class="card shadow">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0 fw-bold">All Orders</h5>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" id="orderSearch" class="form-control" placeholder="Search by order number or status...">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="ordersTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Order #</th>
                            <th>Date</th>
                            <th>Student</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $orders = App\Models\POSOrder::with('student')
                                ->where('IsDeleted', false)
                                ->orderBy('created_at', 'desc')
                                ->paginate(10);
                        @endphp
                        
                        @forelse($orders as $order)
                            <tr>
                                <td class="ps-3 fw-medium">{{ $order->OrderNumber }}</td>
                                <td>{{ date('M d, Y h:i A', strtotime($order->created_at)) }}</td>
                                <td>
                                    @if($order->StudentId)
                                        {{ $order->student->FirstName }} {{ $order->student->LastName }}
                                    @else
                                        <span class="text-muted fst-italic">None</span>
                                    @endif
                                </td>
                                <td class="fw-medium text-primary">₱{{ number_format($order->TotalAmount, 2) }}</td>
                                <td>
                                    @if($order->PaymentMethod == 'cash')
                                        <span class="badge bg-info text-dark">Cash</span>
                                    @elseif($order->PaymentMethod == 'deposit')
                                        <span class="badge bg-secondary">Deposit</span>
                                    @endif
                                </td>
                                <td>
                                    @if($order->Status == 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($order->Status == 'paid')
                                        <span class="badge bg-success">Paid</span>
                                    @elseif($order->Status == 'completed')
                                        <span class="badge bg-primary">Completed</span>
                                    @elseif($order->Status == 'cancelled')
                                        <span class="badge bg-danger">Cancelled</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#viewOrderModal" data-order-id="{{ $order->OrderId }}">
                                                <i class="bi bi-eye me-2"></i> View Details
                                            </a></li>
                                            @if($order->Status == 'pending')
                                                <li><a class="dropdown-item" href="{{ route('pos.cashiering') }}">
                                                    <i class="bi bi-cash-coin me-2"></i> Process Payment
                                                </a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#cancelOrderModal" data-order-id="{{ $order->OrderId }}">
                                                    <i class="bi bi-x-circle me-2"></i> Cancel Order
                                                </a></li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No orders found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    Showing <span class="fw-medium">{{ $orders->firstItem() ?? 0 }}</span> to 
                    <span class="fw-medium">{{ $orders->lastItem() ?? 0 }}</span> of 
                    <span class="fw-medium">{{ $orders->total() }}</span> entries
                </div>
                <div>
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Order Modal -->
<div class="modal fade" id="viewOrderModal" tabindex="-1" aria-labelledby="viewOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewOrderModalLabel">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Loading order details...</p>
                </div>
                <!-- Order details will be loaded here via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Order Modal -->
<div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelOrderModalLabel">Cancel Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this order? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Keep Order</button>
                <form id="cancelOrderForm" action="" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger">Yes, Cancel Order</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#ordersTable').DataTable({
            "paging": false, // Disable DataTables paging since we're using Laravel pagination
            "searching": true,
            "ordering": true,
            "info": false,
            "autoWidth": false,
            "responsive": true
        });
        
        // Search functionality
        $('#orderSearch').on('keyup', function() {
            $('#ordersTable').DataTable().search($(this).val()).draw();
        });
        
        // Handle View Order Modal
        $('#viewOrderModal').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            const orderId = button.data('order-id');
            const modal = $(this);
            
            // Show loading state
            modal.find('.modal-body').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Loading order details...</p>
                </div>
            `);
            
            // Fetch order details using AJAX
            $.ajax({
                url: `/pos/order-details/${orderId}`,
                method: 'GET',
                success: function(response) {
                    const order = response.order;
                    const items = response.items;
                    
                    // Format status badge
                    let statusBadge = '';
                    if (order.Status === 'pending') {
                        statusBadge = '<span class="badge bg-warning text-dark">Pending</span>';
                    } else if (order.Status === 'paid') {
                        statusBadge = '<span class="badge bg-success">Paid</span>';
                    } else if (order.Status === 'completed') {
                        statusBadge = '<span class="badge bg-primary">Completed</span>';
                    } else if (order.Status === 'cancelled') {
                        statusBadge = '<span class="badge bg-danger">Cancelled</span>';
                    }
                    
                    // Format payment method badge
                    let paymentBadge = '';
                    if (order.PaymentMethod === 'cash') {
                        paymentBadge = '<span class="badge bg-info text-dark">Cash</span>';
                    } else if (order.PaymentMethod === 'deposit') {
                        paymentBadge = '<span class="badge bg-secondary">Deposit</span>';
                    }
                    
                    // Format date
                    const orderDate = new Date(order.created_at);
                    const formattedDate = orderDate.toLocaleString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    
                    // Build order details HTML
                    let html = `
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">Order Information</h6>
                                <p class="mb-1"><strong>Order #:</strong> <span>${order.OrderNumber}</span></p>
                                <p class="mb-1"><strong>Date:</strong> <span>${formattedDate}</span></p>
                                <p class="mb-1"><strong>Status:</strong> ${statusBadge}</p>
                                <p class="mb-1"><strong>Payment Method:</strong> ${paymentBadge}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">Student Information</h6>
                    `;
                    
                    // Add student information if available
                    if (order.StudentId) {
                        html += `
                            <p class="mb-1"><strong>ID:</strong> <span>${order.StudentNumber}</span></p>
                            <p class="mb-1"><strong>Name:</strong> <span>${order.FirstName} ${order.LastName}</span></p>
                        `;
                    } else {
                        html += `<p class="text-muted fst-italic">No student associated with this order</p>`;
                    }
                    
                    html += `
                            </div>
                        </div>
                        
                        <h6 class="fw-bold mb-3">Order Items</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    // Add order items
                    let total = 0;
                    items.forEach(item => {
                        const subtotal = parseFloat(item.UnitPrice) * parseInt(item.Quantity);
                        total += subtotal;
                        
                        html += `
                            <tr>
                                <td>${item.ItemName || 'Unknown Item'}</td>
                                <td class="text-center">${item.Quantity}</td>
                                <td class="text-end">₱${parseFloat(item.UnitPrice).toFixed(2)}</td>
                                <td class="text-end">₱${subtotal.toFixed(2)}</td>
                            </tr>
                        `;
                    });
                    
                    // Add total row
                    html += `
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Total:</td>
                                    <td class="text-end fw-bold">₱${total.toFixed(2)}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    `;
                    
                    // Update modal content
                    modal.find('.modal-body').html(html);
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to load order details';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMessage = xhr.responseJSON.error;
                    }
                    
                    modal.find('.modal-body').html(`
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            ${errorMessage}
                        </div>
                    `);
                }
            });
        });
        
        // Handle Cancel Order Modal
        $('#cancelOrderModal').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            const orderId = button.data('order-id');
            const modal = $(this);
            
            // Set the form action
            modal.find('#cancelOrderForm').attr('action', `/pos/cancel-order/${orderId}`);
        });
    });
</script>
@endpush
@endsection 