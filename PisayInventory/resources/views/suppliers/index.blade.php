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
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Suppliers</h2>
        <div>
            <button class="btn btn-outline-secondary" type="button" id="toggleButton">
                <i class="bi bi-archive"></i> <span id="buttonText">Show Deleted</span>
            </button>
            <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
                <i class="bi bi-plus"></i> Add Supplier
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Active Suppliers Table -->
    <div id="activeSuppliers">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Active Suppliers</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="suppliersTable">
                        <thead>
                            <tr>
                                <th>Company Name</th>
                                <th>Contact Person</th>
                                <th>Telephone</th>
                                <th>Mobile</th>
                                <th>Address</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activeSuppliers as $supplier)
                                <tr>
                                    <td>{{ $supplier->CompanyName }}</td>
                                    <td>{{ $supplier->ContactPerson }}</td>
                                    <td>{{ $supplier->TelephoneNumber }}</td>
                                    <td>{{ $supplier->ContactNum }}</td>
                                    <td>{{ $supplier->Address }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('suppliers.edit', $supplier->SupplierID) }}" 
                                               class="btn btn-sm btn-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('suppliers.destroy', $supplier->SupplierID) }}" 
                                                  method="POST" 
                                                  class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" 
                                                        onclick="return confirm('Are you sure you want to delete this supplier?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No active suppliers found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Deleted Suppliers Table -->
    <div id="deletedSuppliers" style="display: none;">
        <div class="card mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Deleted Suppliers</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="deletedSuppliersTable">
                        <thead>
                            <tr>
                                <th>Company Name</th>
                                <th>Contact Person</th>
                                <th>Telephone</th>
                                <th>Mobile</th>
                                <th>Address</th>
                                <th>Deleted Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($deletedSuppliers as $supplier)
                                <tr>
                                    <td>{{ $supplier->CompanyName }}</td>
                                    <td>{{ $supplier->ContactPerson }}</td>
                                    <td>{{ $supplier->TelephoneNumber }}</td>
                                    <td>{{ $supplier->ContactNum }}</td>
                                    <td>{{ $supplier->Address }}</td>
                                    <td>{{ $supplier->DateDeleted }}</td>
                                    <td>
                                        <form action="{{ route('suppliers.restore', $supplier->SupplierID) }}" 
                                              method="POST" 
                                              class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No deleted suppliers found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
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
            }
        });

        const deletedTable = $('#deletedSuppliersTable').DataTable({
            pageLength: 10,
            responsive: true,
            dom: '<"datatable-header"<"dataTables_length"l><"dataTables_filter"f>>' +
                 't' +
                 '<"datatable-footer"<"dataTables_info"i><"dataTables_paginate"p>>',
            language: {
                search: "Search:",
                searchPlaceholder: "Search suppliers..."
            }
        });

        // Toggle functionality
        $('#toggleButton').on('click', function() {
            console.log('Toggle button clicked'); // Debug log
            
            const activeDiv = $('#activeSuppliers');
            const deletedDiv = $('#deletedSuppliers');
            const buttonText = $('#buttonText');
            const button = $(this);

            console.log('Active visible:', activeDiv.is(':visible')); // Debug log

            if (activeDiv.is(':visible')) {
                console.log('Switching to deleted'); // Debug log
                activeDiv.hide();
                deletedDiv.show();
                buttonText.text('Show Active');
                button.removeClass('btn-outline-secondary').addClass('btn-outline-primary');
                deletedTable.columns.adjust().draw();
            } else {
                console.log('Switching to active'); // Debug log
                deletedDiv.hide();
                activeDiv.show();
                buttonText.text('Show Deleted');
                button.removeClass('btn-outline-primary').addClass('btn-outline-secondary');
                activeTable.columns.adjust().draw();
            }
        });
    });
</script>
@endsection 