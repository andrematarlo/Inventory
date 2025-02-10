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
                            <th>Item</th>
                            <th>Classification</th>
                            <th>Unit of Measure</th>
                            <th>Quantity</th>
                            <th>Stocks Added</th>
                            <th>Created By</th>
                            <th>Date Created</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                        <tr>
                            <td>
                                <div class="d-flex gap-2">
                                    @if(!$purchase->IsDeleted)
                                        <button type="button" 
                                                class="btn btn-sm btn-primary flex-grow-1 d-flex align-items-center justify-content-center" 
                                                style="width: 100px; height: 31px;"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editPurchaseModal{{ $purchase->PurchaseId }}">
                                            <i class="bi bi-pencil me-1"></i>
                                            Edit
                                        </button>
                                        <form action="{{ route('purchases.destroy', $purchase->PurchaseId) }}" 
                                              method="POST" 
                                              style="margin: 0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-danger d-flex align-items-center justify-content-center" 
                                                    style="width: 100px; height: 31px;"
                                                    onclick="return confirm('Are you sure you want to delete this purchase record?');">
                                                <i class="bi bi-trash me-1"></i>
                                                Delete
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('purchases.restore', $purchase->PurchaseId) }}" 
                                              method="POST" 
                                              style="margin: 0;">
                                            @csrf
                                            <button type="submit" 
                                                    class="btn btn-sm btn-success d-flex align-items-center justify-content-center" 
                                                    style="width: 100px; height: 31px;"
                                                    onclick="return confirm('Are you sure you want to restore this purchase record?');">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i>
                                                Restore
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $purchase->item->ItemName ?? 'N/A' }}</td>
                            <td>{{ $purchase->item->classification->ClassificationName ?? 'N/A' }}</td>
                            <td>{{ $purchase->unit_of_measure->UnitName ?? 'N/A' }}</td>
                            <td>{{ $purchase->Quantity }}</td>
                            <td>{{ $purchase->StocksAdded }}</td>
                            <td>{{ $purchase->created_by_user->Username ?? 'N/A' }}</td>
                            <td>{{ date('Y-m-d h:i:s A', strtotime($purchase->DateCreated)) }}</td>
                            <td>
                                @if($purchase->IsDeleted)
                                    <span class="badge bg-danger">Deleted</span>
                                @else
                                    <span class="badge bg-success">Active</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">No purchase records found</td>
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
