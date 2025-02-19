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
        @if($userPermissions && $userPermissions->CanAdd)
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addReceivingModal">
            <i class="bi bi-plus-lg"></i> New Receiving
        </button>
        @endif
    </div>

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
                                    <a href="{{ route('receiving.show', $record->ReceivingID) }}" 
                                       class="btn btn-sm btn-blue" 
                                       title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($userPermissions && ($userPermissions->CanEdit || $userPermissions->CanDelete))
                                    <button type="button" 
                                            class="btn btn-sm btn-danger" 
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteReceivingModal{{ $record->ReceivingID }}"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $record->purchaseOrder->PONumber ?? 'N/A' }}</td>
                            <td>{{ $record->purchaseOrder->supplier->CompanyName ?? 'N/A' }}</td>
                            <td>{{ $record->DateReceived ? date('M d, Y', strtotime($record->DateReceived)) : 'N/A' }}</td>
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
                                    <a href="{{ route('receiving.create', ['po_id' => $record->PurchaseOrderID]) }}" 
                                       class="btn btn-sm btn-success" 
                                       title="Create Receiving">
                                        <i class="bi bi-plus-circle"></i>
                                    </a>
                                    <a href="{{ route('purchases.show', $record->PurchaseOrderID) }}" 
                                       class="btn btn-sm btn-blue" 
                                       title="View PO">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </td>
                            <td>{{ $record->PONumber }}</td>
                            <td>{{ $record->supplier->CompanyName }}</td>
                            <td>{{ $record->DateReceived ? date('M d, Y', strtotime($record->DateReceived)) : 'N/A' }}</td>
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
                                    <a href="{{ route('receiving.create', ['po_id' => $record->purchaseOrder->PurchaseOrderID]) }}" 
                                       class="btn btn-sm btn-success" 
                                       title="Continue Receiving">
                                        <i class="bi bi-plus-circle"></i> Continue
                                    </a>
                                    <a href="{{ route('receiving.show', $record->ReceivingID) }}" 
                                       class="btn btn-sm btn-blue" 
                                       title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </td>
                            <td>{{ $record->purchaseOrder->PONumber }}</td>
                            <td>{{ $record->purchaseOrder->supplier->CompanyName }}</td>
                            <td>{{ $record->DateReceived ? date('M d, Y', strtotime($record->DateReceived)) : 'N/A' }}</td>
                            <td>
                                <span class="badge bg-partial">
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
                                <button type="button" class="btn btn-sm btn-success" 
                                        onclick="restoreReceivingRecord({{ $record->ReceivingID }})"
                                        title="Restore">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </button>
                            </td>
                            <td>{{ $record->purchaseOrder->PONumber ?? 'N/A' }}</td>
                            <td>{{ $record->purchaseOrder->supplier->CompanyName ?? 'N/A' }}</td>
                            <td>{{ $record->DateReceived ? date('M d, Y', strtotime($record->DateReceived)) : 'N/A' }}</td>
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

    @foreach($receivingRecords as $record)
    <!-- Delete Modal -->
    <div class="modal fade" id="deleteReceivingModal{{ $record->ReceivingID }}" tabindex="-1" aria-labelledby="deleteReceivingModalLabel{{ $record->ReceivingID }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteReceivingModalLabel{{ $record->ReceivingID }}">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this receiving record?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="deleteRecord({{ $record->ReceivingID }})" data-bs-dismiss="modal">Delete</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
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
    var partialRows = $('#partialReceivingTable tbody tr').length;
    var deletedRows = $('#deletedReceivingTable tbody tr').length;

    // Initialize only if there are rows and not showing "No data" message
    if (activeRows > 0 && !$('#receivingTable tbody tr td').hasClass('text-center')) {
        $('#receivingTable').DataTable(config);
    }

    if (pendingRows > 0 && !$('#pendingReceivingTable tbody tr td').hasClass('text-center')) {
        $('#pendingReceivingTable').DataTable(config);
    }

    if (partialRows > 0 && !$('#partialReceivingTable tbody tr td').hasClass('text-center')) {
        $('#partialReceivingTable').DataTable(config);
    }

    if (deletedRows > 0 && !$('#deletedReceivingTable tbody tr td').hasClass('text-center')) {
        $('#deletedReceivingTable').DataTable(config);
    }

    // Hide other sections initially
    $('#pendingReceiving, #partialReceiving, #deletedReceiving').hide();

    // Button click handlers
    $('#activeRecordsBtn').click(function() {
        $('#activeReceiving').show();
        $('#pendingReceiving, #partialReceiving, #deletedReceiving').hide();
    });

    $('#pendingRecordsBtn').click(function() {
        $('#pendingReceiving').show();
        $('#activeReceiving, #partialReceiving, #deletedReceiving').hide();
    });

    $('#partialRecordsBtn').click(function() {
        $('#partialReceiving').show();
        $('#activeReceiving, #pendingReceiving, #deletedReceiving').hide();
    });

    $('#showDeletedBtn').click(function() {
        $('#deletedReceiving').show();
        $('#activeReceiving, #pendingReceiving, #partialReceiving').hide();
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
    fetch(`/inventory/receiving/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Record deleted successfully');
            window.location.reload();
        } else {
            alert(data.message || 'Error deleting record');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting record: ' + error.message);
    });
}
</script>
@endsection 