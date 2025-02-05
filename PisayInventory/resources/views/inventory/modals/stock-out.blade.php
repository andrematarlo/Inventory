<!-- Stock Out Modal for {{ $inventory->item->ItemName }} -->
<div class="modal fade" id="stockOutModal{{ $inventory->InventoryId }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-box-arrow-right me-1"></i>
                    Stock Out - {{ $inventory->item->ItemName }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="stockOutForm{{ $inventory->InventoryId }}" action="{{ route('inventory.update', $inventory->InventoryId) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Current Stock</label>
                        <input type="text" class="form-control" value="{{ $inventory->StocksAvailable }}" readonly>
                        <input type="hidden" name="AvailableStock" value="{{ $inventory->StocksAvailable }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity to Remove</label>
                        <input type="number" 
                               class="form-control" 
                               name="StockOut" 
                               min="1" 
                               max="{{ $inventory->StocksAvailable }}" 
                               required>
                        <input type="hidden" name="type" value="out">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-box-arrow-right"></i> Stock Out
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
