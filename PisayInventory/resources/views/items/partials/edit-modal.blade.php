<!-- Edit Item Modal -->
<div class="modal fade" 
     id="editItemModal{{ $item->ItemId }}" 
     data-bs-backdrop="static" 
     data-bs-keyboard="false" 
     tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('items.update', $item->ItemId) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <!-- Current Image Preview -->
                    <div class="mb-3">
                        <label class="form-label">Current Image</label>
                        <div>
                            @if($item->ImagePath)
                                <img src="{{ asset('storage/' . $item->ImagePath) }}" 
                                     alt="Current item image" 
                                     class="img-thumbnail"
                                     style="max-height: 100px;">
                            @else
                                <p class="text-muted">No image uploaded</p>
                            @endif
                        </div>
                    </div>

                    <!-- Image Upload Field -->
                    <div class="mb-3">
                        <label for="image{{ $item->ItemId }}" class="form-label">Update Image</label>
                        <input type="file" 
                               class="form-control @error('image') is-invalid @enderror" 
                               id="image{{ $item->ItemId }}" 
                               name="image"
                               accept="image/*">
                        <small class="form-text text-muted">Leave empty to keep current image. Accepted formats: JPEG, PNG, JPG, GIF. Max size: 2MB</small>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="ItemName{{ $item->ItemId }}" class="form-label">Item Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('ItemName') is-invalid @enderror" 
                               id="ItemName{{ $item->ItemId }}" name="ItemName" value="{{ old('ItemName', $item->ItemName) }}" required>
                        @error('ItemName')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="Description{{ $item->ItemId }}" class="form-label">Description</label>
                        <textarea class="form-control @error('Description') is-invalid @enderror" 
                                  id="Description{{ $item->ItemId }}" name="Description" rows="3">{{ old('Description', $item->Description) }}</textarea>
                        @error('Description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="ClassificationId{{ $item->ItemId }}" class="form-label">Classification <span class="text-danger">*</span></label>
                        <select class="form-select @error('ClassificationId') is-invalid @enderror" 
                                id="ClassificationId{{ $item->ItemId }}" name="ClassificationId" required>
                            <option value="">Select Classification</option>
                            @foreach($classifications as $classification)
                                <option value="{{ $classification->ClassificationId }}" 
                                    {{ old('ClassificationId', $item->ClassificationId) == $classification->ClassificationId ? 'selected' : '' }}>
                                    {{ $classification->ClassificationName }}
                                </option>
                            @endforeach
                        </select>
                        @error('ClassificationId')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="UnitOfMeasureId{{ $item->ItemId }}" class="form-label">Unit of Measure <span class="text-danger">*</span></label>
                        <select class="form-select @error('UnitOfMeasureId') is-invalid @enderror" 
                                id="UnitOfMeasureId{{ $item->ItemId }}" name="UnitOfMeasureId" required>
                            <option value="">Select Unit</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->UnitOfMeasureId }}" 
                                    {{ old('UnitOfMeasureId', $item->UnitOfMeasureId) == $unit->UnitOfMeasureId ? 'selected' : '' }}>
                                    {{ $unit->UnitName }}
                                </option>
                            @endforeach
                        </select>
                        @error('UnitOfMeasureId')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="ReorderPoint{{ $item->ItemId }}" class="form-label">Reorder Point <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('ReorderPoint') is-invalid @enderror" 
                               id="ReorderPoint{{ $item->ItemId }}" name="ReorderPoint" 
                               value="{{ old('ReorderPoint', $item->ReorderPoint) }}" min="0" required>
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
