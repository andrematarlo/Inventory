@extends('layouts.app')

@section('title', 'Purchase Orders')

@section('styles')
<style>
    .table-responsive {
        overflow-x: auto;
    }
    
    .table {
        width: 100%;
        margin-bottom: 1rem;
        border-collapse: collapse;
    }
    
    .table th,
    .table td {
        padding: 0.75rem;
        vertical-align: middle;
        border-top: 1px solid #dee2e6;
    }
    
    .table thead th {
        vertical-align: bottom;
        border-bottom: 2px solid #dee2e6;
        background-color: #f8f9fa;
    }
    
    .table tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.075);
    }

    /* Button styles */
    .btn {
        border-radius: 4px;
        margin-right: 5px;
    }

    .btn-active {
        background-color: #0d6efd;
        color: white;
    }

    .btn-warning {
        color: #000;
    }

    .btn-info {
        background-color: #17a2b8;
        color: white;
    }

    .btn-danger {
        background-color: #dc3545;
        color: white;
    }

    .btn-success {
        background-color: #28a745;
        color: white;
    }

    .record-section {
        display: none;
    }
    
    .record-section.active {
        display: block;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mt-4">Purchase Orders</h1>
        <a href="{{ route('purchases.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Create Purchase Order
        </a>
    </div>

    <div class="d-flex mb-3">
        <div>
            <button class="btn btn-active" id="activeRecordsBtn">Active Records</button>
            <button class="btn btn-warning">Pending Records</button>
            <button class="btn btn-danger">
                <i class="bi bi-trash"></i> Show Deleted Records
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    Show 
                    <select id="entriesPerPage" class="form-select-sm">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    entries
                </div>
                <div>
                    Search: <input type="search" class="form-control-sm" id="searchInput">
                </div>
            </div>

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
                                       class="btn btn-sm btn-active" 
                                       title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($po->Status === 'Pending')
                                    <button type="button" 
                                            class="btn btn-sm btn-active" 
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

                @if($purchases instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            Showing {{ $purchases->firstItem() ?? 0 }} to {{ $purchases->lastItem() ?? 0 }} of {{ $purchases->total() }} entries
                        </div>
                        {{ $purchases->appends(request()->except('page'))->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Delete Modals -->
@foreach($purchases as $purchase)
<div class="modal fade" id="deleteModal{{ $purchase->PurchaseOrderID }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Purchase Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this purchase order?</p>
                <p><strong>PO Number:</strong> {{ $purchase->PONumber }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('purchases.destroy', $purchase->PurchaseOrderID) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Show active records section by default
    $('#activeRecords').show();
    $('.record-section').not('#activeRecords').hide();

    // Button click handlers
    $('.d-flex button').click(function() {
        const buttonId = $(this).attr('id');
        let sectionId;

        // Determine which section to show based on button
        if (buttonId === 'activeRecordsBtn') sectionId = 'activeRecords';
        else if ($(this).hasClass('btn-warning')) sectionId = 'pendingRecords';
        else if ($(this).hasClass('btn-danger')) sectionId = 'deletedRecords';

        if (sectionId) {
            // Remove active class from all buttons
            $('.d-flex button').removeClass('active');
            // Add active class to clicked button
            $(this).addClass('active');
            
            // Hide all sections
            $('.record-section').hide();
            // Show selected section
            $(`#${sectionId}`).show();
        }
    });

    // Make first button active by default
    $('#activeRecordsBtn').addClass('active');

    // Handle delete/restore actions with SweetAlert2
    $('[data-action="delete"]').click(function() {
        const purchaseId = $(this).data('purchase-id');
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
                deletePurchaseOrder(purchaseId);
            }
        });
    });

    // Handle entries per page change
    $('#entriesPerPage').change(function() {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', $(this).val());
        window.location.href = url.toString();
    });

    // Handle search input
    let searchTimer;
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            const url = new URL(window.location.href);
            url.searchParams.set('search', $(this).val());
            window.location.href = url.toString();
        }, 500);
    });
});

// Helper functions for delete/restore actions
function deletePurchaseOrder(id) {
    $.ajax({
        url: `/inventory/purchases/${id}`,
        type: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                Swal.fire('Deleted!', 'Purchase order has been deleted.', 'success')
                    .then(() => window.location.reload());
            } else {
                Swal.fire('Error!', response.message, 'error');
            }
        },
        error: function(xhr) {
            Swal.fire('Error!', 'Failed to delete purchase order.', 'error');
        }
    });
}
</script>
@endsection
