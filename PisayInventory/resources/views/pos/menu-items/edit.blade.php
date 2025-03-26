@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h1 class="fs-2 fw-bold mb-0">Edit Menu Item</h1>
                        <p class="mb-0 opacity-75">Update menu item details</p>
                    </div>
                    <a href="{{ route('pos.menu-items.index') }}" class="btn btn-light">
                        <i class="bi bi-arrow-left me-1"></i> Back to Menu Items
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('pos.menu-items.update', $menuItem->MenuItemID) }}" 
                          method="POST" 
                          enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="ItemName" class="form-label">Item Name *</label>
                                    <input type="text" 
                                           class="form-control @error('ItemName') is-invalid @enderror" 
                                           id="ItemName" 
                                           name="ItemName" 
                                           value="{{ old('ItemName', $menuItem->ItemName) }}" 
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
                                              rows="3">{{ old('Description', $menuItem->Description) }}</textarea>
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
                                            <option value="{{ $category->ClassificationId }}" 
                                                    {{ old('ClassificationId', $menuItem->ClassificationId) == $category->ClassificationId ? 'selected' : '' }}>
                                                {{ $category->ClassificationName }}
                                            </option>
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
                                           value="{{ old('Price', $menuItem->Price) }}" 
                                           step="0.01" 
                                           min="0" 
                                           required>
                                    @error('Price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="StocksAvailable" class="form-label">Stock *</label>
                                    <input type="number" 
                                           class="form-control @error('StocksAvailable') is-invalid @enderror" 
                                           id="StocksAvailable" 
                                           name="StocksAvailable" 
                                           value="{{ old('StocksAvailable', $menuItem->StocksAvailable) }}" 
                                           min="0" 
                                           required>
                                    @error('StocksAvailable')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="image" class="form-label">Item Image</label>
                                    @if($menuItem->image_path)
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/' . $menuItem->image_path) }}" 
                                                 alt="{{ $menuItem->ItemName }}"
                                                 class="img-thumbnail"
                                                 style="max-height: 100px;">
                                        </div>
                                    @endif
                                    <input type="file" 
                                           class="form-control @error('image') is-invalid @enderror" 
                                           id="image" 
                                           name="image" 
                                           accept="image/*">
                                    <div class="form-text">Leave empty to keep current image. Maximum file size: 2MB</div>
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Update Menu Item
                            </button>
                            <a href="{{ route('pos.menu-items.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 