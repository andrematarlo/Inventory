@extends('layouts.app')

@section('title', 'Items')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Items Management</h2>
    <button type="button" class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addItemModal">
        <i class="bi bi-plus-lg"></i> Add New Item
    </button>
</div>

<!-- Items Table -->
<div class="card table-card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Description</th>
                        <th>Unit</th>
                        <th>Classification</th>
                        <th>Supplier</th>
                        <th>Created By</th>
                        <th>Date Created</th>
                        <th>Modified By</th>
                        <th>Date Modified</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td>{{ $item->ItemName }}</td>
                        <td>{{ $item->Description }}</td>
                        <td>{{ $item->unitOfMeasure->UnitName }}</td>
                        <td>{{ $item->classification->ClassificationName }}</td>
                        <td>{{ $item->supplier->SupplierName }}</td>
                        <td>{{ $item->createdBy->Username ?? 'N/A' }}</td>
                        <td>{{ $item->DateCreated ? date('Y-m-d H:i', strtotime($item->DateCreated)) : 'N/A' }}</td>
                        <td>{{ $item->modifiedBy->Username ?? 'N/A' }}</td>
                        <td>{{ $item->DateModified ? date('Y-m-d H:i', strtotime($item->DateModified)) : 'N/A' }}</td>
                        <td class="action-buttons">
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editItemModal{{ $item->ItemId }}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteItemModal{{ $item->ItemId }}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No items found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('items.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Item Name</label>
                        <input type="text" class="form-control" name="ItemName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="Description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Unit of Measure</label>
                        <select class="form-select" name="UnitOfMeasureId" required>
                            @foreach($units as $unit)
                                <option value="{{ $unit->UnitOfMeasureId }}">{{ $unit->UnitName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Classification</label>
                        <select class="form-select" name="ClassificationId" required>
                            @foreach($classifications as $classification)
                                <option value="{{ $classification->ClassificationId }}">
                                    {{ $classification->ClassificationName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Supplier</label>
                        <select class="form-select" name="SupplierID" required>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->SupplierID }}">{{ $supplier->SupplierName }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Item Modal -->
@foreach($items as $item)
<div class="modal fade" id="editItemModal{{ $item->ItemId }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('items.update', $item->ItemId) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <!-- Add the same form fields as in Add Item Modal, but with values -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Item</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- Delete Item Modal -->
@foreach($items as $item)
<div class="modal fade" id="deleteItemModal{{ $item->ItemId }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('items.destroy', $item->ItemId) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Are you sure you want to delete this item?</p>
                    <p class="text-danger"><strong>{{ $item->ItemName }}</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Item</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection 