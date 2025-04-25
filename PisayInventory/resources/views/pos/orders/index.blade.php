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
                        <option value="pending"><span class="badge bg-warning">Pending</span></option>
                        <option value="paid"><span class="badge bg-primary">Paid</span></option>
                        <option value="preparing"><span class="badge bg-info">Preparing</span></option>
                        <option value="ready"><span class="badge bg-success">Ready to Serve</span></option>
                        <option value="completed"><span class="badge bg-secondary">Completed</span></option>
                        <option value="cancelled"><span class="badge bg-danger">Cancelled</span></option>
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
                            <th>Student Name</th>
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
                                @php
                                    $student = DB::table('students')
                                        ->where('student_id', $order->student_id)
                                        ->first();
                                @endphp
                                {{ $student ? $student->first_name . ' ' . $student->last_name : 'Walk-in Customer' }}
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
                                <select class="form-select form-select-sm status-select" 
                                        data-order-id="{{ $order->OrderID }}"
                                        style="width: auto;">
                                    <option value="pending" {{ $order->Status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="paid" {{ $order->Status === 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="preparing" {{ $order->Status === 'preparing' ? 'selected' : '' }}>Preparing</option>
                                    <option value="ready" {{ $order->Status === 'ready' ? 'selected' : '' }}>Ready to Serve</option>
                                    <option value="completed" {{ $order->Status === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ $order->Status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" 
                                            class="btn btn-sm btn-info view-order" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#viewOrderModal{{ $order->OrderID }}"
                                            title="View Order">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    
                                    <button type="button" 
                                            class="btn btn-sm btn-danger delete-order"
                                            data-order-id="{{ $order->OrderID }}"
                                            data-order-number="{{ $order->OrderNumber }}"
                                            title="Delete Order">
                                        <i class="fas fa-trash"></i> Delete
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

<!-- View Order Modal -->
@foreach($orders as $order)
<div class="modal fade" id="viewOrderModal{{ $order->OrderID }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Order #{{ $order->OrderNumber }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <!-- Order Details -->
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">Order Information</h6>
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Order Date</dt>
                            <dd class="col-sm-8">{{ \Carbon\Carbon::parse($order->OrderDate)->format('M d, Y h:ia') }}</dd>
                            
                            <dt class="col-sm-4">Student Name</dt>
                            <dd class="col-sm-8">
                                @php
                                    $student = DB::table('students')
                                        ->where('student_id', $order->student_id)
                                        ->first();
                                @endphp
                                {{ $student ? $student->first_name . ' ' . $student->last_name : 'Walk-in Customer' }}
                            </dd>
                            
                            <dt class="col-sm-4">Status</dt>
                            <dd class="col-sm-8">
                                <span class="badge bg-{{ 
                                    $order->Status === 'ready' ? 'success' : 
                                    ($order->Status === 'paid' ? 'primary' : 
                                    ($order->Status === 'preparing' ? 'warning' : 
                                    ($order->Status === 'completed' ? 'info' : 'danger'))) }}">
                                    {{ ucfirst($order->Status) }}
                                </span>
                            </dd>
                            
                            <dt class="col-sm-4">Total Amount</dt>
                            <dd class="col-sm-8">₱{{ number_format($order->TotalAmount, 2) }}</dd>
                        </dl>
                    </div>
                    
                    <!-- Order Items -->
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">Order Items</h6>
                        <div class="table-responsive" style="max-height: 200px;">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Qty</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                    <tr>
                                        <td>{{ $item->ItemName }}</td>
                                        <td>{{ $item->Quantity }}</td>
                                        <td class="text-end">₱{{ number_format($item->UnitPrice, 2) }}</td>
                                        <td class="text-end">₱{{ number_format($item->Subtotal, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" 
                        class="btn btn-primary" 
                        data-bs-dismiss="modal" 
                        data-bs-toggle="modal" 
                        data-bs-target="#editOrderModal{{ $order->OrderID }}">
                    <i class="fas fa-edit"></i> Edit Order
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Order Modal -->
<div class="modal fade" id="editOrderModal{{ $order->OrderID }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Edit Order #{{ $order->OrderNumber }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editOrderForm{{ $order->OrderID }}">
                    @csrf
                    
                    <div class="alert alert-danger" id="editErrorAlert{{ $order->OrderID }}" style="display: none;"></div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Student Name</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="student_name" 
                                   value="{{ $order->student_name }}"
                                   placeholder="Walk-in Customer">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="pending" {{ $order->Status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ $order->Status === 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="preparing" {{ $order->Status === 'preparing' ? 'selected' : '' }}>Preparing</option>
                                <option value="ready" {{ $order->Status === 'ready' ? 'selected' : '' }}>Ready to Serve</option>
                                <option value="completed" {{ $order->Status === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ $order->Status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary update-order" data-order-id="{{ $order->OrderID }}">
                    Update Order
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Process Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="paymentForm">
                    <div class="mb-3">
                        <label class="form-label">Order Total</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="text" class="form-control" id="orderTotal" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="amountPaid" class="form-label">Amount Paid</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" id="amountPaid" required min="0" step="0.01">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Change</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="text" class="form-control" id="changeAmount" readonly>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmPayment">Confirm Payment</button>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection

@push('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
#statusFilter option {
    padding: 8px;
}
#statusFilter .badge {
    display: inline-block;
    width: 100%;
    text-align: center;
}
</style>
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

    // Set initial status filter to pending
    $('#statusFilter').val('pending');

    // Status filter change handler
    $('#statusFilter').on('change', function() {
        const status = $(this).val().toLowerCase();
        
        table.rows().every(function() {
            const rowNode = this.node();
            const statusSelect = $(rowNode).find('.status-select');
            const rowStatus = statusSelect.val().toLowerCase();
            
            if (status === '' || status === 'all') {
                $(rowNode).show();
            } else {
                $(rowNode).toggle(rowStatus === status);
            }
        });

        // Show/hide empty message
        const visibleRows = table.rows(':visible').length;
        if (visibleRows === 0) {
            if ($('.no-orders-message').length === 0) {
                $('.table-responsive').append(`
                    <div class="text-center py-4 no-orders-message">
                        <div class="text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            No ${status} orders found
                        </div>
                    </div>
                `);
            }
        } else {
            $('.no-orders-message').remove();
        }
    });

    // Trigger initial filter
    $('#statusFilter').trigger('change');

    // Custom date range filter function
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        const dateRange = $('#dateFilter').val();
        if (!dateRange) {
            return true;
        }

        const [start, end] = dateRange.split(' - ');
        const startDate = moment(start, 'MM/DD/YYYY').startOf('day');
        const endDate = moment(end, 'MM/DD/YYYY').endOf('day');
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
        table.draw();
        // Reapply status filter
        $('#statusFilter').trigger('change');
    });

    $('#dateFilter').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        table.draw();
        // Reapply status filter
        $('#statusFilter').trigger('change');
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

    // Handle status change
    $('.status-select').on('change', function() {
        const orderId = $(this).data('order-id');
        const newStatus = $(this).val();
        const selectElement = $(this);
        const originalValue = selectElement.find('option:selected').attr('selected', true).val();

        // If status is being changed to paid, show payment modal
        if (newStatus === 'paid') {
            // Get the order total from the row
            const orderTotal = parseFloat($(this).closest('tr').find('td:eq(4)').text().replace('₱', '').replace(',', ''));
            
            // Set the order total in the payment modal
            $('#orderTotal').val(orderTotal.toFixed(2));
            $('#amountPaid').val('').focus();
            $('#changeAmount').val('');
            
            // Show payment modal
            const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
            paymentModal.show();

            // Calculate change when amount is entered
            $('#amountPaid').on('input', function() {
                const amountPaid = parseFloat($(this).val()) || 0;
                const change = amountPaid - orderTotal;
                $('#changeAmount').val(change >= 0 ? change.toFixed(2) : '0.00');
            });

            // Handle payment confirmation
            $('#confirmPayment').off('click').on('click', function() {
                const amountPaid = parseFloat($('#amountPaid').val());
                
                if (!amountPaid || amountPaid < orderTotal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Amount',
                        text: 'Please enter an amount equal to or greater than the order total.'
                    });
                    return;
                }

                // Process the status change with payment
                updateOrderStatus(orderId, newStatus, selectElement, originalValue, {
                    amount_paid: amountPaid,
                    change: parseFloat($('#changeAmount').val())
                });
                
                // Hide payment modal
                paymentModal.hide();
            });

            // If modal is dismissed, revert the status
            $('#paymentModal').on('hidden.bs.modal', function() {
                selectElement.val(originalValue);
            });
        } else {
            // For other statuses, proceed normally
            updateOrderStatus(orderId, newStatus, selectElement, originalValue);
        }
    });

    // Function to update order status
    function updateOrderStatus(orderId, newStatus, selectElement, originalValue, paymentDetails = null) {
        selectElement.prop('disabled', true);
        
        const data = {
            _token: '{{ csrf_token() }}',
            status: newStatus
        };

        // Add payment details if provided
        if (paymentDetails) {
            data.amount_paid = paymentDetails.amount_paid;
            data.change = paymentDetails.change;
        }

        $.ajax({
            url: `/inventory/pos/orders/${orderId}/status`,
            type: 'PUT',
            data: data,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Order status updated successfully',
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    selectElement.val(originalValue);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to update status'
                    });
                }
            },
            error: function(xhr) {
                selectElement.val(originalValue);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to update order status'
                });
            },
            complete: function() {
                selectElement.prop('disabled', false);
            }
        });
    }

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