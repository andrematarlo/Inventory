@extends('layouts.app')

@section('title', 'POS Reports')

@section('styles')
<style>
    .reports-container {
        padding: 2rem 0;
    }
    
    .report-card {
        background: white;
        border-radius: 0.5rem;
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.05);
        transition: transform 0.2s, box-shadow 0.2s;
        height: 100%;
    }
    
    .report-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.1);
    }
    
    .report-card-header {
        padding: 1.5rem;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .report-card-body {
        padding: 1.5rem;
    }
    
    .report-icon {
        width: 60px;
        height: 60px;
        background: #f8f9fa;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
    }
    
    .report-icon i {
        font-size: 1.75rem;
        color: #6c757d;
    }
    
    .report-description {
        color: #6c757d;
        margin-bottom: 1.5rem;
    }
    
    .form-label {
        font-weight: 500;
    }
    
    .btn-generate {
        background: #007bff;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 0.25rem;
        font-weight: 500;
        transition: background 0.2s;
    }
    
    .btn-generate:hover {
        background: #0069d9;
    }
    
    .report-stats-card {
        background: white;
        border-radius: 0.5rem;
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.05);
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stats-number {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .stats-label {
        color: #6c757d;
        font-weight: 500;
    }
    
    .stats-icon {
        font-size: 3rem;
        opacity: 0.2;
        position: absolute;
        right: 1.5rem;
        bottom: 1rem;
    }
    
    .stats-card-sales {
        background: linear-gradient(to right, #28a745, #20c997);
        color: white;
    }
    
    .stats-card-sales .stats-label {
        color: rgba(255,255,255,0.8);
    }
    
    .stats-card-orders {
        background: linear-gradient(to right, #007bff, #17a2b8);
        color: white;
    }
    
    .stats-card-orders .stats-label {
        color: rgba(255,255,255,0.8);
    }
    
    .stats-card-popular {
        background: linear-gradient(to right, #fd7e14, #ffc107);
        color: white;
    }
    
    .stats-card-popular .stats-label {
        color: rgba(255,255,255,0.8);
    }
</style>
@endsection

@section('content')
<div class="container reports-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>POS Reports</h1>
    </div>
    
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="position-relative report-stats-card stats-card-sales">
                <div class="stats-number">₱{{ number_format(App\Models\POSOrder::where('Status', 'Completed')->sum('TotalAmount'), 2) }}</div>
                <div class="stats-label">Total Sales</div>
                <i class="bi bi-currency-dollar stats-icon"></i>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="position-relative report-stats-card stats-card-orders">
                <div class="stats-number">{{ App\Models\POSOrder::where('Status', 'Completed')->count() }}</div>
                <div class="stats-label">Total Orders</div>
                <i class="bi bi-bag-check stats-icon"></i>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="position-relative report-stats-card stats-card-popular">
                <div class="stats-number">
                    {{ 
                        App\Models\POSOrderItem::select('ItemID')
                            ->selectRaw('SUM(Quantity) as total_quantity')
                            ->groupBy('ItemID')
                            ->orderByDesc('total_quantity')
                            ->first()?->item?->ItemName ?? 'N/A' 
                    }}
                </div>
                <div class="stats-label">Most Popular Item</div>
                <i class="bi bi-star stats-icon"></i>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Sales Report Card -->
        <div class="col-lg-6 mb-4">
            <div class="report-card">
                <div class="report-card-header">
                    <div class="report-icon">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <h3>Sales Report</h3>
                    <p class="report-description">
                        Generate a report of all sales within a date range. View daily, weekly, monthly, or annual sales data.
                    </p>
                </div>
                <div class="report-card-body">
                    <form action="{{ route('pos.reports.sales') }}" method="GET">
                        <div class="mb-3">
                            <label for="sales_report_type" class="form-label">Report Type</label>
                            <select class="form-select" id="sales_report_type" name="report_type" required>
                                <option value="daily">Daily Report</option>
                                <option value="weekly">Weekly Report</option>
                                <option value="monthly">Monthly Report</option>
                                <option value="annual">Annual Report</option>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="sales_date_from" class="form-label">Date From</label>
                                <input type="date" class="form-control" id="sales_date_from" name="date_from" required value="{{ date('Y-m-d', strtotime('-30 days')) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="sales_date_to" class="form-label">Date To</label>
                                <input type="date" class="form-control" id="sales_date_to" name="date_to" required value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-generate">
                                <i class="bi bi-file-earmark-text me-2"></i> Generate Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Sales By Cashier Report Card -->
        <div class="col-lg-6 mb-4">
            <div class="report-card">
                <div class="report-card-header">
                    <div class="report-icon">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <h3>Sales by Cashier</h3>
                    <p class="report-description">
                        Generate a report of sales grouped by cashier. Filter by specific cashier or view all cashiers.
                    </p>
                </div>
                <div class="report-card-body">
                    <form action="{{ route('pos.reports.sales-by-cashier') }}" method="GET">
                        <div class="mb-3">
                            <label for="cashier_report_type" class="form-label">Report Type</label>
                            <select class="form-select" id="cashier_report_type" name="report_type" required>
                                <option value="daily">Daily Report</option>
                                <option value="weekly">Weekly Report</option>
                                <option value="monthly">Monthly Report</option>
                                <option value="annual">Annual Report</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="cashier_id" class="form-label">Cashier</label>
                            <select class="form-select" id="cashier_id" name="cashier_id">
                                <option value="">All Cashiers</option>
                                @foreach($cashiers as $cashier)
                                    <option value="{{ $cashier->id }}">{{ $cashier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cashier_date_from" class="form-label">Date From</label>
                                <input type="date" class="form-control" id="cashier_date_from" name="date_from" required value="{{ date('Y-m-d', strtotime('-30 days')) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="cashier_date_to" class="form-label">Date To</label>
                                <input type="date" class="form-control" id="cashier_date_to" name="date_to" required value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-generate">
                                <i class="bi bi-file-earmark-text me-2"></i> Generate Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Sales By Item Report Card -->
        <div class="col-lg-6 mb-4">
            <div class="report-card">
                <div class="report-card-header">
                    <div class="report-icon">
                        <i class="bi bi-box"></i>
                    </div>
                    <h3>Sales by Item</h3>
                    <p class="report-description">
                        Generate a report of sales grouped by menu item. Filter by specific item or view all items.
                    </p>
                </div>
                <div class="report-card-body">
                    <form action="{{ route('pos.reports.sales-by-item') }}" method="GET">
                        <div class="mb-3">
                            <label for="item_report_type" class="form-label">Report Type</label>
                            <select class="form-select" id="item_report_type" name="report_type" required>
                                <option value="daily">Daily Report</option>
                                <option value="weekly">Weekly Report</option>
                                <option value="monthly">Monthly Report</option>
                                <option value="annual">Annual Report</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="item_id" class="form-label">Item</label>
                            <select class="form-select" id="item_id" name="item_id">
                                <option value="">All Items</option>
                                @foreach(App\Models\Item::where('Status', 'Active')->orderBy('ItemName')->get() as $item)
                                    <option value="{{ $item->ItemID }}">{{ $item->ItemName }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="item_date_from" class="form-label">Date From</label>
                                <input type="date" class="form-control" id="item_date_from" name="date_from" required value="{{ date('Y-m-d', strtotime('-30 days')) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="item_date_to" class="form-label">Date To</label>
                                <input type="date" class="form-control" id="item_date_to" name="date_to" required value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-generate">
                                <i class="bi bi-file-earmark-text me-2"></i> Generate Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Export Reports Card -->
        <div class="col-lg-6 mb-4">
            <div class="report-card">
                <div class="report-card-header">
                    <div class="report-icon">
                        <i class="bi bi-file-earmark-arrow-down"></i>
                    </div>
                    <h3>Export Reports</h3>
                    <p class="report-description">
                        Export any report to PDF or Excel format for printing or further analysis.
                    </p>
                </div>
                <div class="report-card-body">
                    <form action="{{ route('pos.reports.export') }}" method="GET" target="_blank">
                        <div class="mb-3">
                            <label for="export_report_type" class="form-label">Report Type</label>
                            <select class="form-select" id="export_report_type" name="report_type" required>
                                <option value="sales">Sales Report</option>
                                <option value="sales_by_cashier">Sales by Cashier</option>
                                <option value="sales_by_item">Sales by Item</option>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="export_date_from" class="form-label">Date From</label>
                                <input type="date" class="form-control" id="export_date_from" name="date_from" required value="{{ date('Y-m-d', strtotime('-30 days')) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="export_date_to" class="form-label">Date To</label>
                                <input type="date" class="form-control" id="export_date_to" name="date_to" required value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="export_format" class="form-label">Export Format</label>
                            <select class="form-select" id="export_format" name="format" required>
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                            </select>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-generate">
                                <i class="bi bi-download me-2"></i> Export Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Select2 dropdowns if available
        if (typeof $.fn.select2 !== 'undefined') {
            $('#cashier_id, #item_id').select2({
                theme: 'bootstrap-5',
                placeholder: 'Select an option...',
                allowClear: true
            });
        }
        
        // Date validation
        const dateFromInputs = document.querySelectorAll('[id$="date_from"]');
        const dateToInputs = document.querySelectorAll('[id$="date_to"]');
        
        dateFromInputs.forEach(input => {
            input.addEventListener('change', function() {
                const formId = this.id.split('_date_from')[0];
                const dateToInput = document.getElementById(`${formId}_date_to`);
                
                if (dateToInput.value && new Date(this.value) > new Date(dateToInput.value)) {
                    dateToInput.value = this.value;
                }
                
                dateToInput.min = this.value;
            });
        });
        
        // Form validation
        const reportForms = document.querySelectorAll('form');
        
        reportForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const dateFrom = this.querySelector('[name="date_from"]').value;
                const dateTo = this.querySelector('[name="date_to"]').value;
                
                if (new Date(dateFrom) > new Date(dateTo)) {
                    e.preventDefault();
                    alert('Date From cannot be later than Date To.');
                }
            });
        });
    });
</script>
@endsection 