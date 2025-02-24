<!-- Add Supplier Modal -->
<div class="modal fade" 
     id="addSupplierModal" 
     data-bs-backdrop="static" 
     data-bs-keyboard="false" 
     tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('suppliers.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="CompanyName" class="form-label">Company Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('CompanyName') is-invalid @enderror" 
                               id="CompanyName" name="CompanyName" value="{{ old('CompanyName') }}" required>
                        @error('CompanyName')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="ContactPerson" class="form-label">Contact Person</label>
                        <input type="text" class="form-control @error('ContactPerson') is-invalid @enderror" 
                               id="ContactPerson" name="ContactPerson" value="{{ old('ContactPerson') }}">
                        @error('ContactPerson')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="TelephoneNumber" class="form-label">Telephone Number</label>
                        <input type="text" class="form-control @error('TelephoneNumber') is-invalid @enderror" 
                               id="TelephoneNumber" name="TelephoneNumber" 
                               value="{{ old('TelephoneNumber') }}"
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
                               value="{{ old('ContactNum') }}"
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
                                  id="Address" name="Address" rows="3">{{ old('Address') }}</textarea>
                        @error('Address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Updated Items Selection Field -->
                    <div class="mb-3">
                        <label for="items" class="form-label">Items Supplied</label>
                        <select class="form-select select2-multiple @error('items') is-invalid @enderror" 
                                id="items" 
                                name="items[]" 
                                multiple="multiple"
                                data-placeholder="Search items">
                            @foreach($items as $item)
                                <option value="{{ $item->ItemId }}"
                                    {{ in_array($item->ItemId, old('items', [])) ? 'selected' : '' }}>
                                    {{ $item->ItemName }}
                                </option>
                            @endforeach
                        </select>
                        @error('items')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2-multiple').select2({
        theme: 'bootstrap-5',
        width: '100%',
        dropdownParent: $('#addSupplierModal'),
        placeholder: 'Search items',
        allowClear: true,
        closeOnSelect: false,
        tags: false
    });
});
</script>
@endpush 