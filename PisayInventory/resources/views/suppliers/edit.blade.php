@extends('layouts.app')

@section('title', 'Edit Supplier')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Edit Supplier</h5>
                    <a href="{{ route('suppliers.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('suppliers.update', $supplier->SupplierID) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="CompanyName" class="form-label">Company Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('CompanyName') is-invalid @enderror" 
                                   id="CompanyName" name="CompanyName" 
                                   value="{{ old('CompanyName', $supplier->CompanyName) }}" required>
                            @error('CompanyName')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="ContactPerson" class="form-label">Contact Person</label>
                            <input type="text" class="form-control @error('ContactPerson') is-invalid @enderror" 
                                   id="ContactPerson" name="ContactPerson" 
                                   value="{{ old('ContactPerson', $supplier->ContactPerson) }}">
                            @error('ContactPerson')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="TelephoneNumber" class="form-label">Telephone Number</label>
                            <input type="text" class="form-control @error('TelephoneNumber') is-invalid @enderror" 
                                   id="TelephoneNumber" name="TelephoneNumber" 
                                   value="{{ old('TelephoneNumber', $supplier->TelephoneNumber) }}"
                                   pattern="[0-9+\-\s]+"
                                   title="Please enter a valid telephone number">
                            @error('TelephoneNumber')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="ContactNum" class="form-label">Contact Number <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('ContactNum') is-invalid @enderror" 
                                   id="ContactNum" 
                                   name="ContactNum" 
                                   value="{{ old('ContactNum', $supplier->ContactNum) }}"
                                   required
                                   pattern="[0-9+\-\s]+"
                                   title="Please enter a valid contact number">
                            @error('ContactNum')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="Email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('Email') is-invalid @enderror" 
                                   id="Email" name="Email" 
                                   value="{{ old('Email', $supplier->Email) }}">
                            @error('Email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="Address" class="form-label">Address</label>
                            <textarea class="form-control @error('Address') is-invalid @enderror" 
                                      id="Address" name="Address" rows="3">{{ old('Address', $supplier->Address) }}</textarea>
                            @error('Address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" onclick="history.back()">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Supplier</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        form.addEventListener('submit', function(event) {
            const companyName = document.getElementById('CompanyName').value.trim();
            const contactNum = document.getElementById('ContactNum').value.trim();
            
            if (!companyName) {
                event.preventDefault();
                alert('Company Name is required');
            }
            
            if (!contactNum) {
                event.preventDefault();
                alert('Contact Number is required');
            }
        });
    });
</script>
@endsection 