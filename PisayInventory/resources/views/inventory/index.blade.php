@extends('layouts.app')

@section('title', 'Inventory')

@section('content')

<div class="container">
    <div class="mb-4">
        <h2>Inventory Management</h2>
    </div>

    <div class="d-flex gap-2 mb-3">
        <button type="button" class="btn btn-primary" id="activeRecords">Active Records</button>
        <button type="button" class="btn btn-outline-danger" id="deletedRecords">
            <i class="bi bi-trash"></i> Show Deleted Records
        </button>
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#generateReportModal">
            <i class="bi bi-file-earmark-text"></i> Generate Report
        </button>
    </div>

    <!-- Add Generate Report Modal -->
    <div class="modal fade" id="generateReportModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Generate Inventory Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('reports.inventory') }}" method="GET">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Report Type</label>
                            <select name="report_type" class="form-select">
                                <option value="all">All Movements</option>
                                <option value="in">Stock In Only</option>
                                <option value="out">Stock Out Only</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" 
                                   class="form-control" 
                                   name="start_date" 
                                   required 
                                   value="{{ now()->subDays(30)->format('Y-m-d') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" 
                                   class="form-control" 
                                   name="end_date" 
                                   required
                                   value="{{ now()->format('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-file-earmark-text"></i> Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Active Records Card -->
    <div class="card mb-4" id="activeRecordsCard">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Active Inventory</h5>
                <div class="d-flex gap-2 align-items-center">
                    <div class="input-group">
                        <input type="text" 
                               class="form-control" 
                               id="activeSearchInput" 
                               placeholder="Search..."
                               aria-label="Search">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
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
                    <tbody id="activeTableBody">
                        @forelse($inventories as $inventory)
                        <tr>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    {{-- Show Stock Out button for everyone --}}
                                    @if(!$inventory->IsDeleted)
                                        <button type="button" 
                                                class="btn btn-sm btn-blue" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#stockOutModal{{ $inventory->InventoryId }}"
                                                title="Stock Out">
                                            <i class="bi bi-box-arrow-right me-1"></i>
                                            Stock Out
                                        </button>
                                        {{-- Show Delete button for admin or users with delete permission --}}
                                        @if(auth()->user()->role === 'Admin' || ($userPermissions && $userPermissions->CanDelete))
                                        <button type="button" 
                                                class="btn btn-danger delete-inventory"
                                                data-inventory-id="{{ $inventory->InventoryId }}"
                                                data-item-name="{{ $inventory->item->ItemName }}">
                                            <i class="bi bi-trash me-1"></i>
                                            Delete
                                        </button>
                                        @endif
                                    @else
                                        {{-- Show Restore button for admin or non-inventory staff --}}
                                        @if(auth()->user()->role === 'Admin' || (auth()->user()->role !== 'Inventory Manager' && auth()->user()->role !== 'Inventory Staff'))
                                        <button type="button" 
                                                class="btn btn-success restore-inventory"
                                                data-inventory-id="{{ $inventory->InventoryId }}"
                                                data-item-name="{{ $inventory->item->ItemName }}">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                                            Restore
                                        </button>
                                        @endif
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
        </div>
    </div>

    <!-- Deleted Records Card -->
    <div class="card mb-4" id="deletedRecordsCard" style="display: none;">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Deleted Inventory</h5>
                <div class="d-flex gap-2 align-items-center">
                    <div class="input-group">
                        <input type="text" 
                               class="form-control" 
                               id="deletedSearchInput" 
                               placeholder="Search..."
                               aria-label="Search">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>Showing {{ $trashedInventories->firstItem() ?? 0 }} to {{ $trashedInventories->lastItem() ?? 0 }} of {{ $trashedInventories->total() }} results</div>
                <div class="pagination-sm">
                    @if($trashedInventories->currentPage() > 1)
                        <a href="{{ $trashedInventories->previousPageUrl() }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-chevron-left"></i> Previous
                        </a>
                    @endif
                    
                    @for($i = 1; $i <= $trashedInventories->lastPage(); $i++)
                        <a href="{{ $trashedInventories->url($i) }}" 
                           class="btn btn-sm {{ $i == $trashedInventories->currentPage() ? 'btn-primary' : 'btn-outline-secondary' }}">
                            {{ $i }}
                        </a>
                    @endfor

                    @if($trashedInventories->hasMorePages())
                        <a href="{{ $trashedInventories->nextPageUrl() }}" class="btn btn-outline-secondary btn-sm">
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
                    <tbody id="deletedTableBody">
                        @forelse($trashedInventories as $inventory)
                        <tr>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    {{-- Show Stock Out button for everyone --}}
                                    @if(!$inventory->IsDeleted)
                                        <button type="button" 
                                                class="btn btn-sm btn-blue" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#stockOutModal{{ $inventory->InventoryId }}"
                                                title="Stock Out">
                                            <i class="bi bi-box-arrow-right me-1"></i>
                                            Stock Out
                                        </button>
                                        {{-- Show Delete button for admin or users with delete permission --}}
                                        @if(auth()->user()->role === 'Admin' || ($userPermissions && $userPermissions->CanDelete))
                                        <button type="button" 
                                                class="btn btn-danger delete-inventory"
                                                data-inventory-id="{{ $inventory->InventoryId }}"
                                                data-item-name="{{ $inventory->item->ItemName }}">
                                            <i class="bi bi-trash me-1"></i>
                                            Delete
                                        </button>
                                        @endif
                                    @else
                                        {{-- Show Restore button for admin or non-inventory staff --}}
                                        @if(auth()->user()->role === 'Admin' || (auth()->user()->role !== 'Inventory Manager' && auth()->user()->role !== 'Inventory Staff'))
                                        <button type="button" 
                                                class="btn btn-success restore-inventory"
                                                data-inventory-id="{{ $inventory->InventoryId }}"
                                                data-item-name="{{ $inventory->item->ItemName }}">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                                            Restore
                                        </button>
                                        @endif
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
<!-- Add jQuery if not already included -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Add DataTables JS -->
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Add SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Handle Session Messages --}}
<script>
    // Check for success message
    const successMessage = '{{ Session::get('success') }}';
    const errorMessage = '{{ Session::get('error') }}';

    if (successMessage) {
        Swal.fire({
            title: 'Success!',
            text: successMessage,
            icon: 'success',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false
        });
    }

    if (errorMessage) {
        Swal.fire({
            title: 'Error!',
            text: errorMessage,
            icon: 'error',
            confirmButtonColor: '#dc3545'
        });
    }
