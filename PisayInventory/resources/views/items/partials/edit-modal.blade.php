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
                        <input type="text" class="form-control @error('ItemName') is-invalid @enderror" 
                               name="ItemName" value="{{ old('ItemName', $item->ItemName) }}" required>
                        @error('ItemName')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control @error('Description') is-invalid @enderror" 
                                name="Description" rows="3">{{ old('Description', $item->Description) }}</textarea>
                        @error('Description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Classification</label>
                        <select class="form-select @error('ClassificationId') is-invalid @enderror" 
                                name="ClassificationId" required>
                            @foreach($classifications as $classification)
                                <option value="{{ $classification->ClassificationId }}"
                                    {{ $item->ClassificationId == $classification->ClassificationId ? 'selected' : '' }}>
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
                            @foreach($units as $unit)
                                <option value="{{ $unit->UnitOfMeasureId }}"
                                    {{ $item->UnitOfMeasureId == $unit->UnitOfMeasureId ? 'selected' : '' }}>
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
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->SupplierID }}"
                                    {{ $item->SupplierID == $supplier->SupplierID ? 'selected' : '' }}>
                                    {{ $supplier->SupplierName }}
                                </option>
                            @endforeach
                        </select>
                        @error('SupplierID')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Stocks Available</label>
                        <input type="number" class="form-control @error('StocksAvailable') is-invalid @enderror" 
                               name="StocksAvailable" min="0" 
                               value="{{ old('StocksAvailable', $item->StocksAvailable) }}" required>
                        @error('StocksAvailable')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reorder Point</label>
                        <input type="number" class="form-control @error('ReorderPoint') is-invalid @enderror" 
                               name="ReorderPoint" min="0" 
                               value="{{ old('ReorderPoint', $item->ReorderPoint) }}" required>
                        @error('ReorderPoint')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
