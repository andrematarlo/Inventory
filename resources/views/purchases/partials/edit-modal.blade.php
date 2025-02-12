<!-- Edit Purchase Modal -->
<div class="modal fade" id="editPurchaseModal{{ $purchase->PurchaseId }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Purchase</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('purchases.update', $purchase->PurchaseId) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Purchase Order Number</label>
                        <input type="text" class="form-control" name="PurchaseOrderNumber" value="{{ $purchase->PurchaseOrderNumber }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Item</label>
                        <select class="form-select" name="ItemId" required>
                            <option value="">Select Item</option>
                            @foreach($items as $item)
                                <option value="{{ $item->ItemId }}" {{ $purchase->ItemId == $item->ItemId ? 'selected' : '' }}>
                                    {{ $item->ItemName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Supplier</label>
                        <select class="form-select" name="SupplierId" required>
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->SupplierID }}" {{ $purchase->SupplierId == $supplier->SupplierID ? 'selected' : '' }}>
                                    {{ $supplier->CompanyName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" class="form-control" name="Quantity" required min="1" value="{{ $purchase->Quantity }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Unit Price</label>
                        <input type="number" class="form-control" name="UnitPrice" required min="0" step="0.01" value="{{ $purchase->UnitPrice }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Purchase Date</label>
                        <input type="date" class="form-control" name="PurchaseDate" required value="{{ date('Y-m-d', strtotime($purchase->PurchaseDate)) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Delivery Date</label>
                        <input type="date" class="form-control" name="DeliveryDate" value="{{ $purchase->DeliveryDate ? date('Y-m-d', strtotime($purchase->DeliveryDate)) : '' }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Purchase</button>
                </div>
            </form>
        </div>
    </div>
</div>
