@extends('layouts.app')

@section('title', 'Inventory')

@section('content')

<div class="container">
    <!-- Header Section -->
     
    <div class="mb-4">
        <h2 class="mb-3">Inventory Management</h2>
        <div class="d-flex justify-content-between align-items-center">
            <div class="btn-group" role="group">
                <a href="{{ route('inventory.index') }}" 
                   class="btn btn-outline-primary {{ !request('show_deleted') ? 'active' : '' }}">
                    Active Records
                </a>
                <a href="{{ route('inventory.index', ['show_deleted' => 1]) }}" 
                   class="btn btn-danger {{ request('show_deleted') ? 'active' : '' }}">
                    <i class="bi bi-trash"></i> Show Deleted Records
                </a>
            </div>
            {{-- Only show Add Inventory button if not Inventory Staff --}}
            @if($userPermissions && $userPermissions->CanAdd)
            <button type="button" 
                    class="btn btn-primary d-flex align-items-center gap-1" 
                    data-bs-toggle="modal" 
                    data-bs-target="#addInventoryModal">
                <i class="bi bi-plus-lg"></i>
                Add Inventory
            </button>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>Showing {{ $inventories->firstItem() ?? 0 }} to {{ $inventories->lastItem() ?? 0 }} of {{ $inventories->total() }} results</div>
                <div class="pagination-sm">
                    @if($inventories->currentPage() > 1)
                        <a href="{{ $inventories->previousPageUrl() }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-chevron-left"></i> Previous
                        </a>
                    @endif
                    
                    @for($i = 1; $i <= $inventories->lastPage(); $i++)
                        <a href="{{ $inventories->url($i) }}" 
                           class="btn btn-sm {{ $i == $inventories->currentPage() ? 'btn-primary' : 'btn-outline-secondary' }}">
                            {{ $i }}
                        </a>
                    @endfor

                    @if($inventories->hasMorePages())
                        <a href="{{ $inventories->nextPageUrl() }}" class="btn btn-outline-secondary btn-sm">
                            Next <i class="bi bi-chevron-right"></i>
                        </a>
                    @endif
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 320px">Actions</th>
                            <th>Item <i class="bi bi-arrow-down-up small-icon"></i></th>
                            <th>Classification <i class="bi bi-arrow-down-up small-icon"></i></th>
                            <th>Stocks In <i class="bi bi-arrow-down-up small-icon"></i></th>
                            <th>Stocks Out <i class="bi bi-arrow-down-up small-icon"></i></th>
                            <th>Stocks Available <i class="bi bi-arrow-down-up small-icon"></i></th>
                            <th>Created By <i class="bi bi-arrow-down-up small-icon"></i></th>
                            <th>Date Created <i class="bi bi-arrow-down-up small-icon"></i></th>
                            <th>Modified By <i class="bi bi-arrow-down-up small-icon"></i></th>
                            <th>Date Modified <i class="bi bi-arrow-down-up small-icon"></i></th>
                            <th>Deleted By <i class="bi bi-arrow-down-up small-icon"></i></th>
                            <th>Date Deleted <i class="bi bi-arrow-down-up small-icon"></i></th>
                            <th>Status <i class="bi bi-arrow-down-up small-icon"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventories as $inventory)
                        <tr>
                            @if($userPermissions && ($userPermissions->CanEdit || $userPermissions->CanDelete))
                            <td>
                                <div class="btn-group btn-group-sm">
                                    @if(!$inventory->IsDeleted)
                                        {{-- Show Stock Out button for everyone --}}
                                        <button type="button" 
                                                class="btn btn-sm btn-blue" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#stockOutModal{{ $inventory->InventoryId }}"
                                                title="Stock Out">
                                            <i class="bi bi-box-arrow-right me-1"></i>
                                            Stock Out
                                        </button>
                                    @if($userPermissions && $userPermissions->CanDelete)
                                    <form action="{{ route('inventory.destroy', $inventory->InventoryId) }}" 
                                        method="POST" 
                                        style="margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-danger"
                                                onclick="return confirm('Are you sure you want to delete this record?');">
                                            <i class="bi bi-trash me-1"></i>
                                            Delete
                                        </button>
                                    </form>
                                    @endif
                                    @else
                                        {{-- Show Restore button only if not Inventory Manager or Staff --}}
                                        @if(auth()->user()->role !== 'Inventory Manager' && auth()->user()->role !== 'Inventory Staff')
                                        <form action="{{ route('inventory.restore', $inventory->InventoryId) }}" 
                                              method="POST" 
                                              style="margin: 0;">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" 
                                                    class="btn btn-success"
                                                    onclick="return confirm('Are you sure you want to restore this record?');">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i>
                                                Restore
                                            </button>
                                        </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            @endif
                            <td>{{ $inventory->item->ItemName ?? 'N/A' }}</td>
                            <td>{{ $inventory->item->classification->ClassificationName ?? 'N/A' }}</td>
                            <td>{{ $inventory->StocksAdded }}</td>
                            <td>{{ $inventory->StockOut }}</td>
                            <td>{{ $inventory->StocksAvailable }}</td>
                            <td>{{ $inventory->created_by_user->Username ?? 'N/A' }}</td>
                            <td>{{ date('Y-m-d h:i:s A', strtotime($inventory->DateCreated)) }}</td>
                            <td>{{ optional($inventory->modified_by_user)->Username ?? 'N/A' }}</td>
                            <td>{{ date('Y-m-d h:i:s A', strtotime($inventory->DateModified)) }}</td>
                            <td>{{ optional($inventory->deleted_by_user)->Username ?? 'N/A' }}</td>
                            <td>{{ $inventory->DateDeleted ? date('Y-m-d H:i', strtotime($inventory->DateDeleted)) : 'N/A' }}</td>
                            <td>
                                @if($inventory->IsDeleted)
                                    <span class="badge bg-danger">Deleted</span>
                                @else
                                    <span class="badge bg-success">Active</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="13" class="text-center">No inventory records found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@foreach($inventories as $inventory)
