@extends('layouts.app')

@section('content')
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
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h1 class="fs-2 fw-bold mb-0">Orders Management</h1>
                        <p class="mb-0 opacity-75">View and manage POS orders</p>
                    </div>
                    <div>
                        <a href="{{ route('pos.orders.create') }}" class="btn btn-light">
                            <i class="bi bi-plus-circle"></i> New Order
                        </a>
                        <button type="button" class="btn btn-light ms-2" data-bs-toggle="modal" data-bs-target="#addMenuItemModal">
                            <i class="bi bi-plus-square"></i> Add Menu Item
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders List -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

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
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
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
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Menu Item Modal -->
<div class="modal fade" id="addMenuItemModal" tabindex="-1" aria-labelledby="addMenuItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark" id="addMenuItemModalLabel">Add New Menu Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addMenuItemForm" action="{{ route('pos.menu-items.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="ajax_request" value="true">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label text-dark">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="price" class="form-label text-dark">Price <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="category" class="form-label text-dark">Category <span class="text-danger">*</span></label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Select a category</option>
                                    @if(isset($classifications) && count($classifications))
                                        @foreach($classifications as $classification)
                                            <option value="{{ $classification->ClassificationId }}">{{ $classification->ClassificationName }}</option>
                                        @endforeach
                                    @else
                                        @php
                                            $fallbackClassifications = \App\Models\Classification::where('IsDeleted', 0)->orderBy('ClassificationName')->get();
                                        @endphp
                                        @foreach($fallbackClassifications as $classification)
                                            <option value="{{ $classification->ClassificationId }}">{{ $classification->ClassificationName }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="description" class="form-label text-dark">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="image" class="form-label text-dark">Menu Item Image</label>
                                <input type="file" class="form-control" id="image" name="image">
                                <small class="form-text text-muted">Upload a square image for best results. Maximum size: 2MB</small>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="available" name="available" value="1" checked>
                                <label class="form-check-label text-dark" for="available">Available for ordering</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-danger d-none" id="modalFormError"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitMenuItem">Save Menu Item</button>
            </div>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="orderDetailsModalLabel">Order Details: <span id="modalOrderNumber"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Order Information -->
                    <div class="col-md-5 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">Order Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-5 fw-bold">Order Number:</div>
                                    <div class="col-md-7" id="detailOrderNumber"></div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-5 fw-bold">Date Created:</div>
                                    <div class="col-md-7" id="detailDateCreated"></div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-5 fw-bold">Customer:</div>
                                    <div class="col-md-7" id="detailCustomer"></div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-5 fw-bold">Status:</div>
                                    <div class="col-md-7" id="detailStatus"></div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-5 fw-bold">Payment Method:</div>
                                    <div class="col-md-7" id="detailPaymentMethod"></div>
                                </div>

                                <div class="row mb-3" id="detailProcessedSection">
                                    <div class="col-md-5 fw-bold">Processed At:</div>
                                    <div class="col-md-7" id="detailProcessedAt"></div>
                                </div>

                                <div class="row mb-3" id="detailRemarksSection">
                                    <div class="col-md-5 fw-bold">Remarks:</div>
                                    <div class="col-md-7" id="detailRemarks"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="col-md-7 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">Order Items</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th width="50">#</th>
                                                <th>Item</th>
                                                <th width="80" class="text-center">Quantity</th>
                                                <th width="120" class="text-end">Unit Price</th>
                                                <th width="120" class="text-end">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody id="detailOrderItems">
                                            <tr>
                                                <td colspan="5" class="text-center">Loading order items...</td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="table-group-divider">
                                            <tr>
                                                <th colspan="4" class="text-end">Total Amount:</th>
                                                <th class="text-end" id="detailTotalAmount">₱0.00</th>
                                            </tr>
                                            <tr id="detailAmountTenderedRow">
                                                <th colspan="4" class="text-end">Amount Tendered:</th>
                                                <td class="text-end" id="detailAmountTendered">₱0.00</td>
                                            </tr>
                                            <tr id="detailChangeRow">
                                                <th colspan="4" class="text-end">Change:</th>
                                                <td class="text-end" id="detailChange">₱0.00</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" id="detailActionButtons">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="detailProcessBtn" class="btn btn-success">
                    <i class="bi bi-check-circle"></i> Process Order
                </a>
                <a href="#" id="detailCancelBtn" class="btn btn-danger">
                    <i class="bi bi-x-circle"></i> Cancel Order
                </a>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    $(document).ready(function() {
        // Simple table search and filtering functionality
        $("#orderSearchInput").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            filterOrders();
        });
        
        $("#statusFilter").on("change", function() {
            filterOrders();
        });
        
        function filterOrders() {
            var searchValue = $("#orderSearchInput").val().toLowerCase();
            var statusValue = $("#statusFilter").val().toLowerCase();
            
            $(".order-row").each(function() {
                var orderNumber = $(this).data("order-number").toLowerCase();
                var customer = $(this).data("customer").toLowerCase();
                var status = $(this).data("status").toLowerCase();
                var orderId = $(this).data("order-id").toString().toLowerCase();
                
                var matchesSearch = orderNumber.includes(searchValue) || 
                                    customer.includes(searchValue) || 
                                    orderId.includes(searchValue);
                
                var matchesStatus = statusValue === "" || status === statusValue;
                
                if (matchesSearch && matchesStatus) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
            
            updateEmptyTableMessage();
        }
        
        function updateEmptyTableMessage() {
            var visibleRows = $(".order-row:visible").length;
            
            if (visibleRows === 0) {
                if ($("#no-results-message").length === 0) {
                    $("#ordersTable tbody").append(
                        '<tr id="no-results-message"><td colspan="8" class="text-center py-4">' + 
                        '<div class="text-muted"><i class="bi bi-search me-2"></i>No orders found matching your search criteria</div>' +
                        '</td></tr>'
                    );
                }
            } else {
                $("#no-results-message").remove();
            }
        }
        
        // Handle menu item form submission via AJAX
        $('#submitMenuItem').on('click', function() {
            const form = $('#addMenuItemForm')[0];
            const formData = new FormData(form);
            
            // Show loading state
            $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
            $('#modalFormError').addClass('d-none');
            
            $.ajax({
                url: form.action,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    // Close modal
                    $('#addMenuItemModal').modal('hide');
                    
                    // Show success alert
                    const alertHtml = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            Menu item added successfully.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                    $('.card-body:first').prepend(alertHtml);
                    
                    // Reset form
                    form.reset();
                    
                    // Reload the page after a short delay to reflect changes
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                },
                error: function(xhr) {
                    // Reset button
                    $('#submitMenuItem').prop('disabled', false).text('Save Menu Item');
                    
                    // Show error message
                    let errorMessage = 'Failed to add menu item';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.errors) {
                            errorMessage = '<ul class="mb-0">';
                            $.each(xhr.responseJSON.errors, function(key, value) {
                                errorMessage += `<li>${value}</li>`;
                            });
                            errorMessage += '</ul>';
                        } else if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                    }
                    
                    $('#modalFormError').html(errorMessage).removeClass('d-none');
                }
            });
        });
        
        // Reset form and errors when modal is closed
        $('#addMenuItemModal').on('hidden.bs.modal', function() {
            $('#addMenuItemForm')[0].reset();
            $('#modalFormError').addClass('d-none');
            $('#submitMenuItem').prop('disabled', false).text('Save Menu Item');
        });

        // View order details in modal
        $('.view-order-btn').on('click', function(e) {
            e.preventDefault();
            const orderId = $(this).data('order-id');
            loadOrderDetails(orderId);
        });
        
        // Load order details via AJAX
        function loadOrderDetails(orderId) {
            $.ajax({
                url: `/inventory/pos/orders/${orderId}/details`,
                type: 'GET',
                success: function(response) {
                    if (response.error) {
                        console.error('Error loading order details:', response.error);
                        return;
                    }
                    
                    displayOrderDetails(response, orderId);
                },
                error: function(xhr) {
                    console.error('Failed to load order details', xhr);
                    alert('Failed to load order details. Please try refreshing the page.');
                }
            });
        }
        
        // Display order details in modal
        function displayOrderDetails(data, orderId) {
            const order = data.order;
            const items = data.items;
            
            // Set order header information
            $('#modalOrderNumber').text(order.OrderNumber);
            
            // Set order details
            $('#detailOrderNumber').text(order.OrderNumber);
            $('#detailDateCreated').text(new Date(order.created_at).toLocaleString());
            
            // Set customer info
            if (order.student) {
                $('#detailCustomer').html(`${order.student.name} <small class="text-muted d-block">(Student ID: ${order.student.id})</small>`);
            } else {
                $('#detailCustomer').text('Walk-in Customer');
            }
            
            // Set status with appropriate badge color
            const statusBadgeClass = order.Status === 'completed' ? 'bg-success' : 
                                    (order.Status === 'pending' ? 'bg-warning text-dark' : 'bg-danger');
            $('#detailStatus').html(`<span class="badge ${statusBadgeClass}">${order.Status.charAt(0).toUpperCase() + order.Status.slice(1)}</span>`);
            
            // Set payment method with appropriate badge color
            const paymentBadgeClass = order.PaymentMethod === 'cash' ? 'bg-success' : 'bg-info';
            $('#detailPaymentMethod').html(`<span class="badge ${paymentBadgeClass}">${order.PaymentMethod.charAt(0).toUpperCase() + order.PaymentMethod.slice(1)}</span>`);
            
            // Handle processed info and remarks
            if (order.ProcessedAt) {
                $('#detailProcessedAt').text(new Date(order.ProcessedAt).toLocaleString());
                $('#detailProcessedSection').show();
            } else {
                $('#detailProcessedSection').hide();
            }
            
            if (order.Notes && order.Notes.trim() !== '') {
                $('#detailRemarks').text(order.Notes);
                $('#detailRemarksSection').show();
            } else {
                $('#detailRemarksSection').hide();
            }
            
            // Update order items
            let itemsHtml = '';
            let total = parseFloat(order.TotalAmount);
            
            if (items.length === 0) {
                itemsHtml = '<tr><td colspan="5" class="text-center">No items found for this order</td></tr>';
            } else {
                items.forEach((item, index) => {
                    const subtotal = parseFloat(item.Subtotal);
                    
                    itemsHtml += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item.ItemName}
                                ${item.IsCustomItem ? '<span class="badge bg-secondary">Custom</span>' : ''}
                            </td>
                            <td class="text-center">${item.Quantity}</td>
                            <td class="text-end">₱${parseFloat(item.UnitPrice).toFixed(2)}</td>
                            <td class="text-end">₱${subtotal.toFixed(2)}</td>
                        </tr>
                    `;
                });
            }
            
            $('#detailOrderItems').html(itemsHtml);
            $('#detailTotalAmount').text(`₱${total.toFixed(2)}`);
            
            // Show/hide payment details
            if (order.AmountTendered) {
                $('#detailAmountTendered').text(`₱${parseFloat(order.AmountTendered).toFixed(2)}`);
                $('#detailChange').text(`₱${parseFloat(order.ChangeAmount || 0).toFixed(2)}`);
                $('#detailAmountTenderedRow, #detailChangeRow').show();
            } else {
                $('#detailAmountTenderedRow, #detailChangeRow').hide();
            }
            
            // Update action buttons based on order status
            if (order.Status === 'pending') {
                $('#detailActionButtons').show();
                $('#detailProcessBtn').attr('href', `/inventory/pos/process/${orderId}`).show();
                $('#detailCancelBtn').attr('href', `/inventory/pos/cancel-order/${orderId}`).show();
            } else {
                $('#detailActionButtons').hide();
            }
            
            // Show the modal
            $('#orderDetailsModal').modal('show');
        }
        
        // Confirm cancel order
        $('#detailCancelBtn').on('click', function(e) {
            if (!confirm('Are you sure you want to cancel this order?')) {
                e.preventDefault();
            }
        });

        // Process order via AJAX
        $('#detailProcessBtn').on('click', function(e) {
            e.preventDefault();
            const orderId = $(this).attr('href').split('/').pop();
            
            // Show processing spinner
            const originalText = $(this).html();
            $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...').prop('disabled', true);
            
            $.ajax({
                url: `/inventory/pos/process/${orderId}`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // Close modal
                    $('#orderDetailsModal').modal('hide');
                    
                    // Show success alert
                    const alertHtml = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            Order has been processed successfully.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                    $('.card-body:first').prepend(alertHtml);
                    
                    // Reload the page after a short delay
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                },
                error: function(xhr) {
                    // Reset button
                    $('#detailProcessBtn').html(originalText).prop('disabled', false);
                    
                    // Show error message
                    alert('Failed to process order. Please try again.');
                    console.error('Error processing order:', xhr.responseText);
                }
            });
        });
        
        // Cancel order via AJAX
        $('#detailCancelBtn').on('click', function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to cancel this order?')) {
                return;
            }
            
            const orderId = $(this).attr('href').split('/').pop();
            
            // Show canceling spinner
            const originalText = $(this).html();
            $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Canceling...').prop('disabled', true);
            
            $.ajax({
                url: `/inventory/pos/cancel-order/${orderId}`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // Close modal
                    $('#orderDetailsModal').modal('hide');
                    
                    // Show success alert
                    const alertHtml = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            Order has been cancelled successfully.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                    $('.card-body:first').prepend(alertHtml);
                    
                    // Reload the page after a short delay
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                },
                error: function(xhr) {
                    // Reset button
                    $('#detailCancelBtn').html(originalText).prop('disabled', false);
                    
                    // Show error message
                    alert('Failed to cancel order. Please try again.');
                    console.error('Error cancelling order:', xhr.responseText);
                }
            });
        });

        // Update the table row buttons to use AJAX as well
        $('.table .btn-success').on('click', function(e) {
            e.preventDefault();
            
            const form = $(this).closest('form');
            const orderId = form.attr('action').split('/').pop();
            
            // Show processing spinner
            const originalText = $(this).html();
            $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>').prop('disabled', true);
            
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(response) {
                    // Show success alert
                    const alertHtml = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            Order has been processed successfully.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                    $('.card-body:first').prepend(alertHtml);
                    
                    // Reload the page after a short delay
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                },
                error: function(xhr) {
                    // Reset button
                    $(this).html(originalText).prop('disabled', false);
                    
                    // Show error message
                    alert('Failed to process order. Please try again.');
                    console.error('Error processing order:', xhr.responseText);
                }
            });
        });
        
        $('.table .btn-danger').on('click', function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to cancel this order?')) {
                return;
            }
            
            const form = $(this).closest('form');
            
            // Show canceling spinner
            const originalText = $(this).html();
            $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>').prop('disabled', true);
            
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(response) {
                    // Show success alert
                    const alertHtml = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            Order has been cancelled successfully.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                    $('.card-body:first').prepend(alertHtml);
                    
                    // Reload the page after a short delay
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                },
                error: function(xhr) {
                    // Reset button
                    $(this).html(originalText).prop('disabled', false);
                    
                    // Show error message
                    alert('Failed to cancel order. Please try again.');
                    console.error('Error cancelling order:', xhr.responseText);
                }
            });
        });
    });
</script>
@endsection 