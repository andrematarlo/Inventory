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