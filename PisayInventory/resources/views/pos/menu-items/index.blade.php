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
                    <div class="d-flex gap-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="showDeletedToggle">
                            <label class="form-check-label text-white" for="showDeletedToggle">Show Deleted Items</label>
                        </div>
                        <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#createMenuItemModal">
                            <i class="bi bi-plus-circle me-1"></i> Add Menu Item
                        </button>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createValueMealModal">
                            <i class="fas fa-utensils"></i> Create Value Meal
                        </button>
                    </div>
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
                <div class="col-md-4">
                    <label for="typeFilter" class="form-label">Type</label>
                    <select class="form-select" id="typeFilter">
                        <option value="">All Types</option>
                        <option value="0">Regular Items</option>
                        <option value="1">Value Meals</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="menuItemsTable">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Available</th>
                            <th>Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($menuItems as $item)
                        <tr class="{{ $item->IsDeleted ? 'table-danger' : '' }}">
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
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input toggle-availability" 
                                           type="checkbox" 
                                           data-id="{{ $item->MenuItemID }}"
                                           {{ $item->IsAvailable ? 'checked' : '' }}
                                           {{ $item->IsDeleted ? 'disabled' : '' }}>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $item->IsValueMeal ? 'info' : 'secondary' }}">
                                    {{ $item->IsValueMeal ? 'Value Meal' : 'Regular Item' }}
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
                                    
                                    @if(!$item->IsDeleted)
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
                                    @else
                                        <button type="button" 
                                                class="btn btn-sm btn-success restore-menu-item" 
                                                data-id="{{ $item->MenuItemID }}"
                                                data-name="{{ $item->ItemName }}">
                                            <i class="fas fa-undo"></i> Restore
                                        </button>
                                    @endif
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

<!-- Create Value Meal Modal -->
@include('pos.menu-items.partials.create-value-meal-modal')

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    const table = $('#menuItemsTable').DataTable({
        order: [[1, 'asc']], // Sort by name by default
        pageLength: 10,
        responsive: true,
        columnDefs: [
            {
                targets: 8, // Actions column
                orderable: false,
                searchable: true
            }
        ],
        createdRow: function(row, data, dataIndex) {
            // Add a data attribute to identify deleted items
            if ($(row).hasClass('table-danger')) {
                $(row).attr('data-deleted', 'true');
            } else {
                $(row).attr('data-deleted', 'false');
            }
        }
    });

    // Handle menu item form submission
    $('#createMenuItemForm').on('submit', function(e) {
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
                // Close the modal
                $('#createMenuItemModal').modal('hide');
                
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Menu item has been added successfully.',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    // Reload the page to show the new item
                    window.location.reload();
                });
            },
            error: function(xhr) {
                // Show error message
                const errors = xhr.responseJSON?.errors || {};
                let errorMessage = 'An error occurred while adding the menu item.';
                
                if (Object.keys(errors).length > 0) {
                    errorMessage = Object.values(errors).flat().join('\n');
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage
                });
            }
        });
    });

    // Show/Hide deleted items
    $('#showDeletedToggle').on('change', function() {
        const showDeleted = $(this).prop('checked');
        if (showDeleted) {
            // Show all items
            table.rows().every(function() {
                $(this.node()).show();
            });
        } else {
            // Hide deleted items
            table.rows().every(function() {
                if ($(this.node()).attr('data-deleted') === 'true') {
                    $(this.node()).hide();
                }
            });
        }
        table.draw();
    });

    // Category filter
    $('#categoryFilter').on('change', function() {
        const category = $(this).val();
        table.column(2).search(category).draw();
    });

    // Type filter
    $('#typeFilter').on('change', function() {
        const type = $(this).val();
        table.column(7).search(type).draw();
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

    // Restore menu item functionality
    $('.restore-menu-item').on('click', function() {
        const itemId = $(this).data('id');
        const itemName = $(this).data('name');
        
        Swal.fire({
            title: 'Restore Menu Item?',
            html: `Are you sure you want to restore <strong>${itemName}</strong>?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, restore it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Restoring...',
                    html: 'Please wait...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Send restore request
                $.ajax({
                    url: `{{ url('inventory/pos/menu-items') }}/${itemId}/restore`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Restored!',
                                text: response.message || 'Menu item has been restored.',
                            }).then(() => {
                                // Reload the page
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: response.message || 'Failed to restore menu item.'
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('Restore error:', xhr);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to restore menu item. Please try again.'
                        });
                    }
                });
            }
        });
    });

    // Toggle availability functionality
    $('.toggle-availability').on('change', function() {
        const itemId = $(this).data('id');
        const isAvailable = $(this).prop('checked');
        
        // Show loading state
        $(this).prop('disabled', true);
        
        $.ajax({
            url: `{{ url('inventory/pos/menu-items') }}/${itemId}/toggle-availability`,
            type: 'POST',
            data: {
                is_available: isAvailable,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message || 'Availability updated successfully.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    // Revert the toggle if failed
                    $(this).prop('checked', !isAvailable);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to update availability.'
                    });
                }
            },
            error: function(xhr) {
                // Revert the toggle if failed
                $(this).prop('checked', !isAvailable);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to update availability. Please try again.'
                });
            },
            complete: function() {
                $(this).prop('disabled', false);
            }
        });
    });
});
</script>
@endpush 