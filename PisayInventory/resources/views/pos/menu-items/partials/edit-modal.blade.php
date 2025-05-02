<div class="modal fade" id="editMenuItemModal{{ $item->MenuItemID }}" tabindex="-1" aria-labelledby="editMenuItemModalLabel{{ $item->MenuItemID }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editMenuItemModalLabel{{ $item->MenuItemID }}">Edit Menu Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editMenuItemForm{{ $item->MenuItemID }}" action="{{ route('pos.menu-items.update', $item->MenuItemID) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="MenuItemID" value="{{ $item->MenuItemID }}">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Item Name</label>
                            <input type="text" class="form-control" name="ItemName" value="{{ $item->ItemName }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Price</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" name="Price" value="{{ $item->Price }}" required min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6 non-value-meal-field">
                            <label class="form-label">Stocks Available</label>
                            <input type="number" class="form-control" name="StocksAvailable" value="{{ $item->StocksAvailable }}" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="ClassificationId" required>
                                @foreach($categories as $category)
                                    <option value="{{ $category->ClassificationId }}" {{ $item->ClassificationId == $category->ClassificationId ? 'selected' : '' }}>
                                        {{ $category->ClassificationName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Unit of Measure</label>
                            <select class="form-select" name="UnitOfMeasureID" required>
                                @foreach($unitOfMeasures as $uom)
                                    <option value="{{ $uom->UnitOfMeasureId }}" {{ $item->UnitOfMeasureID == $uom->UnitOfMeasureId ? 'selected' : '' }}>
                                        {{ $uom->Name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="Description" rows="3">{{ $item->Description }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                            @if($item->image_path)
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $item->image_path) }}" alt="Current Image" class="img-thumbnail" style="max-height: 100px;">
                                </div>
                            @endif
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="IsValueMeal" {{ $item->IsValueMeal ? 'checked' : '' }}>
                                <label class="form-check-label">
                                    This is a Value Meal
                                </label>
                            </div>
                        </div>
                        <div class="col-12 value-meal-items {{ $item->IsValueMeal ? '' : 'd-none' }}">
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
                                    <tbody>
                                        @if($item->IsValueMeal)
                                            @foreach($item->valueMealItems as $valueMealItem)
                                                <tr>
                                                    <td>
                                                        <select class="form-select" name="value_meal_items[{{ $loop->index }}][menu_item_id]" required>
                                                            @foreach($menuItems->where('IsValueMeal', false) as $menuItem)
                                                                <option value="{{ $menuItem->MenuItemID }}" {{ $valueMealItem->menu_item_id == $menuItem->MenuItemID ? 'selected' : '' }}>
                                                                    {{ $menuItem->ItemName }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control" name="value_meal_items[{{ $loop->index }}][quantity]" value="{{ $valueMealItem->quantity }}" min="1" required>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-danger btn-sm remove-item">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                                <button type="button" class="btn btn-sm btn-primary add-value-meal-item">Add Item</button>
                            </div>
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
    // Handle value meal toggle
    $('input[name="IsValueMeal"]').change(function() {
        const isValueMeal = $(this).is(':checked');
        $(this).closest('form').find('.value-meal-items').toggleClass('d-none', !isValueMeal);
        $(this).closest('form').find('.non-value-meal-field').toggleClass('d-none', isValueMeal);
    });

    // Handle add value meal item
    $('.add-value-meal-item').click(function() {
        const form = $(this).closest('form');
        const tbody = form.find('tbody');
        const index = tbody.find('tr').length;
        
        const row = `
            <tr>
                <td>
                    <select class="form-select" name="value_meal_items[${index}][menu_item_id]" required>
                        <option value="">Select Item</option>
                        @foreach($menuItems->where('IsValueMeal', false) as $menuItem)
                            <option value="{{ $menuItem->MenuItemID }}">{{ $menuItem->ItemName }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control" name="value_meal_items[${index}][quantity]" value="1" min="1" required>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-item">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });

    // Handle remove value meal item
    $(document).on('click', '.remove-item', function() {
        $(this).closest('tr').remove();
    });

    // Handle form submission
    $('form[id^="editMenuItemForm"]').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const formData = new FormData(this);
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    form.closest('.modal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Menu item has been updated successfully.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to update menu item.'
                    });
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response?.message || 'An error occurred while updating the menu item.'
                });
            }
        });
    });
});
</script>
@endpush 