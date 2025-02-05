<div class="modal fade" id="addPurchaseModal" tabindex="-1" aria-labelledby="addPurchaseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPurchaseModalLabel">Add New Purchase</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('purchases.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="ItemId" class="form-label">Item <span class="text-danger">*</span></label>
                            <select name="ItemId" id="ItemId" class="form-select" required>
                                <option value="">Select Item</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->ItemId }}">
                                        {{ $item->ItemName }} 
                                        ({{ $item->classification->ClassificationName ?? 'No Classification' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="UnitOfMeasureId" class="form-label">Unit of Measure</label>
                            <select name="UnitOfMeasureId" id="UnitOfMeasureId" class="form-select">
                                <option value="">Select Unit</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->UnitOfMeasureId }}">
                                        {{ $unit->UnitName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="ClassificationId" class="form-label">Classification</label>
                            <select name="ClassificationId" id="ClassificationId" class="form-select">
                                <option value="">Select Classification</option>
                                @foreach($classifications as $classification)
                                    <option value="{{ $classification->ClassificationId }}">
                                        {{ $classification->ClassificationName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="Quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control" 
                                   id="Quantity" 
                                   name="Quantity" 
                                   min="1" 
                                   required 
                                   placeholder="Enter Quantity">
                        </div>

                        <div class="col-md-6">
                            <label for="StocksAdded" class="form-label">Stocks Added <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control" 
                                   id="StocksAdded" 
                                   name="StocksAdded" 
                                   min="1" 
                                   required 
                                   placeholder="Enter Stocks Added">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Purchase</button>
                </div>
            </form>
        </div>
    </div>
</div>
