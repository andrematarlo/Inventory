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
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Purchase Orders</h1>
        <div>
            <a href="{{ route('purchases.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> New Purchase Order
            </a>
        </div>
    </div>

    <div class="mb-4">
        <button type="button" class="btn btn-primary" id="activeRecordsBtn">Active Records</button>
        <button type="button" class="btn btn-warning" id="pendingRecordsBtn">Pending Records</button>
        <button type="button" class="btn btn-danger" id="showDeletedBtn">
            <i class="bi bi-archive"></i> Show Deleted Records
        </button>
    </div>

    <div class="card">
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
                                    <a href="{{ route('purchases.edit', $po->PurchaseOrderID) }}" 
                                       class="btn btn-sm btn-primary" 
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endif
                                    <button type="button" 
                                            class="btn btn-sm btn-danger" 
                                            onclick="deletePurchaseOrder({{ $po->PurchaseOrderID }})"
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
                                    <a href="{{ route('purchases.edit', $po->PurchaseOrderID) }}" 
                                       class="btn btn-sm btn-primary" 
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-danger" 
                                            onclick="deletePurchaseOrder({{ $po->PurchaseOrderID }})"
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
                                        onclick="restorePurchaseOrder({{ $po->PurchaseOrderID }})"
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
});

function restorePurchaseOrder(id) {
    if (confirm('Are you sure you want to restore this purchase order?')) {
        fetch(`/inventory/purchases/${id}/restore`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(json => Promise.reject(json));
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Purchase order restored successfully');
                window.location.reload();
            } else {
                alert(data.message || 'Error restoring purchase order');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error restoring purchase order: ' + (error.message || 'Unknown error'));
        });
    }
}

function deletePurchaseOrder(id) {
    if (confirm('Are you sure you want to delete this purchase order?')) {
        fetch(`/inventory/purchases/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(json => Promise.reject(json));
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Purchase order deleted successfully');
                window.location.reload();
            } else {
                alert(data.message || 'Error deleting purchase order');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting purchase order: ' + (error.message || 'Unknown error'));
        });
    }
}
</script>
@endsection
