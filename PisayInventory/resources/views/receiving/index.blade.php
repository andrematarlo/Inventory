@extends('layouts.app')

@section('title', 'Receiving Management')

@section('styles')
<style>
    .table-responsive {
        overflow-x: auto;
    }
    
    #receivingTable, #pendingReceivingTable, #deletedReceivingTable {
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
        <h1>Receiving Management</h1>
    </div>

    <div class="mb-4">
        <button type="button" class="btn btn-primary" id="activeRecordsBtn">Active Records</button>
        <button type="button" class="btn btn-warning" id="pendingRecordsBtn">Pending Records</button>
        <button type="button" class="btn btn-danger" id="showDeletedBtn">
            <i class="bi bi-archive"></i> Show Deleted Records
        </button>
    </div>

    <div class="card">
        <!-- Active Receiving Records Section -->
        <div id="activeReceiving" class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="receivingTable">
                    <thead>
                        <tr>
                            <th>Actions</th>
                            <th>PO Number</th>
                            <th>Supplier</th>
                            <th>Delivery Date</th>
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
                                    <a href="{{ route('receiving.show', $record->ReceivingID) }}" 
                                       class="btn btn-sm btn-info" 
                                       title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($record->Status === 'Pending')
                                    <a href="{{ route('receiving.edit', $record->ReceivingID) }}" 
                                       class="btn btn-sm btn-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endif
                                    <button type="button" 
                                            class="btn btn-sm btn-danger" 
                                            onclick="deleteRecord({{ $record->ReceivingID }})"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                            <td>{{ $record->purchaseOrder->PONumber ?? 'N/A' }}</td>
                            <td>{{ $record->purchaseOrder->supplier->CompanyName ?? 'N/A' }}</td>
                            <td>{{ $record->DeliveryDate ? $record->DeliveryDate->format('M d, Y') : 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $record->Status === 'Pending' ? 'warning' : 'success' }}">
                                    {{ $record->Status }}
                                </span>
                            </td>
                            <td>₱{{ number_format($record->getTotalAmountAttribute(), 2) }}</td>
                            <td>{{ $record->createdBy->FirstName ?? 'N/A' }} {{ $record->createdBy->LastName ?? '' }}</td>
                            <td>{{ $record->DateCreated ? date('Y-m-d H:i:s', strtotime($record->DateCreated)) : 'N/A' }}</td>
                            <td>{{ $record->modifiedBy->FirstName ?? 'N/A' }} {{ $record->modifiedBy->LastName ?? '' }}</td>
                            <td>{{ $record->DateModified ? date('Y-m-d H:i:s', strtotime($record->DateModified)) : 'N/A' }}</td>
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
                        @forelse($pendingRecords as $record)
                        <tr>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('receiving.create', ['po_id' => $record->PurchaseOrderID]) }}" 
                                       class="btn btn-sm btn-success" 
                                       title="Create Receiving">
                                        <i class="bi bi-plus-circle"></i>
                                    </a>
                                    <a href="{{ route('purchases.show', $record->PurchaseOrderID) }}" 
                                       class="btn btn-sm btn-info" 
                                       title="View PO">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </td>
                            <td>{{ $record->PONumber }}</td>
                            <td>{{ $record->supplier->CompanyName }}</td>
                            <td>{{ $record->OrderDate->format('M d, Y') }}</td>
                            <td>
                                <span class="badge bg-warning">
                                    {{ $record->Status }}
                                </span>
                            </td>
                            <td>₱{{ number_format($record->getTotalAmount(), 2) }}</td>
                            <td>{{ $record->createdBy->FirstName ?? 'N/A' }} {{ $record->createdBy->LastName ?? '' }}</td>
                            <td>{{ $record->DateCreated ? date('Y-m-d H:i:s', strtotime($record->DateCreated)) : 'N/A' }}</td>
                            <td>{{ $record->modifiedBy->FirstName ?? 'N/A' }} {{ $record->modifiedBy->LastName ?? '' }}</td>
                            <td>{{ $record->DateModified ? date('Y-m-d H:i:s', strtotime($record->DateModified)) : 'N/A' }}</td>
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
                                <button type="button" class="btn btn-sm btn-success" 
                                        onclick="restoreReceivingRecord({{ $record->ReceivingID }})"
                                        title="Restore">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </button>
                            </td>
                            <td>{{ $record->purchaseOrder->PONumber ?? 'N/A' }}</td>
                            <td>{{ $record->purchaseOrder->supplier->CompanyName ?? 'N/A' }}</td>
                            <td>{{ $record->DateReceived ? $record->DateReceived->format('M d, Y') : 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $record->Status === 'Pending' ? 'warning' : 'success' }}">
                                    {{ $record->Status }}
                                </span>
                            </td>
                            <td>₱{{ number_format($record->getTotalAmountAttribute(), 2) }}</td>
                            <td>{{ $record->createdBy->FirstName ?? 'N/A' }} {{ $record->createdBy->LastName ?? '' }}</td>
                            <td>{{ $record->DateCreated ? date('Y-m-d H:i:s', strtotime($record->DateCreated)) : 'N/A' }}</td>
                            <td>{{ $record->modifiedBy->FirstName ?? 'N/A' }} {{ $record->modifiedBy->LastName ?? '' }}</td>
                            <td>{{ $record->DateModified ? date('Y-m-d H:i:s', strtotime($record->DateModified)) : 'N/A' }}</td>
                            <td>{{ $record->deletedBy->FirstName ?? 'N/A' }} {{ $record->deletedBy->LastName ?? '' }}</td>
                            <td>{{ $record->DateDeleted ? date('Y-m-d H:i:s', strtotime($record->DateDeleted)) : 'N/A' }}</td>
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
    var activeRows = $('#receivingTable tbody tr').length;
    var pendingRows = $('#pendingReceivingTable tbody tr').length;
    var deletedRows = $('#deletedReceivingTable tbody tr').length;

    // Initialize only if there are rows and not showing "No data" message
    if (activeRows > 0 && !$('#receivingTable tbody tr td').hasClass('text-center')) {
        $('#receivingTable').DataTable(config);
    }

    if (pendingRows > 0 && !$('#pendingReceivingTable tbody tr td').hasClass('text-center')) {
        $('#pendingReceivingTable').DataTable(config);
    }

    if (deletedRows > 0 && !$('#deletedReceivingTable tbody tr td').hasClass('text-center')) {
        $('#deletedReceivingTable').DataTable(config);
    }

    // Hide other sections initially
    $('#pendingReceiving, #deletedReceiving').hide();

    // Button click handlers
    $('#activeRecordsBtn').click(function() {
        $('#activeReceiving').show();
        $('#pendingReceiving, #deletedReceiving').hide();
    });

    $('#pendingRecordsBtn').click(function() {
        $('#pendingReceiving').show();
        $('#activeReceiving, #deletedReceiving').hide();
    });

    $('#showDeletedBtn').click(function() {
        $('#deletedReceiving').show();
        $('#activeReceiving, #pendingReceiving').hide();
    });
});

function restoreReceivingRecord(id) {
    if (confirm('Are you sure you want to restore this receiving record?')) {
        fetch(`/inventory/receiving/${id}/restore`, {
            method: 'POST',
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
                alert('Receiving record restored successfully');
                window.location.reload();
            } else {
                alert(data.message || 'Error restoring receiving record');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error restoring receiving record: ' + error.message);
        });
    }
}

function deleteRecord(id) {
    if (confirm('Are you sure you want to delete this receiving record?')) {
        fetch(`/inventory/receiving/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            return response.json().then(data => {
                if (!response.ok) {
                    throw new Error(data.message || 'Network response was not ok');
                }
                alert(data.message || 'Record deleted successfully');
                window.location.reload();
            });
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting receiving record: ' + error.message);
        });
    }
}
</script>
@endsection 