<div class="modal fade" id="viewMenuItemModal{{ $item->MenuItemID }}" tabindex="-1" aria-labelledby="viewMenuItemModalLabel{{ $item->MenuItemID }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewMenuItemModalLabel{{ $item->MenuItemID }}">View Menu Item</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Image Column -->
                    <div class="col-md-4 text-center mb-3">
                        @if($item->image_path)
                            <img src="{{ asset('storage/' . $item->image_path) }}" 
                                 alt="{{ $item->ItemName }}"
                                 class="img-fluid rounded"
                                 style="max-height: 200px;">
                        @else
                            <div class="border rounded p-4 text-muted">
                                <i class="fas fa-image fa-3x mb-2"></i>
                                <p class="mb-0">No image available</p>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Details Column -->
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="fw-bold">Item Name</label>
                            <p class="mb-2">{{ $item->ItemName }}</p>
                        </div>

                        <div class="mb-3">
                            <label class="fw-bold">Category</label>
                            <p class="mb-2">{{ $item->classification->ClassificationName ?? 'N/A' }}</p>
                        </div>

                        <div class="mb-3">
                            <label class="fw-bold">Description</label>
                            <p class="mb-2">{{ $item->Description ?: 'No description available' }}</p>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Price</label>
                                <p class="mb-2">₱{{ number_format($item->Price, 2) }}</p>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Stock Available</label>
                                <p class="mb-2">
                                    <span class="badge bg-{{ $item->StocksAvailable > 0 ? 'success' : 'danger' }}">
                                        {{ $item->StocksAvailable }} units
                                    </span>
                                </p>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="fw-bold">Status</label>
                            <p class="mb-2">
                                <span class="badge bg-{{ $item->StocksAvailable > 0 ? 'success' : 'danger' }}">
                                    {{ $item->StocksAvailable > 0 ? 'In Stock' : 'Out of Stock' }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" 
                        class="btn btn-primary" 
                        data-bs-dismiss="modal"
                        data-bs-toggle="modal" 
                        data-bs-target="#editMenuItemModal{{ $item->MenuItemID }}">
                    <i class="fas fa-edit"></i> Edit
                </button>
            </div>
        </div>
    </div>
</div> 