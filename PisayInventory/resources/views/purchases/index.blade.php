@extends('layouts.app')

@section('title', 'Purchase Orders')

@section('styles')
<style>
    .table-responsive {
        overflow-x: auto;
    }
    
    #purchaseOrdersTable, #deletedPurchaseOrdersTable {
        min-width: 100%;
        width: auto;
    }
    
    .dataTables_wrapper {
        overflow-x: auto;
    }

    #activeRecordsBtn {
        margin-right: 5px;
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
        <button type="button" class="btn btn-primary" id="activeRecordsBtn">Active Purchase Orders</button>
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
                            <th>Restored By</th>
                            <th>Date Restored</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $po)
                        <tr>
                            <td>{{ $po->PONumber }}</td>
                            <td>{{ $po->supplier->CompanyName }}</td>
                            <td>{{ $po->OrderDate->format('M d, Y') }}</td>
                            <td>
                                <span class="badge bg-{{ $po->Status === 'Pending' ? 'warning' : 'success' }}">
                                    {{ $po->Status }}
                                </span>
                            </td>
                            <td>₱{{ number_format($po->TotalAmount, 2) }}</td>
                            <td>{{ $po->createdBy->FirstName ?? 'N/A' }} {{ $po->createdBy->LastName ?? '' }}</td>
                            <td>{{ $po->DateCreated ? date('Y-m-d H:i:s', strtotime($po->DateCreated)) : 'N/A' }}</td>
                            <td>{{ $po->modifiedBy->FirstName ?? 'N/A' }} {{ $po->modifiedBy->LastName ?? '' }}</td>
                            <td>{{ $po->DateModified ? date('Y-m-d H:i:s', strtotime($po->DateModified)) : 'N/A' }}</td>
                            <td>{{ $po->deletedBy->FirstName ?? 'N/A' }} {{ $po->deletedBy->LastName ?? '' }}</td>
                            <td>{{ $po->DateDeleted ? date('Y-m-d H:i:s', strtotime($po->DateDeleted)) : 'N/A' }}</td>
                            <td>{{ $po->restoredBy->FirstName ?? 'N/A' }} {{ $po->restoredBy->LastName ?? '' }}</td>
                            <td>{{ $po->DateRestored ? date('Y-m-d H:i:s', strtotime($po->DateRestored)) : 'N/A' }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('purchases.show', $po->PurchaseOrderID) }}" 
                                       class="btn btn-sm btn-info" 
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
                        </tr>
                        @empty
                        <tr>
                            <td colspan="14" class="text-center">No purchase orders found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Deleted Purchase Orders Section -->
        <div id="deletedPurchases" class="card-body" style="display: none;">
            <div class="table-responsive">
                <table class="table table-hover" id="deletedPurchaseOrdersTable">
                    <thead>
                        <tr>
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deletedPurchases as $po)
                        <tr>
                            <td>{{ $po->PONumber }}</td>
                            <td>{{ $po->supplier->CompanyName }}</td>
                            <td>{{ $po->OrderDate->format('M d, Y') }}</td>
                            <td>
                                <span class="badge bg-{{ $po->Status === 'Pending' ? 'warning' : 'success' }}">
                                    {{ $po->Status }}
                                </span>
                            </td>
                            <td>₱{{ number_format($po->TotalAmount, 2) }}</td>
                            <td>{{ $po->createdBy->FirstName ?? 'N/A' }} {{ $po->createdBy->LastName ?? '' }}</td>
                            <td>{{ $po->DateCreated ? date('Y-m-d H:i:s', strtotime($po->DateCreated)) : 'N/A' }}</td>
                            <td>{{ $po->modifiedBy->FirstName ?? 'N/A' }} {{ $po->modifiedBy->LastName ?? '' }}</td>
                            <td>{{ $po->DateModified ? date('Y-m-d H:i:s', strtotime($po->DateModified)) : 'N/A' }}</td>
                            <td>{{ $po->deletedBy->FirstName ?? 'N/A' }} {{ $po->deletedBy->LastName ?? '' }}</td>
                            <td>{{ $po->DateDeleted ? date('Y-m-d H:i:s', strtotime($po->DateDeleted)) : 'N/A' }}</td>
                            <td>
                                <button type="button" 
                                        class="btn btn-sm btn-success" 
                                        onclick="restorePurchaseOrder({{ $po->PurchaseOrderID }})"
                                        title="Restore">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </button>
                            </td>
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
    const activeTable = $('#purchaseOrdersTable').DataTable({
        pageLength: 10,
        responsive: true,
        order: [[6, 'desc']],
        language: {
            search: "Search:",
            searchPlaceholder: "Search purchase orders..."
        },
        scrollX: true
    });

    const deletedTable = $('#deletedPurchaseOrdersTable').DataTable({
        pageLength: 10,
        responsive: true,
        order: [[6, 'desc']],
        language: {
            search: "Search:",
            searchPlaceholder: "Search deleted purchase orders..."
        },
        scrollX: true
    });

    // Show active records by default
    $('#deletedPurchases').hide();

    // Toggle between active and deleted records
    $('#activeRecordsBtn').click(function() {
        $('#activePurchases').show();
        $('#deletedPurchases').hide();
        activeTable.columns.adjust().draw();
    });

    $('#showDeletedBtn').click(function() {
        $('#activePurchases').hide();
        $('#deletedPurchases').show();
        deletedTable.columns.adjust().draw();
    });
});

function restorePurchaseOrder(id) {
    if (confirm('Are you sure you want to restore this purchase order?')) {
        fetch(`/purchases/${id}/restore`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
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
            alert('Error restoring purchase order: ' + error.message);
        });
    }
}

function deletePurchaseOrder(id) {
    if (confirm('Are you sure you want to delete this purchase order?')) {
        // Create a form dynamically
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ url("/purchases") }}/' + id;
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        // Add method field
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        form.appendChild(methodField);
        
        // Append form to body and submit
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection
