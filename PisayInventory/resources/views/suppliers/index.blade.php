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
        width: 25%;
        min-width: 200px;
    }

    .suppliers-table .table th:nth-child(3), /* Contact Number */
    .suppliers-table .table td:nth-child(3) {
        width: 15%;
        min-width: 150px;
    }

    .suppliers-table .table th:nth-child(4), /* Address */
    .suppliers-table .table td:nth-child(4) {
        width: 30%;
        min-width: 250px;
    }

    .suppliers-table .table th:nth-child(5), /* Created By */
    .suppliers-table .table td:nth-child(5) {
        width: 10%;
        min-width: 100px;
    }

    .suppliers-table .table th:nth-child(6), /* Date Created */
    .suppliers-table .table td:nth-child(6) {
        width: 10%;
        min-width: 150px;
    }

    .suppliers-table .table th:nth-child(7), /* Modified By */
    .suppliers-table .table td:nth-child(7) {
        width: 10%;
        min-width: 100px;
    }

    .suppliers-table .table th:nth-child(8), /* Date Modified */
    .suppliers-table .table td:nth-child(8) {
        width: 10%;
        min-width: 150px;
    }

    /* Hover effect for rows */
    .suppliers-table .table tbody tr:hover {
        background-color: #f1f5f9;
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
                            <th style="width: 10%; text-align: center">Actions</th>
                            <th style="width: 20%">Supplier Name</th>
                            <th style="width: 15%">Contact Number</th>
                            <th style="width: 25%">Address</th>
                            <th style="width: 10%">Created By</th>
                            <th style="width: 10%">Date Created</th>
                            <th style="width: 10%">Modified By</th>
                            <th style="width: 10%">Date Modified</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $supplier)
                        <tr>
                            <td class="align-middle text-center">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editSupplierModal{{ $supplier->SupplierID }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('suppliers.destroy', $supplier->SupplierID) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this supplier?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            <td class="align-middle">{{ $supplier->SupplierName }}</td>
                            <td class="align-middle">{{ $supplier->ContactNum }}</td>
                            <td class="align-middle">{{ $supplier->Address }}</td>
                            <td class="align-middle">{{ $supplier->created_by_user->Username ?? 'N/A' }}</td>
                            <td class="align-middle">{{ Carbon\Carbon::parse($supplier->DateCreated)->format('Y-m-d h:i:s A') }}</td>
                            <td class="align-middle">{{ $supplier->modified_by_user->Username ?? 'N/A' }}</td>
                            <td class="align-middle">{{ Carbon\Carbon::parse($supplier->DateModified)->format('Y-m-d h:i:s A') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">No suppliers found</td>
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
                            <th>Supplier Name</th>
                            <th>Contact Number</th>
                            <th>Address</th>
                            <th>Deleted By</th>
                            <th>Date Deleted</th>
                            <th style="width: 10%; text-align: center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trashedSuppliers as $supplier)
                        <tr>
                            <td>{{ $supplier->SupplierName }}</td>
                            <td>{{ $supplier->ContactNum }}</td>
                            <td>{{ $supplier->Address }}</td>
                            <td>{{ $supplier->deleted_by_user->Username ?? 'N/A' }}</td>
                            <td>{{ $supplier->DateDeleted ? date('Y-m-d H:i', strtotime($supplier->DateDeleted)) : 'N/A' }}</td>
                            <td class="text-center">
                                <form action="{{ route('suppliers.restore', $supplier->SupplierID) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" title="Restore">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">No deleted suppliers found</td>
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