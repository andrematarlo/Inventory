<div class="modal fade" id="addPurchaseModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addPurchaseModalLabel" aria-hidden="true">
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
                                    <option value="{{ $item->ItemId }}">{{ $item->ItemName }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="SupplierId" class="form-label">Supplier <span class="text-danger">*</span></label>
                            <select name="SupplierId" id="SupplierId" class="form-select" required>
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->SupplierID }}">{{ $supplier->CompanyName }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="Quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="Quantity" name="Quantity" required min="1">
                        </div>

                        <div class="col-md-6">
                            <label for="UnitPrice" class="form-label">Unit Price <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="UnitPrice" name="UnitPrice" required min="0" step="0.01">
                        </div>

                        <div class="col-md-6">
                            <label for="PurchaseOrderNumber" class="form-label">Purchase Order Number</label>
                            <input type="text" class="form-control" id="PurchaseOrderNumber" name="PurchaseOrderNumber">
                        </div>

                        <div class="col-md-6">
                            <label for="PurchaseDate" class="form-label">Purchase Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="PurchaseDate" name="PurchaseDate" required>
                        </div>

                        <div class="col-md-6">
                            <label for="DeliveryDate" class="form-label">Delivery Date</label>
                            <input type="date" class="form-control" id="DeliveryDate" name="DeliveryDate">
                        </div>

                        <div class="col-md-6">
                            <label for="Status" class="form-label">Status</label>
                            <select name="Status" id="Status" class="form-select">
                                <option value="Pending">Pending</option>
                                <option value="Delivered">Delivered</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="Notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="Notes" name="Notes" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Purchase</button>
                </div>
            </form>
        </div>
    </div>
</div>
