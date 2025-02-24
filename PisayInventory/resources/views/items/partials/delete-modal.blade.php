@foreach($activeItems as $item)
<div class="modal fade" 
     id="deleteItemModal{{ $item->ItemId }}" 
     data-bs-backdrop="static" 
     data-bs-keyboard="false" 
     tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('items.destroy', $item->ItemId) }}" method="POST">
                @csrf
                @method('DELETE')
                <input type="hidden" name="ItemId" value="{{ $item->ItemId }}">
                <div class="modal-body">
                    <p>Are you sure you want to delete item: <strong>{{ $item->ItemName }}</strong>?</p>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Current stock: {{ $item->StocksAvailable }}
                    </div>
                    <p class="text-danger"><small>This action can be undone later.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Delete Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach