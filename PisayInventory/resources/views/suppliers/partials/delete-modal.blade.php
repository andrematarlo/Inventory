@foreach($activeSuppliers as $supplier)
<!-- Delete Supplier Modal -->
<div class="modal fade" id="deleteModal{{ $supplier->SupplierID }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Delete Supplier</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete supplier: <strong>{{ $supplier->CompanyName }}</strong>?</p>
                
                @if($supplier->items->count() > 0)
                    <div class="alert alert-warning">
                        <h6 class="alert-heading">This supplier is linked to the following items:</h6>
                        <ul class="mb-0">
                            @foreach($supplier->items as $item)
                                <li>{{ $item->ItemName }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <p class="text-danger"><small>This action can be undone later.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('suppliers.destroy', $supplier->SupplierID) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Delete Supplier
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach 