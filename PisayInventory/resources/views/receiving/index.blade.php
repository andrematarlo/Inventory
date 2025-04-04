@extends('layouts.app')

@section('title', 'Receiving Management')

@section('styles')
<style>
    .table-responsive {
        overflow-x: auto;
    }
    
    #receivingTable, #pendingReceivingTable, #deletedReceivingTable, #partialReceivingTable {
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

    .toggle-btn {
        margin-right: 5px;
    }

    .toggle-btn.active {
        background-color: #0d6efd;
        color: white;
    }

    .table-container {
        margin-top: 20px;
    }
</style>
@endsection

@php
   // Set timezone to Philippines
   date_default_timezone_set('Asia/Manila');
@endphp

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Receiving Management</h2>
    </div>

    @if($userPermissions->CanView)
    <div class="mb-4">
        <button type="button" class="btn btn-primary" id="activeRecordsBtn">Active Records</button>
        <button type="button" class="btn btn-warning" id="pendingRecordsBtn">Pending Records</button>
        <button type="button" class="btn btn-info" id="partialRecordsBtn">
            Partial Records 
            @if($partialRecords->count() > 0)
            <span class="badge bg-light text-dark">{{ $partialRecords->count() }}</span>
            @endif
        </button>
        <button type="button" class="btn btn-danger" id="showDeletedBtn">
            <i class="bi bi-archive"></i> Show Deleted Records
        </button>
    </div>
    @endif

    <div class="card">
        @if($userPermissions->CanView)
        <!-- Active Receiving Records Section -->
        <div id="activeReceiving" class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="receivingTable">
                    <thead>
                        <tr>
                            <th>Actions</th>
                            <th>PO Number</th>
                            <th>Supplier</th>
                            <th>Date Received</th>
                            <th>Status</th>
                            <th>Total Amount</th>
                            <th>Created By</th>
                            <th>Date Created</th>
                            <th>Modified By</th>
                            <th>Date Modified</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($receivingRecords as $record)
                        <tr>
                            <td>
                                <div class="btn-group" role="group">
                                    @if($userPermissions->CanView)
                                    <a href="{{ route('receiving.show', $record->ReceivingID) }}" 
                                       class="btn btn-sm btn-primary" 
                                       title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @endif
                                    
                                    @if($userPermissions->CanDelete)
                                    <button type="button" 
                                            class="btn btn-sm btn-danger" 
                                            onclick="deleteReceivingRecord('{{ $record->ReceivingID }}')"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                            <td>{{ optional($record->purchaseOrder)->PONumber }}</td>
                            <td>{{ optional($record->purchaseOrder)->supplier->CompanyName ?? 'No Supplier' }}</td>
                            <td>{{ $record->DateReceived ? date('M d, Y', strtotime($record->DateReceived)) : 'Not Set' }}</td>
                            <td>
                                <span class="badge bg-{{ $record->Status === 'Pending' ? 'warning' : 'success' }}">
                                    {{ $record->Status ?? 'Unknown' }}
                                </span>
                            </td>
                            <td>₱{{ number_format($record->getTotalAmountAttribute(), 2) }}</td>
                            <td>{{ optional($record->createdBy)->FirstName }} {{ optional($record->createdBy)->LastName }}</td>
                            <td>{{ $record->DateCreated ? date('Y-m-d H:i:s', strtotime($record->DateCreated)) : 'Not Set' }}</td>
                            <td>{{ optional($record->modifiedBy)->FirstName }} {{ optional($record->modifiedBy)->LastName }}</td>
                            <td>{{ $record->DateModified ? date('Y-m-d H:i:s', strtotime($record->DateModified)) : 'Not Set' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center">No receiving records found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pending Receiving Records Section -->
        <div id="pendingReceiving" class="card-body" style="display: none;">
            <div class="table-responsive">
                <table class="table table-hover" id="pendingReceivingTable">
                    <thead>
                        <tr>
                            <th>Actions</th>
                            <th>PO Number</th>
                            <th>Supplier</th>
                            <th>Date Received</th>
                            <th>Status</th>
                            <th>Total Amount</th>
                            <th>Created By</th>
                            <th>Date Created</th>
                            <th>Modified By</th>
                            <th>Date Modified</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingRecords as $record)
                        <tr>
                            <td>
                                <div class="btn-group" role="group">
                                    @if($userPermissions->CanEdit)
                                    <a href="{{ route('receiving.create', ['po_id' => $record->PurchaseOrderID]) }}" 
                                       class="btn btn-sm btn-success" 
                                       title="Create Receiving">
                                        <i class="bi bi-plus-circle"></i>
                                    </a>
                                    @endif
                                    
                                    @if($userPermissions->CanView)
                                    <a href="{{ route('purchases.show', $record->PurchaseOrderID) }}" 
                                       class="btn btn-sm btn-primary" 
                                       title="View PO">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                            <td>{{ optional($record)->PONumber }}</td>
                            <td>{{ optional($record->supplier)->CompanyName ?? 'No Supplier' }}</td>
                            <td>{{ $record->DateReceived ? date('M d, Y', strtotime($record->DateReceived)) : 'Not Set' }}</td>
                            <td>
                                <span class="badge bg-warning">
                                    {{ $record->Status ?? 'Unknown' }}
                                </span>
                            </td>
                            <td>₱{{ number_format($record->getTotalAmount(), 2) }}</td>
                            <td>{{ optional($record->createdBy)->FirstName }} {{ optional($record->createdBy)->LastName }}</td>
                            <td>{{ $record->DateCreated ? date('Y-m-d H:i:s', strtotime($record->DateCreated)) : 'Not Set' }}</td>
                            <td>{{ optional($record->modifiedBy)->FirstName }} {{ optional($record->modifiedBy)->LastName }}</td>
                            <td>{{ $record->DateModified ? date('Y-m-d H:i:s', strtotime($record->DateModified)) : 'Not Set' }}</td>
                        </tr>
                        @empty
                        @endforelse
                        
                        @if($pendingRecords->isEmpty())
                        <tr>
                            <td colspan="10" class="text-center">No pending records found</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Partial Receiving Records Section -->
        <div id="partialReceiving" class="card-body" style="display: none;">
            <div class="table-responsive">
                <table class="table table-hover" id="partialReceivingTable">
                    <thead>
                        <tr>
                            <th>Actions</th>
                            <th>PO Number</th>
                            <th>Supplier</th>
                            <th>Date Received</th>
                            <th>Status</th>
                            <th>Total Amount</th>
                            <th>Created By</th>
                            <th>Date Created</th>
                            <th>Modified By</th>
                            <th>Date Modified</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($partialRecords as $record)
                        <tr>
                            <td>
                                <div class="btn-group" role="group">
                                    @if($userPermissions->CanEdit)
                                    <a href="{{ route('receiving.create', ['po_id' => optional($record->purchaseOrder)->PurchaseOrderID]) }}" 
                                       class="btn btn-sm btn-success" 
                                       title="Continue Receiving">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </a>
                                    @endif
                                    
                                    @if($userPermissions->CanView)
                                    <a href="{{ route('receiving.show', $record->ReceivingID) }}" 
                                       class="btn btn-sm btn-primary" 
                                       title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                            <td>{{ optional($record->purchaseOrder)->PONumber }}</td>
                            <td>{{ optional($record->purchaseOrder)->supplier->CompanyName ?? 'No Supplier' }}</td>
                            <td>{{ $record->DateReceived ? date('M d, Y', strtotime($record->DateReceived)) : 'Not Set' }}</td>
                            <td>
                                <span class="badge bg-partial">
                                    {{ $record->Status ?? 'Unknown' }}
                                </span>
                            </td>
                            <td>₱{{ number_format($record->getTotalAmountAttribute(), 2) }}</td>
                            <td>{{ optional($record->createdBy)->FirstName }} {{ optional($record->createdBy)->LastName }}</td>
                            <td>{{ $record->DateCreated ? date('Y-m-d H:i:s', strtotime($record->DateCreated)) : 'Not Set' }}</td>
                            <td>{{ optional($record->modifiedBy)->FirstName }} {{ optional($record->modifiedBy)->LastName }}</td>
                            <td>{{ $record->DateModified ? date('Y-m-d H:i:s', strtotime($record->DateModified)) : 'Not Set' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center">No partial records found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Deleted Receiving Records Section -->
        <div id="deletedReceiving" class="card-body" style="display: none;">
            <div class="table-responsive">
                <table class="table table-hover" id="deletedReceivingTable">
                    <thead>
                        <tr>
                            <th>Actions</th>
                            <th>PO Number</th>
                            <th>Supplier</th>
                            <th>Date Received</th>
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
                        @forelse($deletedRecords as $record)
                        <tr>
                            <td>
                                @if($userPermissions->CanEdit)
                                <button type="button" class="btn btn-sm btn-success" 
                                        onclick="restoreReceivingRecord('{{ $record->ReceivingID }}')"
                                        title="Restore">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </button>
                                @endif
                            </td>
                            <td>{{ optional($record->purchaseOrder)->PONumber }}</td>
                            <td>{{ optional($record->purchaseOrder)->supplier->CompanyName ?? 'No Supplier' }}</td>
                            <td>{{ $record->DateReceived ? date('M d, Y', strtotime($record->DateReceived)) : 'Not Set' }}</td>
                            <td>
                                <span class="badge bg-{{ $record->Status === 'Pending' ? 'warning' : 'success' }}">
                                    {{ $record->Status ?? 'Unknown' }}
                                </span>
                            </td>
                            <td>₱{{ number_format($record->getTotalAmountAttribute(), 2) }}</td>
                            <td>{{ optional($record->createdBy)->FirstName }} {{ optional($record->createdBy)->LastName }}</td>
                            <td>{{ $record->DateCreated ? date('Y-m-d H:i:s', strtotime($record->DateCreated)) : 'Not Set' }}</td>
                            <td>{{ optional($record->modifiedBy)->FirstName }} {{ optional($record->modifiedBy)->LastName }}</td>
                            <td>{{ $record->DateModified ? date('Y-m-d H:i:s', strtotime($record->DateModified)) : 'Not Set' }}</td>
                            <td>{{ optional($record->deletedBy)->FirstName }} {{ optional($record->deletedBy)->LastName }}</td>
                            <td>{{ $record->DateDeleted ? date('Y-m-d H:i:s', strtotime($record->DateDeleted)) : 'Not Set' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="text-center">No deleted receiving records found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="card-body">
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle-fill"></i> 
                You do not have permission to view receiving records.
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Delete Receiving Modal -->
<div class="modal fade" 
     id="deleteReceivingModal" 
     data-bs-backdrop="static" 
     data-bs-keyboard="false" 
     tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Receiving Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this receiving record?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                @if($userPermissions->CanDelete)
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTables
        const receivingTable = $('#receivingTable').DataTable({
            responsive: true,
            order: [[7, 'desc']] // Order by DateCreated by default
        });

        const pendingTable = $('#pendingReceivingTable').DataTable({
            responsive: true,
            order: [[7, 'desc']]
        });

        const partialTable = $('#partialReceivingTable').DataTable({
            responsive: true,
            order: [[7, 'desc']]
        });

        const deletedTable = $('#deletedReceivingTable').DataTable({
            responsive: true,
            order: [[7, 'desc']]
        });

        // Hide all tables initially
        $('.table-container').hide();
        
        // Show active records by default
        $('#receivingTable').closest('.table-container').show();
        $('#activeRecordsBtn').addClass('active');

        // Toggle button click handlers
        $('#activeRecordsBtn').click(function() {
            $('.table-container').hide();
            $('#receivingTable').closest('.table-container').show();
            $('.toggle-btn').removeClass('active');
            $(this).addClass('active');
        });

        $('#pendingRecordsBtn').click(function() {
            $('.table-container').hide();
            $('#pendingReceivingTable').closest('.table-container').show();
            $('.toggle-btn').removeClass('active');
            $(this).addClass('active');
        });

        $('#partialRecordsBtn').click(function() {
            $('.table-container').hide();
            $('#partialReceivingTable').closest('.table-container').show();
            $('.toggle-btn').removeClass('active');
            $(this).addClass('active');
        });

        $('#showDeletedBtn').click(function() {
            $('.table-container').hide();
            $('#deletedReceivingTable').closest('.table-container').show();
            $('.toggle-btn').removeClass('active');
            $(this).addClass('active');
        });

        // Add active class to buttons
        $('.toggle-btn').click(function() {
            $('.toggle-btn').removeClass('active');
            $(this).addClass('active');
        });
    });

    function deleteReceivingRecord(id) {
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteReceivingModal'), {
            backdrop: 'static',
            keyboard: false
        });
        
        // Store the ID for use in confirmation
        document.getElementById('confirmDeleteBtn').setAttribute('data-receiving-id', id);
        
        // Show the modal
        deleteModal.show();
    }

    // Add event listener for delete confirmation
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        const id = this.getAttribute('data-receiving-id');
        
        $.ajax({
            url: `/inventory/receiving/${id}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    bootstrap.Modal.getInstance(document.getElementById('deleteReceivingModal')).hide();
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Receiving record has been deleted successfully.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    alert(response.message || 'Failed to delete receiving record.');
                }
            },
            error: function(xhr) {
                console.error('Delete error:', xhr);
                alert('Failed to delete receiving record.');
            }
        });
    });

    function restoreReceivingRecord(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to restore this receiving record?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, restore it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/inventory/receiving/${id}/restore`,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Restored!', 'Receiving record has been restored.', 'success')
                                .then(() => window.location.reload());
                        } else {
                            Swal.fire('Error!', response.message || 'Failed to restore receiving record.', 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', 'Failed to restore receiving record.', 'error');
                    }
                });
            }
        });
    }
</script>
@endsection 