@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Menu Items</h3>
                    <a href="{{ route('pos.menu-items.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus"></i> Add Menu Item
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                    
                    <div id="ajax-alert" class="alert alert-success alert-dismissible fade d-none" role="alert">
                        <span id="ajax-alert-message"></span>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchInput" placeholder="Search menu items...">
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="btn-group float-right">
                                <button type="button" class="btn btn-outline-secondary filter-btn" data-filter="all">All</button>
                                <button type="button" class="btn btn-outline-secondary filter-btn" data-filter="available">Available</button>
                                <button type="button" class="btn btn-outline-secondary filter-btn" data-filter="unavailable">Unavailable</button>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="menuItemsTable">
                            <thead>
                                <tr>
                                    <th width="80">Image</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th width="100">Status</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($menuItems as $item)
                                <tr data-available="{{ $item->available ? 'yes' : 'no' }}">
                                    <td>
                                        @if($item->image)
                                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="img-thumbnail" width="60">
                                        @else
                                            <img src="{{ asset('images/no-image.png') }}" alt="No Image" class="img-thumbnail" width="60">
                                        @endif
                                    </td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->category }}</td>
                                    <td>₱{{ number_format($item->price, 2) }}</td>
                                    <td>
                                        <span class="badge {{ $item->available ? 'badge-success' : 'badge-danger' }}">
                                            {{ $item->available ? 'Available' : 'Unavailable' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('pos.menu-items.edit', $item->id) }}" class="btn btn-sm btn-info">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger delete-item" data-toggle="modal" data-target="#deleteModal" data-id="{{ $item->id }}" data-name="{{ $item->name }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No menu items found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        {{ $menuItems->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <span id="itemName" class="font-weight-bold"></span>?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Setup AJAX CSRF token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        // Function to show AJAX alert
        function showAlert(message, type) {
            $('#ajax-alert').removeClass('d-none alert-success alert-danger')
                .addClass('show alert-' + type)
                .find('#ajax-alert-message').text(message);
                
            // Auto hide after 5 seconds
            setTimeout(function() {
                $('#ajax-alert').removeClass('show').addClass('d-none');
            }, 5000);
        }
        
        // Setup delete confirmation
        $('.delete-item').on('click', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            
            $('#itemName').text(name);
            $('#confirmDelete').data('id', id);
        });
        
        // Handle delete confirmation
        $('#confirmDelete').on('click', function() {
            var id = $(this).data('id');
            
            // Show loading state
            $(this).prop('disabled', true).html('<i class="bi bi-arrow-repeat spinner-border-sm"></i> Deleting...');
            
            // Send delete request
            $.ajax({
                url: "{{ route('pos.menu-items.delete', '') }}/" + id,
                type: 'DELETE',
                success: function(response) {
                    // Close modal
                    $('#deleteModal').modal('hide');
                    
                    // Show success message
                    showAlert('Menu item deleted successfully', 'success');
                    
                    // Remove the row from the table
                    $('button.delete-item[data-id="' + id + '"]').closest('tr').fadeOut(500, function() {
                        $(this).remove();
                        
                        // Check if table is empty
                        if ($('#menuItemsTable tbody tr').length === 0) {
                            $('#menuItemsTable tbody').append('<tr><td colspan="6" class="text-center">No menu items found.</td></tr>');
                        }
                    });
                },
                error: function(xhr) {
                    // Reset button
                    $('#confirmDelete').prop('disabled', false).text('Delete');
                    
                    // Show error message
                    var errorMessage = 'Failed to delete menu item';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    showAlert(errorMessage, 'danger');
                }
            });
        });
        
        // Search functionality
        $('#searchInput').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $("#menuItemsTable tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });
        
        // Filter functionality
        $('.filter-btn').on('click', function() {
            $('.filter-btn').removeClass('active');
            $(this).addClass('active');
            
            var filter = $(this).data('filter');
            
            if (filter === 'all') {
                $("#menuItemsTable tbody tr").show();
            } else if (filter === 'available') {
                $("#menuItemsTable tbody tr").hide();
                $("#menuItemsTable tbody tr[data-available='yes']").show();
            } else if (filter === 'unavailable') {
                $("#menuItemsTable tbody tr").hide();
                $("#menuItemsTable tbody tr[data-available='no']").show();
            }
        });
        
        // Set All as active by default
        $('.filter-btn[data-filter="all"]').addClass('active');
    });
</script>
@endsection 