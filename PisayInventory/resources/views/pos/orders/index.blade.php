@extends('layouts.app')

@section('content')
<<<<<<< HEAD
<<<<<<< HEAD
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Orders</h3>
                    <div class="card-tools">
                        <a href="{{ route('pos.orders.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus"></i> New Order
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Student</th>
                                    <th>Total Amount</th>
=======
<style>
/* Custom styles to ensure modal appears above sidebar */
#orderDetailsModal {
    z-index: 1060 !important;
}
.modal-backdrop {
    z-index: 1050 !important;
}
.modal-dialog {
    max-width: 90% !important;
    margin: 1.75rem auto !important;
}
.modal-content {
    border-radius: 0.5rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

/* Fix for content visibility */
.main-content {
    margin-left: 340px !important;
    width: calc(100% - 340px) !important;
    overflow-x: hidden;
    padding-right: 20px;
}
.container-fluid {
    padding-right: 15px;
    padding-left: 15px;
    max-width: 100%;
}
#ordersTable_wrapper {
    width: 100%;
    overflow-x: auto;
    margin-left: 10px;
}

/* Add wrapper style */
.order-management-wrapper {
    padding-left: 10px;
    width: 100%;
    position: relative;
    margin-left: 10px;
}

/* Adjust table styling */
.table-responsive {
    overflow-x: auto;
    min-height: 0.01%;
    width: 100%;
    padding: 5px;
}
.table th, .table td {
    white-space: nowrap;
    padding: 10px;
}
.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}
.btn-group {
    white-space: nowrap;
}

/* Pagination styles */
.pagination {
    margin-top: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}
.page-link {
    color: #0d6efd;
    background-color: #fff;
    border: 1px solid #dee2e6;
    position: relative;
    display: block;
    padding: 0.375rem 0.75rem;
    margin-left: -1px;
    line-height: 1.25;
    transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out;
}
.page-link:hover {
    z-index: 2;
    color: #0a58ca;
    background-color: #e9ecef;
    border-color: #dee2e6;
}
.page-item.active .page-link {
    z-index: 3;
    color: #fff;
    background-color: #0d6efd;
    border-color: #0d6efd;
}
.page-item.disabled .page-link {
    color: #6c757d;
    pointer-events: none;
    background-color: #fff;
    border-color: #dee2e6;
}
.dataTables_info, 
.dataTables_paginate {
    margin-top: 1rem;
    width: 100%;
    text-align: center;
}

/* DataTables Bootstrap 5 styles */
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter {
    padding: 0.5rem 0;
}

.dataTables_wrapper .dataTables_filter input {
    margin-left: 0.5rem;
    border-radius: 0.25rem;
    border: 1px solid #ced4da;
    padding: 0.375rem 0.75rem;
}

.dataTables_wrapper .dataTables_length select {
    padding: 0.375rem 2.25rem 0.375rem 0.75rem;
    border-radius: 0.25rem;
    border: 1px solid #ced4da;
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
    border-radius: 0.25rem;
    margin: 0 0.2rem;
}

.table.dataTable {
    width: 100% !important;
    margin-bottom: 1rem;
    border-collapse: collapse !important;
}

.dataTables_scroll {
    width: 100%;
    margin-bottom: 1rem;
}

.dataTables_scrollBody {
    border-bottom: none !important;
}

/* Custom table styles */
.orders-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.orders-table th {
    background-color: #f8f9fa;
    position: sticky;
    top: 0;
    z-index: 1;
}

.orders-table th, .orders-table td {
    padding: 0.75rem;
}

