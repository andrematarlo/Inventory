<div class="modal fade" id="editMenuItemModal{{ $item->MenuItemID }}" tabindex="-1" aria-labelledby="editMenuItemModalLabel{{ $item->MenuItemID }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMenuItemModalLabel{{ $item->MenuItemID }}">Edit Menu Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editMenuItemForm{{ $item->MenuItemID }}" action="{{ route('pos.menu-items.update', $item->MenuItemID) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="editItemName{{ $item->MenuItemID }}" class="form-label">Item Name</label>
                            <input type="text" class="form-control" id="editItemName{{ $item->MenuItemID }}" name="ItemName" value="{{ $item->ItemName }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="editPrice{{ $item->MenuItemID }}" class="form-label">Price</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" id="editPrice{{ $item->MenuItemID }}" name="Price" step="0.01" value="{{ $item->Price }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="editCategory{{ $item->MenuItemID }}" class="form-label">Category</label>
                            <select class="form-select" id="editCategory{{ $item->MenuItemID }}" name="ClassificationID" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->ClassificationId }}" {{ $item->ClassificationID == $category->ClassificationId ? 'selected' : '' }}>
                                        {{ $category->ClassificationName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="editUnitOfMeasure{{ $item->MenuItemID }}" class="form-label">Unit of Measure</label>
                            <select class="form-select" id="editUnitOfMeasure{{ $item->MenuItemID }}" name="UnitOfMeasureID" required>
                                <option value="">Select Unit</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->UnitOfMeasureId }}" {{ $item->UnitOfMeasureID == $unit->UnitOfMeasureId ? 'selected' : '' }}>
                                        {{ $unit->UnitName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="editDescription{{ $item->MenuItemID }}" class="form-label">Description</label>
                            <textarea class="form-control" id="editDescription{{ $item->MenuItemID }}" name="Description" rows="3">{{ $item->Description }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="editImage{{ $item->MenuItemID }}" class="form-label">Image</label>
                            <input type="file" class="form-control" id="editImage{{ $item->MenuItemID }}" name="image" accept="image/*">
                            @if($item->image_path)
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->ItemName }}" class="img-thumbnail" style="max-height: 100px;">
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label for="editStocksAvailable{{ $item->MenuItemID }}" class="form-label">Current Stock</label>
                            <input type="number" class="form-control" id="editStocksAvailable{{ $item->MenuItemID }}" name="StocksAvailable" value="{{ $item->StocksAvailable }}" min="0">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="editIsValueMeal{{ $item->MenuItemID }}" name="IsValueMeal" value="1" {{ $item->IsValueMeal ? 'checked' : '' }}>
                                <label class="form-check-label" for="editIsValueMeal{{ $item->MenuItemID }}">
                                    This is a Value Meal
                                </label>
                            </div>
                        </div>
                        <div class="col-12 value-meal-items {{ $item->IsValueMeal ? 'd-block' : 'd-none' }}">
                            <label class="form-label">Value Meal Items</label>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Quantity</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="editValueMealItemsList{{ $item->MenuItemID }}">
                                        @foreach($item->valueMealItems as $valueMealItem)
                                            <tr>
                                                <td>
                                                    <select class="form-select form-select-sm" name="value_meal_items[][menu_item_id]" required>
                                                        <option value="">Select Item</option>
                                                        @foreach($menuItems as $menuItem)
                                                            <option value="{{ $menuItem->MenuItemID }}" {{ $valueMealItem->menu_item_id == $menuItem->MenuItemID ? 'selected' : '' }}>
                                                                {{ $menuItem->ItemName }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm" name="value_meal_items[][quantity]" value="{{ $valueMealItem->quantity }}" min="1" required>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-danger remove-value-meal-item">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addEditValueMealItem{{ $item->MenuItemID }}">
                                <i class="fas fa-plus"></i> Add Item
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Menu Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle value meal items section
    $('#editIsValueMeal{{ $item->MenuItemID }}').on('change', function() {
        $('.value-meal-items').toggleClass('d-block d-none');
    });

    // Add value meal item
    $('#addEditValueMealItem{{ $item->MenuItemID }}').on('click', function() {
        const row = `
            <tr>
                <td>
                    <select class="form-select form-select-sm" name="value_meal_items[][menu_item_id]" required>
                        <option value="">Select Item</option>
                        @foreach($menuItems as $menuItem)
                            <option value="{{ $menuItem->MenuItemID }}">{{ $menuItem->ItemName }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" name="value_meal_items[][quantity]" value="1" min="1" required>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-value-meal-item">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#editValueMealItemsList{{ $item->MenuItemID }}').append(row);
    });

    // Remove value meal item
    $(document).on('click', '.remove-value-meal-item', function() {
        $(this).closest('tr').remove();
    });
});
</script>
@endpush 