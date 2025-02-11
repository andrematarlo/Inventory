@extends('layouts.app')

@section('title', 'Edit Supplier')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Supplier</h1>
        <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('suppliers.update', $supplier->SupplierID) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="CompanyName" class="form-label">Company Name</label>
                            <input type="text" 
                                   class="form-control @error('CompanyName') is-invalid @enderror" 
                                   id="CompanyName" 
                                   name="CompanyName" 
                                   value="{{ old('CompanyName', $supplier->CompanyName) }}" 
                                   required>
                            @error('CompanyName')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="ContactPerson" class="form-label">Contact Person</label>
                            <input type="text" 
                                   class="form-control @error('ContactPerson') is-invalid @enderror" 
                                   id="ContactPerson" 
                                   name="ContactPerson" 
                                   value="{{ old('ContactPerson', $supplier->ContactPerson) }}" 
                                   required>
                            @error('ContactPerson')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="ContactNum" class="form-label">Mobile Number</label>
                            <input type="text" 
                                   class="form-control @error('ContactNum') is-invalid @enderror" 
                                   id="ContactNum" 
                                   name="ContactNum" 
                                   value="{{ old('ContactNum', $supplier->ContactNum) }}" 
                                   required>
                            @error('ContactNum')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="TelephoneNumber" class="form-label">Telephone Number</label>
                            <input type="text" 
                                   class="form-control @error('TelephoneNumber') is-invalid @enderror" 
                                   id="TelephoneNumber" 
                                   name="TelephoneNumber" 
                                   value="{{ old('TelephoneNumber', $supplier->TelephoneNumber) }}">
                            @error('TelephoneNumber')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="Address" class="form-label">Address</label>
                    <textarea class="form-control @error('Address') is-invalid @enderror" 
                              id="Address" 
                              name="Address" 
                              rows="3" 
                              required>{{ old('Address', $supplier->Address) }}</textarea>
                    @error('Address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Update Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 