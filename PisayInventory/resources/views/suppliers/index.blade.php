@extends('layouts.app')

@section('title', 'Suppliers')

@section('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<!-- Add SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<style>
    /* Custom styles for suppliers table */
    .suppliers-table {
        margin-top: 1rem;
    }

    .suppliers-table .table {
        font-size: 0.95rem;
    }

    .suppliers-table .table th {
        padding: 1rem;
        font-weight: 600;
        background-color: #f8fafc;
        color: #1e293b;
        white-space: nowrap;
    }

    .suppliers-table .table td {
        padding: 1rem;
        vertical-align: middle;
    }

    /* Table container */
    .table-responsive {
        margin: 0;
        border-radius: 0.375rem;
        box-shadow: 0 0 10px rgba(0,0,0,0.02);
    }

    /* Striped rows */
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0,0,0,.02);
    }

    /* Hover effect */
    .table-hover tbody tr:hover {
        background-color: rgba(0,0,0,.04);
    }

    /* Action buttons */
    .btn-group {
        white-space: nowrap;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .d-flex.gap-1 {
        gap: 0.25rem !important;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    /* Search box styling */
    .search-box {
        max-width: 300px;
        margin-bottom: 1rem;
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

    /* Custom scrollbar */
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

    /* Select2 Custom Styles */
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
        border: 1px solid #ced4da;
    }
    
    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__rendered {
        padding: 0 6px;
    }
    
    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 2px 8px;
        margin: 2px;
        display: flex;
        align-items: center;
    }

    .select2-container--bootstrap-5 .select2-results__option {
        padding: 8px 12px;
        display: flex;
        align-items: center;
    }

    .select2-container--bootstrap-5 .select2-results__option .bi-box {
        margin-right: 8px;
        font-size: 1.1em;
    }

    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice .bi-box {
        margin-right: 6px;
    }
    
    .select2-container--bootstrap-5 .select2-search__field {
        margin-top: 4px;
    }
    
    .select2-results__option--highlighted {
        background-color: #e9ecef !important;
        color: #000 !important;
    }
</style>
@endsection

@section('content')
<!-- Add CSRF Token meta tag -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Suppliers Management</h2>
        @if($userPermissions && $userPermissions->CanAdd)
        <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
            <i class="bi bi-plus-circle"></i> Add Supplier
        </button>
        @endif
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

    <!-- Active Suppliers Section -->
    <div class="card">
        <div id="activeSuppliers" class="card-body">
            <!-- Search Box -->
            <div class="search-box">
                <input type="text" id="activeSearchInput" class="form-control" placeholder="Search suppliers...">
            </div>

            <!-- Pagination Info -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>Showing {{ $activeSuppliers->firstItem() ?? 0 }} to {{ $activeSuppliers->lastItem() ?? 0 }} of {{ $activeSuppliers->total() }} results</div>
                <div class="pagination-sm">
                    @if($activeSuppliers->currentPage() > 1)
                        <a href="{{ $activeSuppliers->previousPageUrl() }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-chevron-left"></i> Previous
                        </a>
                    @endif
                    
                    @for($i = 1; $i <= $activeSuppliers->lastPage(); $i++)
                        <a href="{{ $activeSuppliers->url($i) }}" 
                           class="btn btn-sm {{ $i == $activeSuppliers->currentPage() ? 'btn-primary' : 'btn-outline-secondary' }}">
                            {{ $i }}
                        </a>
                    @endfor

                    @if($activeSuppliers->hasMorePages())
                        <a href="{{ $activeSuppliers->nextPageUrl() }}" class="btn btn-outline-secondary btn-sm">
                            Next <i class="bi bi-chevron-right"></i>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Active Suppliers Table -->
            <div class="table-responsive">
                <table class="table table-hover" id="suppliersTable">
                    <thead>
                        <tr>
                            @if($userPermissions && ($userPermissions->CanEdit || $userPermissions->CanDelete))
                            <th>Actions</th>
                            @endif
                            <th>Company Name</th>
                            <th>Contact Person</th>
                            <th>Telephone</th>
                            <th>Contact Number</th>
                            <th>Address</th>
                            <th>Items Supplied</th>
                            <th>Created By</th>
                            <th>Date Created</th>
                            <th>Modified By</th>
                            <th>Date Modified</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activeSuppliers as $supplier)
                            <tr>
                                @if($userPermissions && ($userPermissions->CanEdit || $userPermissions->CanDelete))
                                <td>
                                    <div class="d-flex gap-2">
                                        @if($userPermissions->CanEdit)
                                        <button type="button" 
                                                class="btn btn-primary btn-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editSupplierModal{{ $supplier->SupplierID }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        @endif
                                        @if($userPermissions->CanDelete)
                                        <form action="{{ route('suppliers.destroy', $supplier->SupplierID) }}" 
                                              method="POST" 
                                              class="d-inline delete-supplier-form">
                                            @csrf
                                            @method('DELETE')
                                        <button type="button" 
                                                    class="btn btn-danger btn-sm delete-supplier-btn" 
                                                data-supplier-id="{{ $supplier->SupplierID }}"
                                                data-supplier-name="{{ $supplier->CompanyName }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                                @endif
                                <td>{{ $supplier->CompanyName }}</td>
                                <td>{{ $supplier->ContactPerson }}</td>
                                <td>{{ $supplier->TelephoneNumber }}</td>
                                <td>{{ $supplier->ContactNum }}</td>
                                <td>{{ $supplier->Address }}</td>
                                <td>
                                    @if($supplier->items->count() > 0)
                                        <ul class="list-unstyled mb-0">
                                            @foreach($supplier->items as $item)
                                                <li>{{ $item->ItemName }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-muted">No items</span>
                                    @endif
                                </td>
                                <td>{{ $supplier->createdBy->Username ?? 'N/A' }}</td>
                                <td>{{ $supplier->DateCreated ? date('Y-m-d H:i:s', strtotime($supplier->DateCreated)) : 'N/A' }}</td>
                                <td>{{ $supplier->modifiedBy->Username ?? 'N/A' }}</td>
                                <td>{{ $supplier->DateModified ? date('Y-m-d H:i:s', strtotime($supplier->DateModified)) : 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-success">Active</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center">No suppliers found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Deleted Suppliers Section -->
    <div id="deletedSuppliers" class="card-body" style="display: none;">
        <!-- Search Box -->
        <div class="search-box">
            <input type="text" id="deletedSearchInput" class="form-control" placeholder="Search deleted suppliers...">
        </div>

        <!-- Deleted Suppliers Table -->
        <div class="table-responsive">
            <table class="table table-hover" id="deletedSuppliersTable">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Company Name</th>
                        <th>Contact Person</th>
                        <th>Telephone</th>
                        <th>Contact Number</th>
                        <th>Address</th>
                        <th>Items Supplied</th>
                        <th>Deleted Date</th>
                        <th>Created By</th>
                        <th>Modified By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deletedSuppliers as $supplier)
                        <tr>
                            <td>
                                <form action="{{ route('suppliers.restore', $supplier->SupplierID) }}" 
                                      method="POST" 
                                      class="d-inline restore-supplier-form">
                                    @csrf
                                    <button type="button" 
                                            class="btn btn-success btn-sm restore-supplier-btn"
                                            data-supplier-id="{{ $supplier->SupplierID }}"
                                            data-supplier-name="{{ $supplier->CompanyName }}">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                </form>
                            </td>
                            <td>{{ $supplier->CompanyName }}</td>
                            <td>{{ $supplier->ContactPerson }}</td>
                            <td>{{ $supplier->TelephoneNumber }}</td>
                            <td>{{ $supplier->ContactNum }}</td>
                            <td>{{ $supplier->Address }}</td>
                            <td>
                                @if($supplier->items->count() > 0)
                                    <ul class="list-unstyled mb-0">
                                        @foreach($supplier->items as $item)
                                            <li>{{ $item->ItemName }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-muted">No items</span>
                                @endif
                            </td>
                            <td>{{ $supplier->DateDeleted ? date('Y-m-d H:i:s', strtotime($supplier->DateDeleted)) : 'N/A' }}</td>
                            <td>{{ $supplier->created_by_user->Username ?? 'N/A' }}</td>
                            <td>{{ $supplier->modified_by_user->Username ?? 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">No deleted suppliers found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($userPermissions && $userPermissions->CanAdd)
    @include('suppliers.partials.add-modal')
@endif

@if($userPermissions && $userPermissions->CanEdit)
    @foreach($activeSuppliers as $supplier)
        @include('suppliers.partials.edit-modal', ['supplier' => $supplier])
    @endforeach
@endif  

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteSupplierModal" tabindex="-1" aria-labelledby="deleteSupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSupplierModalLabel">Delete Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this supplier?</p>
                <p id="supplierNameToDelete" class="fw-bold"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteSupplierForm" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Restore Modal Template -->
<div class="modal fade" 
     id="restoreSupplierModal" 
     data-bs-backdrop="static" 
     data-bs-keyboard="false" 
     tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Restore Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to restore this supplier?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmRestoreBtn">Restore</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Add jQuery if not already included -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Add SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        // Initialize Select2 for items dropdowns
        function initializeSelect2(element) {
            $(element).select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $(element).closest('.modal'),
                placeholder: 'Search items',
                allowClear: true,
                minimumInputLength: 0,
                ajax: {
                    url: '{{ route("items.search") }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            search: params.term || '',
                            page: params.page || 1
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.items.map(function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            })
                        };
                    },
                    cache: true
                },
                templateResult: function(item) {
                    if (!item.id) return item.text;
                    return $('<div><i class="bi bi-box"></i> ' + item.text + '</div>');
                },
                templateSelection: function(item) {
                    if (!item.id) return item.text;
                    return $('<div><i class="bi bi-box"></i> ' + item.text + '</div>');
                },
                escapeMarkup: function(markup) {
                    return markup;
                }
            });
        }

        // Initialize Select2 for all items-select elements
        $('.items-select').each(function() {
            initializeSelect2(this);
        });

        // Reinitialize Select2 when edit modal is shown
        $('.modal').on('shown.bs.modal', function() {
            const select = $(this).find('.items-select');
            if (select.length) {
                select.select2('destroy');
                initializeSelect2(select);
            }
        });

        // Show active records by default
        $('#activeSuppliers').show();
        $('#deletedSuppliers').hide();

        // Toggle between active and deleted records
        $('#activeRecordsBtn').click(function() {
            $(this).addClass('active');
            $('#showDeletedBtn').removeClass('active');
            $('#activeSuppliers').show();
            $('#deletedSuppliers').hide();
        });

        $('#showDeletedBtn').click(function() {
            $(this).addClass('active');
            $('#activeRecordsBtn').removeClass('active');
            $('#activeSuppliers').hide();
            $('#deletedSuppliers').show();
        });

        // Search functionality for active suppliers
        $('#activeSearchInput').on('keyup', function() {
            let searchText = $(this).val().toLowerCase();
            $('#suppliersTable tbody tr').each(function() {
                let rowText = $(this).text().toLowerCase();
                $(this).toggle(rowText.indexOf(searchText) > -1);
            });
        });

        // Search functionality for deleted suppliers
        $('#deletedSearchInput').on('keyup', function() {
            let searchText = $(this).val().toLowerCase();
            $('#deletedSuppliersTable tbody tr').each(function() {
                let rowText = $(this).text().toLowerCase();
                $(this).toggle(rowText.indexOf(searchText) > -1);
            });
        });

        // Delete supplier handler
        $('.delete-supplier-btn').on('click', function(e) {
                    e.preventDefault();
            const button = $(this);
            const supplierName = button.data('supplier-name');
            const form = button.closest('.delete-supplier-form');

            Swal.fire({
                title: 'Delete Supplier',
                html: `Are you sure you want to delete supplier:<br><strong>${supplierName}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait while we delete the supplier.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                            form.submit();
                        }
                    });
                }
            });
        });

        // Restore supplier handler
        $('.restore-supplier-btn').on('click', function(e) {
            e.preventDefault();
            const button = $(this);
            const supplierName = button.data('supplier-name');
            const form = button.closest('.restore-supplier-form');

            Swal.fire({
                title: 'Restore Supplier',
                html: `Are you sure you want to restore supplier:<br><strong>${supplierName}</strong>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, restore it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Restoring...',
                        text: 'Please wait while we restore the supplier.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                        showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                            form.submit();
                        }
                    });
                }
            });
        });
    });
</script>
@endsection 