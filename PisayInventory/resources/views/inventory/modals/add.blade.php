<!-- Add Inventory Modal -->
<div class="modal fade" 
     id="addInventoryModal" 
     data-bs-backdrop="static" 
     data-bs-keyboard="false" 
     tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Inventory</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('inventory.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Item</label>
                        <select class="form-select" name="ItemId" required>
                            <option value="">Select Item</option>
                            @foreach(\App\Models\Item::where('IsDeleted', 0)->with('classification')->orderBy('ItemName')->get() as $item)
                                <option value="{{ $item->ItemId }}">
                                    {{ $item->ItemName }} 
                                    (ID: {{ $item->ItemId }})
                                    - {{ $item->classification->ClassificationName ?? 'No Category' }}
                                    - Available: {{ $item->StocksAvailable }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select class="form-select" name="type" required>
                            <option value="in">Stock In</option>
                            <option value="out">Stock Out</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" class="form-control" name="StocksAdded" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
