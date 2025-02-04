@extends('layouts.app')

@section('title', 'Inventory')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Inventory Management</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
            <i class="bi bi-plus-lg"></i> Add Inventory
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Classification</th>
                            <th>Stocks Added</th>
                            <th>Stocks Available</th>
                            <th>Created By</th>
                            <th>Date Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventories as $inventory)
                        <tr>
                            <td>{{ $inventory->item->ItemName ?? 'N/A' }}</td>
                            <td>{{ $inventory->item->classification->ClassificationName ?? 'N/A' }}</td>
                            <td>{{ $inventory->StocksAdded }}</td>
                            <td>{{ $inventory->StocksAvailable }}</td>
                            <td>{{ $inventory->created_by_user->Username ?? 'N/A' }}</td>
                            <td>{{ $inventory->DateCreated ? date('Y-m-d H:i', strtotime($inventory->DateCreated)) : 'N/A' }}</td>
                            <td>
                                <!-- Add your action buttons here -->
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No inventory records found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $inventories->links() }}
        </div>
    </div>
</div>

<!-- Add Inventory Modal -->
<div class="modal fade" id="addInventoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Inventory</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('inventory.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Item</label>
                        <select class="form-select" name="ItemId" required>
                            <option value="">Select Item</option>
                            @foreach($items as $item)
                                <option value="{{ $item->ItemId }}">
                                    {{ $item->ItemName }} 
                                    (ID: {{ $item->ItemId }})
                                    - {{ $item->classification->ClassificationName ?? 'No Category' }}
                                    - Available: {{ $item->StocksAvailable }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select class="form-select" name="type" required>
                            <option value="in">Stock In</option>
                            <option value="out">Stock Out</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" class="form-control" name="StocksAdded" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 