<div class="modal fade" id="editInventoryModal{{ $inventory->InventoryId }}" tabindex="-1" aria-labelledby="editInventoryModalLabel{{ $inventory->InventoryId }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editInventoryModalLabel{{ $inventory->InventoryId }}">Edit Inventory Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('inventory.update', $inventory->InventoryId) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Item</label>
                        <input type="text" class="form-control" value="{{ $inventory->item->ItemName }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stocks Added</label>
                        <input type="number" class="form-control" name="StocksAdded" value="{{ $inventory->StocksAdded }}" required>
                        <small class="text-muted">Use negative numbers for stock out</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Current Stock</label>
                        <input type="number" class="form-control" value="{{ $inventory->item->StocksAvailable }}" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="stockInModal{{ $inventory->InventoryId }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-box-arrow-in-right me-1"></i>
                    Stock In - {{ $inventory->item->ItemName }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('inventory.update', $inventory->InventoryId) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Current Stock</label>
                        <input type="text" class="form-control" value="{{ $inventory->StocksAvailable }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity to Add</label>
                        <input type="number" 
                               class="form-control" 
                               name="StocksAdded" 
                               min="1" 
                               required>
                        <input type="hidden" name="type" value="in">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-box-arrow-in-right"></i> Stock In
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="stockOutModal{{ $inventory->InventoryId }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-box-arrow-right me-1"></i>
                    Stock Out - {{ $inventory->item->ItemName }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('inventory.update', $inventory->InventoryId) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Current Stock</label>
                        <input type="text" class="form-control" value="{{ $inventory->StocksAvailable }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity to Remove</label>
                        <input type="number" 
                               class="form-control" 
                               name="StocksAdded" 
                               min="1" 
                               max="{{ $inventory->StocksAvailable }}" 
                               required>
                        <input type="hidden" name="type" value="out">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-box-arrow-right"></i> Stock Out
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- Add Inventory Modal - Only include if not Inventory Staff -->
@if($userPermissions && $userPermissions->CanAdd)
    <!-- ... Add Inventory Modal code ... -->
@endif

{{-- Make sure the delete modal is also wrapped with permission check --}}
@if($userPermissions && $userPermissions->CanDelete)
    @foreach($inventories as $inventory)
        <div class="modal fade" id="deleteModal{{ $inventory->InventoryID }}" tabindex="-1">
            <!-- Delete modal content -->
        </div>
    @endforeach
@endif
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Add Inventory Form Submit
    $('#addInventoryForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            ItemId: $('#ItemId').val(),
            type: $('select[name="type"]').val(),
            StocksAdded: $('#StocksAdded').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        console.log('Sending data:', formData);

        $.ajax({
            url: "{{ route('inventory.store') }}",
            type: 'POST',
            data: formData,
            success: function(response) {
                console.log('Success response:', response);
                if (response.success) {
                    const newRow = `
                        <tr data-inventory-id="${response.data.InventoryID}">
                            <td>
                                <div class="d-flex gap-2">
                                    <button type="button" 
                                            class="btn btn-sm btn-blue flex-grow-1 d-flex align-items-center justify-content-center" 
                                            style="width: 100px; height: 31px;"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#stockOutModal${response.data.InventoryID}">
                                        <i class="bi bi-box-arrow-right me-1"></i>
                                        Stock Out
                                    </button>
                                    <form action="/inventory/${response.data.InventoryID}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                            <td>${response.data.ItemName}</td>
                            <td>${response.data.ClassificationName}</td>
                            <td>${response.data.StocksAdded}</td>
                            <td>${response.data.StockOut}</td>
                            <td>${response.data.StocksAvailable}</td>
                            <td>${response.data.CreatedBy}</td>
                            <td>${response.data.DateCreated}</td>
                            <td>N/A</td>
                            <td>N/A</td>
                            <td>N/A</td>
                            <td>N/A</td>
                            <td><span class="badge bg-success">Active</span></td>
                        </tr>
                    `;
                    
                    $('#inventoryTable tbody').prepend(newRow);
                    $('#addInventoryForm')[0].reset();
                    $('#addInventoryModal').modal('hide');
                    
                    alert('Inventory ' + (formData.type === 'in' ? 'added' : 'removed') + ' successfully!');
                    location.reload(); // Reload to update all values
                }
            },
            error: function(xhr, status, error) {
                console.error('Error details:', {
                    xhr: xhr.responseJSON,
                    status: status,
                    error: error
                });
                
                let errorMessage = 'Failed to add inventory';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                alert(errorMessage);
            }
        });
    });
});
</script>
@endsection

