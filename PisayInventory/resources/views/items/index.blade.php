@extends('layouts.app')

@section('title', 'Items')

@section('content')
<div class="container">
    <!-- Active Items Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center">
                    <span>Show</span>
                    <select class="form-select form-select-sm mx-2" style="width: auto;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span>items per page</span>
                </div>
                <div class="d-flex gap-2">
                    <input type="search" class="form-control form-control-sm" placeholder="Search items...">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addItemModal">
                        <i class="bi bi-plus-lg"></i> Add Item
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Classification</th>
                            <th>Unit</th>
                            <th>Supplier</th>
                            <th>Stocks</th>
                            <th>Reorder Point</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td>{{ $item->ItemName }}</td>
                            <td>{{ $item->Description }}</td>
                            <td>{{ $item->classification->ClassificationName ?? 'N/A' }}</td>
                            <td>{{ $item->unitOfMeasure->UnitName ?? 'N/A' }}</td>
                            <td>{{ $item->supplier->SupplierName ?? 'N/A' }}</td>
                            <td>{{ $item->StocksAvailable }}</td>
                            <td>{{ $item->ReorderPoint }}</td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editItemModal{{ $item->ItemId }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('items.destroy', $item->ItemId) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this item?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">No items found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>Showing 1 to {{ $items->count() }} of {{ $items->count() }} items</div>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item disabled">
                            <span class="page-link">Previous</span>
                        </li>
                        <li class="page-item active">
                            <span class="page-link">1</span>
                        </li>
                        <li class="page-item disabled">
                            <span class="page-link">Next</span>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Deleted Items Card -->
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center">
                    <span>Show</span>
                    <select class="form-select form-select-sm mx-2" style="width: auto;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span>deleted items per page</span>
                </div>
                <input type="search" class="form-control form-control-sm" style="width: 200px;" placeholder="Search deleted items...">
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Classification</th>
                            <th>Deleted By</th>
                            <th>Date Deleted</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trashedItems as $item)
                        <tr>
                            <td>{{ $item->ItemName }}</td>
                            <td>{{ $item->Description }}</td>
                            <td>{{ $item->classification->ClassificationName ?? 'N/A' }}</td>
                            <td>{{ $item->deleted_by_user->Username ?? 'N/A' }}</td>
                            <td>{{ $item->DateDeleted ? date('M d, Y h:i A', strtotime($item->DateDeleted)) : 'N/A' }}</td>
                            <td class="text-end">
                                <form action="{{ route('items.restore', $item->ItemId) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="bi bi-trash text-muted" style="font-size: 2rem;"></i>
                                    <h5 class="mt-2 mb-1">No Deleted Items</h5>
                                    <p class="text-muted mb-0">Deleted items will appear here</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>Showing 1 to {{ $trashedItems->count() }} of {{ $trashedItems->count() }} deleted items</div>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item disabled">
                            <span class="page-link">Previous</span>
                        </li>
                        <li class="page-item active">
                            <span class="page-link">1</span>
                        </li>
                        <li class="page-item disabled">
                            <span class="page-link">Next</span>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Add Item Modal -->
@include('items.partials.add-modal')

<!-- Edit Item Modals -->
@foreach($items as $item)
    @include('items.partials.edit-modal', ['item' => $item])
@endforeach
@endsection