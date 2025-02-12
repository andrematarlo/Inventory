@extends('layouts.app')

@section('title', 'Items')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Items Management</h2>
        <div>
            <button class="btn btn-outline-secondary" type="button" id="toggleButton">
                <i class="bi bi-archive"></i> <span id="buttonText">Show Deleted</span>
            </button>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                <i class="bi bi-plus-lg"></i> Add Item
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

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
                                <th>Supplier</th>
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
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" 
                                                    class="btn btn-sm btn-warning"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editItemModal{{ $item->ItemId }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteModal{{ $item->ItemId }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
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
                                    <td>{{ $item->supplier->CompanyName ?? 'N/A' }}</td>
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
                                <th>Supplier</th>
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
                                    <td>{{ $item->supplier->CompanyName ?? 'N/A' }}</td>
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

@include('items.partials.add-modal')
@include('items.partials.delete-modal')
@foreach($activeItems as $item)
    @include('items.partials.edit-modal', ['item' => $item])
@endforeach
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize DataTables
        const activeTable = $('#itemsTable').DataTable({
            responsive: true,
            pageLength: 10,
            order: [[2, 'asc']], // Order by name column
        });

        const deletedTable = $('#deletedItemsTable').DataTable({
            responsive: true,
            pageLength: 10,
            order: [[2, 'asc']], // Order by name column
        });

        // Toggle between active and deleted items
        $('#toggleButton').on('click', function() {
            const activeDiv = $('#activeItems');
            const deletedDiv = $('#deletedItems');
            const buttonText = $('#buttonText');
            const button = $(this);

            if (activeDiv.is(':visible')) {
                activeDiv.hide();
                deletedDiv.show();
                buttonText.text('Show Active');
                button.removeClass('btn-outline-secondary').addClass('btn-outline-primary');
                deletedTable.columns.adjust().draw();
            } else {
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
