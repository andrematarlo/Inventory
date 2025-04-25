<div class="modal fade" id="viewMenuItemModal{{ $item->MenuItemID }}" tabindex="-1" aria-labelledby="viewMenuItemModalLabel{{ $item->MenuItemID }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewMenuItemModalLabel{{ $item->MenuItemID }}">View Menu Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Item Details</h6>
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">Name</dt>
                                    <dd class="col-sm-8">{{ $item->ItemName }}</dd>

                                    <dt class="col-sm-4">Price</dt>
                                    <dd class="col-sm-8">₱{{ number_format($item->Price, 2) }}</dd>

                                    <dt class="col-sm-4">Category</dt>
                                    <dd class="col-sm-8">{{ $item->classification->ClassificationName ?? 'N/A' }}</dd>

                                    <dt class="col-sm-4">Unit</dt>
                                    <dd class="col-sm-8">{{ $item->unit->UnitName ?? 'N/A' }}</dd>

                                    <dt class="col-sm-4">Stock</dt>
                                    <dd class="col-sm-8">{{ $item->StocksAvailable }}</dd>

                                    <dt class="col-sm-4">Status</dt>
                                    <dd class="col-sm-8">
                                        <span class="badge bg-{{ $item->StocksAvailable > 0 ? 'success' : 'danger' }}">
                                            {{ $item->StocksAvailable > 0 ? 'In Stock' : 'Out of Stock' }}
                                        </span>
                                    </dd>

                                    <dt class="col-sm-4">Available</dt>
                                    <dd class="col-sm-8">
                                        <span class="badge bg-{{ $item->IsAvailable ? 'success' : 'danger' }}">
                                            {{ $item->IsAvailable ? 'Yes' : 'No' }}
                                        </span>
                                    </dd>

                                    <dt class="col-sm-4">Type</dt>
                                    <dd class="col-sm-8">
                                        <span class="badge bg-{{ $item->IsValueMeal ? 'info' : 'secondary' }}">
                                            {{ $item->IsValueMeal ? 'Value Meal' : 'Regular Item' }}
                                        </span>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Image</h6>
                                @if($item->image_path)
                                    <img src="{{ asset('storage/' . $item->image_path) }}" 
                                         alt="{{ $item->ItemName }}"
                                         class="img-fluid rounded">
                                @else
                                    <div class="text-center text-muted py-5">
                                        <i class="fas fa-image fa-3x mb-3"></i>
                                        <p>No image available</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Description</h6>
                                <p class="mb-0">{{ $item->Description ?: 'No description available' }}</p>
                            </div>
                        </div>
                    </div>
                    @if($item->IsValueMeal)
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Value Meal Items</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Item</th>
                                                    <th>Quantity</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($item->valueMealItems as $valueMealItem)
                                                    <tr>
                                                        <td>{{ $valueMealItem->menuItem->ItemName }}</td>
                                                        <td>{{ $valueMealItem->quantity }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div> 