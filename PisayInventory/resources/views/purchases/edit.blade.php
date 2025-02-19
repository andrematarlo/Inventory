@extends('layouts.app')

@section('title', 'Edit Purchase Order')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Purchase Order</h1>
        <a href="{{ route('purchases.show', $purchase->PurchaseOrderID) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Details
        </a>
    </div>

    <form action="{{ route('purchases.update', $purchase->PurchaseOrderID) }}" method="POST" id="purchaseOrderForm">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Purchase Order Items</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
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
                                    @foreach($purchase->items as $index => $poItem)
                                    <tr class="item-row">
                                        <td>
                                            <select name="items[{{ $index }}][ItemId]" class="form-control item-select" required>
                                                <option value="">Select Item</option>
                                                @foreach($items as $item)
                                                <option value="{{ $item->ItemId }}" 
                                                        data-price="{{ $item->UnitPrice }}"
                                                        data-supplier="{{ $item->SupplierID }}"
                                                        {{ $item->ItemId == $poItem->ItemId ? 'selected' : '' }}>
                                                    {{ $item->ItemName }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="items[{{ $index }}][SupplierID]" class="form-control supplier-select" required>
                                                <option value="">Select Supplier</option>
                                                @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->SupplierID }}"
                                                        {{ $supplier->SupplierID == $poItem->SupplierID ? 'selected' : '' }}>
                                                    {{ $supplier->CompanyName }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   name="items[{{ $index }}][Quantity]" 
                                                   class="form-control quantity" 
                                                   value="{{ $poItem->Quantity }}"
                                                   min="1" 
                                                   required>
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   name="items[{{ $index }}][UnitPrice]" 
                                                   class="form-control unit-price" 
                                                   value="{{ $poItem->UnitPrice }}"
                                                   step="0.01" 
                                                   required>
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   class="form-control total-price" 
                                                   value="{{ $poItem->Quantity * $poItem->UnitPrice }}"
                                                   readonly>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm remove-item">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="6">
                                            <button type="button" class="btn btn-success btn-sm" id="addItem">
                                                <i class="bi bi-plus-circle"></i> Add Item
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Grand Total:</strong></td>
                                        <td>
                                            <input type="number" 
                                                   id="grandTotal" 
                                                   class="form-control" 
                                                   value="{{ $purchase->TotalAmount }}"
                                                   readonly>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Purchase Order Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">PO Number</label>
                            <input type="text" 
                                   class="form-control" 
                                   value="{{ $purchase->PONumber }}" 
                                   readonly>
                        </div>
                        <input type="hidden" 
                               name="SupplierID" 
                               id="mainSupplierID" 
                               value="{{ $purchase->SupplierID }}">
                        <div class="mb-3">
                            <label class="form-label">Order Date</label>
                            <input type="text" 
                                   class="form-control" 
                                   value="{{ $purchase->OrderDate->format('M d, Y') }}" 
                                   readonly>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            Update Purchase Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize all existing rows
    $('.item-row').each(function() {
        initializeItemRow($(this));
        
        // Get the selected item's supplier
        let itemSelect = $(this).find('.item-select');
        let selectedOption = itemSelect.find('option:selected');
        let supplierId = selectedOption.data('supplier');
        let supplierSelect = $(this).find('.supplier-select');
        
        // Set supplier and disable selection
        if (supplierId) {
            supplierSelect
                .val(supplierId)
                .trigger('change')
                .prop('disabled', true);
        }
    });

    // Add new item row
    $('#addItem').click(function() {
        let rowCount = $('.item-row').length;
        
        // Get all currently selected item IDs
        let selectedItems = [];
        $('.item-select').each(function() {
            let selectedValue = $(this).val();
            if (selectedValue) {
                selectedItems.push(selectedValue);
            }
        });

        // Filter out already selected items
        let availableOptions = $('.item-row:first .item-select option').filter(function() {
            return !selectedItems.includes($(this).val()) && $(this).val() !== '';
        });

        // Check if there are any items left to add
        if (availableOptions.length === 0) {
            alert('All items have been added to the purchase order.');
            return;
        }

        let newRowHtml = `
            <tr class="item-row">
                <td>
                    <select name="items[${rowCount}][ItemId]" class="form-control item-select" required>
                        <option value="">Select Item</option>
                        ${availableOptions.map(function() {
                            return `<option value="${$(this).val()}" 
                                    data-price="${$(this).data('price')}"
                                    data-supplier="${$(this).data('supplier')}">
                                    ${$(this).text()}
                                    </option>`;
                        }).get().join('')}
                    </select>
                </td>
                <td>
                    <select name="items[${rowCount}][SupplierID]" class="form-control supplier-select" required>
                        <option value="">Select Supplier</option>
                        ${$('.item-row:first .supplier-select option').map(function() {
                            return `<option value="${$(this).val()}">${$(this).text()}</option>`;
                        }).get().join('')}
                    </select>
                </td>
                <td>
                    <input type="number" name="items[${rowCount}][Quantity]" class="form-control quantity" min="1" required>
                </td>
                <td>
                    <input type="number" name="items[${rowCount}][UnitPrice]" class="form-control unit-price" step="0.01" required>
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
        `;
        
        $('#itemsTable tbody').append(newRowHtml);
        initializeItemRow($('.item-row:last'));
    });

    // Remove item row
    $(document).on('click', '.remove-item', function() {
        if ($('.item-row').length > 1) {
            $(this).closest('tr').remove();
            calculateGrandTotal();
        }
    });

    // Form submission
    $('#purchaseOrderForm').submit(function(e) {
        if ($('.item-row').length === 0) {
            e.preventDefault();
            alert('Please add at least one item to the purchase order.');
            return false;
        }

        // Enable all supplier selects before submitting
        $('.supplier-select').prop('disabled', false);
    });
});

function initializeItemRow(row) {
    // Initialize Select2 for items
    row.find('.item-select').select2({
        placeholder: 'Select Item',
        width: '100%'
    });

    // Initialize Select2 for suppliers
    row.find('.supplier-select').select2({
        placeholder: 'Select Supplier',
        width: '100%'
    });

    // Item selection change
    row.find('.item-select').on('change', function() {
        let selectedOption = $(this).find('option:selected');
        let unitPrice = selectedOption.data('price');
        let supplierId = selectedOption.data('supplier');
        let row = $(this).closest('.item-row');
        
        // Set the unit price
        row.find('.unit-price').val(unitPrice);
        calculateRowTotal(row);

        // Set the supplier for THIS ROW ONLY
        if (supplierId) {
            row.find('.supplier-select')
                .val(supplierId)
                .trigger('change')
                .prop('disabled', true);
        }
    });

    // When item is deselected or changed
    row.find('.item-select').on('select2:unselecting', function() {
        let row = $(this).closest('.item-row');
        row.find('.supplier-select')
            .prop('disabled', false)
            .val('')
            .trigger('change');
    });

    // Quantity and price change
    row.find('.quantity, .unit-price').on('input', function() {
        calculateRowTotal($(this).closest('.item-row'));
    });
}

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
@endsection 