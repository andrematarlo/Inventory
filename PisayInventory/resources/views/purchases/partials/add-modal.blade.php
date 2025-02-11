<!-- Add Purchase Modal -->
<div class="modal fade" id="addPurchaseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Purchase</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('purchases.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Purchase Order Number</label>
                        <input type="text" class="form-control" name="PurchaseOrderNumber">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Item</label>
                        <select class="form-select" name="ItemId" required>
                            <option value="">Select Item</option>
                            @foreach($items as $item)
                                <option value="{{ $item->ItemId }}">{{ $item->ItemName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Supplier</label>
                        <select class="form-select" name="SupplierId" required>
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->SupplierID }}">{{ $supplier->CompanyName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" class="form-control" name="Quantity" required min="1">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Unit Price</label>
                        <input type="number" class="form-control" name="UnitPrice" required min="0" step="0.01">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Purchase Date</label>
                        <input type="date" class="form-control" name="PurchaseDate" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Delivery Date</label>
                        <input type="date" class="form-control" name="DeliveryDate">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Purchase</button>
                </div>
            </form>
        </div>
    </div>
</div>
