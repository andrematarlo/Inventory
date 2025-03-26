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
            <!-- Add Export Button -->
            <button class="btn btn-info me-2" data-bs-toggle="modal" data-bs-target="#exportModal">
                <i class="bi bi-download"></i> Export Items
            </button>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
            <i class="bi bi-plus-lg"></i> New Item
        </button>
        @endif
    </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="btn-group" role="group">
        <button class="btn btn-primary active" type="button" id="activeRecordsBtn">
            Active Records
        </button>
        <button class="btn btn-danger" type="button" id="showDeletedBtn">
            <i class="bi bi-archive"></i> Show Deleted Records
        </button>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif



    <!-- Active Items Section -->
    <div id="activeItems">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Active Items</h5>
                <div class="input-group" style="width: 250px;">
                    <input type="text" id="searchActiveItems" class="form-control" placeholder="Search...">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
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
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger"
                                                    onclick="deleteResource('{{ route('items.destroy', $item->ItemId) }}', '{{ $item->ItemName }}')">
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
                                                 class="img-thumbnail"
                                                 style="max-width: 100px;">
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
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Deleted Items</h5>
                <div class="input-group" style="width: 250px;">
                    <input type="text" id="searchDeletedItems" class="form-control" placeholder="Search...">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
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
                                        <button type="button" 
                                                class="btn btn-sm btn-success restore-item"
                                                data-item-id="{{ $item->ItemId }}"
                                                data-item-name="{{ $item->ItemName }}">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                    </td>
                                    <td>
                                        @if($item->ImagePath)
                                            <img src="{{ asset('storage/' . $item->ImagePath) }}" 
                                                 alt="{{ $item->ItemName }}" 
                                                 class="img-thumbnail"
                                                 style="max-width: 100px;">
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

