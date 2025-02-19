@extends('layouts.app')

@section('title', 'Purchase Orders')

@section('styles')
<style>
    .table-responsive {
        overflow-x: auto;
    }
    
    #purchaseOrdersTable, #pendingPurchaseTable, #deletedPurchaseTable {
        min-width: 100%;
        width: auto;
    }
    
    .dataTables_wrapper {
        overflow-x: auto;
    }

    #activeRecordsBtn {
        margin-right: 5px;
    }

    .btn-blue {
        background-color: #0d6efd;
        color: white;
    }
    
    .btn-blue:hover {
        background-color: #0b5ed7;
        color: white;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Purchase Orders</h1>
    
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <button class="btn btn-primary" id="activeRecordsBtn">Active</button>
                <button class="btn btn-warning" id="pendingRecordsBtn">Pending</button>
                <button class="btn btn-secondary" id="showDeletedBtn">Deleted</button>
            </div>
            <button type="button" 
                    class="btn btn-success" 
                    data-bs-toggle="modal" 
                    data-bs-target="#addPurchaseModal">
                <i class="bi bi-plus-circle"></i> Add Purchase Order
            </button>
        </div>
        
        <!-- Active Purchase Orders Section -->
        <div id="activePurchases" class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="purchaseOrdersTable">
                    <thead>
                        <tr>
                            <th>Actions</th>
                            <th>PO Number</th>
                            <th>Supplier</th>
                            <th>Order Date</th>
                            <th>Status</th>
                            <th>Total Amount</th>
                            <th>Created By</th>
                            <th>Date Created</th>
                            <th>Modified By</th>
                            <th>Date Modified</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $po)
                        <tr>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('purchases.show', $po->PurchaseOrderID) }}" 
                                       class="btn btn-sm btn-blue" 
                                       title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($po->Status === 'Pending')
                                    <button type="button" 
                                            class="btn btn-sm btn-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editPurchaseModal{{ $po->PurchaseOrderID }}"
                                            title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    @endif
                                    <button type="button" 
                                            class="btn btn-sm btn-danger" 
                                            data-purchase-id="{{ $po->PurchaseOrderID }}"
                                            data-action="delete"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                            <td>{{ $po->PONumber }}</td>
                            <td>{{ $po->supplier->CompanyName }}</td>
                            <td>{{ $po->OrderDate->format('M d, Y') }}</td>
                            <td>
                                <span class="badge bg-{{ $po->Status === 'Pending' ? 'warning' : 'success' }}">
                                    {{ $po->Status }}
                                </span>
                            </td>
                            <td>₱{{ number_format($po->getTotalAmount(), 2) }}</td>
                            <td>{{ $po->createdBy->FirstName ?? 'N/A' }} {{ $po->createdBy->LastName ?? '' }}</td>
                            <td>{{ $po->DateCreated ? date('Y-m-d H:i:s', strtotime($po->DateCreated)) : 'N/A' }}</td>
                            <td>{{ $po->modifiedBy->FirstName ?? 'N/A' }} {{ $po->modifiedBy->LastName ?? '' }}</td>
                            <td>{{ $po->DateModified ? date('Y-m-d H:i:s', strtotime($po->DateModified)) : 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center">No purchase orders found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pending Purchase Orders Section -->
        <div id="pendingPurchases" class="card-body" style="display: none;">
            <div class="table-responsive">
                <table class="table table-hover" id="pendingPurchaseTable">
                    <thead>
                        <tr>
                            <th>Actions</th>
                            <th>PO Number</th>
                            <th>Supplier</th>
                            <th>Order Date</th>
                            <th>Status</th>
                            <th>Total Amount</th>
                            <th>Created By</th>
                            <th>Date Created</th>
                            <th>Modified By</th>
                            <th>Date Modified</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingPurchases as $po)
                        <tr>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('purchases.show', $po->PurchaseOrderID) }}" 
                                       class="btn btn-sm btn-blue" 
                                       title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($po->Status === 'Pending')
                                    <button type="button" 
                                            class="btn btn-sm btn-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editPurchaseModal{{ $po->PurchaseOrderID }}"
                                            title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    @endif
                                    <button type="button" 
                                            class="btn btn-sm btn-danger" 
                                            data-purchase-id="{{ $po->PurchaseOrderID }}"
                                            data-action="delete"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                            <td>{{ $po->PONumber }}</td>
                            <td>{{ $po->supplier->CompanyName }}</td>
                            <td>{{ $po->OrderDate->format('M d, Y') }}</td>
                            <td>
                                <span class="badge bg-warning">
                                    {{ $po->Status }}
                                </span>
                            </td>
                            <td>₱{{ number_format($po->getTotalAmount(), 2) }}</td>
                            <td>{{ $po->createdBy->FirstName ?? 'N/A' }} {{ $po->createdBy->LastName ?? '' }}</td>
                            <td>{{ $po->DateCreated ? date('Y-m-d H:i:s', strtotime($po->DateCreated)) : 'N/A' }}</td>
                            <td>{{ $po->modifiedBy->FirstName ?? 'N/A' }} {{ $po->modifiedBy->LastName ?? '' }}</td>
                            <td>{{ $po->DateModified ? date('Y-m-d H:i:s', strtotime($po->DateModified)) : 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center">No pending purchase orders found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Deleted Purchase Orders Section -->
        <div id="deletedPurchases" class="card-body" style="display: none;">
            <div class="table-responsive">
                <table class="table table-hover" id="deletedPurchaseTable">
                    <thead>
                        <tr>
                            <th>Actions</th>
                            <th>PO Number</th>
                            <th>Supplier</th>
                            <th>Order Date</th>
                            <th>Status</th>
                            <th>Total Amount</th>
                            <th>Created By</th>
                            <th>Date Created</th>
                            <th>Modified By</th>
                            <th>Date Modified</th>
                            <th>Deleted By</th>
                            <th>Date Deleted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deletedPurchases as $po)
                        <tr>
                            <td>
                                <button type="button" 
                                        class="btn btn-sm btn-success" 
                                        data-purchase-id="{{ $po->PurchaseOrderID }}"
                                        data-action="restore"
                                        title="Restore">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </button>
                            </td>
                            <td>{{ $po->PONumber }}</td>
                            <td>{{ $po->supplier->CompanyName }}</td>
                            <td>{{ $po->OrderDate->format('M d, Y') }}</td>
                            <td>
                                <span class="badge bg-{{ $po->Status === 'Pending' ? 'warning' : 'success' }}">
                                    {{ $po->Status }}
                                </span>
                            </td>
                            <td>₱{{ number_format($po->getTotalAmount(), 2) }}</td>
                            <td>{{ $po->createdBy->FirstName ?? 'N/A' }} {{ $po->createdBy->LastName ?? '' }}</td>
                            <td>{{ $po->DateCreated ? date('Y-m-d H:i:s', strtotime($po->DateCreated)) : 'N/A' }}</td>
                            <td>{{ $po->modifiedBy->FirstName ?? 'N/A' }} {{ $po->modifiedBy->LastName ?? '' }}</td>
                            <td>{{ $po->DateModified ? date('Y-m-d H:i:s', strtotime($po->DateModified)) : 'N/A' }}</td>
                            <td>{{ $po->deletedBy->FirstName ?? 'N/A' }} {{ $po->deletedBy->LastName ?? '' }}</td>
                            <td>{{ $po->DateDeleted ? date('Y-m-d H:i:s', strtotime($po->DateDeleted)) : 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="text-center">No deleted purchase orders found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Purchase Order Modal -->
@include('purchases.partials.add-modal')

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Basic DataTable configuration
    var config = {
        pageLength: 10,
        order: [[1, 'desc']],
        columnDefs: [
            { orderable: false, targets: 0 }
        ],
        searching: true,
        info: true,
        paging: true
    };

    // Only initialize tables that have data
    var activeRows = $('#purchaseOrdersTable tbody tr').length;
    var pendingRows = $('#pendingPurchaseTable tbody tr').length;
    var deletedRows = $('#deletedPurchaseTable tbody tr').length;

    // Initialize only if there are rows and not showing "No data" message
    if (activeRows > 0 && !$('#purchaseOrdersTable tbody tr td').hasClass('text-center')) {
        $('#purchaseOrdersTable').DataTable(config);
    }

    if (pendingRows > 0 && !$('#pendingPurchaseTable tbody tr td').hasClass('text-center')) {
        $('#pendingPurchaseTable').DataTable(config);
    }

    if (deletedRows > 0 && !$('#deletedPurchaseTable tbody tr td').hasClass('text-center')) {
        $('#deletedPurchaseTable').DataTable(config);
    }

    // Hide other sections initially
    $('#pendingPurchases, #deletedPurchases').hide();

    // Button click handlers
    $('#activeRecordsBtn').click(function() {
        $('#activePurchases').show();
        $('#pendingPurchases, #deletedPurchases').hide();
    });

    $('#pendingRecordsBtn').click(function() {
        $('#pendingPurchases').show();
        $('#activePurchases, #deletedPurchases').hide();
    });

    $('#showDeletedBtn').click(function() {
        $('#deletedPurchases').show();
        $('#activePurchases, #pendingPurchases').hide();
    });

    // Handle delete button clicks
    $('[data-action="delete"]').click(function() {
        const purchaseId = $(this).data('purchase-id');
        if (confirm('Are you sure you want to delete this purchase order?')) {
            deletePurchaseOrder(purchaseId);
        }
    });

    // Handle restore button clicks
    $('[data-action="restore"]').click(function() {
        const purchaseId = $(this).data('purchase-id');
        if (confirm('Are you sure you want to restore this purchase order?')) {
            restorePurchaseOrder(purchaseId);
        }
    });
});

function deletePurchaseOrder(id) {
    $.ajax({
        url: `/inventory/purchases/${id}`,
        type: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                alert('Purchase order deleted successfully');
                window.location.reload();
            } else {
                alert(response.message || 'Error deleting purchase order');
            }
        },
        error: function(xhr) {
            alert('Error deleting purchase order: ' + xhr.responseJSON?.message);
        }
    });
}

function restorePurchaseOrder(id) {
    $.ajax({
        url: `/inventory/purchases/${id}/restore`,
        type: 'PUT',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                alert('Purchase order restored successfully');
                window.location.reload();
            } else {
                alert(response.message || 'Error restoring purchase order');
            }
        },
        error: function(xhr) {
            alert('Error restoring purchase order: ' + xhr.responseJSON?.message);
        }
    });
}
</script>
@endsection
