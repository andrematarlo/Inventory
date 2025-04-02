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
                        <p class="mb-0 opacity-75">Manage menu items for the POS system</p>
                    </div>
                    <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#createMenuItemModal">
                        <i class="bi bi-plus-circle me-1"></i> Add Menu Item
                    </button>
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

    <!-- Content Section -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
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
                                @if($item->image_path)
                                    <img src="{{ asset('storage/' . $item->image_path) }}" 
                                         alt="{{ $item->ItemName }}"
                                         class="img-thumbnail"
                                         style="max-height: 50px;">
                                @else
                                    <span class="text-muted">No image</span>
                                @endif
                            </td>
                            <td>{{ $item->ItemName }}</td>
                            <td>{{ $item->classification->ClassificationName ?? 'N/A' }}</td>
                            <td>₱{{ number_format($item->Price, 2) }}</td>
                            <td>{{ $item->StocksAvailable }}</td>
                            <td>
                                <span class="badge bg-{{ $item->StocksAvailable > 0 ? 'success' : 'danger' }}">
                                    {{ $item->StocksAvailable > 0 ? 'In Stock' : 'Out of Stock' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <button type="button" 
                                            class="btn btn-sm btn-info" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#viewMenuItemModal{{ $item->MenuItemID }}">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    
                                    <button type="button" 
                                            class="btn btn-sm btn-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editMenuItemModal{{ $item->MenuItemID }}">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    
                                    <button type="button" 
                                            class="btn btn-sm btn-danger delete-menu-item" 
                                            data-id="{{ $item->MenuItemID }}"
                                            data-name="{{ $item->ItemName }}">
                                        <i class="fas fa-trash"></i> Delete
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
@include('pos.menu-items.partials.create-modal')

<!-- View Menu Item Modals -->
@foreach($menuItems as $item)
    @include('pos.menu-items.partials.view-modal', ['item' => $item])
@endforeach

<!-- Edit Menu Item Modals -->
@foreach($menuItems as $item)
    @include('pos.menu-items.partials.edit-modal', ['item' => $item])
@endforeach

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

    // Delete menu item functionality
    $('.delete-menu-item').on('click', function() {
        const itemId = $(this).data('id');
        const itemName = $(this).data('name');
        
        Swal.fire({
            title: 'Delete Menu Item?',
            html: `Are you sure you want to delete <strong>${itemName}</strong>?<br>This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Deleting...',
                    html: 'Please wait...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Send delete request
                $.ajax({
                    url: `{{ url('inventory/pos/menu-items') }}/${itemId}`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message || 'Menu item has been deleted.',
                            }).then(() => {
                                // Reload the page
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: response.message || 'Failed to delete menu item.'
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('Delete error:', xhr);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to delete menu item. Please try again.'
                        });
                    }
                });
            }
        });
    });

    // Create menu item form submission
    let isSubmitting = false;
    $('#createMenuItemForm').on('submit', function(e) {
        e.preventDefault();
        
        // Prevent double submission
        if (isSubmitting) {
            return;
        }
        
        // Set submitting flag
        isSubmitting = true;
        
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
                console.error('Error response:', xhr.responseText);
                let errorMessage = 'An error occurred while creating the menu item.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                $('#errorAlert').text(errorMessage).show();
                
                // Show validation errors
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    Object.keys(xhr.responseJSON.errors).forEach(field => {
                        const input = $(`#${field}`);
                        input.addClass('is-invalid');
                        input.next('.invalid-feedback').text(xhr.responseJSON.errors[field][0]);
                    });
                }
            },
            complete: function() {
                // Reset button state and submission flag
                submitBtn.html(originalBtnText);
                submitBtn.prop('disabled', false);
                isSubmitting = false;
            }
        });
    });
});
</script>
@endpush 