@extends('layouts.app')

@section('title', 'Inventory')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Inventory Management</h2>
    <button type="button" class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
        <i class="bi bi-plus-lg"></i> Add New Stock
    </button>
</div>

<!-- Inventory Table -->
<div class="card table-card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Classification</th>
                        <th>Available Stocks</th>
                        <th>Stocks Added</th>
                        <th>Created By</th>
                        <th>Date Created</th>
                        <th>Modified By</th>
                        <th>Date Modified</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventory as $stock)
                    <tr>
                        <td>{{ $stock->item->ItemName }}</td>
                        <td>{{ $stock->classification->ClassificationName }}</td>
                        <td>{{ $stock->StocksAvailable }}</td>
                        <td>{{ $stock->StocksAdded }}</td>
                        <td>{{ $stock->createdBy->Username ?? 'N/A' }}</td>
                        <td>{{ $stock->DateCreated ? date('Y-m-d H:i', strtotime($stock->DateCreated)) : 'N/A' }}</td>
                        <td>{{ $stock->modifiedBy->Username ?? 'N/A' }}</td>
                        <td>{{ $stock->DateModified ? date('Y-m-d H:i', strtotime($stock->DateModified)) : 'N/A' }}</td>
                        <td>
                            @if($stock->StocksAvailable <= 10)
                                <span class="status-badge status-low">Low Stock</span>
                            @else
                                <span class="status-badge status-good">Good</span>
                            @endif
                        </td>
                        <td class="action-buttons">
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" 
                                    data-bs-target="#editInventoryModal{{ $stock->ItemId }}_{{ $stock->ClassificationId }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" 
                                    data-bs-target="#deleteInventoryModal{{ $stock->ItemId }}_{{ $stock->ClassificationId }}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No inventory records found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Inventory Modal -->
<div class="modal fade" id="addInventoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Stock</h5>
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
                                <option value="{{ $item->ItemId }}">{{ $item->ItemName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Classification</label>
                        <select class="form-select" name="ClassificationId" required>
                            <option value="">Select Classification</option>
                            @foreach($classifications as $classification)
                                <option value="{{ $classification->ClassificationId }}">
                                    {{ $classification->ClassificationName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Available Stocks</label>
                        <input type="number" class="form-control" name="StocksAvailable" required min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stocks to Add</label>
                        <input type="number" class="form-control" name="StocksAdded" required min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Inventory Modals -->
@foreach($inventory as $stock)
<div class="modal fade" id="editInventoryModal{{ $stock->ItemId }}_{{ $stock->ClassificationId }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('inventory.update', ['inventory' => $stock->ItemId, 'classification' => $stock->ClassificationId]) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Item</label>
                        <input type="text" class="form-control" value="{{ $stock->item->ItemName }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Classification</label>
                        <input type="text" class="form-control" value="{{ $stock->classification->ClassificationName }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Available Stocks</label>
                        <input type="number" class="form-control" name="StocksAvailable" 
                               value="{{ $stock->StocksAvailable }}" required min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stocks to Add</label>
                        <input type="number" class="form-control" name="StocksAdded" 
                               value="{{ $stock->StocksAdded }}" required min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- Delete Inventory Modals -->
@foreach($inventory as $stock)
<div class="modal fade" id="deleteInventoryModal{{ $stock->ItemId }}_{{ $stock->ClassificationId }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Stock Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('inventory.destroy', ['inventory' => $stock->ItemId, 'classification' => $stock->ClassificationId]) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Are you sure you want to delete this inventory record?</p>
                    <p class="text-danger">
                        <strong>{{ $stock->item->ItemName }} - {{ $stock->classification->ClassificationName }}</strong>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Record</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection 