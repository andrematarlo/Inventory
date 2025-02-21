@extends('layouts.app')

@section('title', 'Edit Item')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Edit Item</h3>
                    <a href="{{ route('items.index') }}" class="btn btn-outline-light">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('items.update', $item->ItemId) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="ItemName" class="form-label">Item Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('ItemName') is-invalid @enderror" 
                                   id="ItemName" name="ItemName" value="{{ old('ItemName', $item->ItemName) }}" required>
                            @error('ItemName')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="Description" class="form-label">Description</label>
                            <textarea class="form-control @error('Description') is-invalid @enderror" 
                                      id="Description" name="Description" rows="3">{{ old('Description', $item->Description) }}</textarea>
                            @error('Description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="ClassificationId" class="form-label">Classification <span class="text-danger">*</span></label>
                            <select class="form-select @error('ClassificationId') is-invalid @enderror" 
                                    id="ClassificationId" name="ClassificationId" required>
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
                            <label for="UnitOfMeasureId" class="form-label">Unit of Measure <span class="text-danger">*</span></label>
                            <select class="form-select @error('UnitOfMeasureId') is-invalid @enderror" 
                                    id="UnitOfMeasureId" name="UnitOfMeasureId" required>
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
                            <label for="ReorderPoint" class="form-label">Reorder Point <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('ReorderPoint') is-invalid @enderror" 
                                   id="ReorderPoint" name="ReorderPoint" value="{{ old('ReorderPoint', $item->ReorderPoint) }}" 
                                   min="0" required>
                            @error('ReorderPoint')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="reset" class="btn btn-secondary me-md-2">
                                <i class="bi bi-x-circle"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 