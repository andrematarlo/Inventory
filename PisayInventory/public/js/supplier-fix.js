// Supplier fix script - Final direct approach
$(document).ready(function() {
    console.log('DIRECT supplier fix script loaded at: ' + new Date().toISOString());
    
    // Direct event handler for item selection change
    $(document).on('change', '.item-select', function() {
        var row = $(this).closest('tr');
        var itemId = $(this).val();
        var supplierSelect = row.find('.supplier-select');
        
        console.log('Item changed to:', itemId);
        
        // Clear and disable supplier dropdown
        supplierSelect.empty().prop('disabled', true);
        supplierSelect.append('<option value="">Loading suppliers...</option>');
        
        if (!itemId) {
            supplierSelect.empty();
            supplierSelect.append('<option value="">Select Item First</option>');
            return;
        }
        
        // Get suppliers from data attribute
        var selectedOption = $(this).find('option:selected');
        var suppliersText = selectedOption.attr('data-suppliers');
        
        console.log('Raw suppliers data:', suppliersText);
        
        try {
            var suppliers = JSON.parse(suppliersText);
            
            // Clear the dropdown
            supplierSelect.empty();
            
            if (suppliers && suppliers.length > 0) {
                // Add default option
                supplierSelect.append('<option value="">Select Supplier</option>');
                
                // Add supplier options
                $.each(suppliers, function(i, supplier) {
                    supplierSelect.append(
                        $('<option></option>')
                            .attr('value', supplier.SupplierID)
                            .text(supplier.CompanyName)
                    );
                });
                
                // Important: Enable the dropdown
                supplierSelect.prop('disabled', false);
                
                console.log('Added ' + suppliers.length + ' suppliers to dropdown');
                
                // Auto-select if only one supplier
                if (suppliers.length === 1) {
                    supplierSelect.val(suppliers[0].SupplierID);
                    console.log('Auto-selected supplier: ' + suppliers[0].CompanyName);
                }
                
                // Force browser to redraw the select
                supplierSelect.hide().show();
            } else {
                supplierSelect.append('<option value="">No suppliers available</option>');
                console.log('No suppliers found for this item');
            }
        } catch (e) {
            console.error('Error parsing suppliers:', e);
            supplierSelect.empty();
            supplierSelect.append('<option value="">Error loading suppliers</option>');
        }
    });
    
    // Force trigger change event on any pre-selected items
    $('.item-select').each(function() {
        if ($(this).val()) {
            console.log('Triggering change on pre-selected item:', $(this).val());
            $(this).trigger('change');
        }
    });
    
    // Debugging: Show all available items and their suppliers
    console.log('----- AVAILABLE ITEMS AND SUPPLIERS -----');
    $('.item-select option').each(function() {
        if ($(this).val()) {
            console.log('Item: ' + $(this).text());
            console.log('Value: ' + $(this).val());
            console.log('Suppliers: ' + $(this).attr('data-suppliers'));
            console.log('---');
        }
    });
}); 