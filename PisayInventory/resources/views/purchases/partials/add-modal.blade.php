<!-- Add Purchase Modal -->
<div class="modal fade" id="addPurchaseModal" tabindex="-1" aria-labelledby="addPurchaseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Purchase Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="purchaseOrderForm" action="{{ route('purchases.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <!-- Purchase Order Details -->
                    <div class="card mb-3">
                        <div class="card-header">
                            Purchase Order Details
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <label>PO Number</label>
                                    <input type="text" class="form-control" name="PONumber" 
                                        value="PO-{{ date('YmdHis') }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label>Order Date</label>
                                    <input type="text" class="form-control" name="OrderDate" 
                                        value="{{ date('M d, Y') }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Purchase Order Items -->
                    <div class="card">
                        <div class="card-header">
                            Purchase Order Items
                        </div>
                        <div class="card-body">
                            <table class="table" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Supplier</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="item-row">
                                        <td>
                                            <select name="items[0][ItemId]" class="form-control item-select" required>
                                                <option value="">Select Item</option>
                                                @foreach($items as $item)
                                                <option value="{{ $item->ItemId }}" 
                                                        data-suppliers='@json($item->suppliers)'>
                                                    {{ $item->ItemName }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="items[0][SupplierID]" class="form-control supplier-select" required disabled>
                                                <option value="">Select Item First</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="items[0][Quantity]" class="form-control quantity" min="1" required>
                                        </td>
                                        <td>
                                            <input type="number" name="items[0][UnitPrice]" class="form-control unit-price" step="0.01" required>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control total-price" readonly>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm remove-item">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4">
                                            <button type="button" class="btn btn-success btn-sm" id="addItem">
                                                <i class="bi bi-plus-circle"></i> Add Item
                                            </button>
                                        </td>
                                        <td>
                                            <strong>Grand Total:</strong>
                                        </td>
                                        <td>
                                            <input type="number" id="grandTotal" class="form-control" readonly>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Purchase Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .select2-container {
        z-index: 9999 !important;
    }
    .modal-body .select2-container {
        width: 100% !important;
    }
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
        border-radius: 4px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
        padding-left: 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
        right: 6px;
    }
    .select2-dropdown {
        z-index: 9999 !important;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2 with a specific parent
    function initializeSelect2() {
        $('.item-select').each(function() {
            if (!$(this).data('select2')) {
                $(this).select2({
                    placeholder: 'Select Item',
                    width: '100%',
                    dropdownParent: $(this).closest('.modal-body')
                });
            }
        });

        $('.supplier-select').each(function() {
            if (!$(this).data('select2')) {
                $(this).select2({
                    placeholder: 'Select Item First',
                    width: '100%',
                    dropdownParent: $(this).closest('.modal-body')
                });
            }
        });
    }

    // Initialize on document ready
    initializeSelect2();

    // Re-initialize when modal is shown
    $('#addPurchaseModal').on('shown.bs.modal', function() {
        initializeSelect2();
    });

    // Item selection change
    $(document).on('change', '.item-select', function() {
        const row = $(this).closest('tr');
        const supplierSelect = row.find('.supplier-select');
        
        if (this.value) {
            const selectedOption = $(this).find('option:selected');
            const suppliers = selectedOption.data('suppliers');
            
            supplierSelect.empty();
            
            if (suppliers && suppliers.length > 0) {
                supplierSelect.prop('disabled', false);
                supplierSelect.append('<option value="">Select Supplier</option>');
                
                suppliers.forEach(supplier => {
                    supplierSelect.append(`
                        <option value="${supplier.SupplierID}">
                            ${supplier.CompanyName}
                        </option>
                    `);
                });
            } else {
                supplierSelect.prop('disabled', true);
                supplierSelect.append('<option value="">No suppliers available</option>');
            }
            
            supplierSelect.trigger('change');
        } else {
            supplierSelect.empty();
            supplierSelect.prop('disabled', true);
            supplierSelect.append('<option value="">Select Item First</option>');
            supplierSelect.trigger('change');
        }
    });

    // Add new item row
    $('#addItem').click(function() {
        let rowCount = $('.item-row').length;
        let newRow = $('.item-row:first').clone();
        
        // Reset values
        newRow.find('input').val('');
        newRow.find('select').each(function() {
            if ($(this).data('select2')) {
                $(this).select2('destroy');
            }
        });
        newRow.find('select').val('');
        
        // Update names
        newRow.find('.item-select').attr('name', `items[${rowCount}][ItemId]`);
        newRow.find('.supplier-select').attr('name', `items[${rowCount}][SupplierID]`);
        newRow.find('.quantity').attr('name', `items[${rowCount}][Quantity]`);
        newRow.find('.unit-price').attr('name', `items[${rowCount}][UnitPrice]`);
        
        // Reset supplier select
        newRow.find('.supplier-select')
            .prop('disabled', true)
            .html('<option value="">Select Item First</option>');
        
        $('#itemsTable tbody').append(newRow);
        initializeSelect2();
    });

    // Remove item row
    $(document).on('click', '.remove-item', function() {
        if ($('.item-row').length > 1) {
            $(this).closest('.item-row').remove();
            calculateGrandTotal();
        }
    });

    // Update the form submission handling
    $('#purchaseOrderForm').submit(function(e) {
        e.preventDefault();

        // Validate form
        let isValid = true;
        $('#itemsTable tbody tr').each(function() {
            const row = $(this);
            if (!row.find('.item-select').val() || 
                !row.find('.quantity').val() || 
                !row.find('.unit-price').val()) {
                isValid = false;
                return false; // break the loop
            }
        });

        if (!isValid) {
            alert('Please fill in all required fields for each item');
            return;
        }

        // Submit form via AJAX
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        window.location.reload();
                    }
                } else {
                    alert(response.message || 'Error creating purchase order');
                }
            },
            error: function(xhr) {
                alert('Error creating purchase order: ' + (xhr.responseJSON?.message || 'Unknown error'));
            }
        });
    });
});

function calculateRowTotal(row) {
    let quantity = parseFloat(row.find('.quantity').val()) || 0;
    let unitPrice = parseFloat(row.find('.unit-price').val()) || 0;
    let total = quantity * unitPrice;
    row.find('.total-price').val(total.toFixed(2));
    calculateGrandTotal();
}

function calculateGrandTotal() {
    let grandTotal = 0;
    $('.total-price').each(function() {
        grandTotal += parseFloat($(this).val()) || 0;
    });
    $('#grandTotal').val(grandTotal.toFixed(2));
}
</script>
@endpush
