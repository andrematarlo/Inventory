@extends('layouts.app')

@section('title', 'Edit Supplier')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Edit Supplier</h3>
                    <a href="{{ route('suppliers.index') }}" class="btn btn-outline-light">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('suppliers.update', $supplier->SupplierID) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="CompanyName" class="form-label">Company Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('CompanyName') is-invalid @enderror" 
                                   id="CompanyName" name="CompanyName" value="{{ old('CompanyName', $supplier->CompanyName) }}" required>
                            @error('CompanyName')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="ContactPerson" class="form-label">Contact Person</label>
                            <input type="text" class="form-control @error('ContactPerson') is-invalid @enderror" 
                                   id="ContactPerson" name="ContactPerson" value="{{ old('ContactPerson', $supplier->ContactPerson) }}">
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
                            <input type="text" class="form-control @error('ContactNum') is-invalid @enderror" 
                                   id="ContactNum" name="ContactNum" 
                                   value="{{ old('ContactNum', $supplier->ContactNum) }}"
                                   required
                                   pattern="[0-9+\-\s]+"
                                   title="Please enter a valid contact number">
                            @error('ContactNum')
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

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="reset" class="btn btn-secondary me-md-2">
                                <i class="bi bi-x-circle"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update Supplier
                            </button>
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
        // Phone number formatting
        const contactInput = document.getElementById('ContactNum');
        const telephoneInput = document.getElementById('TelephoneNumber');

        function formatPhoneNumber(input) {
            input.addEventListener('input', function(e) {
                let x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
                e.target.value = !x[2] ? x[1] : `${x[1]}-${x[2]}` + (x[3] ? `-${x[3]}` : ``);
            });
        }

        if (contactInput) formatPhoneNumber(contactInput);
        if (telephoneInput) formatPhoneNumber(telephoneInput);
    });
</script>
@endsection 