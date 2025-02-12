@extends('layouts.app')

@section('title', 'Purchase Orders')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Purchase Orders</h1>
        <a href="{{ route('purchases.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> New Purchase Order
        </a>
    </div>

    <div class="card">
        <div class="card-body">
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
                            <td>{{ $po->createdBy->FirstName }} {{ $po->createdBy->LastName }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('purchases.show', $po->PurchaseOrderID) }}" 
                                       class="btn btn-sm btn-info" 
                                       title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($po->Status === 'Pending')
                                        <a href="{{ route('purchases.edit', $po->PurchaseOrderID) }}" 
                                           class="btn btn-sm btn-primary" 
                                           title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('purchases.destroy', $po->PurchaseOrderID) }}" 
                                              method="POST" 
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-danger" 
                                                    title="Delete"
                                                    onclick="return confirm('Are you sure you want to delete this purchase order?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No purchase orders found</td>
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
    $('#purchaseOrdersTable').DataTable({
        pageLength: 10,
        responsive: true,
        order: [[2, 'desc']], // Order by Order Date
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        language: {
            search: "Search:",
            searchPlaceholder: "Search purchase orders..."
        }
    });
});
</script>
@endsection