<!-- Export Modal -->
<div class="modal fade" 
     id="exportModal" 
     data-bs-backdrop="static" 
     data-bs-keyboard="false" 
     tabindex="-1" 
     aria-labelledby="exportModalLabel" 
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel">Export Items</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('items.export') }}" method="POST" id="exportForm">
            @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Fields to Export</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fields[]" value="ItemName" checked>
                            <label class="form-check-label">Item Name</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fields[]" value="Description">
                            <label class="form-check-label">Description</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fields[]" value="Classification">
                            <label class="form-check-label">Classification</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fields[]" value="Unit">
                            <label class="form-check-label">Unit</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fields[]" value="StocksAvailable">
                            <label class="form-check-label">Stocks Available</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fields[]" value="ReorderPoint">
                            <label class="form-check-label">Reorder Point</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Export Format</label>
                        <select class="form-select" name="format">
                            <option value="xlsx">Excel (.xlsx)</option>
                            <option value="csv">CSV (.csv)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Items to Export</label>
                        <select class="form-select" name="items_status">
                            <option value="active">Active Items Only</option>
                            <option value="deleted">Deleted Items Only</option>
                            <option value="all">All Items</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="exportBtn">
                        <i class="bi bi-download"></i> Export
                    </button>
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
        <!-- Delete Modal -->
        <div class="modal fade" 
             id="deleteModal{{ $item->ItemId }}"
             data-bs-backdrop="static"
             data-bs-keyboard="false"
             tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center p-4">
                        <div class="mb-4">
                            <i class="bi bi-exclamation-circle text-warning" style="font-size: 3rem;"></i>
                        </div>
                        <h4 class="mb-3">Are you sure?</h4>
                        <p class="mb-4">You won't be able to revert this!</p>
                        <form action="{{ route('items.destroy', $item->ItemId) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Yes, delete it!</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
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

            // Replace your existing import form submission handler with this:
            $('#importForm').on('submit', function(e) {
                e.preventDefault();
                
                // Check if file is selected
                if (!$('#excel_file').val()) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Please select an Excel file'
                    });
                    return;
                }

                // Check if required field (Name) is mapped
                if (!$('select[name="column_mapping[ItemName]"]').val()) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Please map the Name field'
                    });
                    return;
                }

                // Show loading state on the import button
                const importBtn = $('#importBtn');
                importBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Importing...');
                
                const formData = new FormData(this);
                
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#importExcelModal').modal('hide');
                        
                        let message = '';
                        if (response.import_result) {
                            const successCount = parseInt(response.import_result.message.match(/\d+/)[0]);
                            const skippedCount = response.import_result.skipped ? response.import_result.skipped.length : 0;
                            
                            message += 'Successfully imported ' + successCount + ' ' + (successCount === 1 ? 'item' : 'items') + '.' + ' ';
                            
                            if (skippedCount > 0) {
                                message += '<br>Skipped ' + skippedCount + ' ' + (skippedCount === 1 ? 'item' : 'items') + ' ' + '(already exist in the system)' + ':<br><br>';
                                
                                message += '<div style="text-align: left; margin-left: 20px;">'; // Add div for left alignment with indent
                                response.import_result.skipped.forEach(skip => {
                                    message += `Row ${skip.row}: ${skip.itemName}` + 
                                            (skip.description ? ` -- Description: ${skip.description}` : '') + 
                                            '<br>';
                                });
                                message += '</div>';
                            }
                        }
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Import Successful!',
                            html: message,
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#198754' // Bootstrap's btn-success color
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        const errorMessage = xhr.responseJSON?.error || 'An error occurred during import';
                        Toast.fire({
                            icon: 'error',
                            title: errorMessage
                        });
                        importBtn.prop('disabled', false).text('Import Data');
                    }
                });
            });
        }

        // Real-time search for active items
        $('#searchActiveItems').on('keyup', function() {
            const searchText = $(this).val().toLowerCase();
            
            $('#itemsTable tbody tr').each(function() {
                const $row = $(this);
                let text = '';
                
                // Get text from each cell except Actions column
                $row.find('td:not(:first-child)').each(function() {
                    text += $(this).text() + ' ';
                });
                
                text = text.toLowerCase();
                $row.toggle(text.includes(searchText));
            });

            // Update the "Showing X to Y of Z results" text
            updateActiveItemsCounter();
        });

        // Real-time search for deleted items
        $('#searchDeletedItems').on('keyup', function() {
            const searchText = $(this).val().toLowerCase();
            
            $('#deletedItemsTable tbody tr').each(function() {
                const $row = $(this);
                let text = '';
                
                // Get text from each cell except Actions column
                $row.find('td:not(:first-child)').each(function() {
                    text += $(this).text() + ' ';
                });
                
                text = text.toLowerCase();
                $row.toggle(text.includes(searchText));
            });

            // Update the "Showing X to Y of Z results" text
            updateDeletedItemsCounter();
        });

        // Function to update active items counter
        function updateActiveItemsCounter() {
            const totalRows = $('#itemsTable tbody tr').length;
            const visibleRows = $('#itemsTable tbody tr:visible').length;
            const counterText = `Showing ${visibleRows} of ${totalRows} results`;
            $('#activeItems .card-body .d-flex:first-child div:first-child').text(counterText);
        }

        // Function to update deleted items counter
        function updateDeletedItemsCounter() {
            const totalRows = $('#deletedItemsTable tbody tr').length;
            const visibleRows = $('#deletedItemsTable tbody tr:visible').length;
            const counterText = `Showing ${visibleRows} of ${totalRows} results`;
            $('#deletedItems .card-body .d-flex:first-child div:first-child').text(counterText);
        }

        // Clear search and reset view when switching between active and deleted
        $('#activeRecordsBtn').on('click', function() {
            $('#searchActiveItems').val('').trigger('keyup');
            $('#activeItems').show();
            $('#deletedItems').hide();
            $(this).addClass('active');
            $('#showDeletedBtn').removeClass('active');
        });

        $('#showDeletedBtn').on('click', function() {
            $('#searchDeletedItems').val('').trigger('keyup');
            $('#deletedItems').show();
            $('#activeItems').hide();
            $(this).addClass('active');
            $('#activeRecordsBtn').removeClass('active');
        });

        // Success/Error messages with Toast
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });

        @if(Session::has('success'))
            Toast.fire({
                icon: 'success',
                title: @json(Session::get('success'))
            });
        @endif

        @if(Session::has('error'))
            Toast.fire({
                icon: 'error',
                title: @json(Session::get('error'))
            });
        @endif

        // Initialize delete modals with static backdrop
        const deleteModals = document.querySelectorAll('[id^="deleteModal"]');
        deleteModals.forEach(modal => {
            const bsModal = new bootstrap.Modal(modal, {
                backdrop: 'static',
                keyboard: false
            });

            // Prevent modal from closing when clicking outside
            $(modal).on('mousedown', function(e) {
                if ($(e.target).is('.modal')) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            });
        });

        // Delete confirmation handler
        $('.delete-item').click(function(e) {
            e.preventDefault();
            const itemId = $(this).data('item-id');
            const itemName = $(this).data('item-name');
            const stocksAvailable = $(this).data('stocks');

            // Show the static delete modal instead of SweetAlert
            $(`#deleteModal${itemId}`).modal('show');
        });

        // Restore confirmation handler
        $('.restore-item').click(function(e) {
            e.preventDefault();
            const itemId = $(this).data('item-id');
            const itemName = $(this).data('item-name');

            Swal.fire({
                title: 'Restore Item?',
                html: `Are you sure you want to restore item: <strong>${itemName}</strong>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, restore it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = "{{ url('/inventory/items') }}/" + itemId + "/restore";
                    form.innerHTML = `
                        @csrf
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });

        // Initialize export modal with static backdrop
        const exportModal = document.getElementById('exportModal');
        if (exportModal) {
            const bsModal = new bootstrap.Modal(exportModal, {
                backdrop: 'static',
                keyboard: false
            });

            // Prevent modal from closing when clicking outside
            $(exportModal).on('mousedown', function(e) {
                if ($(e.target).is('.modal')) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            });

            // Handle export form submission
            $('#exportForm').on('submit', function() {
                // Close the modal
                $('#exportModal').modal('hide');
            });
        }
    });
