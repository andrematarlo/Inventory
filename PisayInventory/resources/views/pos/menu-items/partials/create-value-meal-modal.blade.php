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
                <input type="hidden" name="ClassificationID" value="{{ $categories->first()->ClassificationId }}">
                <input type="hidden" name="UnitOfMeasureID" value="{{ $units->first()->UnitOfMeasureId }}">
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
    // Store menu items data
    const menuItemsData = [
        @foreach($menuItems as $item)
            @if(!$item->IsValueMeal)
            {
                id: "{{ $item->MenuItemID }}",
                name: "{{ $item->ItemName }}",
                stock: {{ $item->StocksAvailable }}
            },
            @endif
        @endforeach
    ];

    // Function to get available menu items
    function getAvailableMenuItems() {
        const selectedItems = new Set();
        $('#valueMealItemsList .menu-item-select').each(function() {
            const val = $(this).val();
            if (val) selectedItems.add(val);
        });

        return menuItemsData.filter(item => !selectedItems.has(item.id));
    }

    // Function to create options HTML
    function createOptionsHtml(availableItems) {
        let html = '<option value="">Select Item</option>';
        availableItems.forEach(item => {
            html += `<option value="${item.id}">
                ${item.name} (Stock: ${item.stock})
            </option>`;
        });
        return html;
    }

    // Function to create a new row
    function createNewRow(index) {
        const availableItems = getAvailableMenuItems();
        return `
            <tr data-row="${index}">
                <td>
                    <select class="form-select form-select-sm menu-item-select" name="value_meal_items[${index}][menu_item_id]" required>
                        ${createOptionsHtml(availableItems)}
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm quantity-input" 
                           name="value_meal_items[${index}][quantity]" value="1" min="1" required>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-value-meal-item">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    }

    // Function to reindex rows
    function reindexRows() {
        $('#valueMealItemsList tr').each(function(index) {
            $(this).attr('data-row', index);
            $(this).find('select, input').each(function() {
                const name = $(this).attr('name');
                if (name) {
                    $(this).attr('name', name.replace(/\[\d+\]/, `[${index}]`));
                }
            });
        });
    }
    
    // Update available items when selection changes
    $(document).on('change', '.menu-item-select', function() {
        // Update available items in other dropdowns
        const availableItems = getAvailableMenuItems();
        const optionsHtml = createOptionsHtml(availableItems);
        $('.menu-item-select').each(function() {
            const currentValue = $(this).val();
            if (!currentValue) {
                $(this).html(optionsHtml);
            }
        });
    });

    // Add value meal item
    $('#addValueMealItem').off('click').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const rowCount = $('#valueMealItemsList tr').length;
        const newRow = createNewRow(rowCount);
        $('#valueMealItemsList').append(newRow);
        return false;
    });

    // Remove value meal item
    $(document).off('click', '.remove-value-meal-item').on('click', '.remove-value-meal-item', function(e) {
        e.preventDefault();
        e.stopPropagation();
        if ($('#valueMealItemsList tr').length > 1) {
            $(this).closest('tr').remove();
            reindexRows();
            
            // Update all dropdowns with available items
            const availableItems = getAvailableMenuItems();
            const optionsHtml = createOptionsHtml(availableItems);
            $('.menu-item-select').each(function() {
                const currentValue = $(this).val();
                if (!currentValue) {
                    $(this).html(optionsHtml);
                }
            });
        } else {
            alert('Value meal must have at least one item');
        }
        return false;
    });

    // Add initial row
    $('#addValueMealItem').trigger('click');

    // Reset form when modal is hidden
    $('#createValueMealModal').on('hidden.bs.modal', function() {
        $('#createValueMealForm')[0].reset();
        $('#valueMealItemsList').empty();
        $('#addValueMealItem').trigger('click');
    });
});
</script>
@endpush 