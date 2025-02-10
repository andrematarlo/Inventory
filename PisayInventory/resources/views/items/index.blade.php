@extends('layouts.app')

@section('title', 'Items')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Items Management</h2>
        <button type="button" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addItemModal">
            <i class="bi bi-plus-lg me-1"></i> Add Item
        </button>
    </div>

    <!-- Active Items Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Active Items</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Classification</th>
                            <th>Unit</th>
                            <th>Supplier</th>
                            <th>Stocks</th>
                            <th>Reorder Point</th>
                            <th>Created</th>
                            <th>Modified</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td>
                                @if($item->ImagePath)
                                    <img src="{{ asset('storage/' . $item->ImagePath) }}" 
                                         alt="{{ $item->ItemName }}" 
                                         class="img-thumbnail" 
                                         style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center" 
                                         style="width: 50px; height: 50px;">
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td>{{ $item->ItemName }}</td>
                            <td>{{ $item->Description }}</td>
                            <td>{{ $item->classification->ClassificationName ?? 'N/A' }}</td>
                            <td>{{ $item->unitOfMeasure->UnitName ?? 'N/A' }}</td>
                            <td>{{ $item->supplier->SupplierName ?? 'N/A' }}</td>
                            <td>{{ $item->StocksAvailable }}</td>
                            <td>{{ $item->ReorderPoint }}</td>
                            <td>
                                <small>
                                    {{ $item->DateCreated ? date('M d, Y h:i A', strtotime($item->DateCreated)) : 'N/A' }}<br>
                                    <span class="text-muted">By: {{ $item->created_by_user->Username ?? 'N/A' }}</span>
                                </small>
                            </td>
                            <td>
                                <small>
                                    {{ $item->DateModified ? date('M d, Y h:i A', strtotime($item->DateModified)) : 'N/A' }}<br>
                                    <span class="text-muted">By: {{ $item->modified_by_user->Username ?? 'N/A' }}</span>
                                </small>
                            </td>
                            <td class="text-end d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-primary d-flex align-items-center" 
                                        data-bs-toggle="modal" data-bs-target="#editItemModal{{ $item->ItemId }}">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </button>
                                <form action="{{ route('items.destroy', $item->ItemId) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger d-flex align-items-center" 
                                            onclick="return confirm('Are you sure you want to delete this item?')">
                                        <i class="bi bi-trash me-1"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center py-4">No items found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $items->links() }}
            </div>
        </div>
    </div>

    <!-- Deleted Items Card -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Deleted Items</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Classification</th>
                            <th>Created</th>
                            <th>Modified</th>
                            <th>Deleted</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trashedItems as $item)
                        <tr>
                            <td>{{ $item->ItemName }}</td>
                            <td>{{ $item->Description }}</td>
                            <td>{{ $item->classification->ClassificationName ?? 'N/A' }}</td>
                            <td>
                                <small>
                                    {{ $item->DateCreated ? date('M d, Y h:i A', strtotime($item->DateCreated)) : 'N/A' }}<br>
                                    <span class="text-muted">By: {{ $item->created_by_user->Username ?? 'N/A' }}</span>
                                </small>
                            </td>
                            <td>
                                <small>
                                    {{ $item->DateModified ? date('M d, Y h:i A', strtotime($item->DateModified)) : 'N/A' }}<br>
                                    <span class="text-muted">By: {{ $item->modified_by_user->Username ?? 'N/A' }}</span>
                                </small>
                            </td>
                            <td>
                                <small>
                                    {{ $item->DateDeleted ? date('M d, Y h:i A', strtotime($item->DateDeleted)) : 'N/A' }}<br>
                                    <span class="text-muted">By: {{ $item->deleted_by_user->Username ?? 'N/A' }}</span>
                                </small>
                            </td>
                            <td class="text-end">
                                <form action="{{ route('items.restore', $item->ItemId) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success d-flex align-items-center">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Restore
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="bi bi-trash2 text-muted mb-2" style="font-size: 2rem;"></i>
                                    <p class="text-muted mb-0">No deleted items found</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $trashedItems->links() }}
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
