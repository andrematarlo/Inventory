@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">POS Reports Dashboard</h5>
                    <div>
                        <button class="btn btn-sm btn-light" onclick="showTab('sales')">
                            <i class="bi bi-graph-up"></i> Sales Reports
                        </button>
                        <button class="btn btn-sm btn-light" onclick="showTab('deposits')">
                            <i class="bi bi-wallet2"></i> Deposit Reports
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Content -->
    <div id="tabContent">
        <!-- Dashboard Tab -->
        <div id="dashboardTab" class="tab-pane active">
    <div class="row">
        <!-- Summary Cards -->
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow-sm border-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Sales Summary</h6>
                            <h3 class="mb-0">Sales Reports</h3>
                            <p class="text-muted">View detailed sales data and trends</p>
                        </div>
                        <div class="bg-light p-3 rounded">
                            <i class="bi bi-cash text-primary" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                            <button onclick="showTab('sales')" class="btn btn-primary mt-3 w-100">View Sales</button>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow-sm border-success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Deposit Summary</h6>
                            <h3 class="mb-0">Deposit Reports</h3>
                            <p class="text-muted">Track student deposits and withdrawals</p>
                        </div>
                        <div class="bg-light p-3 rounded">
                            <i class="bi bi-wallet2 text-success" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                            <button onclick="showTab('deposits')" class="btn btn-success mt-3 w-100">View Deposits</button>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card shadow-sm border-info h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Inventory Summary</h6>
                            <h3 class="mb-0">Inventory Reports</h3>
                            <p class="text-muted">Monitor stock levels and consumption</p>
                        </div>
                        <div class="bg-light p-3 rounded">
                            <i class="bi bi-box-seam text-info" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                    <a href="#" class="btn btn-info mt-3 w-100">View Inventory</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0">Report Categories</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="bi bi-graph-up-arrow text-primary" style="font-size: 2.5rem;"></i>
                                    </div>
                                    <h5>Daily Sales</h5>
                                    <p class="text-muted small">View day-by-day sales performance</p>
                                            <button onclick="showTab('sales')" class="btn btn-sm btn-outline-primary">View Report</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="bi bi-currency-exchange text-success" style="font-size: 2.5rem;"></i>
                                    </div>
                                    <h5>Transaction History</h5>
                                    <p class="text-muted small">Detailed transaction logs</p>
                                            <button onclick="showTab('sales')" class="btn btn-sm btn-outline-success">View Report</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="bi bi-people text-danger" style="font-size: 2.5rem;"></i>
                                    </div>
                                    <h5>Student Activity</h5>
                                    <p class="text-muted small">Track student purchasing patterns</p>
                                            <button onclick="showTab('sales')" class="btn btn-sm btn-outline-danger">View Report</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 col-lg-3 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="bi bi-bar-chart text-warning" style="font-size: 2.5rem;"></i>
                                    </div>
                                    <h5>Popular Items</h5>
                                    <p class="text-muted small">Most frequently purchased items</p>
                                            <button onclick="showTab('sales')" class="btn btn-sm btn-outline-warning">View Report</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Report Tab -->
        <div id="salesTab" class="tab-pane" style="display: none;">
            <div class="container-fluid px-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">Sales Report</h5>
                    <button class="btn btn-sm btn-light" onclick="showTab('dashboard')">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </button>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-funnel me-1"></i>
                        Filter Options
                    </div>
                    <div class="card-body">
                        <form id="salesReportForm" class="row g-3">
                            <div class="col-md-4">
                                <label for="date_range" class="form-label">Date Range</label>
                                <select name="date_range" id="date_range" class="form-select" onchange="toggleCustomDateInputs()">
                                    <option value="today">Today</option>
                                    <option value="yesterday">Yesterday</option>
                                    <option value="last7days">Last 7 Days</option>
                                    <option value="last30days">Last 30 Days</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                            </div>
                            <div id="custom_date_container" class="col-md-8" style="display: none;">
                                <div class="col-md-6">
                                    <label for="date_from" class="form-label">From Date</label>
                                    <input type="date" class="form-control" id="date_from" name="date_from">
                                </div>
                                <div class="col-md-6">
                                    <label for="date_to" class="form-label">To Date</label>
                                    <input type="date" class="form-control" id="date_to" name="date_to">
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                                <button type="button" class="btn btn-secondary" onclick="resetSalesForm()">Reset</button>
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
                                    <i class="bi bi-cash-stack fa-2x text-white-50"></i>
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
                                    <i class="bi bi-cart-check fa-2x text-white-50"></i>
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
                                    <i class="bi bi-graph-up fa-2x text-white-50"></i>
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
                                    <i class="bi bi-box-seam fa-2x text-white-50"></i>
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
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-graph-up-arrow me-1"></i>
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
                                <i class="bi bi-pie-chart me-1"></i>
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
                        <i class="bi bi-trophy me-1"></i>
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
                        <i class="bi bi-table me-1"></i>
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
        </div>

        <!-- Deposits Report Tab -->
        <div id="depositsTab" class="tab-pane" style="display: none;">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Deposit Report</h5>
                    <button class="btn btn-sm btn-light" onclick="showTab('dashboard')">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </button>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('pos.reports.deposits') }}" class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date" value="{{ request('start_date', now()->subDays(30)->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date" value="{{ request('end_date', now()->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-success me-2">Generate Report</button>
                            <a href="{{ route('pos.reports.deposits') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Student ID</th>
                                    <th>Transaction Type</th>
                                    <th>Amount</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deposits ?? [] as $deposit)
                                <tr>
                                    <td>{{ $deposit->created_at->format('M d, Y') }}</td>
                                    <td>{{ $deposit->student_id }}</td>
                                    <td>{{ $deposit->transaction_type }}</td>
                                    <td>₱{{ number_format($deposit->amount, 2) }}</td>
                                    <td>₱{{ number_format($deposit->balance, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No deposit data found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let salesTrendChart, paymentMethodsChart;

function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-pane').forEach(tab => {
        tab.style.display = 'none';
    });
    
    // Show selected tab
    document.getElementById(tabName + 'Tab').style.display = 'block';
    
    // Initialize charts if showing sales tab
    if (tabName === 'sales') {
        initializeCharts();
        fetchSalesData();
    }
}

