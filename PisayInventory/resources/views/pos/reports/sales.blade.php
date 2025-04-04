@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Sales Report</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('pos.reports.deposits') }}">Deposit Reports</a></li>
        <li class="breadcrumb-item active">Sales</li>
    </ol>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter me-1"></i>
            Filter Options
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('pos.reports.sales') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="date_range" class="form-label">Date Range</label>
                    <select name="date_range" id="date_range" class="form-select" onchange="toggleCustomDateInputs()">
                        <option value="today" {{ $dateRange == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="yesterday" {{ $dateRange == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option value="last7days" {{ $dateRange == 'last7days' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="last30days" {{ $dateRange == 'last30days' ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="custom" {{ $dateRange == 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>
                <div id="custom_date_container" class="col-md-8" style="display: {{ $dateRange == 'custom' ? 'flex' : 'none' }}">
                    <div class="col-md-6">
                        <label for="date_from" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" value="{{ $startDate ? $startDate->format('Y-m-d') : '' }}">
                    </div>
                    <div class="col-md-6">
                        <label for="date_to" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" value="{{ $endDate ? $endDate->format('Y-m-d') : '' }}">
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="{{ route('pos.reports.sales') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Total Sales</div>
                            <div class="fs-4 fw-bold">₱{{ number_format($totals->total_sales ?? 0, 2) }}</div>
                        </div>
                        <i class="fas fa-cash-register fa-2x text-white-50"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span class="small text-white">Period Total</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Total Orders</div>
                            <div class="fs-4 fw-bold">{{ $totals->total_orders ?? 0 }}</div>
                        </div>
                        <i class="fas fa-shopping-cart fa-2x text-white-50"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span class="small text-white">Number of Transactions</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Average Order Value</div>
                            <div class="fs-4 fw-bold">₱{{ number_format(($totals->total_orders ?? 0) > 0 ? ($totals->total_sales ?? 0) / ($totals->total_orders ?? 1) : 0, 2) }}</div>
                        </div>
                        <i class="fas fa-chart-line fa-2x text-white-50"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span class="small text-white">Per Transaction</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Total Items Sold</div>
                            <div class="fs-4 fw-bold">{{ $totals->total_items ?? 0 }}</div>
                        </div>
                        <i class="fas fa-box fa-2x text-white-50"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span class="small text-white">Across All Orders</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Top Selling Items
                </div>
                <div class="card-body">
                    <div id="topItemsCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            @foreach($topItems->take(3) as $key => $item)
                                <div class="carousel-item {{ $key === 0 ? 'active' : '' }}">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            @if($item->image_path)
                                                <img src="{{ asset('storage/' . $item->image_path) }}" 
                                                     class="d-block w-100 rounded" 
                                                     alt="{{ $item->ItemName }}"
                                                     style="height: 300px; object-fit: cover;">
                                            @else
                                                <div class="d-block w-100 rounded bg-light d-flex align-items-center justify-content-center" 
                                                     style="height: 300px;">
                                                    <i class="fas fa-image fa-4x text-muted"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <h3 class="mb-3">{{ $item->ItemName }}</h3>
                                            <div class="mb-3">
                                                <span class="badge bg-primary fs-5">{{ $item->total_quantity }} units sold</span>
                                            </div>
                                            <div class="mb-3">
                                                <h4 class="text-success">₱{{ number_format($item->total_revenue, 2) }}</h4>
                                                <p class="text-muted">Total Revenue</p>
                                            </div>
                                            <div class="progress mb-3">
                                                <div class="progress-bar bg-success" role="progressbar" 
                                                     style="width: {{ ($item->total_revenue / ($totals->total_sales ?: 1)) * 100 }}%">
                                                    {{ number_format(($item->total_revenue / ($totals->total_sales ?: 1)) * 100, 1) }}% of total sales
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#topItemsCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#topItemsCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-line me-1"></i>
                    Sales Trend
                </div>
                <div class="card-body">
                    <div class="btn-group mb-3 w-100" role="group">
                        <button type="button" class="btn btn-outline-primary active" onclick="showSalesData('daily')">Daily</button>
                        <button type="button" class="btn btn-outline-primary" onclick="showSalesData('weekly')">Weekly</button>
                        <button type="button" class="btn btn-outline-primary" onclick="showSalesData('monthly')">Monthly</button>
                    </div>
                    <div id="dailySales" class="sales-data">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Date</th>
                                        <th class="text-end">Sales</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($chartData['daily']['labels'] as $key => $label)
                                        <tr>
                                            <td>{{ $label }}</td>
                                            <td class="text-end fw-bold">₱{{ number_format($chartData['daily']['data'][$key], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="weeklySales" class="sales-data" style="display: none;">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Week</th>
                                        <th class="text-end">Sales</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($chartData['weekly']['labels'] as $key => $label)
                                        <tr>
                                            <td>{{ $label }}</td>
                                            <td class="text-end fw-bold">₱{{ number_format($chartData['weekly']['data'][$key], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="monthlySales" class="sales-data" style="display: none;">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Month</th>
                                        <th class="text-end">Sales</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($chartData['monthly']['labels'] as $key => $label)
                                        <tr>
                                            <td>{{ $label }}</td>
                                            <td class="text-end fw-bold">₱{{ number_format($chartData['monthly']['data'][$key], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Top Selling Items -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-trophy me-1"></i>
            Top Selling Items
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Item Name</th>
                            <th>Quantity Sold</th>
                            <th>Total Revenue</th>
                            <th>% of Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topItems as $item)
                        <tr>
                            <td>{{ $item->ItemName }}</td>
                            <td>{{ $item->total_quantity }}</td>
                            <td class="text-end">₱{{ number_format($item->total_revenue, 2) }}</td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: {{ ($item->total_revenue / ($totals->total_sales ?: 1)) * 100 }}%">
                                        {{ number_format(($item->total_revenue / ($totals->total_sales ?: 1)) * 100, 1) }}%
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">No sales data available for the selected period.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Sales Table -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Sales Data
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Order ID</th>
                            <th>Date & Time</th>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Subtotal</th>
                            <th>Student</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                        <tr>
                            <td><a href="{{ route('pos.orders.show', $sale->OrderID) }}" class="text-decoration-none">{{ $sale->OrderID }}</a></td>
                            <td>{{ \Carbon\Carbon::parse($sale->created_at)->format('M d, Y g:i A') }}</td>
                            <td>{{ $sale->ItemName }}</td>
                            <td class="text-end">{{ $sale->Quantity }}</td>
                            <td class="text-end">₱{{ number_format($sale->UnitPrice, 2) }}</td>
                            <td class="text-end">₱{{ number_format($sale->Subtotal, 2) }}</td>
                            <td>{{ $sale->StudentID ?? 'Walk-in' }}</td>
                            <td>{{ ucfirst($sale->PaymentMethod ?? 'Unknown') }}</td>
                            <td>
                                @if($sale->Status == 'COMPLETED')
                                    <span class="badge bg-success">Completed</span>
                                @elseif($sale->Status == 'PENDING')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($sale->Status == 'CANCELLED')
                                    <span class="badge bg-danger">Cancelled</span>
                                @else
                                    <span class="badge bg-secondary">{{ $sale->Status }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">No sales data available for the selected period.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing {{ $sales->firstItem() ?? 0 }} to {{ $sales->lastItem() ?? 0 }} of {{ $sales->total() }} entries
                </div>
                <div>
                    {{ $sales->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function showSalesData(type) {
        // Hide all sales data
        document.querySelectorAll('.sales-data').forEach(el => {
            el.style.display = 'none';
        });
        
        // Show selected sales data
        document.getElementById(type + 'Sales').style.display = 'block';
        
        // Update button states
        document.querySelectorAll('.btn-group .btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.target.classList.add('active');
    }

    function toggleCustomDateInputs() {
        const dateRange = document.getElementById('date_range').value;
        const customDateContainer = document.getElementById('custom_date_container');
        
        if (dateRange === 'custom') {
            customDateContainer.style.display = 'flex';
        } else {
            customDateContainer.style.display = 'none';
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize the carousel with auto-play
        const carouselElement = document.getElementById('topItemsCarousel');
        const carousel = new bootstrap.Carousel(carouselElement, {
            interval: 4000,
            pause: 'hover',
            wrap: true
        });
        
        // Start the carousel
        carousel.cycle();

        // Add click event listeners to the buttons
        document.querySelectorAll('.btn-group .btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const type = this.getAttribute('onclick').match(/'([^']+)'/)[1];
                showSalesData(type);
            });
        });
    });
</script>
@endsection 