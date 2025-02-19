@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Manage Items</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                        Add New Item
                    </button>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Description</th>
                                    <th>Classification</th>
                                    <th>Unit</th>
                                    <th>Stocks</th>
                                    <th>Reorder Point</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                    <tr>
                                        <td>{{ $item->ItemName }}</td>
                                        <td>{{ $item->Description }}</td>
                                        <td>{{ $item->classification->ClassificationName ?? 'N/A' }}</td>
                                        <td>{{ $item->unitOfMeasure->UnitName ?? 'N/A' }}</td>
                                        <td>{{ $item->StocksAvailable }}</td>
                                        <td>{{ $item->ReorderPoint }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editItemModal{{ $item->ItemId }}">
                                                Edit
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteItemModal{{ $item->ItemId }}">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No items found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
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
                        <input type="text" class="form-control @error('ItemName') is-invalid @enderror" 
                               name="ItemName" value="{{ old('ItemName') }}" required>
                        @error('ItemName')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control @error('Description') is-invalid @enderror" 
                                name="Description">{{ old('Description') }}</textarea>
                        @error('Description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Classification</label>
                        <select class="form-select @error('ClassificationId') is-invalid @enderror" 
                                name="ClassificationId" required>
                            <option value="">Select Classification</option>
                            @foreach($classifications as $classification)
                                <option value="{{ $classification->ClassificationId }}">
                                    {{ $classification->ClassificationName }}
                                </option>
                            @endforeach
                        </select>
                        @error('ClassificationId')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Unit of Measure</label>
                        <select class="form-select @error('UnitOfMeasureId') is-invalid @enderror" 
                                name="UnitOfMeasureId" required>
                            <option value="">Select Unit</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->UnitOfMeasureId }}">
                                    {{ $unit->UnitName }}
                                </option>
                            @endforeach
                        </select>
                        @error('UnitOfMeasureId')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Initial Stock</label>
                        <input type="number" class="form-control @error('StocksAvailable') is-invalid @enderror" 
                               name="StocksAvailable" min="0" value="{{ old('StocksAvailable', 0) }}" required>
                        @error('StocksAvailable')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reorder Point</label>
                        <input type="number" class="form-control @error('ReorderPoint') is-invalid @enderror" 
                               name="ReorderPoint" min="0" value="{{ old('ReorderPoint', 0) }}" required>
                        @error('ReorderPoint')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Item Modals -->
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
                    <div class="mb-3">
                        <label class="form-label">Item Name</label>
                        <input type="text" class="form-control" name="ItemName" 
                               value="{{ old('ItemName', $item->ItemName) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="Description">{{ old('Description', $item->Description) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Classification</label>
                        <select class="form-select" name="ClassificationId" required>
                            @foreach($classifications as $classification)
                                <option value="{{ $classification->ClassificationId }}"
                                    {{ $item->ClassificationId == $classification->ClassificationId ? 'selected' : '' }}>
                                    {{ $classification->ClassificationName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Unit of Measure</label>
                        <select class="form-select" name="UnitOfMeasureId" required>
                            @foreach($units as $unit)
                                <option value="{{ $unit->UnitOfMeasureId }}"
                                    {{ $item->UnitOfMeasureId == $unit->UnitOfMeasureId ? 'selected' : '' }}>
                                    {{ $unit->UnitName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Item Modal -->
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