function initializeCharts() {
    // Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    salesTrendChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Daily Sales',
                data: [],
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                borderColor: 'rgba(13, 110, 253, 1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '₱' + context.raw.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Payment Methods Chart
    const paymentCtx = document.getElementById('paymentMethodsChart').getContext('2d');
    paymentMethodsChart = new Chart(paymentCtx, {
        type: 'doughnut',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [
                    'rgba(13, 110, 253, 0.8)',
                    'rgba(25, 135, 84, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(108, 117, 125, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.raw;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `₱${value.toLocaleString()} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
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

function fetchSalesData() {
    const form = document.getElementById('salesReportForm');
    const formData = new FormData(form);
    
    // Show loading state
    document.querySelector('.table-responsive tbody').innerHTML = '<tr><td colspan="9" class="text-center">Loading...</td></tr>';
    
    fetch(`{{ route('pos.reports.sales') }}?${new URLSearchParams(formData)}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Update summary cards
        document.querySelector('.bg-primary .fs-4').textContent = `₱${parseFloat(data.totals.total_sales).toLocaleString('en-US', {minimumFractionDigits: 2})}`;
        document.querySelector('.bg-warning .fs-4').textContent = data.totals.total_orders;
        document.querySelector('.bg-success .fs-4').textContent = `₱${parseFloat(data.totals.average_order).toLocaleString('en-US', {minimumFractionDigits: 2})}`;
        document.querySelector('.bg-info .fs-4').textContent = data.totals.total_items;

        // Update sales trend chart
        salesTrendChart.data.labels = data.chartData.labels;
        salesTrendChart.data.datasets[0].data = data.chartData.data;
        salesTrendChart.update();

        // Update payment methods chart
        paymentMethodsChart.data.labels = data.paymentMethods.map(item => item.PaymentMethod);
        paymentMethodsChart.data.datasets[0].data = data.paymentMethods.map(item => item.total_amount);
        paymentMethodsChart.update();

        // Update top selling items table
        const topItemsHtml = data.topItems.map(item => `
            <tr>
                <td>${item.ItemName}</td>
                <td>${item.total_quantity}</td>
                <td class="text-end">₱${parseFloat(item.total_revenue).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                <td>
                    <div class="progress">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: ${(item.total_revenue / data.totals.total_sales) * 100}%">
                            ${((item.total_revenue / data.totals.total_sales) * 100).toFixed(1)}%
                        </div>
                    </div>
                </td>
            </tr>
        `).join('');
        document.querySelector('.card:has(.bi-trophy) .table-responsive tbody').innerHTML = topItemsHtml;

        // Update sales table
        const salesHtml = data.sales.map(sale => `
            <tr>
                <td><a href="{{ route('pos.orders.show', '') }}/${sale.OrderID}" class="text-decoration-none">${sale.OrderID}</a></td>
                <td>${new Date(sale.created_at).toLocaleString()}</td>
                <td>${sale.ItemName}</td>
                <td class="text-end">${sale.Quantity}</td>
                <td class="text-end">₱${parseFloat(sale.UnitPrice).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                <td class="text-end">₱${parseFloat(sale.Subtotal).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                <td>${sale.StudentID || 'Walk-in'}</td>
                <td>${sale.PaymentMethod ? sale.PaymentMethod.charAt(0).toUpperCase() + sale.PaymentMethod.slice(1) : 'Unknown'}</td>
                <td>
                    <span class="badge bg-${sale.Status === 'COMPLETED' ? 'success' : sale.Status === 'PENDING' ? 'warning' : sale.Status === 'CANCELLED' ? 'danger' : 'secondary'}">
                        ${sale.Status}
                    </span>
                </td>
            </tr>
        `).join('');
        document.querySelector('.card:has(.bi-table) .table-responsive tbody').innerHTML = salesHtml;

        // Update pagination
        document.querySelector('.card:has(.bi-table) .d-flex.justify-content-between').innerHTML = `
            <div>
                Showing ${data.sales.firstItem || 0} to ${data.sales.lastItem || 0} of ${data.sales.total || 0} entries
            </div>
            <div>
                ${data.sales.links || ''}
            </div>
        `;
    })
    .catch(error => {
        console.error('Error:', error);
        document.querySelector('.table-responsive tbody').innerHTML = 
            '<tr><td colspan="9" class="text-center text-danger">Error loading data. Please try again.</td></tr>';
    });
}

function resetSalesForm() {
    const form = document.getElementById('salesReportForm');
    form.reset();
    document.getElementById('custom_date_container').style.display = 'none';
    fetchSalesData();
}

// Check URL parameters to show specific tab on page load
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    if (tab) {
        showTab(tab);
    }

    // Initialize sales report form
    document.getElementById('salesReportForm').addEventListener('submit', function(e) {
        e.preventDefault();
        fetchSalesData();
    });
});
</script>
@endpush
@endsection 