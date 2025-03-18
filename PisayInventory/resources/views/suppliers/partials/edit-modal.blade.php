@php
use App\Models\Item;
@endphp

<div class="modal fade" 
     id="editSupplierModal{{ $supplier->SupplierID }}" 
     data-bs-backdrop="static" 
     data-bs-keyboard="false" 
     tabindex="-1" 
     aria-labelledby="editSupplierModalLabel{{ $supplier->SupplierID }}" 
     aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editSupplierModalLabel{{ $supplier->SupplierID }}">Edit Supplier</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('suppliers.update', $supplier->SupplierID) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="CompanyName{{ $supplier->SupplierID }}" class="form-label">Company Name</label>
                                <input type="text" class="form-control" id="CompanyName{{ $supplier->SupplierID }}" 
                                       name="CompanyName" value="{{ $supplier->CompanyName }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="ContactPerson{{ $supplier->SupplierID }}" class="form-label">Contact Person</label>
                                <input type="text" class="form-control" id="ContactPerson{{ $supplier->SupplierID }}" 
                                       name="ContactPerson" value="{{ $supplier->ContactPerson }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="TelephoneNumber{{ $supplier->SupplierID }}" class="form-label">Telephone Number</label>
                                <input type="text" class="form-control" id="TelephoneNumber{{ $supplier->SupplierID }}" 
                                       name="TelephoneNumber" value="{{ $supplier->TelephoneNumber }}">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ContactNum{{ $supplier->SupplierID }}" class="form-label">Contact Number</label>
                                <input type="text" class="form-control" id="ContactNum{{ $supplier->SupplierID }}" 
                                       name="ContactNum" value="{{ $supplier->ContactNum }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="Address{{ $supplier->SupplierID }}" class="form-label">Address</label>
                                <textarea class="form-control" id="Address{{ $supplier->SupplierID }}" 
                                          name="Address" required>{{ $supplier->Address }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="items{{ $supplier->SupplierID }}" class="form-label">Items Supplied</label>
                        <select class="form-select select2-multiple" 
                                id="items{{ $supplier->SupplierID }}" 
                                name="items[]" 
                                multiple="multiple">
                            @foreach($items as $item)
                                <option value="{{ $item->ItemId }}"
                                    {{ $supplier->items->contains('ItemId', $item->ItemId) ? 'selected' : '' }}>
                                    {{ $item->ItemName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if(config('app.debug'))
                    <div class="d-none">
                        Debug Info:
                        <pre>
                        Route exists: {{ Route::has('items.search') ? 'Yes' : 'No' }}
                        Items count: {{ App\Models\Item::where('IsDeleted', false)->count() }}
                        Selected items: {{ $supplier->items->pluck('ItemName') }}
                        </pre>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function initializeSelect2() {
        $('#items{{ $supplier->SupplierID }}').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#editSupplierModal{{ $supplier->SupplierID }}'),
            placeholder: 'Search items',
            allowClear: true,
            closeOnSelect: false,
            templateResult: formatOption,
            templateSelection: formatOption
        }).on('select2:unselect', function(e) {
            // Prevent dropdown from closing
            e.preventDefault();
            
            // Keep dropdown open and focused after unselecting
            var self = $(this);
            setTimeout(function() {
                self.select2('open');
                $('.select2-search__field').val('').focus();
            }, 0);
        }).on('select2:opening', function() {
            // Show all items when dropdown opens
            setTimeout(function() {
                $('.select2-search__field').val('').focus();
            }, 0);
        });
    }

    function formatOption(item) {
        if (!item.id) return item.text;
        return $('<div style="display: flex; align-items: center;"><i class="bi bi-box" style="margin-right: 8px;"></i>' + item.text + '</div>');
    }

    // Initialize Select2
    initializeSelect2();

    // Re-initialize when modal is shown
    $('#editSupplierModal{{ $supplier->SupplierID }}').on('shown.bs.modal', function() {
        initializeSelect2();
    });
});
</script>

<style>
/* Dropdown styles */
.select2-container--bootstrap-5 .select2-dropdown {
    border: 1px solid #dee2e6;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.select2-container--bootstrap-5 .select2-results__option {
    padding: 8px 12px;
    margin: 0;
    display: flex;
    align-items: center;
}

.select2-container--bootstrap-5 .select2-results__option--highlighted {
    background-color: #f8f9fa !important;
    color: #212529 !important;
}

.select2-container--bootstrap-5 .select2-search__field {
    border: none;
    padding: 8px;
    width: 100%;
}

/* Selection area styles */
.select2-container--bootstrap-5 .select2-selection--multiple {
    min-height: 38px;
    border: 1px solid #dee2e6 !important;
}

.select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
    background-color: #fff;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    margin: 4px;
    padding: 4px 8px;
    display: flex;
    align-items: center;
}

.select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove {
    border: none;
    background: none;
    padding: 0;
    margin-left: 8px;
    color: #6c757d;
    order: 2;
}

.select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove:hover {
    color: #dc3545;
}

/* Add these styles to your existing styles */
.select2-container--bootstrap-5 .select2-results__option[aria-disabled=true] {
    display: none !important;
}

.select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
    background-color: #fff;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    margin: 4px;
    padding: 4px 8px;
    display: flex;
    align-items: center;
}

.select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove {
    border: none;
    background: none;
    padding: 0;
    margin-left: 8px;
    color: #6c757d;
    order: 2;
}

.select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove:hover {
    color: #dc3545;
}
</style> 