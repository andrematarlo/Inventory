@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Laboratory</h1>
        <a href="{{ route('laboratories.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Laboratory Information</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ url('/inventory/laboratories/' . $laboratory->laboratory_id) }}">
                @csrf
                @method('PUT')

                <div class="form-group row">
                    <div class="col-md-6">
                        <label for="laboratory_id">Laboratory ID</label>
                        <input type="text" 
                               class="form-control" 
                               id="laboratory_id" 
                               name="laboratory_id" 
                               value="{{ $laboratory->laboratory_id }}" 
                               readonly>
                    </div>

                    <div class="col-md-6">
                        <label for="laboratory_name">Laboratory Name <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('laboratory_name') is-invalid @enderror" 
                               id="laboratory_name" 
                               name="laboratory_name" 
                               value="{{ old('laboratory_name', $laboratory->laboratory_name) }}" 
                               required>
                        @error('laboratory_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group row mt-3">
                    <div class="col-md-6">
                        <label for="location">Location <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('location') is-invalid @enderror" 
                               id="location" 
                               name="location" 
                               value="{{ old('location', $laboratory->location) }}" 
                               required>
                        @error('location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="capacity">Capacity <span class="text-danger">*</span></label>
                        <input type="number" 
                               class="form-control @error('capacity') is-invalid @enderror" 
                               id="capacity" 
                               name="capacity" 
                               value="{{ old('capacity', $laboratory->capacity) }}" 
                               min="1" 
                               required>
                        @error('capacity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group row mt-3">
                    <div class="col-md-6">
                        <label for="status">Status <span class="text-danger">*</span></label>
                        <select class="form-control @error('status') is-invalid @enderror" 
                                id="status" 
                                name="status" 
                                required>
                            <option value="Available" {{ old('status', $laboratory->status) === 'Available' ? 'selected' : '' }}>Available</option>
                            <option value="Occupied" {{ old('status', $laboratory->status) === 'Occupied' ? 'selected' : '' }}>Occupied</option>
                            <option value="Under Maintenance" {{ old('status', $laboratory->status) === 'Under Maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group mt-3">
                    <label for="description">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" 
                              name="description" 
                              rows="3">{{ old('description', $laboratory->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Update Laboratory
                    </button>
                    <a href="{{ route('laboratories.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush
@endsection 