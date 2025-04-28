@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Sales Report
        <button onclick="printReport()" class="btn btn-primary float-end">
            <i class="fas fa-print me-1"></i> Print Report
        </button>
    </h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('pos.reports.deposits') }}">Deposit Reports</a></li>
        <li class="breadcrumb-item active">Sales</li>
    </ol>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-filter me-1"></i>
            Filter Options
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('pos.reports.sales') }}" class="row g-3">
                <!-- Date Range Filter -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="date_range" class="form-label fw-bold">
                            <i class="fas fa-calendar me-1"></i> Date Range
                        </label>
                        <select name="date_range" id="date_range" class="form-select" onchange="toggleCustomDateInputs()">
                            <option value="today" {{ $dateRange == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="yesterday" {{ $dateRange == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                            <option value="last7days" {{ $dateRange == 'last7days' ? 'selected' : '' }}>Last 7 Days</option>
                            <option value="last30days" {{ $dateRange == 'last30days' ? 'selected' : '' }}>Last 30 Days</option>
                            <option value="custom" {{ $dateRange == 'custom' ? 'selected' : '' }}>Custom Range</option>
                        </select>
                    </div>
                </div>

                <!-- Custom Date Range -->
                <div id="custom_date_container" class="col-md-8" style="display: {{ $dateRange == 'custom' ? 'flex' : 'none' }}">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="date_from" class="form-label fw-bold">
                                <i class="fas fa-calendar-alt me-1"></i> From Date
                            </label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="{{ $startDate ? $startDate->format('Y-m-d') : '' }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="date_to" class="form-label fw-bold">
                                <i class="fas fa-calendar-alt me-1"></i> To Date
                            </label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="{{ $endDate ? $endDate->format('Y-m-d') : '' }}">
                        </div>
                    </div>
                </div>

                <!-- Filter by Item -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="item_id" class="form-label fw-bold">
                            <i class="fas fa-box me-1"></i> Filter by Item
                        </label>
                        <select name="item_id" id="item_id" class="form-select select2">
                            <option value="">All Items</option>
                            @foreach($items ?? [] as $item)
                                <option value="{{ $item->id }}" {{ request()->item_id == $item->id ? 'selected' : '' }}>
                                    {{ $item->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Filter by Cashier -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="cashier_id" class="form-label fw-bold">
                            <i class="fas fa-user me-1"></i> Filter by Cashier
                        </label>
                        <select name="cashier_id" id="cashier_id" class="form-select select2">
                            <option value="">All Cashiers</option>
                            @foreach($cashiers ?? [] as $cashier)
                                <option value="{{ $cashier->id }}" {{ request()->cashier_id == $cashier->id ? 'selected' : '' }}>
                                    {{ $cashier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i> Apply Filters
                    </button>
                    <a href="{{ route('pos.reports.sales') }}" class="btn btn-secondary">
                        <i class="fas fa-undo me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- AJAX-replaceable report content -->
    <div id="report-content">
        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card bg-primary text-white mb-4 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small text-white-50">Total Sales</div>
                                <div class="display-6 fw-bold">₱{{ number_format($totals->total_sales ?? 0, 2) }}</div>
                            </div>
                            <i class="fas fa-cash-register fa-3x text-white-50"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <span class="small text-white">Period Total</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-warning text-white mb-4 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small text-white-50">Total Orders</div>
                                <div class="display-6 fw-bold">{{ $totals->total_orders ?? 0 }}</div>
                            </div>
                            <i class="fas fa-shopping-cart fa-3x text-white-50"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <span class="small text-white">Number of Transactions</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-success text-white mb-4 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small text-white-50">Average Order Value</div>
                                <div class="display-6 fw-bold">₱{{ number_format(($totals->total_orders ?? 0) > 0 ? ($totals->total_sales ?? 0) / ($totals->total_orders ?? 1) : 0, 2) }}</div>
                            </div>
                            <i class="fas fa-chart-line fa-3x text-white-50"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <span class="small text-white">Per Transaction</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-info text-white mb-4 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small text-white-50">Total Items Sold</div>
                                <div class="display-6 fw-bold">{{ $totals->total_items ?? 0 }}</div>
                            </div>
                            <i class="fas fa-box fa-3x text-white-50"></i>
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
                            <button type="button" class="btn btn-outline-primary" onclick="showSalesData('yearly')">Yearly</button>
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
                        <div id="yearlySales" class="sales-data" style="display: none;">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Year</th>
                                            <th class="text-end">Sales</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($chartData['yearly']['labels'] as $key => $label)
                                            <tr>
                                                <td>{{ $label }}</td>
                                                <td class="text-end fw-bold">₱{{ number_format($chartData['yearly']['data'][$key], 2) }}</td>
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
</div>

@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
    }
    .card {
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }
    .card-header {
        font-weight: 600;
    }
    .form-label {
        margin-bottom: 0.5rem;
    }
    .display-6 {
        font-size: 1.8rem;
    }
</style>
@endpush

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
        // AJAX filter for item dropdown
        $('#item_id').on('change', function() {
            var form = $(this).closest('form');
            $.ajax({
                url: form.attr('action'),
                type: 'GET',
                data: form.serialize(),
                success: function(response) {
                    var newContent = $(response).find('#report-content').html();
                    $('#report-content').html(newContent);
                    // Re-initialize Select2 for new content
                    $('.select2').select2({
                        theme: 'bootstrap-5',
                        width: '100%'
                    });
                }
            });
            return false;
        });
    });

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

        // Initialize Select2 with enhanced functionality
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });

        // Add change event listener for item filter
        $('#item_id').on('change', function() {
            const selectedItemId = $(this).val();
            filterSalesByItem(selectedItemId);
        });
    });

    // Function to filter sales by item
    function filterSalesByItem(itemId) {
        // Get all sales rows
        const salesRows = document.querySelectorAll('table tbody tr');
        
        // If no item is selected (All Items), show all rows
        if (!itemId) {
            salesRows.forEach(row => {
                row.style.display = '';
            });
            return;
        }
        
        // Get the selected item name from the dropdown
        const selectedItemName = $('#item_id option:selected').text();
        
        // Filter rows based on item name
        salesRows.forEach(row => {
            const itemNameCell = row.querySelector('td:nth-child(3)'); // Assuming item name is in the 3rd column
            if (itemNameCell && itemNameCell.textContent.trim() === selectedItemName.trim()) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
        
        // Update summary statistics based on filtered data
        updateSummaryStatistics();
    }
    
    // Function to update summary statistics based on filtered data
    function updateSummaryStatistics() {
        // Get visible rows (filtered data)
        const visibleRows = document.querySelectorAll('table tbody tr:not([style*="display: none"])');
        
        // Calculate totals
        let totalSales = 0;
        let totalOrders = 0;
        let totalItems = 0;
        
        // Track unique order IDs to count total orders
        const uniqueOrderIds = new Set();
        
        visibleRows.forEach(row => {
            // Get order ID (assuming it's in the first column)
            const orderId = row.querySelector('td:first-child').textContent.trim();
            uniqueOrderIds.add(orderId);
            
            // Get subtotal (assuming it's in the 6th column)
            const subtotalCell = row.querySelector('td:nth-child(6)');
            if (subtotalCell) {
                const subtotalText = subtotalCell.textContent.trim();
                const subtotal = parseFloat(subtotalText.replace('₱', '').replace(/,/g, ''));
                if (!isNaN(subtotal)) {
                    totalSales += subtotal;
                }
            }
            
            // Get quantity (assuming it's in the 4th column)
            const quantityCell = row.querySelector('td:nth-child(4)');
            if (quantityCell) {
                const quantity = parseInt(quantityCell.textContent.trim());
                if (!isNaN(quantity)) {
                    totalItems += quantity;
                }
            }
        });
        
        totalOrders = uniqueOrderIds.size;
        
        // Update summary cards
        document.querySelector('.bg-primary .fw-bold').textContent = '₱' + totalSales.toFixed(2);
        document.querySelector('.bg-warning .fw-bold').textContent = totalOrders;
        document.querySelector('.bg-success .fw-bold').textContent = '₱' + (totalOrders > 0 ? (totalSales / totalOrders).toFixed(2) : '0.00');
        document.querySelector('.bg-info .fw-bold').textContent = totalItems;
    }

    function printReport() {
        // Create the print window content
        let printContent = `
            <html>
            <head>
                <title>Sales Report</title>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .text-end { text-align: right; }
                    .text-center { text-align: center; }
                    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                    th, td { border: 1px solid #ddd; padding: 8px; }
                    th { background-color: #f4f4f4; }
                    .report-header { text-align: center; margin-bottom: 20px; }
                    .summary-box { 
                        border: 1px solid #ddd; 
                        padding: 10px; 
                        margin-bottom: 20px;
                        display: inline-block;
                        margin-right: 10px;
                    }
                    @media print {
                        .no-print { display: none; }
                    }
                </style>
            </head>
            <body>
                <div class="report-header">
                    <h2>Sales Report</h2>
                    <p>Period: ${document.getElementById('date_range').options[document.getElementById('date_range').selectedIndex].text}</p>
                    <p>Generated on: ${new Date().toLocaleString()}</p>
                </div>

                <div style="margin-bottom: 20px;">
                    <div class="summary-box">
                        <h4>Total Sales</h4>
                        <p>₱${document.querySelector('.bg-primary .fw-bold').textContent}</p>
                    </div>
                    <div class="summary-box">
                        <h4>Total Orders</h4>
                        <p>${document.querySelector('.bg-warning .fw-bold').textContent}</p>
                    </div>
                    <div class="summary-box">
                        <h4>Average Order Value</h4>
                        <p>₱${document.querySelector('.bg-success .fw-bold').textContent}</p>
                    </div>
                    <div class="summary-box">
                        <h4>Total Items Sold</h4>
                        <p>${document.querySelector('.bg-info .fw-bold').textContent}</p>
                    </div>
                </div>

                <h3>Top Selling Items</h3>
                ${document.querySelector('.table-responsive:last-of-type table').outerHTML}
            </body>
            </html>
        `;

        // Open print window
        let printWindow = window.open('', '_blank');
        printWindow.document.write(printContent);
        printWindow.document.close();

        // Add event listener for when content is loaded
        printWindow.onload = function() {
            printWindow.print();
        };
    }
</script>
@endsection 