@extends('layouts.app')

@section('title', 'Suppliers')

@section('styles')
<!-- Add DataTables CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">

<style>
    /* Custom styles for suppliers table */
    .suppliers-table {
        margin-top: 1rem;
    }

    .suppliers-table .table {
        font-size: 0.95rem; /* Slightly larger font */
    }

    .suppliers-table .table th {
        padding: 1rem;
        font-weight: 600;
        background-color: #f8fafc;
        color: #1e293b;
    }

    .suppliers-table .table td {
        padding: 1rem;
        vertical-align: middle;
    }

    /* Make the table take up more space */
    .suppliers-table .table-responsive {
        min-height: 500px;
    }

    /* Adjust column widths */
    .suppliers-table .table th:nth-child(1), /* Actions */
    .suppliers-table .table td:nth-child(1) {
        width: 10%;
        min-width: 120px;
        text-align: center;
    }

    .suppliers-table .table th:nth-child(2), /* Company Name */
    .suppliers-table .table td:nth-child(2) {
        width: 20%;
        min-width: 200px;
    }

    .suppliers-table .table th:nth-child(3), /* Contact Person */
    .suppliers-table .table td:nth-child(3) {
        width: 15%;
        min-width: 150px;
    }

    .suppliers-table .table th:nth-child(4), /* Telephone Number */
    .suppliers-table .table td:nth-child(4) {
        width: 15%;
        min-width: 150px;
    }

    .suppliers-table .table th:nth-child(5), /* Mobile Number */
    .suppliers-table .table td:nth-child(5) {
        width: 15%;
        min-width: 150px;
    }

    .suppliers-table .table th:nth-child(6), /* Address */
    .suppliers-table .table td:nth-child(6) {
        width: 25%;
        min-width: 250px;
    }

    .suppliers-table .table th:nth-child(7), /* Created By */
    .suppliers-table .table td:nth-child(7) {
        width: 10%;
        min-width: 100px;
    }

    .suppliers-table .table th:nth-child(8), /* Date Created */
    .suppliers-table .table td:nth-child(8) {
        width: 10%;
        min-width: 150px;
    }

    .suppliers-table .table th:nth-child(9), /* Modified By */
    .suppliers-table .table td:nth-child(9) {
        width: 10%;
        min-width: 100px;
    }

    .suppliers-table .table th:nth-child(10), /* Date Modified */
    .suppliers-table .table td:nth-child(10) {
        width: 10%;
        min-width: 150px;
    }

    /* Hover effect for rows */
    .suppliers-table .table tbody tr:hover {
        background-color: #f1f5f9;
    }
    .btn-icon-text {
        display: inline-flex;
        align-items: center;
        gap: 5px; /* Space between icon and text */
    }


    .btn-group .btn {
        margin-right: 8px;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }
    .btn-success {
        display: inline-flex;
        align-items: center;
        gap: 5px; /* Adds space between the icon and text */
        white-space: nowrap; /* Prevents wrapping */
    }

    /* Table styles */
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

    /* Sortable columns */
    .sortable {
        cursor: pointer;
    }

    .sortable i {
        font-size: 12px;
        color: #6c757d;
    }

    .sortable:hover i {
        color: #000;
    }

    /* Action buttons */
    .btn-group .btn {
        padding: 4px 8px;
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

    /* DataTables customization */
    .dataTables_wrapper .dataTables_length select {
        min-width: 60px;
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

    .btn-blue {
        background-color: #0d6efd;
        color: white;
    }
    
    .btn-blue:hover {
        background-color: #0b5ed7;
        color: white;
    }

    /* Remove ALL DataTables sorting indicators completely */
    table.dataTable thead > tr > th.sorting:before,
    table.dataTable thead > tr > th.sorting:after,
    table.dataTable thead > tr > th.sorting_asc:before,
    table.dataTable thead > tr > th.sorting_asc:after,
    table.dataTable thead > tr > th.sorting_desc:before,
    table.dataTable thead > tr > th.sorting_desc:after,
    table.dataTable thead > tr > td.sorting:before,
    table.dataTable thead > tr > td.sorting:after,
    table.dataTable thead > tr > td.sorting_asc:before,
    table.dataTable thead > tr > td.sorting_asc:after,
    table.dataTable thead > tr > td.sorting_desc:before,
    table.dataTable thead > tr > td.sorting_desc:after {
        opacity: 0 !important;
        content: '' !important;
        display: none !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Suppliers Management</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
            <i class="bi bi-plus-lg"></i> New Supplier
        </button>
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
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Actions</th>
                            <th>Company Name <i class="bi bi-arrow-down-up small-icon"></i></th>
                            <th>Contact Person <i class="bi bi-arrow-down-up small-icon"></i></th>
                            <th>Telephone <i class="bi bi-arrow-down-up small-icon"></i></th>
                            <th>Contact Number <i class="bi bi-arrow-down-up small-icon"></i></th>
                            <th>Address <i class="bi bi-arrow-down-up small-icon"></i></th>
                            <th>Created By <i class="bi bi-arrow-down-up small-icon"></i></th>
                            <th>Date Created <i class="bi bi-arrow-down-up small-icon"></i></th>
                            <th>Modified By <i class="bi bi-arrow-down-up small-icon"></i></th>
                            <th>Date Modified <i class="bi bi-arrow-down-up small-icon"></i></th>
                            <th>Status <i class="bi bi-arrow-down-up small-icon"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activeSuppliers as $supplier)
                            <tr>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button"
                                            class="btn btn-sm btn-blue"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editSupplierModal{{ $supplier->SupplierID }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-sm btn-danger" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteModal{{ $supplier->SupplierID }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>{{ $supplier->CompanyName }}</td>
                                <td>{{ $supplier->ContactPerson }}</td>
                                <td>{{ $supplier->TelephoneNumber }}</td>
                                <td>{{ $supplier->ContactNum }}</td>
                                <td>{{ $supplier->Address }}</td>
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
                                <td colspan="11" class="text-center">No suppliers found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Deleted Suppliers Section -->
    <div id="deletedSuppliers" class="card-body" style="display: none;">
        <div class="table-responsive">
            <table class="table table-hover" id="deletedSuppliersTable">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Company Name <i class="bi bi-arrow-down-up small-icon"></i></th>
                        <th>Contact Person <i class="bi bi-arrow-down-up small-icon"></i></th>
                        <th>Telephone <i class="bi bi-arrow-down-up small-icon"></i></th>
                        <th>Contact Number <i class="bi bi-arrow-down-up small-icon"></i></th>
                        <th>Address <i class="bi bi-arrow-down-up small-icon"></i></th>
                        <th>Deleted Date <i class="bi bi-arrow-down-up small-icon"></i></th>
                        <th>Created By <i class="bi bi-arrow-down-up small-icon"></i></th>
                        <th>Modified By <i class="bi bi-arrow-down-up small-icon"></i></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deletedSuppliers as $supplier)
                        <tr>
                            <td>
                                <form action="{{ route('suppliers.restore', $supplier->SupplierID) }}" 
                                      method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                </form>
                            </td>
                            <td>{{ $supplier->CompanyName }}</td>
                            <td>{{ $supplier->ContactPerson }}</td>
                            <td>{{ $supplier->TelephoneNumber }}</td>
                            <td>{{ $supplier->ContactNum }}</td>
                            <td>{{ $supplier->Address }}</td>
                            <td>{{ $supplier->DateDeleted ? date('Y-m-d H:i:s', strtotime($supplier->DateDeleted)) : 'N/A' }}</td>
                            <td>{{ $supplier->created_by_user->Username ?? 'N/A' }}</td>
                            <td>{{ $supplier->modified_by_user->Username ?? 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">No deleted suppliers found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('suppliers.partials.add-modal')
@include('suppliers.partials.delete-modal')
@foreach($activeSuppliers as $supplier)
    @include('suppliers.partials.edit-modal', ['supplier' => $supplier])
@endforeach
@endsection

@section('scripts')
<!-- Add jQuery if not already included -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Add DataTables JS -->
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>

<script>
    $(document).ready(function() {
        console.log('Document ready'); // Debug log

        // Initialize DataTables
        const activeTable = $('#suppliersTable').DataTable({
            pageLength: 10,
            responsive: true,
            dom: '<"datatable-header"<"dataTables_length"l><"dataTables_filter"f>>' +
                 't' +
                 '<"datatable-footer"<"dataTables_info"i><"dataTables_paginate"p>>',
            language: {
                search: "Search:",
                searchPlaceholder: "Search suppliers..."
            },
            columnDefs: [
                { className: "actions-column", targets: 0, width: "100px", orderable: false },
                { className: "name-column", targets: 1 },
                { className: "contact-person-column", targets: 2 },
                { className: "contact-number-column", targets: 3 },
                { className: "email-column", targets: 4 },
                { className: "address-column", targets: 5 },
                { className: "created-by-column", targets: 6 },
                { className: "created-date-column", targets: 7 }
            ],
            order: [[1, 'asc']], // Order by name column by default
        });

        const deletedTable = $('#deletedSuppliersTable').DataTable({
            pageLength: 10,
            responsive: {
                details: false
            },
            language: {
                search: "Search:",
                searchPlaceholder: "Search suppliers..."
            },
            "ordering": false,
            "order": [],
            "columnDefs": [{
                "orderable": false,
                "targets": "_all"
            }]
        });

        // Show active records by default
        $('#deletedSuppliers').hide();

        // Toggle between active and deleted records
        $('#activeRecordsBtn').click(function() {
            $('#activeSuppliers').show();
            $('#deletedSuppliers').hide();
            activeTable.columns.adjust().draw();
        });

        $('#showDeletedBtn').click(function() {
            $('#activeSuppliers').hide();
            $('#deletedSuppliers').show();
            deletedTable.columns.adjust().draw();
        });
    });
</script>
@endsection 