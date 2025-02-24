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
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0">
                            @if($activeItems->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link"><i class="bi bi-chevron-left small"></i></span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $activeItems->previousPageUrl() }}">
                                        <i class="bi bi-chevron-left small"></i>
                                    </a>
                                </li>
                            @endif

                            @for($i = 1; $i <= $activeItems->lastPage(); $i++)
                                <li class="page-item {{ $activeItems->currentPage() == $i ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $activeItems->url($i) }}">{{ $i }}</a>
                                </li>
                            @endfor

                            @if($activeItems->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $activeItems->nextPageUrl() }}">
                                        <i class="bi bi-chevron-right small"></i>
                                    </a>
                                </li>
                            @else
                                <li class="page-item disabled">
                                    <span class="page-link"><i class="bi bi-chevron-right small"></i></span>
                                </li>
                            @endif
                        </ul>
                    </nav>
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
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $item->ItemId }}">
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
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Deleted Items</h5>
            </div>
            <div class="card-body">
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
                    <div class="d-flex justify-content-end mt-3">
                        <div class="custom-pagination">
                            {{ $deletedItems->links() }}
                        </div>
                    </div>
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

@include('items.partials.delete-modal')
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


                // New script for Excel import
                
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
                    if (column.includes('name')) {
                        $('select[name="column_mapping[ItemName]"]').val(column);
                    }
                    if (column.includes('desc')) {
                        $('select[name="column_mapping[Description]"]').val(column);
                    }
                    if (column.includes('class')) {
                        $('select[name="column_mapping[ClassificationId]"]').val(column);
                    }
                    if (column.includes('unit')) {
                        $('select[name="column_mapping[UnitId]"]').val(column);
                    }
                    if (column.includes('stock') || column.includes('qty')) {
                        $('select[name="column_mapping[StocksAvailable]"]').val(column);
                    }
                    if (column.includes('reorder') || column.includes('minimum')) {
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

    // Form submission handler
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
