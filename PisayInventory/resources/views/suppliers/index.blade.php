@extends('layouts.app')

@section('title', 'Suppliers')

@section('additional_styles')
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

    .suppliers-table .table th:nth-child(2), /* Supplier Name */
    .suppliers-table .table td:nth-child(2) {
        width: 20%;
        min-width: 200px;
    }

    .suppliers-table .table th:nth-child(3), /* Contact Number */
    .suppliers-table .table td:nth-child(3) {
        width: 15%;
        min-width: 150px;
    }

    .suppliers-table .table th:nth-child(4), /* Address */
    .suppliers-table .table td:nth-child(4) {
        width: 25%;
        min-width: 250px;
    }

    .suppliers-table .table th:nth-child(5), /* Created */
    .suppliers-table .table td:nth-child(5) {
        width: 10%;
        min-width: 100px;
    }

    .suppliers-table .table th:nth-child(6), /* Modified */
    .suppliers-table .table td:nth-child(6) {
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
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Suppliers Management</h2>
        <button type="button" class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
            <i class="bi bi-plus-lg me-2"></i>Add New Supplier
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="suppliersTable">
                    <thead>
                        <tr>
                            <th style="width: 10%">Actions</th>
                            <th style="width: 20%">Supplier Name</th>
                            <th style="width: 15%">Contact Number</th>
                            <th style="width: 25%">Address</th>
                            <th style="width: 10%">Created</th>
                            <th style="width: 10%">Modified</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $supplier)
                        <tr>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary d-flex align-items-center" 
                                        data-bs-toggle="modal" data-bs-target="#editSupplierModal{{ $supplier->SupplierID }}">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </button>
                                <form action="{{ route('suppliers.destroy', $supplier->SupplierID) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger d-flex align-items-center" 
                                            onclick="return confirm('Are you sure you want to delete this supplier?')">
                                        <i class="bi bi-trash me-1"></i> Delete
                                    </button>
                                </form>
                            </td>
                            <td class="align-middle">{{ $supplier->SupplierName }}</td>
                            <td class="align-middle">{{ $supplier->ContactNum }}</td>
                            <td class="align-middle">{{ $supplier->Address }}</td>
                            <td class="align-middle">
                                <small>
                                    {{ Carbon\Carbon::parse($supplier->DateCreated)->format('M d, Y h:i A') }}<br>
                                    <span class="text-muted">By: {{ $supplier->created_by_user->Username ?? 'N/A' }}</span>
                                </small>
                            </td>
                            <td class="align-middle">
                                <small>
                                    {{ Carbon\Carbon::parse($supplier->DateModified)->format('M d, Y h:i A') }}<br>
                                    <span class="text-muted">By: {{ $supplier->modified_by_user->Username ?? 'N/A' }}</span>
                                </small>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">No suppliers found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add this after your main suppliers table -->
    <div class="card mt-4">
        <div class="card-header">
            <h3>Deleted Suppliers</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="trashedSuppliersTable">
                    <thead>
                        <tr>
                            <th>Actions</th>
                            <th>Supplier Name</th>
                            <th>Contact Number</th>
                            <th>Address</th>
                            <th>Created</th>
                            <th>Deleted</th>
                            <th>Modified</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trashedSuppliers as $supplier)
                        <tr>
                            <td>
                                <form action="{{ route('suppliers.restore', $supplier->SupplierID) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success d-flex align-items-center" title="Restore">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Restore
                                    </button>
                                </form>
                            </td>
                            <td>{{ $supplier->SupplierName }}</td>
                            <td>{{ $supplier->ContactNum }}</td>
                            <td>{{ $supplier->Address }}</td>
                            <td>
                                <small>
                                    {{ Carbon\Carbon::parse($supplier->DateCreated)->format('M d, Y h:i A') }}<br>
                                    <span class="text-muted">By: {{ $supplier->created_by_user->Username ?? 'N/A' }}</span>
                                </small>
                            </td>
                            <td>
                                <small>
                                    {{ $supplier->DateDeleted ? date('M d, Y h:i A', strtotime($supplier->DateDeleted)) : 'N/A' }}<br>
                                    <span class="text-muted">By: {{ $supplier->deleted_by_user->Username ?? 'N/A' }}</span>
                                </small>
                            </td>
                            <td>
                                <small>
                                    {{ Carbon\Carbon::parse($supplier->DateModified)->format('M d, Y h:i A') }}<br>
                                    <span class="text-muted">By: {{ $supplier->modified_by_user->Username ?? 'N/A' }}</span>
                                </small>
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

