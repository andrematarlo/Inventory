@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">POS Reports</h5>
                    <div>
                        <a href="{{ route('pos.reports.sales') }}" class="btn btn-sm btn-light">
                            <i class="bi bi-graph-up"></i> Sales Reports
                        </a>
                        <a href="{{ route('pos.reports.deposits') }}" class="btn btn-sm btn-light">
                            <i class="bi bi-wallet2"></i> Deposit Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Summary Cards -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Sales (Last 30 Days)</h6>
                            <h3 class="mb-0">₱{{ number_format($salesData->sum('total_sales'), 2) }}</h3>
                        </div>
                        <div class="bg-light p-3 rounded">
                            <i class="bi bi-cash text-primary" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Transactions</h6>
                            <h3 class="mb-0">{{ number_format($salesData->sum('transaction_count')) }}</h3>
                        </div>
                        <div class="bg-light p-3 rounded">
                            <i class="bi bi-receipt text-success" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-info h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Average Transaction</h6>
                            @php
                                $totalTransactions = $salesData->sum('transaction_count');
                                $totalSales = $salesData->sum('total_sales');
                                $average = $totalTransactions > 0 ? $totalSales / $totalTransactions : 0;
                            @endphp
                            <h3 class="mb-0">₱{{ number_format($average, 2) }}</h3>
                        </div>
                        <div class="bg-light p-3 rounded">
                            <i class="bi bi-calculator text-info" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Sales Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h6 class="mb-0">Sales Trend (Last 30 Days)</h6>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" height="300"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Recent Sales Table -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h6 class="mb-0">Recent Sales</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover m-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Transactions</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($salesData->take(7) as $sale)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($sale->date)->format('M d') }}</td>
                                    <td>{{ $sale->transaction_count }}</td>
                                    <td class="text-end">₱{{ number_format($sale->total_sales, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('pos.reports.sales') }}" class="btn btn-sm btn-outline-primary">View All Sales</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        // Prepare data for chart
        const salesData = {!! json_encode($salesData) !!};
        
        // Process data for chart
        const labels = [];
        const data = [];
        
        salesData.slice().reverse().forEach(item => {
            labels.push(new Date(item.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
            data.push(parseFloat(item.total_sales));
        });
        
        // Create the chart
        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Daily Sales',
                    data: data,
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
    });
</script>
@endsection 