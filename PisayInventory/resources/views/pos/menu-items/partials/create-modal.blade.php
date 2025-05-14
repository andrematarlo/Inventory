<!-- Regular Menu Item Modal -->
<div class="modal fade" id="createMenuItemModal" tabindex="-1" aria-labelledby="createMenuItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createMenuItemModalLabel">Add New Menu Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createMenuItemForm" action="{{ route('pos.menu-items.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="IsValueMeal" value="0">
                <input type="hidden" name="IsAvailable" value="1">
                <input type="hidden" name="IsDeleted" value="0">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="itemName" class="form-label">Item Name</label>
                            <input type="text" class="form-control" id="itemName" name="ItemName" required>
                        </div>
                        <div class="col-md-6">
                            <label for="price" class="form-label">Price</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" id="price" name="Price" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="ClassificationId" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->ClassificationId }}">{{ $category->ClassificationName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="unit" class="form-label">Unit of Measure</label>
                            <select class="form-select" id="unit" name="UnitOfMeasureID" required>
                                <option value="">Select Unit</option>
                                @foreach($unitOfMeasures as $uom)
                                    <option value="{{ $uom->UnitOfMeasureId }}">{{ $uom->UnitName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="stocks" class="form-label">Initial Stock</label>
                            <input type="number" class="form-control" id="stocks" name="StocksAvailable" min="0" required>
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="Description" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <label for="image" class="form-label">Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Menu Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Form submission handling
    $('#createMenuItemForm').on('submit', function(e) {
        e.preventDefault();
        
        // Create FormData object
        const formData = new FormData(this);
        
        // Send AJAX request
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        // Close modal and refresh page
                        $('#createMenuItemModal').modal('hide');
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while creating the menu item.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: errorMessage
                });
            }
        });
    });

    // Toggle value meal items section
    $('#isValueMeal').on('change', function() {
        $('.value-meal-items').toggleClass('d-block d-none');
    });

    // Add value meal item
    $('#addValueMealItem').on('click', function() {
        const row = `
            <tr>
                <td>
                    <select class="form-select form-select-sm" name="value_meal_items[0][menu_item_id]" required>
                        <option value="">Select Item</option>
                        @foreach($menuItems as $item)
                            <option value="{{ $item->MenuItemID }}">{{ $item->ItemName }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm" name="value_meal_items[0][quantity]" value="1" min="1" required>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-value-meal-item">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        const rowCount = $('#valueMealItemsList tr').length;
        const newRow = row.replace(/\[0\]/g, `[${rowCount}]`);
        $('#valueMealItemsList').append(newRow);
    });

    // Remove value meal item
    $(document).on('click', '.remove-value-meal-item', function() {
        $(this).closest('tr').remove();
    });
});
</script>
@endpush 