.orders-filter {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.orders-filter .form-control {
    max-width: 250px;
}

.orders-table-container {
    overflow-x: auto;
    max-height: 75vh;
}

/* Search box style */
.search-box {
    position: relative;
}

.search-box .form-control {
    padding-left: 2.5rem;
}

.search-box i {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
}
</style>

<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="order-management-wrapper">
    <div class="container-fluid py-4 px-3">
    <!-- Header -->
=======
<div class="container-fluid py-4">
    <!-- Header Section -->
>>>>>>> af373e2190daf43e403fd7f80b715607d86cc09a
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h1 class="fs-2 fw-bold mb-0">Orders Management</h1>
                        <p class="mb-0 opacity-75">View and manage all orders</p>
                    </div>
                    <div>
                        <a href="{{ route('pos.orders.create') }}" class="btn btn-light">
                            <i class="bi bi-plus-circle me-1"></i> New Order
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="dateFilter" class="form-label">Date Range</label>
                    <input type="text" class="form-control" id="dateFilter">
                </div>
                <div class="col-md-3">
                    <label for="statusFilter" class="form-label">Status</label>
                    <select class="form-select" id="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="ordersTable">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders ?? [] as $order)
                        <tr>
                            <td>
                                <strong>#{{ $order->OrderID }}</strong>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($order->OrderDate)->format('M d, Y h:ia') }}</td>
                            <td>
                                @if($order->student_id)
                                    @if($order->customer)
                                        {{ $order->customer->FirstName . ' ' . $order->customer->LastName }}
                                        <br>
                                        <small class="text-muted">ID: {{ $order->student_id }}</small>
                                    @else
                                        <span class="text-muted">Student ID: {{ $order->student_id }}</span>
                                        <br>
                                        <small class="text-danger">(Student not found)</small>
                                    @endif
                                @else
                                    <span class="text-muted">Walk-in Customer</span>
                                @endif
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
                            <td>
                                <span class="badge bg-{{ $order->Status === 'completed' ? 'success' : 
                                    ($order->Status === 'pending' ? 'warning' : 
                                    ($order->Status === 'cancelled' ? 'danger' : 'info')) }}">
                                    {{ ucfirst($order->Status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('pos.process-payment', $order->OrderID) }}" 
                                       class="btn btn-sm btn-primary"
                                       title="Process Payment">
                                        <i class="bi bi-cash-register"></i> Process
                                    </a>
                                    <a href="{{ route('pos.orders.show', $order->OrderID) }}" 
                                       class="btn btn-sm btn-outline-primary"
                                       title="View Order">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('pos.orders.edit', $order->OrderID) }}" 
                                       class="btn btn-sm btn-outline-warning"
                                       title="Edit Order">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger delete-order"
                                            data-order-id="{{ $order->OrderID }}"
                                            data-order-number="{{ $order->OrderNumber }}"
                                            title="Delete Order">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                    No orders found
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

<<<<<<< HEAD
                    <div class="orders-filter">
                        <div class="search-box">
                            <i class="bi bi-search"></i>
                            <input type="text" id="orderSearchInput" class="form-control" placeholder="Search orders...">
                        </div>
                        <div>
                            <select id="statusFilter" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div class="orders-table-container">
                        <table class="table table-hover orders-table" id="ordersTable">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Order Number</th>
                                    <th>Student</th>
                                    <th>Amount</th>
                                    <th>Payment Method</th>
