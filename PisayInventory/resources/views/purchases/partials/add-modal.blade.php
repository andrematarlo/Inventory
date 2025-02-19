<!-- Add Purchase Modal -->
<div class="modal fade" id="addPurchaseModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Create Purchase Order</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('purchases.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="items" class="form-label">Items Supplied</label>
                                <select class="form-select select2-multiple @error('items') is-invalid @enderror" 
                                        id="items" 
                                        name="items[]" 
                                        multiple="multiple"
                                        data-placeholder="Search items">
                                    @foreach($items as $item)
                                        <option value="{{ $item->ItemId }}">
                                            {{ $item->ItemName }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('items')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Purchase Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2-multiple').select2({
        theme: 'bootstrap-5',
        width: '100%',
        dropdownParent: $('#addPurchaseModal'),
        placeholder: 'Search items',
        allowClear: true,
        closeOnSelect: false,
        tags: false
    });
});
</script>
@endpush
