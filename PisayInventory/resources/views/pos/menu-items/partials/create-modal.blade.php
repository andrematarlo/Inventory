<div class="modal fade" id="createMenuItemModal" tabindex="-1" aria-labelledby="createMenuItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="createMenuItemModalLabel">Create Menu Item</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createMenuItemForm" action="{{ route('pos.menu-items.store') }}" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    @csrf
                    
                    <div class="alert alert-danger" id="errorAlert" style="display: none;"></div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ItemName" class="form-label">Item Name *</label>
                                <input type="text" 
                                       class="form-control @error('ItemName') is-invalid @enderror" 
                                       id="ItemName" 
                                       name="ItemName" 
                                       value="{{ old('ItemName') }}" 
                                       required>
                                @error('ItemName')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="Description" class="form-label">Description</label>
                                <textarea class="form-control @error('Description') is-invalid @enderror" 
                                          id="Description" 
                                          name="Description" 
                                          rows="3">{{ old('Description') }}</textarea>
                                @error('Description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="ClassificationId" class="form-label">Category *</label>
                                <select class="form-select @error('ClassificationId') is-invalid @enderror" 
                                        id="ClassificationId" 
                                        name="ClassificationId" 
                                        required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        @if(!$category->IsDeleted)
                                            <option value="{{ $category->ClassificationId }}" 
                                                    {{ old('ClassificationId') == $category->ClassificationId ? 'selected' : '' }}>
                                                {{ $category->ClassificationName }}
                                                @if($category->ParentClassificationId)
                                                    ({{ $categories->firstWhere('ClassificationId', $category->ParentClassificationId)->ClassificationName ?? 'N/A' }})
                                                @endif
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('ClassificationId')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="Price" class="form-label">Price (₱) *</label>
                                <input type="number" 
                                       class="form-control @error('Price') is-invalid @enderror" 
                                       id="Price" 
                                       name="Price" 
                                       value="{{ old('Price') }}" 
                                       step="0.01" 
                                       min="0" 
                                       required>
                                @error('Price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="StocksAvailable" class="form-label">Initial Stock *</label>
                                <input type="number" 
                                       class="form-control @error('StocksAvailable') is-invalid @enderror" 
                                       id="StocksAvailable" 
                                       name="StocksAvailable" 
                                       value="{{ old('StocksAvailable') }}" 
                                       min="0" 
                                       required>
                                @error('StocksAvailable')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="image" class="form-label">Item Image</label>
                                <input type="file" 
                                       class="form-control @error('image') is-invalid @enderror" 
                                       id="image" 
                                       name="image" 
                                       accept="image/*">
                                <div class="form-text">Maximum file size: 2MB. Supported formats: JPEG, PNG, JPG, GIF</div>
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Menu Item</button>
                </div>
            </form>
        </div>
    </div>
</div> 