<div class="mb-3">
    <label class="form-label">Classification <span class="text-danger">*</span></label>
    <select class="form-select @error('ClassificationId') is-invalid @enderror" 
            name="ClassificationId" required>
        <option value="">Select Classification</option>
        @foreach($classifications as $classification)
            <option value="{{ $classification->ClassificationId }}" 
                {{ old('ClassificationId', $item->ClassificationId ?? '') == $classification->ClassificationId ? 'selected' : '' }}>
                {{ $classification->ClassificationName }}
            </option>
        @endforeach
    </select>
    @error('ClassificationId')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Supplier <span class="text-danger">*</span></label>
    <select class="form-select @error('SupplierID') is-invalid @enderror" 
            name="SupplierID" required>
        <option value="">Select Supplier</option>
        @foreach($suppliers as $supplier)
            <option value="{{ $supplier->SupplierID }}" 
                {{ old('SupplierID', $item->SupplierID ?? '') == $supplier->SupplierID ? 'selected' : '' }}>
                {{ $supplier->CompanyName }}
            </option>
        @endforeach
    </select>
    @error('SupplierID')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div> 