<!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('suppliers.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Supplier Name</label>
                        <input type="text" class="form-control" name="SupplierName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Number</label>
                        <input type="text" class="form-control" name="ContactNum">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="Address" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Supplier Modals -->
@foreach($suppliers as $supplier)
<div class="modal fade" id="editSupplierModal{{ $supplier->SupplierID }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('suppliers.update', $supplier->SupplierID) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Supplier Name</label>
                        <input type="text" class="form-control" name="SupplierName" value="{{ $supplier->SupplierName }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Number</label>
                        <input type="text" class="form-control" name="ContactNum" value="{{ $supplier->ContactNum }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" name="Address" rows="3">{{ $supplier->Address }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- Delete Supplier Modals -->
@foreach($suppliers as $supplier)
<div class="modal fade" id="deleteSupplierModal{{ $supplier->SupplierID }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('suppliers.destroy', $supplier->SupplierID) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Are you sure you want to delete this supplier?</p>
                    <p class="text-danger"><strong>{{ $supplier->SupplierName }}</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection

@section('scripts')
<script>
    function updateDates() {
        document.querySelectorAll('.datetime-cell').forEach(cell => {
            const timestamp = parseInt(cell.getAttribute('data-timestamp'));
            if (timestamp) {
                const date = new Date(timestamp + (8 * 60 * 60 * 1000)); // Add 8 hours for PHT
                const hours = date.getHours().toString().padStart(2, '0');
                const minutes = date.getMinutes().toString().padStart(2, '0');
                const ampm = hours >= 12 ? 'PM' : 'AM';
                const formattedHours = (hours % 12) || 12;
                
                const formatted = `${date.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                })} ${formattedHours}:${minutes} ${ampm}`;
                
                cell.textContent = formatted;
            }
        });
    }

    // Update dates every second
    setInterval(updateDates, 1000);

    // Initial update
    updateDates();

    // Initialize DataTables with existing configuration
    $(document).ready(function() {
        // Initialize suppliers table
        if ($.fn.DataTable.isDataTable('#suppliersTable')) {
            $('#suppliersTable').DataTable().destroy();
        }
        
        $('#suppliersTable').DataTable({
            pageLength: 10,
            ordering: true,
            responsive: true,
            columnDefs: [
                { orderable: false, targets: 0 }
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search suppliers...",
                lengthMenu: "Show _MENU_ suppliers per page",
                info: "Showing _START_ to _END_ of _TOTAL_ suppliers",
                infoEmpty: "Showing 0 to 0 of 0 suppliers",
                infoFiltered: "(filtered from _MAX_ total suppliers)"
            },
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            drawCallback: function(settings) {
                $('.dataTables_wrapper .row').addClass('g-3');
                $('.dataTables_length select').addClass('form-select form-select-sm');
                $('.dataTables_filter input').addClass('form-control form-control-sm');
                $('.dataTables_info').addClass('text-muted');
                $('.pagination').addClass('pagination-sm');
            }
        });

        // Initialize trashed suppliers table
        if ($.fn.DataTable.isDataTable('#trashedSuppliersTable')) {
            $('#trashedSuppliersTable').DataTable().destroy();
        }

        $('#trashedSuppliersTable').DataTable({
            pageLength: 10,
            ordering: true,
            responsive: true,
            columnDefs: [
                { orderable: false, targets: -1 }
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search deleted suppliers...",
                lengthMenu: "Show _MENU_ deleted suppliers per page",
                info: "Showing _START_ to _END_ of _TOTAL_ deleted suppliers",
                infoEmpty: "Showing 0 to 0 of 0 deleted suppliers",
                infoFiltered: "(filtered from _MAX_ total deleted suppliers)"
            },
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            drawCallback: function(settings) {
                $('.dataTables_wrapper .row').addClass('g-3');
                $('.dataTables_length select').addClass('form-select form-select-sm');
                $('.dataTables_filter input').addClass('form-control form-control-sm');
                $('.dataTables_info').addClass('text-muted');
                $('.pagination').addClass('pagination-sm');
            }
        });
    });
</script>
@endsection 