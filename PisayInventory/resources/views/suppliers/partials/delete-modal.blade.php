@foreach($activeSuppliers as $supplier)
<div class="modal fade" 
     id="deleteSupplierModal{{ $supplier->SupplierID }}" 
     data-bs-backdrop="static" 
     data-bs-keyboard="false" 
     tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Delete Supplier</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('suppliers.destroy', $supplier->SupplierID) }}" method="POST">
                @csrf
                @method('DELETE')
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
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach