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
                        <td>{{ $item->unitOfMeasure->UnitName ?? 'N/A' }}</td>
                        <td>{{ $item->classification->ClassificationName ?? 'N/A' }}</td>
                        <td>{{ $item->supplier->SupplierName ?? 'N/A' }}</td>
                        <td>{{ $item->created_by_user->Username ?? 'N/A' }}</td>
                        <td data-timestamp="{{ strtotime($item->DateCreated) * 1000 }}" class="datetime-cell">
                            {{ $item->DateCreated ? date('M d, Y h:i A', strtotime($item->DateCreated)) : 'N/A' }}
                        </td>
                        <td>{{ $item->modified_by_user->Username ?? 'N/A' }}</td>
                        <td data-timestamp="{{ strtotime($item->DateModified) * 1000 }}" class="datetime-cell">
                            {{ $item->DateModified ? date('M d, Y h:i A', strtotime($item->DateModified)) : 'N/A' }}
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editItemModal{{ $item->ItemId }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('items.destroy', $item->ItemId) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this item?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center">No items found</td>
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
                        <input type="text" class="form-control @error('ItemName') is-invalid @enderror" 
                               name="ItemName" value="{{ old('ItemName') }}" required>
                        @error('ItemName')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control @error('Description') is-invalid @enderror" 
                                name="Description" rows="3">{{ old('Description') }}</textarea>
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
                                <option value="{{ $classification->ClassificationId }}"
                                    {{ old('ClassificationId') == $classification->ClassificationId ? 'selected' : '' }}>
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
                                <option value="{{ $unit->UnitOfMeasureId }}"
                                    {{ old('UnitOfMeasureId') == $unit->UnitOfMeasureId ? 'selected' : '' }}>
                                    {{ $unit->UnitName }}
                                </option>
                            @endforeach
                        </select>
                        @error('UnitOfMeasureId')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Supplier</label>
                        <select class="form-select @error('SupplierID') is-invalid @enderror" 
                                name="SupplierID" required>
                            <option value="">Select Supplier</option>
                            @foreach($suppliers ?? [] as $supplier)
                                <option value="{{ $supplier->SupplierID }}"
                                    {{ old('SupplierID') == $supplier->SupplierID ? 'selected' : '' }}>
                                    {{ $supplier->SupplierName }}
                                </option>
                            @endforeach
                        </select>
                        @error('SupplierID')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if(empty($suppliers) || $suppliers->count() == 0)
                            <div class="text-danger mt-1">
                                <small>Please add suppliers first before creating items.</small>
                            </div>
                        @endif
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

                    <div class="mb-3">
                        <label class="form-label">Supplier</label>
                        <select class="form-select" name="SupplierID" required>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->SupplierID }}"
                                    {{ $item->SupplierID == $supplier->SupplierID ? 'selected' : '' }}>
                                    {{ $supplier->SupplierName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Stocks Available</label>
                        <input type="number" class="form-control" name="StocksAvailable" 
                               value="{{ old('StocksAvailable', $item->StocksAvailable) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reorder Point</label>
                        <input type="number" class="form-control" name="ReorderPoint" 
                               value="{{ old('ReorderPoint', $item->ReorderPoint) }}" required>
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

@section('scripts')
<script>
    function updateDates() {
        document.querySelectorAll('.datetime-cell').forEach(cell => {
            const timestamp = parseInt(cell.getAttribute('data-timestamp'));
            if (timestamp) {
                const date = new Date(timestamp + (8 * 60 * 60 * 1000)); // Add 8 hours for PHT
                const hours = date.getHours().toString().padStart(2, '0');
                const minutes = date.getMinutes().toString().padStart(2, '0');
                const ampm = hours >= 12 ? 'PM' : 'AM';
                const formattedHours = (hours % 12) || 12;
                
                const formatted = `${date.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                })} ${formattedHours}:${minutes} ${ampm}`;
                
                cell.textContent = formatted;
            }
        });
    }

    // Update dates every second
    setInterval(updateDates, 1000);

    // Initial update
    updateDates();
</script>
@endsection 