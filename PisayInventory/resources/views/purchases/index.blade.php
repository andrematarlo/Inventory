@extends('layouts.app')

@php
    use App\Enums\PurchaseStatus;
@endphp

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

    .badge.bg-partial {
        background-color: #ffc107;
        color: #000;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Purchase Orders</h2>
        <a href="{{ route('purchases.create') }}" class="btn btn-success">
            <i class="bi bi-plus-lg"></i> Create Purchase Order
        </a>
    </div>

    <div class="mb-4">
        <button type="button" class="btn btn-primary" id="activeRecordsBtn">Active Records</button>
        <button type="button" class="btn btn-warning" id="pendingRecordsBtn">Pending Records</button>
        <button type="button" class="btn btn-danger" id="showDeletedBtn">
            <i class="bi bi-archive"></i> Show Deleted Records
        </button>
    </div>

    <!-- Active Records Section -->
    <div class="card mb-4" id="activeRecords">
        <div class="card-body">
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
                        @forelse($purchases->where('Status', '!=', PurchaseStatus::PENDING->value) as $po)
                        <tr>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('purchases.show', $po->PurchaseOrderID) }}" 
                                       class="btn btn-sm btn-blue" 
                                       title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($userPermissions && $userPermissions->CanDelete)
                                        <button type="button" 
                                                class="btn btn-sm btn-danger" 
                                                onclick="deletePurchase({{ $po->PurchaseOrderID }})"
                                                title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $po->PONumber }}</td>
                            <td>{{ $po->supplier->CompanyName }}</td>
                            <td>{{ $po->OrderDate->format('M d, Y') }}</td>
                            <td>
                                <span class="badge bg-success">
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
                            <td colspan="10" class="text-center">No active purchase orders found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pending Records Section -->
    <div class="card mb-4" id="pendingRecords" style="display: none;">
        <div class="card-body">
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
                                    <button type="button" 
                                            class="btn btn-sm btn-danger" 
                                            onclick="deletePurchase({{ $po->PurchaseOrderID }})"
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
    </div>

    <!-- Deleted Records Section -->
    <div class="card mb-4" id="deletedRecords" style="display: none;">
        <div class="card-body">
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
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deletedPurchases as $po)
                        <tr>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('purchases.show', $po->PurchaseOrderID) }}" 
                                       class="btn btn-sm btn-blue" 
                                       title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-success" 
                                            onclick="restorePurchase({{ $po->PurchaseOrderID }})"
                                            title="Restore">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                </div>
                            </td>
                            <td>{{ $po->PONumber }}</td>
                            <td>{{ $po->supplier->CompanyName }}</td>
                            <td>{{ $po->OrderDate->format('M d, Y') }}</td>
                            <td>
                                <span class="badge bg-danger">
                                    Deleted
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
                            <td colspan="10" class="text-center">No deleted purchase orders found</td>
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
    // Initialize all purchase modals
    const purchaseModals = document.querySelectorAll('[id^="addPurchaseModal"], [id^="editPurchaseModal"]');
    purchaseModals.forEach(modal => {
        // Initialize with Bootstrap's options
        const bsModal = new bootstrap.Modal(modal, {
            backdrop: 'static',
            keyboard: false
        });

        // Add click handler to prevent closing
        $(modal).on('click mousedown', function(e) {
            if ($(e.target).hasClass('modal')) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });

        // Also prevent Esc key
        $(modal).on('keydown', function(e) {
            if (e.key === 'Escape') {
                e.preventDefault();
                return false;
            }
        });
    });

    // Initialize DataTables
    var tables = ['purchaseOrdersTable', 'pendingPurchaseTable', 'deletedPurchaseTable'];
    tables.forEach(function(tableId) {
        if ($('#' + tableId + ' tbody tr').length > 1) {
            $('#' + tableId).DataTable({
                pageLength: 10,
                order: [[1, 'desc']],
                columnDefs: [
                    { orderable: false, targets: 0 }
                ]
            });
        }
    });

    // Hide other sections initially
    $('#pendingRecords, #deletedRecords').hide();

    // Button click handlers
    $('#activeRecordsBtn').click(function() {
        $('#activeRecords').show();
        $('#pendingRecords, #deletedRecords').hide();
        $(this).addClass('btn-primary').removeClass('btn-light');
        $('#pendingRecordsBtn, #showDeletedBtn').removeClass('btn-primary btn-warning btn-danger').addClass('btn-light');
    });

    $('#pendingRecordsBtn').click(function() {
        $('#pendingRecords').show();
        $('#activeRecords, #deletedRecords').hide();
        $(this).addClass('btn-warning').removeClass('btn-light');
        $('#activeRecordsBtn, #showDeletedBtn').removeClass('btn-primary btn-warning btn-danger').addClass('btn-light');
    });

    $('#showDeletedBtn').click(function() {
        $('#deletedRecords').show();
        $('#activeRecords, #pendingRecords').hide();
        $(this).addClass('btn-danger').removeClass('btn-light');
        $('#activeRecordsBtn, #pendingRecordsBtn').removeClass('btn-primary btn-warning btn-danger').addClass('btn-light');
    });
});

function deletePurchase(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/inventory/purchases/${id}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'Purchase order has been deleted.',
                            icon: 'success'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error!', response.message || 'Failed to delete purchase order.', 'error');
                    }
                },
                error: function(xhr) {
                    console.error('Delete error:', xhr);
                    Swal.fire('Error!', 'Failed to delete purchase order.', 'error');
                }
            });
        }
    });
}

function restorePurchase(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "Do you want to restore this purchase order?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, restore it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/inventory/purchases/${id}/restore`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Restored!',
                            text: 'Purchase order has been restored.',
                            icon: 'success'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error!', response.message || 'Failed to restore purchase order.', 'error');
                    }
                },
                error: function(xhr) {
                    console.error('Restore error:', xhr);
                    Swal.fire('Error!', 'Failed to restore purchase order.', 'error');
                }
            });
        }
    });
}
</script>
@endsection 