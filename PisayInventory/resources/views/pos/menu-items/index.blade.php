@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h1 class="fs-2 fw-bold mb-0">Menu Items</h1>
                        <p class="mb-0 opacity-75">Manage your menu items here</p>
                    </div>
                    <a href="#" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#createMenuItemModal">
                        <i class="bi bi-plus-circle me-1"></i> Add New Item
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="categoryFilter" class="form-label">Category</label>
                    <select class="form-select" id="categoryFilter">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->ClassificationId }}">
                                {{ $category->ClassificationName }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="searchInput" class="form-label">Search</label>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search items...">
                </div>
            </div>
        </div>
    </div>

    <!-- Menu Items Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="menuItemsTable">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($menuItems as $item)
                        <tr>
                            <td>
                                @if($item->image_path && Storage::disk('public')->exists($item->image_path))
                                    <img src="{{ asset('storage/' . $item->image_path) }}" 
                                         alt="{{ $item->ItemName }}"
                                         style="width: 50px; height: 50px; object-fit: cover;"
                                         class="rounded">
                                @else
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                         style="width: 50px; height: 50px;">
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $item->ItemName }}</strong>
                                <br>
                                <small class="text-muted">{{ Str::limit($item->Description, 50) }}</small>
                            </td>
                            <td>{{ $item->classification ? $item->classification->ClassificationName : 'N/A' }}</td>
                            <td>₱{{ number_format($item->Price, 2) }}</td>
                            <td>{{ $item->StocksAvailable }}</td>
                            <td>
                                <span class="badge bg-{{ $item->StocksAvailable > 0 ? 'success' : 'danger' }}">
                                    {{ $item->StocksAvailable > 0 ? 'In Stock' : 'Out of Stock' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('pos.menu-items.edit', $item->MenuItemID) }}" 
                                       class="btn btn-sm btn-outline-warning"
                                       title="Edit Item">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger delete-item"
                                            data-id="{{ $item->MenuItemID }}"
                                            data-name="{{ $item->ItemName }}"
                                            title="Delete Item">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create Menu Item Modal -->
<div class="modal fade" id="createMenuItemModal" tabindex="-1" aria-labelledby="createMenuItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="createMenuItemModalLabel">Create Menu Item</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createMenuItemForm" action="{{ route('pos.menu-items.store') }}" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    @csrf
                    
                    <div class="alert alert-danger" id="errorAlert" style="display: none;"></div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ItemName" class="form-label">Item Name *</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="ItemName" 
                                       name="ItemName" 
                                       required>
                                <div class="invalid-feedback" id="ItemNameError"></div>
                            </div>

                            <div class="mb-3">
                                <label for="Description" class="form-label">Description</label>
                                <textarea class="form-control" 
                                          id="Description" 
                                          name="Description" 
                                          rows="3"></textarea>
                                <div class="invalid-feedback" id="DescriptionError"></div>
                            </div>

                            <div class="mb-3">
                                <label for="ClassificationId" class="form-label">Category *</label>
                                <select class="form-select" 
                                        id="ClassificationId" 
                                        name="ClassificationId" 
                                        required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->ClassificationId }}">
                                            {{ $category->ClassificationName }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="ClassificationIdError"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="Price" class="form-label">Price (₱) *</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="Price" 
                                       name="Price" 
                                       step="0.01" 
                                       min="0" 
                                       required>
                                <div class="invalid-feedback" id="PriceError"></div>
                            </div>

                            <div class="mb-3">
                                <label for="StocksAvailable" class="form-label">Initial Stock *</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="StocksAvailable" 
                                       name="StocksAvailable" 
                                       min="0" 
                                       required>
                                <div class="invalid-feedback" id="StocksAvailableError"></div>
                            </div>

                            <div class="mb-3">
                                <label for="image" class="form-label">Item Image</label>
                                <input type="file" 
                                       class="form-control" 
                                       id="image" 
                                       name="image" 
                                       accept="image/*">
                                <div class="form-text">Maximum file size: 2MB. Supported formats: JPEG, PNG, JPG, GIF</div>
                                <div class="invalid-feedback" id="imageError"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Create Menu Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Menu Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete "<span id="deleteItemName"></span>"?</p>
                <p class="text-danger"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    const table = $('#menuItemsTable').DataTable({
        order: [[1, 'asc']], // Sort by name by default
        pageLength: 10,
        responsive: true
    });

    // Category filter
    $('#categoryFilter').on('change', function() {
        const category = $(this).val();
        table.column(2).search(category).draw();
    });

    // Search functionality
    $('#searchInput').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Delete functionality
    $('.delete-item').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        
        $('#deleteItemName').text(name);
        $('#deleteItemModal').modal('show');
        
        $('#confirmDelete').off('click').on('click', function() {
            $.ajax({
                url: `/inventory/pos/menu-items/${id}`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Menu item deleted successfully'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Error deleting menu item'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error deleting menu item'
                    });
                },
                complete: function() {
                    $('#deleteItemModal').modal('hide');
                }
            });
        });
    });

    // Handle form submission
    $('#createMenuItemForm').on('submit', function(e) {
        e.preventDefault();
        
        // Reset previous error states
        $('.is-invalid').removeClass('is-invalid');
        $('#errorAlert').hide();
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Creating...');
        submitBtn.prop('disabled', true);

        let formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Close modal
                    $('#createMenuItemModal').modal('hide');
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                    }).then((result) => {
                        location.reload();
                    });
                } else {
                    $('#errorAlert').text(response.message).show();
                }
            },
            error: function(xhr) {
                // Reset button state
                submitBtn.html(originalBtnText);
                submitBtn.prop('disabled', false);

                if (xhr.status === 422) {
                    // Validation errors
                    const errors = xhr.responseJSON.errors;
                    
                    Object.keys(errors).forEach(function(field) {
                        const element = $(`#${field}`);
                        element.addClass('is-invalid');
                        $(`#${field}Error`).text(errors[field][0]);
                    });

                    $('#errorAlert').text('Please correct the errors below.').show();
                } else {
                    // Show general error
                    $('#errorAlert')
                        .text(xhr.responseJSON?.message || 'An error occurred while creating the menu item. Please try again.')
                        .show();
                }
            },
            complete: function() {
                // Reset button state
                submitBtn.html(originalBtnText);
                submitBtn.prop('disabled', false);
            }
        });
    });

    // Reset form when modal is closed
    $('#createMenuItemModal').on('hidden.bs.modal', function() {
        $('#createMenuItemForm')[0].reset();
        $('.is-invalid').removeClass('is-invalid');
        $('#errorAlert').hide();
    });
});
</script>
@endpush 