@section('additional_styles')
<style>
    /* Hide default scrolling buttons */
    .table-responsive::-webkit-scrollbar-button {
        display: none;
    }

    /* Custom scrollbar styling */
    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    .table th {
        padding: 12px 16px;
        white-space: nowrap;
    }

    .table th .small-icon {
        font-size: 10px;
        color: #6c757d;
        margin-left: 3px;
    }

    .table td {
        padding: 12px 16px;
        vertical-align: middle;
    }

    /* Action buttons styling */
    .btn-group {
        white-space: nowrap;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .btn-group form {
        display: inline-block;
    }

    /* Consistent button styles */
    .btn {
        transition: all 0.3s ease;
        font-size: 0.875rem;
    }
    
    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    /* Remove form margins */
    form {
        margin: 0;
        padding: 0;
    }

    /* Header styles */
    h2 {
        color: #2c3e50;
        font-weight: 600;
    }

    .btn-group .btn {
        padding: 0.5rem 1rem;
    }

    .btn-primary {
        background-color: #3498db;
        border-color: #3498db;
    }

    .btn-primary:hover {
        background-color: #2980b9;
        border-color: #2980b9;
    }

    .btn-outline-primary {
        color: #3498db;
        border-color: #3498db;
    }

    .btn-outline-primary:hover,
    .btn-outline-primary.active {
        background-color: #3498db;
        border-color: #3498db;
        color: white;
    }

    .btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
        color: white;
    }

    .btn-danger:hover {
        background-color: #bb2d3b;
        border-color: #b02a37;
        color: white;
    }

    .btn-danger.active {
        background-color: #bb2d3b !important;
        border-color: #b02a37 !important;
        color: white !important;
    }

    .btn-group .btn i {
        margin-right: 0.25rem;
    }

    /* Pagination styling */
    .pagination {
        margin-bottom: 0;
        gap: 5px;
    }

    .pagination .page-link {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        line-height: 1.5;
        border-radius: 4px;
        color: #3498db;
        min-width: 35px;
        text-align: center;
        margin: 0;
    }

    .pagination .page-item {
        margin: 0;
    }

    .pagination .page-item:first-child .page-link,
    .pagination .page-item:last-child .page-link {
        border-radius: 4px;
    }

    .pagination .page-item.active .page-link {
        background-color: #3498db;
        border-color: #3498db;
        color: white;
    }

    .pagination .page-link:hover {
        background-color: #e9ecef;
        border-color: #dee2e6;
        color: #2980b9;
    }

    .pagination .page-item.disabled .page-link {
        color: #6c757d;
        pointer-events: none;
        background-color: #fff;
        border-color: #dee2e6;
    }

    /* Custom pagination styling */
    .pagination-sm {
        display: flex;
        gap: 5px;
        align-items: center;
    }

    .pagination-sm .btn {
        min-width: 32px;
        padding: 4px 8px;
        font-size: 0.875rem;
    }

    .pagination-sm .btn i {
        font-size: 12px;
    }

    .btn-blue {
        background-color: #0d6efd;
        color: white;
    }
    
    .btn-blue:hover {
        background-color: #0b5ed7;
        color: white;
    }
</style>
@endsection 