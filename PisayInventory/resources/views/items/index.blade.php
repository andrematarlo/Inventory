@extends('layouts.app')

@section('title', 'Items')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Items Management</h2>
        <div>
            @if($userPermissions && $userPermissions->CanAdd)
                <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#importExcelModal">
                    <i class="bi bi-upload"></i> Import Items
                </button>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                    <i class="bi bi-plus-lg"></i> New Item
                </button>
            @endif
        </div>
    </div>

    <div class="btn-group mb-4" role="group">
        <button class="btn btn-primary active" type="button" id="activeRecordsBtn">
            Active Records
        </button>
        <button class="btn btn-danger" type="button" id="showDeletedBtn">
            <i class="bi bi-archive"></i> Show Deleted Records
        </button>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Active Items Section -->
    <div id="activeItems">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Active Items</h5>
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
                                <th>Actions</th>
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
                                    @if($userPermissions && ($userPermissions->CanEdit || $userPermissions->CanDelete))
                                    <td>
                                        <div class="btn-group" role="group">
                                            @if($userPermissions->CanEdit)
                                            <button type="button" class="btn btn-sm btn-blue" data-bs-toggle="modal" data-bs-target="#editItemModal{{ $item->ItemId }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            @endif
                                            @if($userPermissions && $userPermissions->CanDelete)
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
                                    <td colspan="14" class="text-center">No active items found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Deleted Items Section -->
    <div id="deletedItems" style="display: none;">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Deleted Items</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>Showing {{ $deletedItems->firstItem() ?? 0 }} to {{ $deletedItems->lastItem() ?? 0 }} of {{ $deletedItems->total() }} results</div>
                    <div class="pagination-sm">
                        @if($deletedItems->currentPage() > 1)
                            <a href="{{ $deletedItems->previousPageUrl() . '&tab=deleted' }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-chevron-left"></i> Previous
                            </a>
                        @endif
                        
                        @for($i = 1; $i <= $deletedItems->lastPage(); $i++)
                            <a href="{{ $deletedItems->url($i) . '&tab=deleted' }}" 
                               class="btn btn-sm {{ $i == $deletedItems->currentPage() ? 'btn-primary' : 'btn-outline-secondary' }}">
                                {{ $i }}
                            </a>
                        @endfor

                        @if($deletedItems->hasMorePages())
                            <a href="{{ $deletedItems->nextPageUrl() . '&tab=deleted' }}" class="btn btn-outline-secondary btn-sm">
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
                                                <i class="bi bi-arrow-counterclockwise"></i>
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
</div>

@if($userPermissions && $userPermissions->CanAdd)
        @include('items.partials.add-modal')

        <!-- Import Excel Modal -->
