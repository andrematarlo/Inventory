<div class="mb-3">
    <label class="form-label">Items Supplied</label>
    <select class="form-select select2 @error('items') is-invalid @enderror" 
            name="items[]" multiple>
        @foreach($items as $item)
            <option value="{{ $item->ItemId }}"
                {{ in_array($item->ItemId, old('items', $supplier->items->pluck('ItemId')->toArray() ?? [])) ? 'selected' : '' }}>
                {{ $item->ItemName }}
            </option>
        @endforeach
    </select>
    @error('items')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple items</small>
</div> 