@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h1 class="fs-2 fw-bold mb-0">Create Menu Item</h1>
                        <p class="mb-0 opacity-75">Add a new item to your menu</p>
                    </div>
                    <a href="{{ route('pos.menu-items.index') }}" class="btn btn-light">
                        <i class="bi bi-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Form -->
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('pos.menu-items.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

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
                            <label for="ClassificationId" class="form-label">Classification *</label>
                            <select class="form-select @error('ClassificationId') is-invalid @enderror" 
                                    id="ClassificationId" 
                                    name="ClassificationId" 
                                    required>
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

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Create Menu Item
                    </button>
                    <a href="{{ route('pos.menu-items.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 