<div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importExcelModalLabel">Import Items</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('items.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                @csrf
                <div class="modal-body">
                    <!-- Step 1: File Upload -->
                    <div id="step1">
                        <div class="mb-3">
                            <label for="excel_file" class="form-label">Upload Excel File</label>
                            <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx, .xls" required>
                        </div>
                        <button type="button" class="btn btn-primary" id="previewBtn">Preview Columns</button>
                    </div>

                    <!-- Step 2: Column Mapping -->
                    <div id="step2" style="display: none;">
                        <h5>Map Excel Columns to Item Fields</h5>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Field</th>
                                        <th>Excel Column</th>
                                        <th>Required/Default Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Name</td>
                                        <td>
                                            <select name="column_mapping[ItemName]" class="form-select form-select-sm" required>
                                                <option value="">Select Column</option>
                                            </select>
                                        </td>
                                        <td><span class="badge bg-danger">Required</span></td>
                                    </tr>
                                    <tr>
                                        <td>Description</td>
                                        <td>
                                            <select name="column_mapping[Description]" class="form-select form-select-sm">
                                                <option value="">Select Column</option>
                                            </select>
                                        </td>
                                        <td><span class="badge bg-secondary">Optional</span></td>
                                    </tr>
                                    <tr>
                                        <td>Classification</td>
                                        <td>
                                            <select name="column_mapping[ClassificationId]" class="form-select form-select-sm">
                                                <option value="">Select Column</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="default_classification" class="form-select form-select-sm">
                                                <option value="">Default Classification</option>
                                                @foreach($classifications as $classification)
                                                    <option value="{{ $classification->ClassificationId }}">
                                                        {{ $classification->ClassificationName }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Unit</td>
                                        <td>
                                            <select name="column_mapping[UnitId]" class="form-select form-select-sm">
                                                <option value="">Select Column</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="default_unit" class="form-select form-select-sm">
                                                <option value="">Default Unit</option>
                                                @foreach($units as $unit)
                                                    <option value="{{ $unit->UnitId }}">
                                                        {{ $unit->UnitName }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Stocks Available</td>
                                        <td>
                                            <select name="column_mapping[StocksAvailable]" class="form-select form-select-sm">
                                                <option value="">Select Column</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="default_stocks" class="form-control form-control-sm" placeholder="Default: 0" min="0">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Reorder Point</td>
                                        <td>
                                            <select name="column_mapping[ReorderPoint]" class="form-select form-select-sm">
                                                <option value="">Select Column</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="default_reorder_point" class="form-control form-control-sm" placeholder="Default: 0" min="0">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button type="submit" class="btn btn-success" id="importBtn">Import Data</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
    @endif

@if($userPermissions && $userPermissions->CanEdit)
    @foreach($activeItems as $item)
        @include('items.partials.edit-modal', ['item' => $item])
    @endforeach
@endif

@if($userPermissions && $userPermissions->CanDelete)
    @foreach($activeItems as $item)
        @include('items.partials.delete-modal', ['item' => $item])
    @endforeach
@endif
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
        // Toggle between active and deleted items
        $('#activeRecordsBtn, #showDeletedBtn').on('click', function() {
            const activeDiv = $('#activeItems');
            const deletedDiv = $('#deletedItems');
            const activeBtn = $('#activeRecordsBtn');
            const deletedBtn = $('#showDeletedBtn');

            if ($(this).attr('id') === 'showDeletedBtn') {
                activeDiv.hide();
                deletedDiv.show();
                activeBtn.removeClass('active');
                deletedBtn.addClass('active');
            } else {
                deletedDiv.hide();
                activeDiv.show();
                deletedBtn.removeClass('active');
                activeBtn.addClass('active');
            }
        });

        // Initialize import modal
        const importModal = document.getElementById('importExcelModal');
        if (importModal) {
            const bsModal = new bootstrap.Modal(importModal, {
                backdrop: 'static',
                keyboard: false
            });

            // Prevent modal from closing when clicking outside
            $(importModal).on('mousedown', function(e) {
                if ($(e.target).is('.modal')) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            });

            // Preview button click handler
            $('#previewBtn').click(function() {
                const fileInput = document.getElementById('excel_file');
                if (!fileInput.files.length) {
                    alert('Please select a file first');
                    return;
                }

                const formData = new FormData();
                formData.append('excel_file', fileInput.files[0]);
                formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

                // Show loading state
                $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Loading...');

                $.ajax({
                    url: "{{ route('items.preview-columns') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        // Reset all dropdowns first
                        const columnSelects = $('select[name^="column_mapping"]');
                        columnSelects.empty().append('<option value="">Select Column</option>');
                        
                        // Add the columns to all dropdowns
                        response.columns.forEach(column => {
                            columnSelects.append(`<option value="${column}">${column}</option>`);
                        });

                        // Auto-map columns based on similar names
                        response.columns.forEach(column => {
                            const lowerColumn = column.toLowerCase();
                            if (lowerColumn.includes('name')) {
                                $('select[name="column_mapping[ItemName]"]').val(column);
                            }
                            if (lowerColumn.includes('desc')) {
                                $('select[name="column_mapping[Description]"]').val(column);
                            }
                            if (lowerColumn.includes('stock') || lowerColumn.includes('qty')) {
                                $('select[name="column_mapping[StocksAvailable]"]').val(column);
                            }
                            if (lowerColumn.includes('reorder') || lowerColumn.includes('minimum')) {
                                $('select[name="column_mapping[ReorderPoint]"]').val(column);
                            }
                        });

                        // Show step 2
                        $('#step1').hide();
                        $('#step2').show();
                    },
                    error: function(xhr) {
                        const errorMessage = xhr.responseJSON?.error || 'An error occurred while previewing columns';
                        alert('Preview failed: ' + errorMessage);
                    },
                    complete: function() {
                        // Reset button state
                        $('#previewBtn').prop('disabled', false).text('Preview Columns');
                    }
                });
            });

            // Handle form submission
            $('#importForm').on('submit', function(e) {
                e.preventDefault();
                
                // Check if file is selected
                if (!$('#excel_file').val()) {
                    alert('Please select an Excel file');
                    return;
                }

                // Check if required field (Name) is mapped
                if (!$('select[name="column_mapping[ItemName]"]').val()) {
                    alert('Please map the Name field');
                    return;
                }

                // Show loading state on the import button
                const importBtn = $('#importBtn');
                importBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Importing...');
                
                const formData = new FormData(this);
                
                $.ajax({
                    url: "{{ route('items.import') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        alert('Import successful!');
                        window.location.reload();
                    },
                    error: function(xhr) {
                        const errorMessage = xhr.responseJSON?.error || 'An error occurred during import';
                        alert('Import failed: ' + errorMessage);
                        importBtn.prop('disabled', false).text('Import Data');
                    }
                });
            });
        }

        // Initialize DataTable
        if (!$.fn.DataTable.isDataTable('#itemsTable')) {
            $('#itemsTable').DataTable({
                pageLength: 10,
                responsive: true,
                dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center"ip>',
                language: {
                    search: "Search:",
                    searchPlaceholder: "Search items..."
                }
            });
        }
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

    /* Pagination styling */
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
</style>
@endsection
