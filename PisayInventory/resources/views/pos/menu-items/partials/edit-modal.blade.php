<div class="modal fade" id="editMenuItemModal{{ $item->MenuItemID }}" tabindex="-1" aria-labelledby="editMenuItemModalLabel{{ $item->MenuItemID }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editMenuItemModalLabel{{ $item->MenuItemID }}">Edit Menu Item</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editMenuItemForm{{ $item->MenuItemID }}" action="{{ route('pos.menu-items.update', $item->MenuItemID) }}" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    @csrf
                    @method('PUT')
                    
                    <style>
                        .alert-danger {
                            background-color: #f8d7da;
                            border-color: #f5c6cb;
                            color: #721c24;
                            padding: 1rem;
                            margin-bottom: 1rem;
                            border: 1px solid transparent;
                            border-radius: 0.25rem;
                        }
                        .invalid-feedback {
                            display: block;
                            color: #dc3545;
                            margin-top: 0.25rem;
                        }
                    </style>

                    <div class="alert alert-danger" id="errorAlert{{ $item->MenuItemID }}" role="alert" style="display: none; margin-bottom: 15px;">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <div>
                                <div class="fw-bold">Error</div>
                                <div id="errorMessage{{ $item->MenuItemID }}"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ItemName{{ $item->MenuItemID }}" class="form-label">Item Name *</label>
                                <input type="text" 
                                       class="form-control @error('ItemName') is-invalid @enderror" 
                                       id="ItemName{{ $item->MenuItemID }}" 
                                       name="ItemName" 
                                       value="{{ old('ItemName', $item->ItemName) }}" 
                                       required>
                                @error('ItemName')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="Description{{ $item->MenuItemID }}" class="form-label">Description</label>
                                <textarea class="form-control @error('Description') is-invalid @enderror" 
                                          id="Description{{ $item->MenuItemID }}" 
                                          name="Description" 
                                          rows="3">{{ old('Description', $item->Description) }}</textarea>
                                @error('Description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="ClassificationID{{ $item->MenuItemID }}" class="form-label">Category *</label>
                                <select class="form-select @error('ClassificationID') is-invalid @enderror" 
                                        id="ClassificationID{{ $item->MenuItemID }}" 
                                        name="ClassificationID" 
                                        required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        @if($category->IsDeleted == 0)
                                            <option value="{{ $category->ClassificationId }}" 
                                                    {{ old('ClassificationID', $item->ClassificationID) == $category->ClassificationId ? 'selected' : '' }}>
                                                {{ $category->ClassificationName }}
                                                @if($category->ParentClassificationId)
                                                    ({{ $categories->firstWhere('ClassificationId', $category->ParentClassificationId)->ClassificationName ?? 'N/A' }})
                                                @endif
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('ClassificationID')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="Price{{ $item->MenuItemID }}" class="form-label">Price (₱) *</label>
                                <input type="number" 
                                       class="form-control @error('Price') is-invalid @enderror" 
                                       id="Price{{ $item->MenuItemID }}" 
                                       name="Price" 
                                       value="{{ old('Price', $item->Price) }}" 
                                       step="0.01" 
                                       min="0" 
                                       required>
                                @error('Price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="StocksAvailable{{ $item->MenuItemID }}" class="form-label">Stock *</label>
                                <input type="number" 
                                       class="form-control @error('StocksAvailable') is-invalid @enderror" 
                                       id="StocksAvailable{{ $item->MenuItemID }}" 
                                       name="StocksAvailable" 
                                       value="{{ old('StocksAvailable', $item->StocksAvailable) }}" 
                                       min="0" 
                                       required>
                                @error('StocksAvailable')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="image{{ $item->MenuItemID }}" class="form-label">Item Image</label>
                                @if($item->image_path)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $item->image_path) }}" 
                                             alt="{{ $item->ItemName }}"
                                             class="img-thumbnail"
                                             style="max-height: 100px;">
                                    </div>
                                @endif
                                <input type="file" 
                                       class="form-control @error('image') is-invalid @enderror" 
                                       id="image{{ $item->MenuItemID }}" 
                                       name="image" 
                                       accept="image/*">
                                <div class="form-text">Leave empty to keep current image. Maximum file size: 2MB</div>
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
    $('#editMenuItemForm{{ $item->MenuItemID }}').on('submit', function(e) {
        e.preventDefault();
        
        // Reset previous error states
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        $('#errorAlert{{ $item->MenuItemID }}').hide();
        $('#errorMessage{{ $item->MenuItemID }}').empty();
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...');
        submitBtn.prop('disabled', true);

        let formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Close modal
                    $('#editMenuItemModal{{ $item->MenuItemID }}').modal('hide');
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                    }).then((result) => {
                        location.reload();
                    });
                } else {
                    // Show error message
                    $('#errorMessage{{ $item->MenuItemID }}').html(response.message || 'An error occurred while updating the menu item.');
                    $('#errorAlert{{ $item->MenuItemID }}').fadeIn();
                }
            },
            error: function(xhr, status, error) {
                // Log the full error details
                console.group('Update Error Details');
                console.log('Status:', status);
                console.log('Error:', error);
                console.log('Response:', xhr.responseJSON);
                console.log('Status Code:', xhr.status);
                console.groupEnd();

                let errorMessages = [];
                let mainError = 'An error occurred while updating the menu item.';
                
                // Handle validation errors
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    Object.keys(xhr.responseJSON.errors).forEach(field => {
                        const input = $(`#${field}{{ $item->MenuItemID }}`);
                        input.addClass('is-invalid');
                        
                        const errorMessage = xhr.responseJSON.errors[field][0];
                        errorMessages.push(errorMessage);
                        
                        // Add error message below the field
                        const feedback = $(`<div class="invalid-feedback">${errorMessage}</div>`);
                        input.parent().find('.invalid-feedback').remove();
                        input.after(feedback);
                    });
                }
                
                // Set main error message from response if available
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    mainError = xhr.responseJSON.message;
                }
                
                // Combine validation errors with main error if they exist
                if (errorMessages.length > 0) {
                    mainError += '<ul class="mb-0 mt-2">';
                    errorMessages.forEach(msg => {
                        mainError += `<li>${msg}</li>`;
                    });
                    mainError += '</ul>';
                }
                
                // Display error message
                $('#errorMessage{{ $item->MenuItemID }}').html(mainError);
                $('#errorAlert{{ $item->MenuItemID }}').fadeIn();

                // Scroll to top of modal to show error
                $('.modal-body').scrollTop(0);
            },
            complete: function() {
                // Reset button state
                submitBtn.html(originalBtnText);
                submitBtn.prop('disabled', false);
            }
        });
    });
});
</script>
@endpush 