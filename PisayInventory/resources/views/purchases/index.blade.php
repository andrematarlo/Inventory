@extends('layouts.app')

@section('title', 'Purchases Management')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2 class="mb-3">Purchases Management</h2>
        <div class="d-flex justify-content-between align-items-center">
            <div class="btn-group" role="group">
                <a href="{{ route('purchases.index') }}" 
                   class="btn btn-outline-primary {{ !request('show_deleted') ? 'active' : '' }}">
                    Active Purchases
                </a>
                <a href="{{ route('purchases.index', ['show_deleted' => 1]) }}" 
                   class="btn btn-outline-primary {{ request('show_deleted') ? 'active' : '' }}">
                    Show All Purchases
                </a>
            </div>
            <button type="button" 
                    class="btn btn-primary d-flex align-items-center gap-1" 
                    data-bs-toggle="modal" 
                    data-bs-target="#addPurchaseModal">
                <i class="bi bi-plus-lg"></i>
                Add Purchase
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Actions</th>
                            <th>PO Number</th>
                            <th>Item</th>
                            <th>Supplier</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total Amount</th>
                            <th>Purchase Date</th>
                            <th>Delivery Date</th>
                            <th>Status</th>
                            <th>Created By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                        <tr>
                            <td>
                                <div class="d-flex gap-2">
                                    @if(!$purchase->IsDeleted)
                                    <button type="button" 
                                            class="btn btn-sm btn-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editPurchaseModal{{ $purchase->PurchaseId }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('purchases.destroy', $purchase->PurchaseId) }}" 
                                          method="POST" 
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-sm btn-danger"
                                                onclick="return confirm('Are you sure you want to delete this purchase?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @else
                                    <form action="{{ route('purchases.restore', $purchase->PurchaseId) }}" 
                                          method="POST" 
                                          class="d-inline">
                                        @csrf
                                        <button type="submit" 
                                                class="btn btn-sm btn-success"
                                                onclick="return confirm('Are you sure you want to restore this purchase?')">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $purchase->PurchaseOrderNumber ?? 'N/A' }}</td>
                            <td>{{ $purchase->item->ItemName }}</td>
                            <td>{{ $purchase->supplier->CompanyName }}</td>
                            <td>{{ $purchase->Quantity }}</td>
                            <td>₱{{ number_format($purchase->UnitPrice, 2) }}</td>
                            <td>₱{{ number_format($purchase->TotalAmount, 2) }}</td>
                            <td>{{ date('M d, Y', strtotime($purchase->PurchaseDate)) }}</td>
                            <td>{{ $purchase->DeliveryDate ? date('M d, Y', strtotime($purchase->DeliveryDate)) : 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $purchase->Status == 'Delivered' ? 'success' : ($purchase->Status == 'Pending' ? 'warning' : 'danger') }}">
                                    {{ $purchase->Status }}
                                </span>
                            </td>
                            <td>{{ $purchase->created_by->name ?? 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center">No purchases found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $purchases->links() }}
        </div>
    </div>

    <!-- Add Purchase Modal -->
    @include('purchases.modals.add')

    <!-- Edit Purchase Modals -->
    @foreach($purchases as $purchase)
        @include('purchases.modals.edit', ['purchase' => $purchase])
    @endforeach
</div>
@endsection

@section('scripts')
<script>
    // Add any specific JavaScript for purchases page
</script>
@endsection
