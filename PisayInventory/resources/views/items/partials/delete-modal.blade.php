@foreach($activeItems as $item)
<div class="modal fade" id="deleteModal{{ $item->ItemId }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Add this temporarily for debugging -->
                <small class="text-muted">Item ID: {{ $item->ItemId }}</small>
                
                <p>Are you sure you want to delete item: <strong>{{ $item->ItemName }}</strong>?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Current stock: {{ $item->StocksAvailable }}
                </div>
                <p class="text-danger"><small>This action can be undone later.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <!-- Modified form to ensure proper ID passing -->
                <form action="{{ route('items.destroy', ['item' => $item->ItemId]) }}" 
                      method="POST" 
                      class="d-inline"
                      id="deleteForm{{ $item->ItemId }}">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="item_id" value="{{ $item->ItemId }}">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Delete Item
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach