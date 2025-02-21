@extends('layouts.app')

@section('title', 'Create Purchase Order')

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
        <h1>Create Purchase Order</h1>
        <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>

    <form action="{{ route('purchases.store') }}" method="POST" id="purchaseOrderForm">
        @csrf
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
                                            <select name="items[0][SupplierID]" class="form-control supplier-select" required>
                                                <option value="">Select Supplier</option>
                                                @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->SupplierID }}">
                                                    {{ $supplier->CompanyName }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   name="items[0][Quantity]" 
                                                   class="form-control quantity" 
                                                   min="1" 
                                                   required>
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   name="items[0][UnitPrice]" 
                                                   class="form-control unit-price" 
                                                   step="0.01" 
                                                   required>
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   class="form-control total-price" 
                                                   readonly>
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
                                   name="PONumber"
                                   class="form-control" 
                                   value="PO-{{ date('YmdHis') }}" 
                                   readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Order Date</label>
                            <input type="text" 
                                   class="form-control" 
                                   value="{{ date('M d, Y') }}" 
                                   readonly>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            Create Purchase Order
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
    // Initialize Select2
    initializeItemRow($('.item-row:first'));

    // Add new item row
    $('#addItem').click(function() {
        let rowCount = $('.item-row').length;
        let newRow = $('<tr class="item-row">' + 
            '<td>' +
                '<select name="items[' + rowCount + '][ItemId]" class="form-control item-select" required>' +
                    '<option value="">Select Item</option>' +
                    '@foreach($items as $item)' +
                    '<option value="{{ $item->ItemId }}" data-suppliers=\'@json($item->suppliers)\'>' +
                        '{{ $item->ItemName }}' +
                    '</option>' +
                    '@endforeach' +
                '</select>' +
            '</td>' +
            '<td>' +
                '<select name="items[' + rowCount + '][SupplierID]" class="form-control supplier-select" required disabled>' +
                    '<option value="">Select Item First</option>' +
                '</select>' +
            '</td>' +
            '<td>' +
                '<input type="number" name="items[' + rowCount + '][Quantity]" class="form-control quantity" min="1" required>' +
            '</td>' +
            '<td>' +
                '<input type="number" name="items[' + rowCount + '][UnitPrice]" class="form-control unit-price" step="0.01" required>' +
            '</td>' +
            '<td>' +
                '<input type="number" class="form-control total-price" readonly>' +
            '</td>' +
            '<td>' +
                '<button type="button" class="btn btn-danger btn-sm remove-item">' +
                    '<i class="bi bi-trash"></i>' +
                '</button>' +
            '</td>' +
        '</tr>');
        
        $('#itemsTable tbody').append(newRow);
        initializeItemRow(newRow);
    });

    function initializeItemRow(row) {
        // Initialize Select2 for items
        row.find('.item-select').select2({
            placeholder: 'Select Item',
            width: '100%'
        });

        // Initialize Select2 for suppliers
        row.find('.supplier-select').select2({
            placeholder: 'Select Item First',
            width: '100%'
        });

        // Item selection change
        row.find('.item-select').on('change', function() {
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

        // Quantity and price change handlers
        row.find('.quantity, .unit-price').on('input', function() {
            calculateRowTotal(row);
        });
    }

    // Remove item row with SweetAlert
    $(document).on('click', '.remove-item', function() {
        const row = $(this).closest('.item-row');
        if ($('.item-row').length > 1) {
            Swal.fire({
                title: 'Remove Item?',
                text: "Are you sure you want to remove this item?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, remove it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    row.remove();
            calculateGrandTotal();
                }
            });
        } else {
            Swal.fire({
                title: 'Cannot Remove',
                text: 'At least one item is required.',
                icon: 'error'
            });
        }
    });

    // Form submission with SweetAlert
    $('#purchaseOrderForm').submit(function(e) {
        e.preventDefault();

        if ($('.item-row').length === 0) {
            Swal.fire({
                title: 'Error',
                text: 'Please add at least one item to the purchase order.',
                icon: 'error'
            });
            return false;
        }

        // Enable all supplier selects before submitting
        $('.supplier-select').prop('disabled', false);

        // Submit form via AJAX
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: response.message,
                        icon: 'error'
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'An error occurred while creating the purchase order.',
                    icon: 'error'
                });
            }
        });
    });

    // Add item validation
    $('#addItem').click(function() {
        const existingItems = [];
        $('.item-select').each(function() {
            const selectedValue = $(this).val();
            if (selectedValue) {
                existingItems.push(selectedValue);
            }
        });

        const availableItems = $('option', '.item-select:first').filter(function() {
            return !existingItems.includes($(this).val()) && $(this).val() !== '';
        });

        if (availableItems.length === 0) {
            Swal.fire({
                title: 'No More Items',
                text: 'All available items have been added to the purchase order.',
                icon: 'info'
            });
            return;
        }

        // Add new row code remains the same...
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
@endsection 