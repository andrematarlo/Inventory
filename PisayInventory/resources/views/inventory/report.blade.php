<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Inventory Report</h2>
            <p class="text-muted">Period: {{ $start_date }} - {{ $end_date }}</p>
        </div>
        <div class="btn-group">
            <button class="btn btn-primary active">All Movements</button>
            <button class="btn btn-success">Stock In</button>
            <button class="btn btn-danger">Stock Out</button>
            <button class="btn btn-secondary">🖨️ Print</button>
            <button class="btn btn-primary">Download PDF</button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6>Total Movements</h6>
                    <h2>{{ $total_movements }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6>Total Stock In</h6>
                    <h2>{{ $total_stock_in }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6>Total Stock Out</h6>
                    <h2>{{ $total_stock_out }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6>Unique Items</h6>
                    <h2>{{ $unique_items }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Movements Table -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Stock Movements</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Item</th>
                            <th>Classification</th>
                            <th>Movement</th>
                            <th>Created By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($movements as $movement)
                        <tr>
                            <td>{{ $movement->created_at->format('M d, Y h:i A') }}</td>
                            <td>{{ $movement->item->ItemName }}</td>
                            <td>{{ $movement->item->classification->ClassificationName }}</td>
                            <td>
                                @if($movement->movement_type == 'in')
                                    <span class="badge bg-success">+{{ $movement->quantity }}</span>
                                @else
                                    <span class="badge bg-danger">-{{ $movement->quantity }}</span>
                                @endif
                            </td>
                            <td>{{ $movement->created_by_user->name ?? 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Current Stock Levels -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Current Stock Levels</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Classification</th>
                            <th>Current Stock</th>
                            <th>Stock In</th>
                            <th>Stock Out</th>
                            <th>Net Movement</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stock_levels as $stock)
                        <tr>
                            <td>{{ $stock->item->ItemName }}</td>
                            <td>{{ $stock->item->classification->ClassificationName }}</td>
                            <td>{{ $stock->current_stock }}</td>
                            <td>{{ $stock->total_stock_in }}</td>
                            <td>{{ $stock->total_stock_out }}</td>
                            <td>{{ $stock->net_movement }}</td>
                            <td>{{ $stock->status }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div> 