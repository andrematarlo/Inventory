<div class="modal fade" 
     id="editPurchaseModal{{ $purchase->PurchaseId }}" 
     data-bs-backdrop="static" 
     data-bs-keyboard="false" 
     tabindex="-1">
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
                                    <option value="{{ $item->ItemId }}" {{ $purchase->ItemId == $item->ItemId ? 'selected' : '' }}>
                                        {{ $item->ItemName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="SupplierId{{ $purchase->PurchaseId }}" class="form-label">Supplier <span class="text-danger">*</span></label>
                            <select name="SupplierId" id="SupplierId{{ $purchase->PurchaseId }}" class="form-select" required>
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->SupplierID }}" {{ $purchase->SupplierId == $supplier->SupplierID ? 'selected' : '' }}>
                                        {{ $supplier->CompanyName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="Quantity{{ $purchase->PurchaseId }}" class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="Quantity{{ $purchase->PurchaseId }}" name="Quantity" value="{{ $purchase->Quantity }}" required min="1">
                        </div>

                        <div class="col-md-6">
                            <label for="UnitPrice{{ $purchase->PurchaseId }}" class="form-label">Unit Price <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="UnitPrice{{ $purchase->PurchaseId }}" name="UnitPrice" value="{{ $purchase->UnitPrice }}" required min="0" step="0.01">
                        </div>

                        <div class="col-md-6">
                            <label for="PurchaseOrderNumber{{ $purchase->PurchaseId }}" class="form-label">Purchase Order Number</label>
                            <input type="text" class="form-control" id="PurchaseOrderNumber{{ $purchase->PurchaseId }}" name="PurchaseOrderNumber" value="{{ $purchase->PurchaseOrderNumber }}">
                        </div>

                        <div class="col-md-6">
                            <label for="PurchaseDate{{ $purchase->PurchaseId }}" class="form-label">Purchase Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="PurchaseDate{{ $purchase->PurchaseId }}" name="PurchaseDate" value="{{ date('Y-m-d', strtotime($purchase->PurchaseDate)) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label for="DeliveryDate{{ $purchase->PurchaseId }}" class="form-label">Delivery Date</label>
                            <input type="date" class="form-control" id="DeliveryDate{{ $purchase->PurchaseId }}" name="DeliveryDate" value="{{ $purchase->DeliveryDate ? date('Y-m-d', strtotime($purchase->DeliveryDate)) : '' }}">
                        </div>

                        <div class="col-md-6">
                            <label for="Status{{ $purchase->PurchaseId }}" class="form-label">Status</label>
                            <select name="Status" id="Status{{ $purchase->PurchaseId }}" class="form-select">
                                <option value="Pending" {{ $purchase->Status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Delivered" {{ $purchase->Status == 'Delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="Cancelled" {{ $purchase->Status == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="Notes{{ $purchase->PurchaseId }}" class="form-label">Notes</label>
                            <textarea class="form-control" id="Notes{{ $purchase->PurchaseId }}" name="Notes" rows="3">{{ $purchase->Notes }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
