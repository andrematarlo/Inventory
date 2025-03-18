@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 fw-bold">POS Reports</h1>
        <div class="d-flex gap-2">
            <button id="export-pdf" class="btn btn-danger">
                <i class="bi bi-file-pdf"></i> Export PDF
            </button>
            <button id="export-excel" class="btn btn-success">
                <i class="bi bi-file-excel"></i> Export Excel
            </button>
        </div>
    </div>

    <!-- Report Filters -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">Report Filters</h5>
        </div>
        <div class="card-body">
            <form id="report-filters" class="row g-3">
                <div class="col-md-4">
                    <label for="report-type" class="form-label fw-bold">Report Type</label>
                    <select id="report-type" class="form-select">
                        <option value="sales">Sales Summary</option>
                        <option value="items">Item Sales</option>
                        <option value="payments">Payment Methods</option>
                        <option value="categories">Sales by Category</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="date-from" class="form-label fw-bold">Date From</label>
                    <input type="date" id="date-from" class="form-control">
                </div>
                <div class="col-md-4">
                    <label for="date-to" class="form-label fw-bold">Date To</label>
                    <input type="date" id="date-to" class="form-control">
                </div>
            </form>
            <div class="mt-3 text-end">
                <button id="generate-report" class="btn btn-primary">
                    Generate Report
                </button>
            </div>
        </div>
    </div>

    <!-- Sales Summary Dashboard -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-primary">Total Sales</h5>
                    <p class="display-6 fw-bold" id="total-sales">₱0.00</p>
                    <p class="text-muted small mt-2"><span id="total-transactions">0</span> transactions</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-success">Cash Payments</h5>
                    <p class="display-6 fw-bold" id="cash-sales">₱0.00</p>
                    <p class="text-muted small mt-2"><span id="cash-percentage">0%</span> of total sales</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title text-purple">Deposit Payments</h5>
                    <p class="display-6 fw-bold" id="deposit-sales">₱0.00</p>
                    <p class="text-muted small mt-2"><span id="deposit-percentage">0%</span> of total sales</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Chart -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">Sales Trend</h5>
        </div>
        <div class="card-body">
            <div style="height: 300px;">
                <canvas id="sales-chart"></canvas>
            </div>
        </div>
    </div>

    <!-- Sales Data Table -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">Sales Data</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="sales-table">
                    <thead>
                        <tr class="table-light">
                            <th class="sales-date-header">Date</th>
                            <th class="sales-total-header">Total Sales</th>
                            <th class="sales-count-header">Transactions</th>
                            <th class="sales-avg-header">Average Sale</th>
                        </tr>
                    </thead>
                    <tbody id="sales-data-table">
                        <!-- This will be populated with report data -->
                        @if(isset($salesData) && count($salesData) > 0)
                            @foreach($salesData as $day)
                                <tr>
                                    <td>{{ $day->date }}</td>
                                    <td>₱{{ number_format($day->total_sales, 2) }}</td>
                                    <td>{{ $day->transaction_count }}</td>
                                    <td>₱{{ number_format($day->total_sales / $day->transaction_count, 2) }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="4" class="text-center text-muted">No sales data available</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set default dates (last 30 days)
        const today = new Date();
        const thirtyDaysAgo = new Date(today);
        thirtyDaysAgo.setDate(today.getDate() - 30);
        
        document.getElementById('date-to').valueAsDate = today;
        document.getElementById('date-from').valueAsDate = thirtyDaysAgo;
        
        // Mock data for sales chart
        const mockDates = [];
        const mockSalesData = [];
        const mockTransactionCounts = [];
        
        // Generate last 30 days of data
        for (let i = 29; i >= 0; i--) {
            const date = new Date(today);
            date.setDate(today.getDate() - i);
            mockDates.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
            
            // Generate random sales between 500 and 5000
            const sales = Math.floor(Math.random() * 4500) + 500;
            mockSalesData.push(sales);
            
            // Generate random transaction count between 5 and 50
            const transactions = Math.floor(Math.random() * 45) + 5;
            mockTransactionCounts.push(transactions);
        }
        
        // Initialize chart
        const ctx = document.getElementById('sales-chart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: mockDates,
                datasets: [{
                    label: 'Daily Sales (₱)',
                    data: mockSalesData,
                    backgroundColor: 'rgba(13, 110, 253, 0.2)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 2,
                    tension: 0.4
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
                }
            }
        });
        
        // Calculate and display summary data
        calculateSummaryData(mockSalesData, mockTransactionCounts);
        
        // Generate report button handler
        document.getElementById('generate-report').addEventListener('click', function() {
            const reportType = document.getElementById('report-type').value;
            
            // Update table headers based on report type
            updateTableHeaders(reportType);
            
            // In a real application, this would make an AJAX call to get data from the server
            // For this demo, we'll just regenerate the mock data
            
            // Clear the table
            const tableBody = document.getElementById('sales-data-table');
            tableBody.innerHTML = '';
            
            if (reportType === 'sales') {
                // Add rows to the table
                for (let i = 0; i < mockDates.length; i++) {
                    const row = document.createElement('tr');
                    
                    const sales = mockSalesData[i];
                    const transactions = mockTransactionCounts[i];
                    const avgSale = sales / transactions;
                    
                    row.innerHTML = `
                        <td>${mockDates[i]}</td>
                        <td>₱${sales.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td>${transactions}</td>
                        <td>₱${avgSale.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    `;
                    
                    tableBody.appendChild(row);
                }
            } else if (reportType === 'items') {
                // Mock item sales data
                const itemsData = [
                    { name: 'Chicken Adobo', qty: 150, total: 12500 },
                    { name: 'Beef Tapa', qty: 125, total: 10625 },
                    { name: 'Bottled Water', qty: 300, total: 4500 },
                    { name: 'Cheese Burger', qty: 200, total: 9000 },
                    { name: 'Spaghetti', qty: 175, total: 11375 }
                ];
                
                // Update the table
                itemsData.forEach(item => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${item.name}</td>
                        <td>₱${item.total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        <td>${item.qty}</td>
                        <td>₱${(item.total / item.qty).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    `;
                    tableBody.appendChild(row);
                });
            }
        });
        
        function updateTableHeaders(reportType) {
            const dateHeader = document.querySelector('.sales-date-header');
            const totalHeader = document.querySelector('.sales-total-header');
            const countHeader = document.querySelector('.sales-count-header');
            const avgHeader = document.querySelector('.sales-avg-header');
            
            if (reportType === 'sales') {
                dateHeader.textContent = 'Date';
                totalHeader.textContent = 'Total Sales';
                countHeader.textContent = 'Transactions';
                avgHeader.textContent = 'Average Sale';
            } else if (reportType === 'items') {
                dateHeader.textContent = 'Item Name';
                totalHeader.textContent = 'Total Sales';
                countHeader.textContent = 'Quantity Sold';
                avgHeader.textContent = 'Average Price';
            } else if (reportType === 'payments') {
                dateHeader.textContent = 'Payment Method';
                totalHeader.textContent = 'Total Amount';
                countHeader.textContent = 'Transaction Count';
                avgHeader.textContent = 'Average Transaction';
            } else if (reportType === 'categories') {
                dateHeader.textContent = 'Category';
                totalHeader.textContent = 'Total Sales';
                countHeader.textContent = 'Items Sold';
                avgHeader.textContent = 'Average Item Price';
            }
        }
        
        function calculateSummaryData(salesData, transactionCounts) {
            // Calculate total sales
            const totalSales = salesData.reduce((sum, sale) => sum + sale, 0);
            document.getElementById('total-sales').textContent = `₱${totalSales.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            
            // Calculate total transactions
            const totalTransactions = transactionCounts.reduce((sum, count) => sum + count, 0);
            document.getElementById('total-transactions').textContent = totalTransactions.toLocaleString();
            
            // Mock payment type distribution (70% cash, 30% deposit)
            const cashSales = totalSales * 0.7;
            const depositSales = totalSales * 0.3;
            
            document.getElementById('cash-sales').textContent = `₱${cashSales.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            document.getElementById('deposit-sales').textContent = `₱${depositSales.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            
            document.getElementById('cash-percentage').textContent = '70%';
            document.getElementById('deposit-percentage').textContent = '30%';
        }
    });
</script>
@endpush
@endsection 