@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Sales Report</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('reports') }}">Reports</a></li>
        <li class="breadcrumb-item active">Sales</li>
    </ol>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter me-1"></i>
            Filter Options
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('reports.sales') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="date_range" class="form-label">Date Range</label>
                    <select name="date_range" id="date_range" class="form-select" onchange="toggleCustomDateInputs()">
                        <option value="today" {{ $dateRange == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="yesterday" {{ $dateRange == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option value="last7days" {{ $dateRange == 'last7days' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="last30days" {{ $dateRange == 'last30days' ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="thisMonth" {{ $dateRange == 'thisMonth' ? 'selected' : '' }}>This Month</option>
                        <option value="lastMonth" {{ $dateRange == 'lastMonth' ? 'selected' : '' }}>Last Month</option>
                        <option value="custom" {{ $dateRange == 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>
                <div id="custom_date_container" class="row g-3 mt-1" style="{{ $dateRange == 'custom' ? '' : 'display: none;' }}">
                    <div class="col-md-6">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                            value="{{ $startDate ? $startDate->format('Y-m-d') : '' }}">
                    </div>
                    <div class="col-md-6">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                            value="{{ $endDate ? $endDate->format('Y-m-d') : '' }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="payment_method" class="form-label">Payment Method</label>
                    <select name="payment_method" id="payment_method" class="form-select">
                        <option value="">All Payment Methods</option>
                        <option value="cash" {{ request()->payment_method == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="deposit" {{ request()->payment_method == 'deposit' ? 'selected' : '' }}>Deposit</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="status" class="form-label">Order Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="COMPLETED" {{ request()->status == 'COMPLETED' ? 'selected' : '' }}>Completed</option>
                        <option value="PENDING" {{ request()->status == 'PENDING' ? 'selected' : '' }}>Pending</option>
                        <option value="CANCELLED" {{ request()->status == 'CANCELLED' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="{{ route('reports.sales') }}" class="btn btn-secondary">Reset</a>
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
                            <div class="fs-4 fw-bold">₱{{ number_format($sales->sum('TotalAmount'), 2) }}</div>
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
                            <div class="fs-4 fw-bold">{{ $sales->total() }}</div>
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
                            <div class="small text-white-50">Average Order</div>
                            <div class="fs-4 fw-bold">₱{{ $sales->count() > 0 ? number_format($sales->sum('TotalAmount') / $sales->count(), 2) : '0.00' }}</div>
                        </div>
                        <i class="fas fa-chart-line fa-2x text-white-50"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span class="small text-white">Average Transaction Value</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Items Sold</div>
                            <div class="fs-4 fw-bold">{{ $itemCounts }}</div>
                        </div>
                        <i class="fas fa-utensils fa-2x text-white-50"></i>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <span class="small text-white">Total Items</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Sales Trend
                </div>
                <div class="card-body">
                    <canvas id="salesChart" height="225"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Payment Methods
                </div>
                <div class="card-body">
                    <canvas id="paymentMethodsChart" height="225"></canvas>
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
                        @forelse($topItems ?? [] as $item)
                        <tr>
                            <td>{{ $item->ItemName }}</td>
                            <td>{{ $item->total_quantity }}</td>
                            <td class="text-end">₱{{ number_format($item->total_revenue, 2) }}</td>
                            <td>
                                <div class="progress">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: {{ ($item->total_revenue / ($sales->sum('TotalAmount') ?: 1)) * 100 }}%">
                                        {{ number_format(($item->total_revenue / ($sales->sum('TotalAmount') ?: 1)) * 100, 1) }}%
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
                            <th>Student</th>
                            <th>Items</th>
                            <th>Payment Method</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                        <tr>
                            <td><a href="{{ route('pos.orders.show', $sale->OrderID) }}" class="text-decoration-none">{{ $sale->OrderID }}</a></td>
                            <td>{{ \Carbon\Carbon::parse($sale->created_at)->format('M d, Y g:i A') }}</td>
                            <td>{{ $sale->StudentID ?? 'Walk-in' }}</td>
                            <td>{{ $sale->item_count ?? 0 }}</td>
                            <td>{{ ucfirst($sale->PaymentMethod ?? 'Unknown') }}</td>
                            <td class="text-end">₱{{ number_format($sale->TotalAmount, 2) }}</td>
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
                            <td colspan="7" class="text-center">No sales data available for the selected period.</td>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
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
        // Sales Chart
        const chartLabels = JSON.parse('{{ json_encode($chartDataFormatted["labels"] ?? []) }}'.replace(/&quot;/g, '"'));
        const chartData = JSON.parse('{{ json_encode($chartDataFormatted["data"] ?? []) }}'.replace(/&quot;/g, '"'));
        
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Sales Amount (₱)',
                    data: chartData,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
        
        // Payment Methods Chart
        const paymentLabels = JSON.parse('{{ json_encode($paymentChartData["labels"] ?? []) }}'.replace(/&quot;/g, '"'));
        const paymentData = JSON.parse('{{ json_encode($paymentChartData["data"] ?? []) }}'.replace(/&quot;/g, '"'));
        
        const paymentCtx = document.getElementById('paymentMethodsChart').getContext('2d');
        new Chart(paymentCtx, {
            type: 'doughnut',
            data: {
                labels: paymentLabels,
                datasets: [{
                    data: paymentData,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
    });
</script>
@endsection 