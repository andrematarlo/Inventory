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
                                            <select name="items[0][SupplierID]" class="form-control supplier-select" required disabled>
                                                <option value="">Select Item First</option>
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
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2 for the first row
    initializeSelects($('.item-row:first'));

    function initializeSelects(row) {
        row.find('.item-select').select2({
            width: '100%'
        });
        
        row.find('.supplier-select').select2({
            width: '100%'
        });
    }

    // Handle item selection change
    $(document).on('change', '.item-select', function() {
        const row = $(this).closest('tr');
        const supplierSelect = row.find('.supplier-select');
        const selectedOption = $(this).find('option:selected');
        
        // Get suppliers data from the data attribute
        let suppliers = [];
        try {
            const suppliersString = selectedOption.attr('data-suppliers');
            suppliers = JSON.parse(suppliersString || '[]');
        } catch (e) {
            console.error('Error parsing suppliers:', e);
            suppliers = [];
        }

        // Reset supplier select
        supplierSelect.empty();
        
        if (this.value) {
            supplierSelect.append(new Option('Select Supplier', ''));
            
            if (suppliers && suppliers.length > 0) {
                suppliers.forEach(supplier => {
                    supplierSelect.append(new Option(supplier.CompanyName, supplier.SupplierID));
                });
                supplierSelect.prop('disabled', false);
            } else {
                supplierSelect.append(new Option('No suppliers available', ''));
                supplierSelect.prop('disabled', true);
            }
        } else {
            supplierSelect.append(new Option('Select Item First', ''));
            supplierSelect.prop('disabled', true);
        }

        // Refresh Select2
        supplierSelect.trigger('change');
    });

    // Handle quantity and price changes
    $(document).on('input', '.quantity, .unit-price', function() {
        const row = $(this).closest('tr');
        const quantity = parseFloat(row.find('.quantity').val()) || 0;
        const unitPrice = parseFloat(row.find('.unit-price').val()) || 0;
        const total = quantity * unitPrice;
        
        row.find('.total-price').val(total.toFixed(2));
        calculateGrandTotal();
    });

    // Add new item row
    $('#addItem').click(function() {
        const rowCount = $('.item-row').length;
        const newRow = $(`
            <tr class="item-row">
                <td>
                    <select name="items[${rowCount}][ItemId]" class="form-control item-select" required>
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
                    <select name="items[${rowCount}][SupplierID]" class="form-control supplier-select" required disabled>
                        <option value="">Select Item First</option>
                    </select>
                </td>
                <td>
                    <input type="number" name="items[${rowCount}][Quantity]" 
                           class="form-control quantity" min="1" required>
                </td>
                <td>
                    <input type="number" name="items[${rowCount}][UnitPrice]" 
                           class="form-control unit-price" step="0.01" required>
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
        `);
        
        $('#itemsTable tbody').append(newRow);
        initializeSelects(newRow);
    });

    // Remove item row
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

    // Form submission
    $('#purchaseOrderForm').submit(function(e) {
        e.preventDefault();
        
        // Validate form
        let isValid = true;
        $('.item-row').each(function() {
            const row = $(this);
            const itemSelect = row.find('.item-select');
            const supplierSelect = row.find('.supplier-select');
            const quantity = row.find('.quantity');
            const unitPrice = row.find('.unit-price');
            
            // Remove previous validation classes
            [itemSelect, supplierSelect, quantity, unitPrice].forEach(field => {
                field.removeClass('is-invalid');
            });
            
            // Validate each field
            if (!itemSelect.val()) {
                itemSelect.addClass('is-invalid');
                isValid = false;
            }
            if (!supplierSelect.val()) {
                supplierSelect.addClass('is-invalid');
                isValid = false;
            }
            if (!quantity.val() || quantity.val() < 1) {
                quantity.addClass('is-invalid');
                isValid = false;
            }
            if (!unitPrice.val() || unitPrice.val() <= 0) {
                unitPrice.addClass('is-invalid');
                isValid = false;
            }
        });
        
        if (!isValid) {
            Swal.fire({
                title: 'Error',
                text: 'Please fill in all required fields correctly.',
                icon: 'error'
            });
            return false;
        }
        
        // Enable all supplier selects before submitting
        $('.supplier-select').prop('disabled', false);
        
        // Submit form
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
});

function calculateGrandTotal() {
    let grandTotal = 0;
    $('.total-price').each(function() {
        grandTotal += parseFloat($(this).val()) || 0;
    });
    $('#grandTotal').val(grandTotal.toFixed(2));
}
</script>
@endsection 