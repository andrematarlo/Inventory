<!-- Value Meal Modal -->
<div class="modal fade" id="createValueMealModal" tabindex="-1" aria-labelledby="createValueMealModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createValueMealModalLabel">Create New Value Meal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createValueMealForm" action="{{ route('pos.menu-items.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="IsValueMeal" value="1">
                <input type="hidden" name="UnitOfMeasureID" value="{{ $unitOfMeasures->first() ? $unitOfMeasures->first()->UnitOfMeasureId : '' }}">
                <input type="hidden" name="StocksAvailable" value="0">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="valueMealName" class="form-label">Value Meal Name</label>
                            <input type="text" class="form-control" id="valueMealName" name="ItemName" required>
                        </div>
                        <div class="col-md-4">
                            <label for="valueMealPrice" class="form-label">Price</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" id="valueMealPrice" name="Price" min="0" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label for="ClassificationId" class="form-label">Category</label>
                            <select class="form-select" id="ClassificationId" name="ClassificationId" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->ClassificationId }}">{{ $category->ClassificationName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="valueMealImage" class="form-label">Image</label>
                            <input type="file" class="form-control" id="valueMealImage" name="image" accept="image/*">
                        </div>
                        <div class="col-12">
                            <div class="mb-2">
                                <label class="form-label mb-0">Included Items</label>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Quantity</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="valueMealItemsList">
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addValueMealItem">
                                <i class="fas fa-plus"></i> Add Item
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Value Meal</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Store menu items data - only regular menu items (explicitly filtered)
    const menuItemsData = [
        @foreach($regularMenuItems as $item)
        {
            id: "{{ $item->MenuItemID }}",
            name: "{{ $item->ItemName }}",
            stock: {{ $item->StocksAvailable }},
            isValueMeal: {{ $item->IsValueMeal ? 'true' : 'false' }}
        },
        @endforeach
    ].filter(item => !item.isValueMeal);  // Extra safety check to filter out value meals

    // Function to get available menu items excluding selected ones
    function getAvailableMenuItems() {
        const selectedItems = new Set();
        $('#valueMealItemsList .menu-item-select').each(function() {
            const val = $(this).val();
            if (val) selectedItems.add(val);
        });
        return menuItemsData.filter(item => !selectedItems.has(item.id) && !item.isValueMeal);
    }

    // Function to create options HTML
    function createOptionsHtml(availableItems) {
        let html = '<option value="">Select Item</option>';
        availableItems.forEach(item => {
            if (!item.isValueMeal) {  // Extra check to ensure no value meals
                html += `<option value="${item.id}">${item.name} (Stock: ${item.stock})</option>`;
            }
        });
        return html;
    }

    // Function to create a new row
    function createNewRow(rowIndex) {
        const availableItems = getAvailableMenuItems();
        return `
            <tr data-row="${rowIndex}">
                <td>
                    <select class="form-select form-select-sm menu-item-select" name="value_meal_items[${rowIndex}][menu_item_id]" required>
                        ${createOptionsHtml(availableItems)}
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm quantity-input" 
                           name="value_meal_items[${rowIndex}][quantity]" value="1" min="1" required>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-value-meal-item">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    }

    // Initialize modal with a single row
    $('#createValueMealModal').on('show.bs.modal', function() {
        $('#valueMealItemsList').empty();  // Clear any existing rows
        const newRow = createNewRow(0);    // Create just one row
        $('#valueMealItemsList').append(newRow);
    });

    // Add value meal item - ensure only one row is added at a time
    $('#addValueMealItem').off('click').on('click', function(e) {
        e.preventDefault();  // Prevent any default behavior
        const rowCount = $('#valueMealItemsList tr').length;
        const availableItems = getAvailableMenuItems();
        
        if (availableItems.length > 0) {
            const newRow = createNewRow(rowCount);
            $('#valueMealItemsList').append(newRow);
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'No More Items',
                text: 'No more items available to add.'
            });
        }
    });

    // Remove value meal item
    $(document).on('click', '.remove-value-meal-item', function() {
        $(this).closest('tr').remove();
        updateRowIndexes();
        
        // Update all dropdowns with new available items
        const availableItems = getAvailableMenuItems();
        $('.menu-item-select').each(function() {
            const currentValue = $(this).val();
            if (!currentValue) {
                $(this).html(createOptionsHtml(availableItems));
            }
        });
    });

    // Update row indexes
    function updateRowIndexes() {
        $('#valueMealItemsList tr').each(function(index) {
            $(this).attr('data-row', index);
            $(this).find('.menu-item-select').attr('name', `value_meal_items[${index}][menu_item_id]`);
            $(this).find('.quantity-input').attr('name', `value_meal_items[${index}][quantity]`);
        });
    }

    // Handle item selection change
    $(document).on('change', '.menu-item-select', function() {
        // Update dropdowns in other rows
        const availableItems = getAvailableMenuItems();
        $('.menu-item-select').each(function() {
            const currentValue = $(this).val();
            if (!currentValue) {
                $(this).html(createOptionsHtml(availableItems));
            }
        });
    });

    // Reset form when modal is hidden
    $('#createValueMealModal').on('hidden.bs.modal', function() {
        $('#createValueMealForm')[0].reset();
        $('#valueMealItemsList').empty();
    });

    // Handle form submission
    $('#createValueMealForm').on('submit', function(e) {
        e.preventDefault();
        
        // Get form data
        const formData = new FormData(this);
        formData.set('IsValueMeal', '1'); // Ensure IsValueMeal is set to 1
        
        // Validate required fields
        if (!formData.get('ItemName')) {
            Swal.fire('Error', 'Please enter a value meal name', 'error');
            return;
        }
        if (!formData.get('Price')) {
            Swal.fire('Error', 'Please enter a price', 'error');
            return;
        }
        
        const categoryId = $('#ClassificationId').val();
        if (!categoryId) {
            Swal.fire('Error', 'Please select a category', 'error');
            return;
        }

        // Check if at least one item is selected
        const hasItems = $('#valueMealItemsList tr').length > 0 && 
                        $('#valueMealItemsList .menu-item-select').toArray().some(select => select.value);
        if (!hasItems) {
            Swal.fire('Error', 'Please add at least one item to the value meal', 'error');
            return;
        }

        // Get all selected items
        const selectedItems = [];
        $('#valueMealItemsList tr').each(function() {
            const menuItemId = $(this).find('.menu-item-select').val();
            const quantity = $(this).find('.quantity-input').val();
            if (menuItemId && quantity) {
                selectedItems.push({
                    menu_item_id: menuItemId,
                    quantity: quantity
                });
            }
        });

        // Add the selected items to the form data
        selectedItems.forEach((item, index) => {
            formData.append(`value_meal_items[${index}][menu_item_id]`, item.menu_item_id);
            formData.append(`value_meal_items[${index}][quantity]`, item.quantity);
        });

        // Submit form
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error', response.message || 'Failed to create value meal', 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                console.log('Error response:', response); // Add this for debugging
                if (response && response.errors) {
                    const errorMessages = Object.values(response.errors).flat().join('\n');
                    Swal.fire('Validation Error', errorMessages, 'error');
                } else {
                    Swal.fire('Error', response?.message || 'Failed to create value meal', 'error');
                }
            }
        });
    });
});
</script>
@endpush 