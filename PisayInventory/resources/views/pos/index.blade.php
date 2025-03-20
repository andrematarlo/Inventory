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
                    <form action="{{ route('pos.index') }}" method="GET" class="d-flex">
                        <div class="input-group">
                            <input type="text" name="search" id="orderSearch" class="form-control" 
                                placeholder="Search orders..." value="{{ request('search') }}">
                            <button class="btn btn-outline-primary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                            @if(request('search'))
                                <a href="{{ route('pos.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i>
                                </a>
                            @endif
                        </div>
                    </form>
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
                        @forelse($orders as $order)
                            <tr class="order-row">
                                <td class="ps-3 fw-medium">{{ $order->OrderNumber }}</td>
                                <td>{{ date('M d, Y h:i A', strtotime($order->created_at)) }}</td>
                                <td>
                                    @if($order->student_id)
                                        {{ $order->student->first_name }} {{ $order->student->last_name }}
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
                                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#viewOrderModal" data-order-id="{{ $order->OrderID }}">
                                                <i class="bi bi-eye me-2"></i> View Details
                                            </a></li>
                                            @if($order->Status == 'pending')
                                                <li><a class="dropdown-item" href="{{ route('pos.cashiering') }}">
                                                    <i class="bi bi-cash-coin me-2"></i> Process Payment
                                                </a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#cancelOrderModal" data-order-id="{{ $order->OrderID }}">
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
                <div class="pagination-container">
                    {{ $orders->links('pagination::bootstrap-5') }}
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
document.addEventListener('DOMContentLoaded', function() {
    // For client-side filtering while user types before submitting
    const searchInput = document.getElementById('orderSearch');
    
    // Debounce function for search input
    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        
        searchTimeout = setTimeout(() => {
            // This will update the filter immediately while typing, before form submission
            const searchTerm = this.value.toLowerCase();
            const orderRows = document.querySelectorAll('.order-row');
            let visibleRowCount = 0;
            
            if (searchTerm.length === 0) return; // Skip immediate filtering if empty
            
            orderRows.forEach(row => {
                const orderNumber = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
                const date = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const student = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                const status = row.querySelector('td:nth-child(6)').textContent.toLowerCase();
                
                const matchesSearch = orderNumber.includes(searchTerm) || 
                                    date.includes(searchTerm) || 
                                    student.includes(searchTerm) || 
                                    status.includes(searchTerm);
                
                row.style.display = matchesSearch ? '' : 'none';
                if (matchesSearch) visibleRowCount++;
            });
            
            // Show message if no matching records
            const tableBody = document.querySelector('#ordersTable tbody');
            const noResultsRow = tableBody.querySelector('.no-results');
            
            if (visibleRowCount === 0 && !noResultsRow) {
                const newRow = document.createElement('tr');
                newRow.className = 'no-results';
                newRow.innerHTML = `<td colspan="7" class="text-center py-4 text-muted">No matching records found</td>`;
                tableBody.appendChild(newRow);
            } else if (visibleRowCount > 0 && noResultsRow) {
                noResultsRow.remove();
            }
        }, 300); // 300ms debounce
    });
    
    // Handle View Order Modal
    const viewOrderModal = document.getElementById('viewOrderModal');
    viewOrderModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const orderId = button.getAttribute('data-order-id');
        const modal = this;
        
        // Show loading state
        modal.querySelector('.modal-body').innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3">Loading order details...</p>
            </div>
        `;
        
        // Fetch order details
        fetch(`{{ route('pos.order-details', '') }}/${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }

                const order = data.order;
                const items = data.items;
                
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
                const html = `
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="mb-2">Order Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th width="35%">Order Number:</th>
                                    <td>${order.OrderNumber}</td>
                                </tr>
                                <tr>
                                    <th>Date:</th>
                                    <td>${formattedDate}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>${statusBadge}</td>
                                </tr>
                                <tr>
                                    <th>Payment Method:</th>
                                    <td>${paymentBadge}</td>
                                </tr>
                                <tr>
                                    <th>Total Amount:</th>
                                    <td class="fw-bold">₱${parseFloat(order.TotalAmount).toFixed(2)}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-2">Student Information</h6>
                            <table class="table table-sm">
                                ${order.student ? `
                                    <tr>
                                        <th width="35%">Student ID:</th>
                                        <td>${order.student.id}</td>
                                    </tr>
                                    <tr>
                                        <th>Name:</th>
                                        <td>${order.student.name}</td>
                                    </tr>
                                ` : `
                                    <tr>
                                        <td colspan="2" class="text-muted fst-italic">No student information</td>
                                    </tr>
                                `}
                            </table>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th class="text-end">Quantity</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${Array.isArray(items) ? items.map(item => `
                                    <tr>
                                        <td>${item.ItemName || 'Unknown Item'}</td>
                                        <td class="text-end">${item.Quantity}</td>
                                        <td class="text-end">₱${parseFloat(item.UnitPrice || 0).toFixed(2)}</td>
                                        <td class="text-end">₱${parseFloat(item.Subtotal || 0).toFixed(2)}</td>
                                    </tr>
                                `).join('') : ''}
                                <tr class="table-light">
                                    <td colspan="3" class="text-end fw-bold">Total:</td>
                                    <td class="text-end fw-bold">₱${parseFloat(order.TotalAmount).toFixed(2)}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                `;
                
                // Update modal content
                modal.querySelector('.modal-body').innerHTML = html;
            })
            .catch(error => {
                modal.querySelector('.modal-body').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        ${error.message}
                    </div>
                `;
            });
    });
    
    // Handle Cancel Order Modal
    const cancelOrderModal = document.getElementById('cancelOrderModal');
    cancelOrderModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const orderId = button.getAttribute('data-order-id');
        const form = this.querySelector('#cancelOrderForm');
        
        form.action = `/pos/cancel-order/${orderId}`;
    });
});
</script>
@endpush

@push('styles')
<style>
    .pagination-container .pagination {
        margin-bottom: 0;
    }
    
    .pagination-container .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    
    .pagination-container .page-link {
        color: #0d6efd;
    }
    
    .pagination-container .page-link:hover {
        background-color: #e9ecef;
    }
</style>
@endpush
@endsection 