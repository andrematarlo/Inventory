@extends('layouts.app')

@section('title', 'Items')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Items Management</h2>
        @if(auth()->user()->role === 'Admin' || ($userPermissions && $userPermissions->CanAdd))
        <div>
            <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#importExcelModal">
                <i class="bi bi-upload"></i> Import Items
            </button>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                <i class="bi bi-plus-lg"></i> Add Item
            </button>
        </div>
        @endif
    </div>

    <div class="d-flex gap-2 mb-3">
        <button type="button" class="btn btn-primary active" id="activeRecords">Active Records</button>
        <button type="button" class="btn btn-outline-danger" id="deletedRecords">
            <i class="bi bi-trash"></i> Show Deleted Records
        </button>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Active Items Card -->
    <div class="card mb-4" id="activeRecordsCard">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Active Items</h5>
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
                <div>Showing {{ $activeItems->firstItem() ?? 0 }} to {{ $activeItems->lastItem() ?? 0 }} of {{ $activeItems->total() }} results</div>
                <div class="pagination-sm">
                    @if($activeItems->currentPage() > 1)
                        <a href="{{ $activeItems->previousPageUrl() }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-chevron-left"></i> Previous
                        </a>
                    @endif
                    
                    @for($i = 1; $i <= $activeItems->lastPage(); $i++)
                        <a href="{{ $activeItems->url($i) }}" 
                           class="btn btn-sm {{ $i == $activeItems->currentPage() ? 'btn-primary' : 'btn-outline-secondary' }}">
                            {{ $i }}
                        </a>
                    @endfor

                    @if($activeItems->hasMorePages())
                        <a href="{{ $activeItems->nextPageUrl() }}" class="btn btn-outline-secondary btn-sm">
                            Next <i class="bi bi-chevron-right"></i>
                        </a>
                    @endif
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover" id="itemsTable">
                    <thead>
                        <tr>
                            @if(auth()->user()->role === 'Admin' || ($userPermissions && ($userPermissions->CanEdit || $userPermissions->CanDelete)))
                            <th style="width: 100px">Actions</th>
                            @endif
                            <th>Image</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Classification</th>
                            <th>Unit</th>
                            <th>Stocks</th>
                            <th>Reorder Point</th>
                            <th>Created By</th>
                            <th>Date Created</th>
                            <th>Modified By</th>
                            <th>Date Modified</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activeItems as $item)
                            <tr>
                                @if(auth()->user()->role === 'Admin' || ($userPermissions && ($userPermissions->CanEdit || $userPermissions->CanDelete)))
                                <td>
                                    <div class="btn-group" role="group">
                                        @if(auth()->user()->role === 'Admin' || ($userPermissions && $userPermissions->CanEdit))
                                        <button type="button" class="btn btn-sm btn-blue" data-bs-toggle="modal" data-bs-target="#editItemModal{{ $item->ItemId }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        @endif
                                        @if(auth()->user()->role === 'Admin' || ($userPermissions && $userPermissions->CanDelete))
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteItemModal{{ $item->ItemId }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                                @endif
                                <td>
                                    @if($item->ImagePath)
                                        <img src="{{ asset('storage/' . $item->ImagePath) }}" 
                                             alt="{{ $item->ItemName }}" 
                                             style="height: 50px; width: 50px; object-fit: cover;">
                                    @else
                                        <span class="text-muted">No image</span>
                                    @endif
                                </td>
                                <td>{{ $item->ItemName }}</td>
                                <td>{{ $item->Description }}</td>
                                <td>{{ $item->classification->ClassificationName ?? 'N/A' }}</td>
                                <td>{{ $item->unitOfMeasure->UnitName ?? 'N/A' }}</td>
                                <td>{{ $item->StocksAvailable }}</td>
                                <td>{{ $item->ReorderPoint }}</td>
                                <td>{{ $item->createdBy->Username ?? 'N/A' }}</td>
                                <td>{{ $item->DateCreated ? date('Y-m-d H:i:s', strtotime($item->DateCreated)) : 'N/A' }}</td>
                                <td>{{ $item->modifiedBy->Username ?? 'N/A' }}</td>
                                <td>{{ $item->DateModified ? date('Y-m-d H:i:s', strtotime($item->DateModified)) : 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-success">Active</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()->role === 'Admin' || ($userPermissions && ($userPermissions->CanEdit || $userPermissions->CanDelete)) ? '14' : '13' }}" class="text-center">No active items found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Deleted Items Card -->
    <div class="card mb-4" id="deletedRecordsCard" style="display: none;">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Deleted Items</h5>
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
                <div>Showing {{ $deletedItems->firstItem() ?? 0 }} to {{ $deletedItems->lastItem() ?? 0 }} of {{ $deletedItems->total() }} results</div>
                <div class="pagination-sm">
                    @if($deletedItems->currentPage() > 1)
                        <a href="{{ $deletedItems->previousPageUrl() }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-chevron-left"></i> Previous
                        </a>
                    @endif
                    
                    @for($i = 1; $i <= $deletedItems->lastPage(); $i++)
                        <a href="{{ $deletedItems->url($i) }}" 
                           class="btn btn-sm {{ $i == $deletedItems->currentPage() ? 'btn-primary' : 'btn-outline-secondary' }}">
                            {{ $i }}
                        </a>
                    @endfor

                    @if($deletedItems->hasMorePages())
                        <a href="{{ $deletedItems->nextPageUrl() }}" class="btn btn-outline-secondary btn-sm">
                            Next <i class="bi bi-chevron-right"></i>
                        </a>
                    @endif
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover" id="deletedItemsTable">
                    <thead>
                        <tr>
                            <th>Actions</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Classification</th>
                            <th>Unit</th>
                            <th>Stocks</th>
                            <th>Deleted By</th>
                            <th>Date Deleted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deletedItems as $item)
                            <tr>
                                <td>
                                    <form action="{{ route('items.restore', $item->ItemId) }}" 
                                          method="POST" 
                                          class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bi bi-arrow-counterclockwise" style="font-size: 14px;"></i>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    @if($item->ImagePath)
                                        <img src="{{ asset('storage/' . $item->ImagePath) }}" 
                                             alt="{{ $item->ItemName }}" 
                                             style="height: 50px; width: 50px; object-fit: cover;">
                                    @else
                                        <span class="text-muted">No image</span>
                                    @endif
                                </td>
                                <td>{{ $item->ItemName }}</td>
                                <td>{{ $item->Description }}</td>
                                <td>{{ $item->classification->ClassificationName ?? 'N/A' }}</td>
                                <td>{{ $item->unitOfMeasure->UnitName ?? 'N/A' }}</td>
                                <td>{{ $item->StocksAvailable }}</td>
                                <td>{{ $item->deletedBy->Username ?? 'N/A' }}</td>
                                <td>{{ $item->DateDeleted ? date('Y-m-d H:i:s', strtotime($item->DateDeleted)) : 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">No deleted items found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Include modals -->
@if(auth()->user()->role === 'Admin' || ($userPermissions && $userPermissions->CanAdd))
    @include('items.partials.add-modal')
    @include('items.partials.import-modal')
@endif

@if(auth()->user()->role === 'Admin' || ($userPermissions && $userPermissions->CanEdit))
    @foreach($activeItems as $item)
        <div class="modal fade" id="editItemModal{{ $item->ItemId }}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('items.update', $item->ItemId) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <!-- Current Image Preview -->
                            <div class="mb-3">
                                <label class="form-label">Current Image</label>
                                <div>
                                    @if($item->ImagePath)
                                        <img src="{{ asset('storage/' . $item->ImagePath) }}" 
                                             alt="Current item image" 
                                             class="img-thumbnail"
                                             style="max-height: 100px;">
                                    @else
                                        <p class="text-muted">No image uploaded</p>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Update Image</label>
                                <input type="file" class="form-control" name="image" accept="image/*">
                                <small class="text-muted">Leave empty to keep current image</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Item Name</label>
                                <input type="text" class="form-control" name="ItemName" value="{{ $item->ItemName }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="Description" rows="3">{{ $item->Description }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Classification</label>
                                <select class="form-select" name="ClassificationId" required>
                                    @foreach($classifications as $classification)
                                        <option value="{{ $classification->ClassificationId }}" 
                                            {{ $item->ClassificationId == $classification->ClassificationId ? 'selected' : '' }}>
                                            {{ $classification->ClassificationName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Unit of Measure</label>
                                <select class="form-select" name="UnitOfMeasureId" required>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->UnitOfMeasureId }}" 
                                            {{ $item->UnitOfMeasureId == $unit->UnitOfMeasureId ? 'selected' : '' }}>
                                            {{ $unit->UnitName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Reorder Point</label>
                                <input type="number" class="form-control" name="ReorderPoint" value="{{ $item->ReorderPoint }}" min="0" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endif

@if(auth()->user()->role === 'Admin' || ($userPermissions && $userPermissions->CanDelete))
    @foreach($activeItems as $item)
        <div class="modal fade" id="deleteItemModal{{ $item->ItemId }}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('items.destroy', $item->ItemId) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="modal-body">
                            <p>Are you sure you want to delete this item?</p>
                            <p><strong>{{ $item->ItemName }}</strong></p>
                            @if($item->StocksAvailable > 0)
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    This item has {{ $item->StocksAvailable }} units in stock.
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Delete Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endif

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const activeRecordsBtn = document.getElementById('activeRecords');
    const deletedRecordsBtn = document.getElementById('deletedRecords');
    const activeRecordsCard = document.getElementById('activeRecordsCard');
    const deletedRecordsCard = document.getElementById('deletedRecordsCard');
    const activeSearchInput = document.getElementById('activeSearchInput');
    const deletedSearchInput = document.getElementById('deletedSearchInput');

    function filterTable(tableBody, searchTerm) {
        const rows = tableBody.getElementsByTagName('tr');
        
        for (let row of rows) {
            const cells = row.getElementsByTagName('td');
            let shouldShow = false;
            
            if (cells.length <= 1) continue;

            for (let cell of cells) {
                const text = cell.textContent.toLowerCase();
                if (text.includes(searchTerm.toLowerCase())) {
                    shouldShow = true;
                    break;
                }
            }
            
            row.style.display = shouldShow ? '' : 'none';
        }
    }

    activeSearchInput.addEventListener('input', (e) => {
        const activeTableBody = activeRecordsCard.querySelector('tbody');
        filterTable(activeTableBody, e.target.value);
    });

    deletedSearchInput.addEventListener('input', (e) => {
        const deletedTableBody = deletedRecordsCard.querySelector('tbody');
        filterTable(deletedTableBody, e.target.value);
    });

    function toggleRecords(showActive) {
        if (showActive) {
            activeRecordsCard.style.display = 'block';
            deletedRecordsCard.style.display = 'none';
            activeRecordsBtn.classList.add('active');
            activeRecordsBtn.classList.remove('btn-outline-primary');
            activeRecordsBtn.classList.add('btn-primary');
            deletedRecordsBtn.classList.remove('active');
            deletedRecordsBtn.classList.add('btn-outline-danger');
            deletedRecordsBtn.classList.remove('btn-danger');
            deletedSearchInput.value = '';
        } else {
            activeRecordsCard.style.display = 'none';
            deletedRecordsCard.style.display = 'block';
            deletedRecordsBtn.classList.add('active');
            deletedRecordsBtn.classList.remove('btn-outline-danger');
            deletedRecordsBtn.classList.add('btn-danger');
            activeRecordsBtn.classList.remove('active');
            activeRecordsBtn.classList.add('btn-outline-primary');
            activeRecordsBtn.classList.remove('btn-primary');
            activeSearchInput.value = '';
        }
    }

    activeRecordsBtn.addEventListener('click', () => toggleRecords(true));
    deletedRecordsBtn.addEventListener('click', () => toggleRecords(false));

    // Initialize view
    toggleRecords(true);
});
</script>
@endsection

@section('additional_styles')
<style>
    .btn-blue {
        background-color: #0d6efd;
        color: white;
    }
    
    .btn-blue:hover {
        background-color: #0b5ed7;
        color: white;
    }

    /* Custom pagination styles */
    .pagination {
        margin: 0;
    }
    
    .pagination .page-link {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        line-height: 1.5;
        border-radius: 0.2rem;
    }

    .pagination .page-link i {
        font-size: 10px;
    }

    .pagination .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .pagination .page-item.disabled .page-link {
        color: #6c757d;
        pointer-events: none;
        background-color: #fff;
        border-color: #dee2e6;
    }

    .btn-outline-secondary {
        color: #6c757d;
        border-color: #6c757d;
    }

    .btn-outline-secondary:hover {
        background-color: #6c757d;
        border-color: #6c757d;
        color: white;
    }

    .btn-primary {
        background-color: #3498db;
        border-color: #3498db;
    }

    .btn-primary:hover {
        background-color: #2980b9;
        border-color: #2980b9;
    }

    .btn-group .btn {
        border-radius: 0;
    }
    
    .btn-group .btn:first-child {
        border-top-left-radius: 4px;
        border-bottom-left-radius: 4px;
    }
    
    .btn-group .btn:last-child {
        border-top-right-radius: 4px;
        border-bottom-right-radius: 4px;
    }

    .btn-group .btn.active {
        opacity: 1;
    }

    .btn-group .btn:not(.active) {
        opacity: 0.8;
    }

    .btn-group .btn:hover:not(.active) {
        opacity: 0.9;
    }
    .modal-lg {
        max-width: 900px;
    }

    .table-sm td, .table-sm th {
        padding: 0.5rem;
    }

    .form-select-sm {
        padding-top: 0.25rem;
        padding-bottom: 0.25rem;
        padding-left: 0.5rem;
        font-size: 0.875rem;
    }

    /* Restore button icon style */
    .btn-success i {
        font-size: 14px !important;
    }

    .btn-success {
        padding: 0.25rem 0.5rem;
    }

    /* Override Laravel pagination styling */
    .custom-pagination svg {
        width: 12px !important;
        height: 12px !important;
    }

    .custom-pagination nav {
        display: flex;
        justify-content: center;
    }

    .custom-pagination .shadow-sm {
        box-shadow: none !important;
    }

    .custom-pagination .relative {
        position: relative;
        display: inline-flex;
        align-items: center;
    }

    .custom-pagination .relative, 
    .custom-pagination button {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.875rem !important;
        line-height: 1.5 !important;
        height: auto !important;
        min-width: auto !important;
    }
</style>
@endsection
