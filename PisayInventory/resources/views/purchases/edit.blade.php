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
                                                        {{ $item->ItemId == $poItem->ItemId ? 'selected' : '' }}>
                                                    {{ $item->ItemName }}
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
                                                   value="{{ $poItem->TotalPrice }}"
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
                                        <td colspan="5">
                                            <button type="button" class="btn btn-success btn-sm" id="addItem">
                                                <i class="bi bi-plus-circle"></i> Add Item
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Grand Total:</strong></td>
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
                        <div class="mb-3">
                            <label class="form-label">Supplier</label>
                            <select name="SupplierID" class="form-control supplier-select" required>
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->SupplierID }}"
                                        {{ $supplier->SupplierID == $purchase->SupplierID ? 'selected' : '' }}>
                                    {{ $supplier->CompanyName }}
                                </option>
                                @endforeach
                            </select>
                        </div>
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
// Same JavaScript as create.blade.php
$(document).ready(function() {
    $('.supplier-select').select2({
        placeholder: 'Select Supplier',
        width: '100%'
    });

    $('.item-row').each(function() {
        initializeItemRow($(this));
    });

    $('#addItem').click(function() {
        let newRow = $('.item-row:first').clone();
        let rowCount = $('.item-row').length;
        
        newRow.find('[name]').each(function() {
            let name = $(this).attr('name');
            $(this).attr('name', name.replace('[0]', '[' + rowCount + ']'));
        });

        newRow.find('input').val('');
        newRow.find('select').val('').trigger('change');
        newRow.find('.select2').remove();

        $('#itemsTable tbody').append(newRow);
        initializeItemRow(newRow);
    });

    $(document).on('click', '.remove-item', function() {
        if ($('.item-row').length > 1) {
            $(this).closest('tr').remove();
            calculateGrandTotal();
        }
    });

    $('#purchaseOrderForm').submit(function(e) {
        if ($('.item-row').length === 0) {
            e.preventDefault();
            alert('Please add at least one item to the purchase order.');
            return false;
        }
    });
});

function initializeItemRow(row) {
    row.find('.item-select').select2({
        placeholder: 'Select Item',
        width: '100%'
    });

    row.find('.item-select').on('change', function() {
        let selectedOption = $(this).find('option:selected');
        let unitPrice = selectedOption.data('price') || 0;
        let row = $(this).closest('tr');
        row.find('.unit-price').val(unitPrice);
        calculateRowTotal(row);
    });

    row.find('.quantity, .unit-price').on('input', function() {
        calculateRowTotal($(this).closest('tr'));
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