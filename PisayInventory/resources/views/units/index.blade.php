@extends('layouts.app')

@section('title', 'Units')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Units Management</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUnitModal">
            <i class="bi bi-plus-lg"></i> Add New Unit
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="unitsTable">
                    <thead>
                        <tr>
                            <th class="text-center">Actions</th>
                            <th>Unit Name</th>
                            <th>Created By</th>
                            <th>Date Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($units as $unit)
                        <tr>
                            <td class="text-center">
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editUnitModal{{ $unit->UnitOfMeasureId }}">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteUnitModal{{ $unit->UnitOfMeasureId }}">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </td>
                            <td>{{ $unit->UnitName }}</td>
                            <td>
                                @if($unit->createdBy)
                                    {{ $unit->createdBy->Username }}
                                @else
                                    System
                                @endif
                            </td>
                            <td>{{ $unit->DateCreated ? date('M d, Y h:i A', strtotime($unit->DateCreated)) : 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">No units found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Unit Modal -->
<div class="modal fade" id="addUnitModal" tabindex="-1" aria-labelledby="addUnitModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUnitModalLabel">Add New Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('units.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="UnitName" class="form-label">Unit Name</label>
                        <input type="text" class="form-control" id="UnitName" name="UnitName" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Unit Modal -->
@foreach($units as $unit)
<div class="modal fade" id="editUnitModal{{ $unit->UnitOfMeasureId }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('units.update', ['unit' => $unit->UnitOfMeasureId]) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="UnitName{{ $unit->UnitOfMeasureId }}" class="form-label">Unit Name</label>
                        <input type="text" class="form-control" id="UnitName{{ $unit->UnitOfMeasureId }}" 
                               name="UnitName" value="{{ $unit->UnitName }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Unit Modal -->
<div class="modal fade" id="deleteUnitModal{{ $unit->UnitOfMeasureId }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('units.destroy', ['unit' => $unit->UnitOfMeasureId]) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Are you sure you want to delete this unit: <strong>{{ $unit->UnitName }}</strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        // Destroy existing DataTable if it exists
        if ($.fn.DataTable.isDataTable('#unitsTable')) {
            $('#unitsTable').DataTable().destroy();
        }
        
        // Initialize DataTable
        const table = $('#unitsTable').DataTable({
            pageLength: 10,
            ordering: true,
            order: [[3, 'desc']], // Sort by date created by default
            responsive: true,
            destroy: true, // Allow table to be destroyed and recreated
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search units..."
            },
            columnDefs: [
                { orderable: false, targets: 0 } // Disable sorting on actions column
            ]
        });

        // Handle modal events to prevent DataTable issues
        $('.modal').on('hidden.bs.modal', function () {
            if ($.fn.DataTable.isDataTable('#unitsTable')) {
                table.draw();
            }
        });
    });
</script>
@endsection 