</script>

<script>
$(document).ready(function() {
    // Get the elements
    const activeRecordsBtn = $('#activeRecords');
    const deletedRecordsBtn = $('#deletedRecords');
    const activeRecordsCard = $('#activeRecordsCard');
    const deletedRecordsCard = $('#deletedRecordsCard');

    // Initialize - show active records by default
    activeRecordsCard.show();
    deletedRecordsCard.hide();
    activeRecordsBtn.addClass('active');

    // Toggle between active and deleted records
    activeRecordsBtn.click(function() {
        activeRecordsCard.show();
        deletedRecordsCard.hide();
        activeRecordsBtn.removeClass('btn-outline-primary').addClass('btn-primary active');
        deletedRecordsBtn.removeClass('btn-danger active').addClass('btn-outline-danger');
    });

    deletedRecordsBtn.click(function() {
        activeRecordsCard.hide();
        deletedRecordsCard.show();
        deletedRecordsBtn.removeClass('btn-outline-danger').addClass('btn-danger active');
        activeRecordsBtn.removeClass('btn-primary active').addClass('btn-outline-primary');
    });

    // Add delete confirmation handler
    $('.delete-inventory').click(function(e) {
        e.preventDefault();
        const inventoryId = $(this).data('inventory-id');
        const itemName = $(this).data('item-name');

        Swal.fire({
            title: 'Delete Inventory Record?',
            html: `Are you sure you want to delete the inventory record for: <strong>${itemName}</strong>?<br>
                  <p class="text-danger mt-3"><small>This action can be undone later.</small></p>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Create and submit the form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('inventory.index') }}/" + inventoryId;
                form.innerHTML = `
                    @csrf
                    @method('DELETE')
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    });

    // Add restore confirmation handler
    $('.restore-inventory').click(function(e) {
        e.preventDefault();
        const inventoryId = $(this).data('inventory-id');
        const itemName = $(this).data('item-name');

        Swal.fire({
            title: 'Restore Inventory Record?',
            html: `Are you sure you want to restore the inventory record for: <strong>${itemName}</strong>?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754', // Bootstrap success color
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, restore it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Create and submit the form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('inventory.index') }}/" + inventoryId + "/restore";
                form.innerHTML = `
                    @csrf
                    @method('PUT')
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    });

    // Check for success/error messages from the session
    @if(Session::has('success'))
        Swal.fire({
            title: 'Success!',
            text: "{{ Session::get('success') }}",
            icon: 'success',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false
        });
    @endif

    @if(Session::has('error'))
        Swal.fire({
            title: 'Error!',
            text: "{{ Session::get('error') }}",
            icon: 'error',
            confirmButtonColor: '#dc3545'
        });
    @endif
});
</script>
@endsection

@section('styles')
<!-- Add DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<!-- Add SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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