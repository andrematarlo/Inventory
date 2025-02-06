<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('items.store') }}" method="POST" enctype="multipart/form-data">
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
                        <label class="form-label">Item Image</label>
                        <input type="file" class="form-control @error('image') is-invalid @enderror" 
                               name="image" accept="image/*" required>
                        <small class="form-text text-muted">Upload an image of the item (JPG, PNG, or GIF)</small>
                        @error('image')
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
