@extends('layouts.app')

@section('title', 'Receiving')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Receiving</h1>
        <a href="{{ route('receiving.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> New Receiving
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="receivingTable">
                    <thead>
                        <tr>
                            <th>PO Number</th>
                            <th>Supplier</th>
                            <th>Date Received</th>
                            <th>Status</th>
                            <th>Received By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($receivings as $receiving)
                        <tr>
                            <td>{{ $receiving->purchaseOrder->PONumber }}</td>
                            <td>{{ $receiving->purchaseOrder->supplier->CompanyName }}</td>
                            <td>{{ $receiving->DateReceived->format('M d, Y') }}</td>
                            <td>
                                <span class="badge bg-{{ $receiving->Status === 'Pending' ? 'warning' : 'success' }}">
                                    {{ $receiving->Status }}
                                </span>
                            </td>
                            <td>{{ $receiving->receivedBy->FirstName }} {{ $receiving->receivedBy->LastName }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('receiving.show', $receiving->ReceivingID) }}" 
                                       class="btn btn-sm btn-info" 
                                       title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($receiving->Status === 'Pending')
                                        <a href="{{ route('receiving.edit', $receiving->ReceivingID) }}" 
                                           class="btn btn-sm btn-primary" 
                                           title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('receiving.destroy', $receiving->ReceivingID) }}" 
                                              method="POST" 
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-danger" 
                                                    title="Delete"
                                                    onclick="return confirm('Are you sure you want to delete this receiving record?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">No receiving records found</td>
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
    $('#receivingTable').DataTable({
        pageLength: 10,
        responsive: true,
        order: [[2, 'desc']], // Order by Date Received
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        language: {
            search: "Search:",
            searchPlaceholder: "Search receiving..."
        }
    });
});
</script>
@endsection 