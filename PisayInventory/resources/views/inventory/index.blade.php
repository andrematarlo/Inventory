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
                   class="btn btn-outline-primary {{ request('show_deleted') ? 'active' : '' }}">
                    Show All Records
                </a>
            </div>
            <button type="button" 
                    class="btn btn-primary d-flex align-items-center gap-1" 
                    data-bs-toggle="modal" 
                    data-bs-target="#addInventoryModal">
                <i class="bi bi-plus-lg"></i>
                Add Inventory
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Actions</th>
                            <th>Item</th>
                            <th>Classification</th>
                            <th>Stocks In</th>
                            <th>Stocks Out</th>
                            <th>Stocks Available</th>
                            <th>Created By</th>
                            <th>Date Created</th>
                            <th>Modified By</th>
                            <th>Date Modified</th>
                            <th>Deleted By</th>
                            <th>Date Deleted</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventories as $inventory)
                        <tr>
                            <td>
                                <div class="d-flex gap-2">
                                    @if(!$inventory->IsDeleted)
                                        <button type="button" 
                                                class="btn btn-sm btn-success flex-grow-1 d-flex align-items-center justify-content-center" 
                                                style="width: 100px; height: 31px;"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#stockInModal{{ $inventory->InventoryId }}"
                                                title="Stock In">
                                            <i class="bi bi-box-arrow-in-right me-1"></i>
                                            Stock In
                                        </button>
                                        <button type="button" 
                                                class="btn btn-sm btn-warning flex-grow-1 d-flex align-items-center justify-content-center" 
                                                style="width: 100px; height: 31px;"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#stockOutModal{{ $inventory->InventoryId }}"
                                                title="Stock Out">
                                            <i class="bi bi-box-arrow-right me-1"></i>
                                            Stock Out
                                        </button>
                                        <form action="{{ route('inventory.destroy', $inventory->InventoryId) }}" 
                                              method="POST" 
                                              style="margin: 0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-danger d-flex align-items-center justify-content-center" 
                                                    style="width: 100px; height: 31px;"
                                                    onclick="return confirm('Are you sure you want to delete this record?');">
                                                <i class="bi bi-trash me-1"></i>
                                                Delete
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('inventory.restore', $inventory->InventoryId) }}" 
                                              method="POST" 
                                              style="margin: 0;">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-success d-flex align-items-center justify-content-center" 
                                                    style="width: 100px; height: 31px;"
                                                    onclick="return confirm('Are you sure you want to restore this record?');">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i>
                                                Restore
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
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

            {{ $inventories->links() }}
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

<!-- Add Inventory Modal -->
<div class="modal fade" id="addInventoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Inventory</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addInventoryForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Item</label>
                        <select class="form-select" name="ItemId" id="ItemId" required>
                            <option value="">Select Item</option>
                            @foreach($items as $item)
                                <option value="{{ $item->ItemId }}">
                                    {{ $item->ItemName }} 
                                    (ID: {{ $item->ItemId }})
                                    - {{ $item->classification->ClassificationName ?? 'No Category' }}
                                    - Available: {{ $item->StocksAvailable }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select class="form-select" name="type" required>
                            <option value="in">Stock In</option>
                            <option value="out">Stock Out</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" class="form-control" name="StocksAdded" id="StocksAdded" min="1" required>
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
                                            class="btn btn-sm btn-success flex-grow-1 d-flex align-items-center justify-content-center" 
                                            style="width: 100px; height: 31px;"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#stockInModal${response.data.InventoryID}">
                                        <i class="bi bi-box-arrow-in-right me-1"></i>
                                        Stock In
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm btn-warning flex-grow-1 d-flex align-items-center justify-content-center" 
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

<style>
    /* Consistent button styles */
    .btn {
        transition: all 0.3s ease;
        font-size: 0.875rem; /* Consistent font size */
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
    
    /* Action buttons container */
    .d-flex.gap-2 {
        min-width: 320px;
    }
    
    /* Make all buttons in the action column the same height */
    .d-flex.gap-2 .btn,
    .d-flex.gap-2 form .btn {
        height: 31px !important;
        line-height: 1;
        padding-top: 0;
        padding-bottom: 0;
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
</style> 