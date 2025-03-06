@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="container py-4">
    <!-- Header Section -->
    <div class="rounded-3 mb-5" id="reports-header">
        <h2 class="mt-4 reports-title">Reports</h2>
        <p class="text-muted lead mb-0">Generate and view various inventory reports</p>
    </div>

    <!-- Report Types with enhanced cards -->
    <div class="row g-4">
    <!-- Inventory Report -->
        <div class="col-md-6 col-lg-4">
            <div class="card report-card h-100 border-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#inventoryReportModal">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="card-title mb-3">Inventory Report</h5>
                            <p class="card-text text-muted">View comprehensive inventory status and stock levels</p>
                        </div>
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                            <i class="bi bi-box-seam text-primary fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Report -->
        <div class="col-md-6 col-lg-4">
            <div class="card report-card h-100 border-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#salesReportModal">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="card-title mb-3">Sales Analysis</h5>
                            <p class="card-text text-muted">View sales performance and trends</p>
                        </div>
                        <div class="rounded-circle bg-success bg-opacity-10 p-3">
                            <i class="bi bi-graph-up text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Report -->
        <div class="col-md-6 col-lg-4">
            <div class="card report-card h-100 border-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#lowStockReportModal">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="card-title mb-3">Low Stock Alert</h5>
                            <p class="card-text text-muted">Identify items that need restocking</p>
                        </div>
                        <div class="rounded-circle bg-danger bg-opacity-10 p-3">
                            <i class="bi bi-exclamation-triangle text-danger fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Inventory Report Modal -->
    <div class="modal fade" 
         id="inventoryReportModal" 
         data-bs-backdrop="static" 
         data-bs-keyboard="false" 
         tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-box-seam"></i> Generate Inventory Report
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('reports.inventory') }}" method="GET">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Report Type</label>
                            <select name="report_type" class="form-select">
                                <option value="all">All Movements</option>
                                <option value="in">Stock In Only</option>
                                <option value="out">Stock Out Only</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date" required 
                                   value="{{ now()->subDays(30)->format('Y-m-d') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date" required
                                   value="{{ now()->format('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-file-earmark-text"></i> Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sales Report Modal -->
    <div class="modal fade" 
         id="salesReportModal" 
         data-bs-backdrop="static" 
         data-bs-keyboard="false" 
         tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-graph-up"></i> Generate Sales Report
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('reports.sales') }}" method="GET">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-file-earmark-text"></i> Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Low Stock Report Modal -->
    <div class="modal fade" 
         id="lowStockReportModal" 
         data-bs-backdrop="static" 
         data-bs-keyboard="false" 
         tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle"></i> Generate Low Stock Report
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('reports.low-stock') }}" method="GET">
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="bi bi-info-circle"></i> This report will show all items with stock levels below the minimum threshold.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-file-earmark-text"></i> Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@section('styles')
<style>
            /* More specific selector using ID and class combination */
            #reports-header h2.reports-title {
                color: #2d3748 !important;
                font-weight: 600 !important;
                margin-bottom: 1.5rem !important;
            }

            /* Make cards clickable with direct selector */
            div[data-bs-toggle="modal"].card.report-card {
                cursor: pointer !important;
            }


            .card.report-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
            }

            .card.report-card .bi {
                transition: transform 0.3s ease;
            }

            .card.report-card:hover .bi {
                transform: scale(1.1);
            }
            /* Print styles */
    @media print {
        .no-print {
            display: none !important;
        }
        
        .container {
            width: 100%;
            max-width: none;
            margin: 0;
            padding: 0;
        }

        .card {
            border: none;
            box-shadow: none;
        }

        .table {
            width: 100% !important;
            border-collapse: collapse !important;
        }

        .table td,
        .table th {
            background-color: #fff !important;
            border: 1px solid #ddd !important;
            padding: 8px !important;
        }

        .badge {
            border: 1px solid #000 !important;
            padding: 2px 5px !important;
        }

        .text-success { color: #000 !important; }
        .text-danger { color: #000 !important; }
        .text-warning { color: #000 !important; }
        
        /* Header and footer for printed pages */
        @page {
            margin: 2cm;
        }

        /* Add page breaks */
        .page-break {
            page-break-before: always;
        }
    }

    /* Modal Styles */
    .modal-header.bg-primary,
    .modal-header.bg-success,
    .modal-header.bg-danger {
        color: white;
    }

    .btn-close-white {
        filter: brightness(0) invert(1);
    }

    .modal .alert {
        margin-bottom: 0;
    }

    .modal-footer {
        border-top: 1px solid #dee2e6;
    }

    .modal .btn i {
        margin-right: 5px;
    }
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all modals with static backdrop
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        const bsModal = new bootstrap.Modal(modal, {
            backdrop: 'static',
            keyboard: false
        });

        // Prevent modal from closing when clicking outside
        $(modal).on('mousedown', function(e) {
            if ($(e.target).is('.modal')) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        });
    });

    // Print functionality
    window.printReport = function() {
        // Remove unnecessary elements
        const elementsToHide = document.querySelectorAll('.no-print');
        elementsToHide.forEach(el => el.style.display = 'none');

        // Print the document
        window.print();

        // Restore elements
        elementsToHide.forEach(el => el.style.display = '');
    };
});
</script>
@endpush
@endsection 