</script>
@endsection

@section('styles')
<style>
    .table-responsive {
        overflow-x: auto;
    }
    
    #itemsTable, #deletedItemsTable {
        min-width: 100%;
        width: auto;
    }
    
    .dataTables_wrapper {
        overflow-x: auto;
    }

    .btn-blue {
        background-color: #0d6efd;
        color: white;
    }
    
    .btn-blue:hover {
        background-color: #0b5ed7;
        color: white;
    }

    .btn-blue .bi-pencil {
        color: #fff;
    }
</style>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@push('scripts')
<script>
// Add the deleteResource function
function deleteResource(url, resourceName) {
    Swal.fire({
        title: 'Delete Item?',
        html: `Are you sure you want to delete <strong>${resourceName}</strong>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create and submit form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            form.style.display = 'none';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            const method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'DELETE';
            
            form.appendChild(csrfToken);
            form.appendChild(method);
            document.body.appendChild(form);
            
            // Show loading state
            Swal.fire({
                title: 'Deleting...',
                text: 'Please wait',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
            
            form.submit();
        }
    });
}

// Run this when document is ready
$(document).ready(function() {
    // Remove the old delete confirmation handler
    $('.delete-item').off('click');
    
    // Add new delete handler using the global function
    $('.delete-item').on('click', function(e) {
        e.preventDefault();
        const itemId = $(this).data('item-id');
        const itemName = $(this).data('item-name');
        deleteResource('{{ url('/inventory/items') }}/' + itemId, itemName);
    });
});
</script>
@endpush
