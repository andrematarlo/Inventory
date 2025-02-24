@extends('layouts.app')

@section('title', 'Report')

@section('content')
<div class="report-container">
    <!-- Header for screen view -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h2>@yield('report-title')</h2>
            <p class="text-muted">@yield('report-subtitle')</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-secondary">
                <i class="bi bi-printer"></i> Print Report
            </button>
            @yield('report-actions')
            <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
                Back to Reports
            </a>
        </div>
    </div>
    
    @yield('report-content')
</div>

@push('styles')
<style>
    /* General styles */
    .report-container {
        background-color: white;
        border-radius: 10px;
        padding: 2rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .print-only {
        display: none;
    }

    /* Print styles */
    @media print {
        /* Reset all styles first */
        * {
            margin: 0;
            padding: 0;
            background: none !important;
        }

        body {
            visibility: hidden;
            margin: 0;
            padding: 0;
            font-size: 12pt;
        }

        .report-container {
            padding: 0;
            margin: 0;
            box-shadow: none;
        }

        /* Show only print content */
        .print-content {
            visibility: visible !important;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
        }

        /* Hide screen-only elements */
        .no-print,
        nav,
        footer,
        .card-header {
            display: none !important;
        }

        /* Table styles */
        .table {
            width: 100% !important;
            border-collapse: collapse !important;
            margin-bottom: 1rem;
        }

        .table th,
        .table td {
            padding: 8px !important;
            border: 1px solid #000 !important;
            font-size: 10pt;
        }

        .table th {
            font-weight: bold;
            background-color: #f8f9fa !important;
            -webkit-print-color-adjust: exact;
        }

        /* Keep table headers on new pages */
        thead {
            display: table-header-group;
        }

        /* Text colors for print */
        .text-success { color: #000 !important; }
        .text-danger { color: #000 !important; }
        
        /* Badge styles for print */
        .badge {
            padding: 2px 5px !important;
            border: 1px solid #000 !important;
            font-size: 9pt !important;
            color: #000 !important;
        }

        /* Page settings */
        @page {
            size: portrait;
            margin: 2cm;
        }

        /* Fix layout issues */
        .card {
            border: none !important;
            margin: 0 !important;
        }

        .card-body {
            padding: 0 !important;
        }

        .table-responsive {
            overflow: visible !important;
        }
    }
</style>
@endpush
@endsection 