<div class="modal fade" id="editSupplierModal{{ $supplier->SupplierID }}" tabindex="-1" aria-labelledby="editSupplierModalLabel{{ $supplier->SupplierID }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editSupplierModalLabel{{ $supplier->SupplierID }}">Edit Supplier</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('suppliers.update', $supplier->SupplierID) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="CompanyName{{ $supplier->SupplierID }}" class="form-label">Company Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('CompanyName') is-invalid @enderror" 
                               id="CompanyName{{ $supplier->SupplierID }}" name="CompanyName" 
                               value="{{ old('CompanyName', $supplier->CompanyName) }}" required>
                        @error('CompanyName')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="ContactPerson{{ $supplier->SupplierID }}" class="form-label">Contact Person <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('ContactPerson') is-invalid @enderror" 
                               id="ContactPerson{{ $supplier->SupplierID }}" name="ContactPerson" 
                               value="{{ old('ContactPerson', $supplier->ContactPerson) }}" required>
                        @error('ContactPerson')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="TelephoneNumber{{ $supplier->SupplierID }}" class="form-label">Telephone Number</label>
                        <input type="text" class="form-control @error('TelephoneNumber') is-invalid @enderror" 
                               id="TelephoneNumber{{ $supplier->SupplierID }}" name="TelephoneNumber" 
                               value="{{ old('TelephoneNumber', $supplier->TelephoneNumber) }}"
                               pattern="[0-9+\-\s]+"
                               title="Please enter a valid telephone number">
                        @error('TelephoneNumber')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="ContactNum{{ $supplier->SupplierID }}" class="form-label">Contact Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('ContactNum') is-invalid @enderror" 
                               id="ContactNum{{ $supplier->SupplierID }}" name="ContactNum" 
                               value="{{ old('ContactNum', $supplier->ContactNum) }}"
                               required
                               pattern="[0-9+\-\s]+"
                               title="Please enter a valid contact number">
                        @error('ContactNum')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="Address{{ $supplier->SupplierID }}" class="form-label">Address <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('Address') is-invalid @enderror" 
                                  id="Address{{ $supplier->SupplierID }}" name="Address" 
                                  rows="3" required>{{ old('Address', $supplier->Address) }}</textarea>
                        @error('Address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Update Supplier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> 