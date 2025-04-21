/**
 * PSHS Inventory System - Purchase Order JavaScript
 * 
 * This file contains the client-side logic for managing purchase orders.
 * It handles item selection, supplier filtering, and calculations.
 */

$(document).ready(function() {
    // Initialize the first row when the page loads
    initializeItemRow($('.item-row').first());
    
    // Calculate initial grand total
    calculateGrandTotal();
    
    /**
     * Add New Item button click handler
     */
    $('#addItem').click(function() {
        // Count existing rows
        const rowCount = $('.item-row').length;
        
        // Get list of already selected items
        const selectedItems = [];
        $('.item-select').each(function() {
            if ($(this).val()) {
                selectedItems.push($(this).val());
            }
        });
        
        // Create new row HTML
        const newRow = $('<tr class="item-row">' + 
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
                '<select name="items[' + rowCount + '][SupplierID]" class="form-control supplier-select" required>' +
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
        
        // Add row to table
        $('#itemsTable tbody').append(newRow);
        
        // Initialize the new row
        initializeItemRow(newRow);
        
        // Disable already selected items
        newRow.find('.item-select option').each(function() {
            if ($(this).val() && selectedItems.includes($(this).val())) {
                $(this).prop('disabled', true);
            }
        });
        
        // Re-initialize Select2 after modifying options
        newRow.find('.item-select').select2('destroy').select2({
            placeholder: 'Select Item',
            width: '100%'
        });
        
        // Update Add Item button status
        updateAddItemButtonState();
    });
    
    /**
     * Initialize a row with all event handlers
     */
    function initializeItemRow(row) {
        // Initialize Select2 for item dropdown
        row.find('.item-select').select2({
            placeholder: 'Select Item',
            width: '100%'
        });
        
        // Initialize Select2 for supplier dropdown
        row.find('.supplier-select').select2({
            placeholder: 'Select Item First',
            width: '100%'
        });
        
        // Item selection change handler
        row.find('.item-select').on('change', function() {
            const itemSelect = $(this);
            const row = itemSelect.closest('tr');
            const supplierSelect = row.find('.supplier-select');
            
            // Clear supplier dropdown
            supplierSelect.empty();
            
            if (itemSelect.val()) {
                // Get item details
                const selectedOption = itemSelect.find('option:selected');
                const suppliers = selectedOption.data('suppliers');
                const itemName = selectedOption.text().trim();
                
                if (suppliers && suppliers.length > 0) {
                    // Enable supplier dropdown
                    supplierSelect.prop('disabled', false);
                    
                    // Add default option
                    supplierSelect.append(`<option value="">Select supplier for ${itemName}</option>`);
                    
                    // Add suppliers with price info
                    suppliers.forEach(function(supplier) {
                        let priceText = '';
                        if (supplier.UnitPrice) {
                            priceText = ` - ₱${parseFloat(supplier.UnitPrice).toFixed(2)} (last price)`;
                        }
                        
                        supplierSelect.append(`
                            <option value="${supplier.SupplierID}" 
                                   data-price="${supplier.UnitPrice || ''}">
                                ${supplier.CompanyName}${priceText}
                            </option>
                        `);
                    });
                    
                    // If there's only one supplier, auto-select it
                    if (suppliers.length === 1) {
                        console.log('Auto-selecting supplier: ' + suppliers[0].CompanyName);
                        supplierSelect.val(suppliers[0].SupplierID);
                        supplierSelect.trigger('change');
                    }
                } else {
                    // No suppliers available
                    supplierSelect.prop('disabled', true);
                    supplierSelect.append('<option value="">No suppliers available</option>');
                    
                    // Show warning
                    Swal.fire({
                        title: 'No Suppliers Available',
                        text: `The selected item "${itemName}" has no associated suppliers. Please select a different item or add suppliers for this item.`,
                        icon: 'warning'
                    });
                }
            } else {
                // No item selected
                supplierSelect.prop('disabled', true);
                supplierSelect.append('<option value="">Select Item First</option>');
            }
            
            // Refresh Select2
            supplierSelect.trigger('change.select2');
            
            // Clear other fields
            row.find('.quantity').val('');
            row.find('.unit-price').val('');
            calculateRowTotal(row);
        });
        
        // Supplier selection change handler
        row.find('.supplier-select').on('change', function() {
            const supplierSelect = $(this);
            const row = supplierSelect.closest('tr');
            const unitPriceInput = row.find('.unit-price');
            
            if (supplierSelect.val()) {
                // Get selected option
                const selectedOption = supplierSelect.find('option:selected');
                const defaultPrice = selectedOption.data('price');
                
                // Set price if available
                if (defaultPrice) {
                    unitPriceInput.val(defaultPrice);
                    calculateRowTotal(row);
                }
                
                // Focus on quantity field
                row.find('.quantity').focus();
            }
        });
        
        // Quantity and price change handlers
        row.find('.quantity, .unit-price').on('input', function() {
            calculateRowTotal(row);
        });
    }
    
    /**
     * Remove item click handler
     */
    $(document).on('click', '.remove-item', function() {
        const row = $(this).closest('.item-row');
        
        if ($('.item-row').length > 1) {
            // Get the item being removed
            const itemId = row.find('.item-select').val();
            
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
                    // Remove the row
                    row.remove();
                    
                    // Re-enable this item in other dropdowns
                    if (itemId) {
                        $('.item-select option[value="' + itemId + '"]').prop('disabled', false);
                        
                        // Refresh all Select2 instances
                        $('.item-select').each(function() {
                            $(this).select2('destroy').select2({
                                placeholder: 'Select Item',
                                width: '100%'
                            });
                        });
                    }
                    
                    // Update calculations and button state
                    calculateGrandTotal();
                    updateAddItemButtonState();
                    renumberRows();
                }
            });
        } else {
            // Can't remove the last row
            Swal.fire({
                title: 'Cannot Remove',
                text: 'At least one item is required.',
                icon: 'error'
            });
        }
    });
    
    /**
     * Calculate the total for a single row
     */
    function calculateRowTotal(row) {
        const quantity = parseFloat(row.find('.quantity').val()) || 0;
        const unitPrice = parseFloat(row.find('.unit-price').val()) || 0;
        const total = quantity * unitPrice;
        
        row.find('.total-price').val(total.toFixed(2));
        calculateGrandTotal();
    }
    
    /**
     * Calculate the grand total of all rows
     */
    function calculateGrandTotal() {
        let grandTotal = 0;
        
        $('.total-price').each(function() {
            grandTotal += parseFloat($(this).val()) || 0;
        });
        
        $('#grandTotal').val(grandTotal.toFixed(2));
    }
    
    /**
     * Update the Add Item button state
     */
    function updateAddItemButtonState() {
        // Get currently selected items
        const selectedItems = [];
        $('.item-select').each(function() {
            if ($(this).val()) {
                selectedItems.push($(this).val());
            }
        });
        
        // Check if there are unselected items available
        let itemsAvailable = false;
        $('.item-select:first option').each(function() {
            if ($(this).val() && !selectedItems.includes($(this).val())) {
                itemsAvailable = true;
                return false; // Break the loop
            }
        });
        
        // Update button state
        $('#addItem').prop('disabled', !itemsAvailable);
        $('#addItem').attr('title', itemsAvailable ? 
            'Add another item' : 'All available items have been added');
    }
    
    /**
     * Renumber the form fields after removing a row
     */
    function renumberRows() {
        $('.item-row').each(function(index) {
            $(this).find('.item-select').attr('name', `items[${index}][ItemId]`);
            $(this).find('.supplier-select').attr('name', `items[${index}][SupplierID]`);
            $(this).find('.quantity').attr('name', `items[${index}][Quantity]`);
            $(this).find('.unit-price').attr('name', `items[${index}][UnitPrice]`);
        });
    }
    
    /**
     * Form submission handler
     */
    $('#purchaseOrderForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validate form
        let isValid = true;
        let firstInvalidElement = null;
        
        // Check if there are items
        if ($('.item-row').length === 0) {
            Swal.fire('Error', 'Please add at least one item to the purchase order', 'error');
            return false;
        }
        
        // Validate all required fields
        $('.item-row').each(function() {
            const row = $(this);
            const itemSelect = row.find('.item-select');
            const supplierSelect = row.find('.supplier-select');
            const quantity = row.find('.quantity');
            const unitPrice = row.find('.unit-price');
            
            // Validate item
            if (!itemSelect.val()) {
                isValid = false;
                itemSelect.closest('.select2-container').addClass('is-invalid-select2');
                if (!firstInvalidElement) firstInvalidElement = itemSelect;
            } else {
                itemSelect.closest('.select2-container').removeClass('is-invalid-select2');
            }
            
            // Validate supplier
            if (!supplierSelect.val()) {
                isValid = false;
                supplierSelect.closest('.select2-container').addClass('is-invalid-select2');
                if (!firstInvalidElement) firstInvalidElement = supplierSelect;
            } else {
                supplierSelect.closest('.select2-container').removeClass('is-invalid-select2');
            }
            
            // Validate quantity
            if (!quantity.val() || parseFloat(quantity.val()) <= 0) {
                isValid = false;
                quantity.addClass('is-invalid');
                if (!firstInvalidElement) firstInvalidElement = quantity;
            } else {
                quantity.removeClass('is-invalid');
            }
            
            // Validate unit price
            if (!unitPrice.val() || parseFloat(unitPrice.val()) <= 0) {
                isValid = false;
                unitPrice.addClass('is-invalid');
                if (!firstInvalidElement) firstInvalidElement = unitPrice;
            } else {
                unitPrice.removeClass('is-invalid');
            }
        });
        
        // If validation fails
        if (!isValid) {
            Swal.fire({
                title: 'Validation Error',
                text: 'Please fill in all required fields correctly',
                icon: 'error'
            });
            
            // Scroll to the first invalid element
            if (firstInvalidElement) {
                $('html, body').animate({
                    scrollTop: firstInvalidElement.offset().top - 100
                }, 500);
            }
            
            return false;
        }
        
        // Enable any disabled supplier selects before submitting
        $('.supplier-select:disabled').prop('disabled', false);
        
        // Show loading indicator
        Swal.fire({
            title: 'Creating Purchase Order...',
            html: 'Please wait while we process your request',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Submit form via AJAX
        const formData = $(this).serialize();
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message || 'Purchase order created successfully',
                        icon: 'success',
                        confirmButtonText: 'View Purchase Order'
                    }).then((result) => {
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else {
                            window.location.href = '/inventory/purchases';
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.message || 'Failed to create purchase order',
                        icon: 'error'
                    });
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                
                let errorMessage = 'An error occurred while creating the purchase order.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                // Check for validation errors
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    let errorList = '<ul class="text-start">';
                    
                    for (const key in errors) {
                        if (errors.hasOwnProperty(key)) {
                            errorList += `<li>${errors[key]}</li>`;
                        }
                    }
                    
                    errorList += '</ul>';
                    
                    Swal.fire({
                        title: 'Validation Error',
                        html: errorList,
                        icon: 'error'
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: errorMessage,
                        icon: 'error'
                    });
                }
            }
        });
    });
    
    // Check initial state
    updateAddItemButtonState();
}); 