@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
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
                        <option value="paid">Paid</option>
                        <option value="preparing">Preparing</option>
                        <option value="ready">Ready to Serve</option>
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
                            <td>
                                <span class="badge bg-{{ 
                                    $order->Status === 'ready' ? 'success' : 
                                    ($order->Status === 'paid' ? 'primary' : 
                                    ($order->Status === 'preparing' ? 'warning' : 
                                    ($order->Status === 'completed' ? 'info' : 'danger'))) }}">
                                    {{ ucfirst($order->Status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
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
                </div>
            </div>
        </div>
    </div>
</div>

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
                            <option value="paid">Paid</option>
                            <option value="preparing">Preparing</option>
                            <option value="ready">Ready to Serve</option>
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
@endpush 