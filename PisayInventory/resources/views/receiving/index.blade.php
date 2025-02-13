@extends('layouts.app')

@section('title', 'Receiving Management')

@section('styles')
<style>
    .table-responsive {
        overflow-x: auto;
    }
    
    #receivingTable, #deletedReceivingTable {
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
        <div>
            <a href="{{ route('receiving.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> New Receiving Record
            </a>
        </div>
    </div>

    <div class="mb-4">
        <button type="button" class="btn btn-primary" id="activeRecordsBtn">Active Records</button>
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
                            <th>RR Number</th>
                            <th>PO Number</th>
                            <th>Supplier</th>
                            <th>Date Received</th>
                            <th>Status</th>
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
                                       class="btn btn-sm btn-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($record->Status === 'Pending')
                                    <a href="{{ route('receiving.edit', $record->ReceivingID) }}" 
                                       class="btn btn-sm btn-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="deleteRecord({{ $record->ReceivingID }})"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                            <td>{{ $record->RRNumber }}</td>
                            <td>{{ $record->purchaseOrder->PONumber }}</td>
                            <td>{{ $record->purchaseOrder->supplier->CompanyName }}</td>
                            <td>{{ $record->DateReceived->format('M d, Y') }}</td>
                            <td>
                                <span class="badge bg-{{ $record->Status === 'Pending' ? 'warning' : 'success' }}">
                                    Receiving: {{ $record->Status }}
                                </span>
                                <br>
                                <span class="badge bg-{{ $record->purchaseOrder->Status === 'Pending' ? 'warning' : 'success' }}">
                                    PO: {{ $record->purchaseOrder->Status }}
                                </span>
                            </td>
                            <td>{{ optional($record->createdBy)->FirstName }} {{ optional($record->createdBy)->LastName }}</td>
                            <td>{{ $record->DateCreated->format('M d, Y') }}</td>
                            <td>{{ optional($record->modifiedBy)->FirstName }} {{ optional($record->modifiedBy)->LastName }}</td>
                            <td>{{ optional($record->DateModified)->format('M d, Y') ?? 'N/A' }}</td>
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

        <!-- Deleted Receiving Records Section -->
        <div id="deletedReceiving" class="card-body" style="display: none;">
            <div class="table-responsive">
                <table class="table table-hover" id="deletedReceivingTable">
                    <thead>
                        <tr>
                            <th>Actions</th>
                            <th>RR Number</th>
                            <th>PO Number</th>
                            <th>Supplier</th>
                            <th>Date Received</th>
                            <th>Status</th>
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
                            <td>{{ $record->RRNumber }}</td>
                            <td>{{ $record->purchaseOrder->PONumber }}</td>
                            <td>{{ $record->purchaseOrder->supplier->CompanyName }}</td>
                            <td>{{ $record->DateReceived->format('M d, Y') }}</td>
                            <td>
                                <span class="badge bg-{{ $record->Status === 'Pending' ? 'warning' : 'success' }}">
                                    Receiving: {{ $record->Status }}
                                </span>
                                <br>
                                <span class="badge bg-{{ $record->purchaseOrder->Status === 'Pending' ? 'warning' : 'success' }}">
                                    PO: {{ $record->purchaseOrder->Status }}
                                </span>
                            </td>
                            <td>{{ optional($record->createdBy)->FirstName }} {{ optional($record->createdBy)->LastName }}</td>
                            <td>{{ $record->DateCreated->format('M d, Y') }}</td>
                            <td>{{ optional($record->modifiedBy)->FirstName }} {{ optional($record->modifiedBy)->LastName }}</td>
                            <td>{{ optional($record->DateModified)->format('M d, Y') ?? 'N/A' }}</td>
                            <td>{{ optional($record->deletedBy)->FirstName }} {{ optional($record->deletedBy)->LastName }}</td>
                            <td>{{ optional($record->DateDeleted)->format('M d, Y') ?? 'N/A' }}</td>
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
    const activeTable = $('#receivingTable').DataTable({
        pageLength: 10,
        order: [[1, 'desc']], // Sort by RR Number
        columnDefs: [{
            orderable: false,
            searchable: false,
            targets: 0
        }],
        language: {
            search: "Search:",
            searchPlaceholder: "Search receiving records..."
        },
        scrollX: true
    });

    const deletedTable = $('#deletedReceivingTable').DataTable({
        pageLength: 10,
        order: [[1, 'desc']], // Sort by RR Number
        columnDefs: [{
            orderable: false,
            searchable: false,
            targets: 0
        }],
        language: {
            search: "Search:",
            searchPlaceholder: "Search deleted receiving records..."
        },
        scrollX: true
    });

    // Show active records by default
    $('#deletedReceiving').hide();

    // Toggle between active and deleted records
    $('#activeRecordsBtn').click(function() {
        $('#activeReceiving').show();
        $('#deletedReceiving').hide();
        activeTable.columns.adjust().draw();
    });

    $('#showDeletedBtn').click(function() {
        $('#activeReceiving').hide();
        $('#deletedReceiving').show();
        deletedTable.columns.adjust().draw();
    });
});

function restoreReceivingRecord(id) {
    if (confirm('Are you sure you want to restore this receiving record?')) {
        fetch(`/receiving/${id}/restore`, {
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
        fetch(`/receiving/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
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