>>>>>>> a0fca33e1c51bfb4f1f4e6b51bd5b88860c884b9
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
<<<<<<< HEAD
                                @forelse($orders as $order)
                                <tr>
                                    <td>{{ $order->order_number }}</td>
                                    <td>{{ optional($order->student)->name }}</td>
                                    <td>₱{{ number_format($order->total_amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $order->status === 'completed' ? 'success' : ($order->status === 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $order->created_at->format('M d, Y h:i A') }}</td>
                                    <td>
                                        <a href="{{ route('pos.orders.show', $order) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('pos.orders.edit', $order) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('pos.orders.destroy', $order) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No orders found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $orders->links() }}
=======
                                @foreach($orders as $order)
                                <tr class="order-row" 
                                    data-order-id="{{ $order->OrderID }}"
                                    data-order-number="{{ $order->OrderNumber ?? 'N/A' }}"
                                    data-customer="{{ $order->student ? $order->student->first_name . ' ' . $order->student->last_name : 'Walk-in Customer' }}"
                                    data-status="{{ $order->Status }}">
                                    <td>{{ $order->OrderID }}</td>
                                    <td>{{ $order->OrderNumber ?? 'N/A' }}</td>
                                    <td>
                                        @if($order->student)
                                            {{ $order->student->first_name }} {{ $order->student->last_name }}
                                        @else
                                            Walk-in Customer
                                        @endif
                                    </td>
                                    <td>₱{{ number_format($order->TotalAmount, 2) }}</td>
                                    <td>
                                        @if($order->PaymentMethod == 'cash')
                                            <span class="badge bg-success">Cash</span>
                                        @elseif($order->PaymentMethod == 'deposit')
                                            <span class="badge bg-info">Deposit</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $order->PaymentMethod }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($order->Status == 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @elseif($order->Status == 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @elseif($order->Status == 'cancelled')
                                            <span class="badge bg-danger">Cancelled</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $order->Status }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $order->created_at->format('M d, Y g:i A') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="#" class="btn btn-sm btn-primary view-order-btn" data-order-id="{{ $order->OrderID }}">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                            
                                            @if($order->Status == 'pending')
                                            <form action="{{ route('pos.process.by.id', $order->OrderID) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success mx-1">
                                                    <i class="bi bi-check-circle"></i> Process
                                                </button>
                                            </form>
                                            
                                            <form action="{{ route('pos.cancel', $order->OrderID) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to cancel this order?')">
                                                    <i class="bi bi-x-circle"></i> Cancel
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Showing {{ $orders->firstItem() ?? 0 }} to {{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }} entries
                        </div>
                        <div>
                            {{ $orders->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
>>>>>>> a0fca33e1c51bfb4f1f4e6b51bd5b88860c884b9
=======
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
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Quantity</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="orderItemsList">
                            <!-- Items will be loaded here -->
                        </tbody>
                    </table>
>>>>>>> af373e2190daf43e403fd7f80b715607d86cc09a
                </div>
            </div>
        </div>
    </div>
</div>
<<<<<<< HEAD
=======

<!-- Status Update Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Order Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="updateStatusForm">
                    <div class="mb-3">
                        <label for="newStatus" class="form-label">New Status</label>
                        <select class="form-select" id="newStatus" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmStatusUpdate">Update Status</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteOrderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete Order #<span id="deleteOrderNumber"></span>?</p>
                <p class="text-danger"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endpush

@push('scripts')
<!-- Make sure these are added AFTER jQuery -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable with custom date sorting
    const table = $('#ordersTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true
    });

    // Custom date range filter function
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        const dateRange = $('#dateFilter').val();
        if (!dateRange) {
            return true; // Show all rows if no date range is selected
        }

        const [start, end] = dateRange.split(' - ');
        const startDate = moment(start, 'MM/DD/YYYY').startOf('day');
        const endDate = moment(end, 'MM/DD/YYYY').endOf('day');
        
        // Data[1] is the date column (index 1)
        const rowDate = moment(data[1], 'MMM DD, YYYY h:mma');

        return rowDate.isBetween(startDate, endDate, 'day', '[]');
    });

    // Initialize DateRangePicker
    $('#dateFilter').daterangepicker({
        opens: 'left',
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear',
            format: 'MM/DD/YYYY'
        }
    });

    // Date filter events
    $('#dateFilter').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
        table.draw(); // Redraw table with filter
    });

    $('#dateFilter').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        table.draw(); // Redraw table without filter
    });

    // Status filter
    $('#statusFilter').on('change', function() {
        const status = $(this).val();
        table.column(5).search(status).draw();
    });

    // View items modal
    $('.view-items').on('click', function() {
        const orderId = $(this).data('order-id');
        $.get(`/inventory/pos/orders/${orderId}/items`, function(data) {
            $('#orderItemsList').html(data);
        }).fail(function() {
            $('#orderItemsList').html(`
                <tr>
                    <td colspan="4" class="text-center text-muted py-3">
                        <i class="bi bi-exclamation-circle me-2"></i>Error loading order items
                    </td>
                </tr>
            `);
        });
    });

    // Update status
    $('.update-status').on('click', function() {
        const orderId = $(this).data('order-id');
        const currentStatus = $(this).data('current-status');
        $('#updateStatusModal').modal('show');
        $('#newStatus').val(currentStatus);
        
        $('#confirmStatusUpdate').off('click').on('click', function() {
            const newStatus = $('#newStatus').val();
            
            $.ajax({
                url: `/pos/orders/${orderId}/status`,
                type: 'PUT',
                data: {
                    status: newStatus,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error updating status');
                    }
                },
                error: function() {
                    alert('Error updating status');
                }
            });
        });
    });

    // Delete order functionality
    $('.delete-order').on('click', function() {
        const orderId = $(this).data('order-id');
        const orderNumber = $(this).data('order-number');
        
        $('#deleteOrderNumber').text(orderNumber);
        $('#deleteOrderModal').modal('show');
        
        $('#confirmDelete').off('click').on('click', function() {
            $.ajax({
                url: `/inventory/pos/orders/${orderId}`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Order deleted successfully'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Error deleting order'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error deleting order'
                    });
                },
                complete: function() {
                    $('#deleteOrderModal').modal('hide');
                }
            });
        });
    });
});
</script>
<<<<<<< HEAD
>>>>>>> a0fca33e1c51bfb4f1f4e6b51bd5b88860c884b9
@endsection 
=======
@endpush 
>>>>>>> af373e2190daf43e403fd7f80b715607d86cc09a
