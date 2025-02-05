<div class="modal fade" id="editPurchaseModal{{ $purchase->PurchaseId }}" tabindex="-1" aria-labelledby="editPurchaseModalLabel{{ $purchase->PurchaseId }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPurchaseModalLabel{{ $purchase->PurchaseId }}">Edit Purchase</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('purchases.update', $purchase->PurchaseId) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="ItemId{{ $purchase->PurchaseId }}" class="form-label">Item <span class="text-danger">*</span></label>
                            <select name="ItemId" id="ItemId{{ $purchase->PurchaseId }}" class="form-select" required>
                                <option value="">Select Item</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->ItemId }}" 
                                        {{ $purchase->ItemId == $item->ItemId ? 'selected' : '' }}>
                                        {{ $item->ItemName }} 
                                        ({{ $item->classification->ClassificationName ?? 'No Classification' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="UnitOfMeasureId{{ $purchase->PurchaseId }}" class="form-label">Unit of Measure</label>
                            <select name="UnitOfMeasureId" id="UnitOfMeasureId{{ $purchase->PurchaseId }}" class="form-select">
                                <option value="">Select Unit</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->UnitOfMeasureId }}" 
                                        {{ $purchase->UnitOfMeasureId == $unit->UnitOfMeasureId ? 'selected' : '' }}>
                                        {{ $unit->UnitName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="ClassificationId{{ $purchase->PurchaseId }}" class="form-label">Classification</label>
                            <select name="ClassificationId" id="ClassificationId{{ $purchase->PurchaseId }}" class="form-select">
                                <option value="">Select Classification</option>
                                @foreach($classifications as $classification)
                                    <option value="{{ $classification->ClassificationId }}" 
                                        {{ $purchase->ClassificationId == $classification->ClassificationId ? 'selected' : '' }}>
                                        {{ $classification->ClassificationName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="Quantity{{ $purchase->PurchaseId }}" class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control" 
                                   id="Quantity{{ $purchase->PurchaseId }}" 
                                   name="Quantity" 
                                   min="1" 
                                   required 
                                   value="{{ $purchase->Quantity }}"
                                   placeholder="Enter Quantity">
                        </div>

                        <div class="col-md-6">
                            <label for="StocksAdded{{ $purchase->PurchaseId }}" class="form-label">Stocks Added <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control" 
                                   id="StocksAdded{{ $purchase->PurchaseId }}" 
                                   name="StocksAdded" 
                                   min="1" 
                                   required 
                                   value="{{ $purchase->StocksAdded }}"
                                   placeholder="Enter Stocks Added">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Purchase</button>
                </div>
            </form>
        </div>
    </div>
</div>
