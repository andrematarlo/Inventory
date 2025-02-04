<div>
    <!-- Active Items Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">Add Item</button>
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
                <div>
                    Showing {{ $items->firstItem() ?? 0 }} to {{ $items->lastItem() ?? 0 }} of {{ $items->total() }} entries
                </div>
                <div>
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Deleted Items Card -->
    <div class="card">
        <div class="card-body">
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
                <div>
                    Showing {{ $trashedItems->firstItem() ?? 0 }} to {{ $trashedItems->lastItem() ?? 0 }} of {{ $trashedItems->total() }} entries
                </div>
                <div>
                    {{ $trashedItems->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
