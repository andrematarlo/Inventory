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
        <button onclick="printReport()" class="btn btn-secondary">
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


<script>
    function printReport() {
    var printContents = document.querySelector(".print-only").innerHTML;
    var originalContents = document.body.innerHTML;

    document.body.innerHTML = '<div style="background:white; color:black; padding:20px;">' + printContents + '</div>';
    window.print();
    document.body.innerHTML = originalContents;
    location.reload(); // Restore the original page after printing
}
</script>

@push('styles')
<style>

    /* General layout styles */
    .report-container {
        background-color: white !important;
        border-radius: 10px;
        padding: 2rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* Hide print-only content on screen */
    .print-only {
        display: none;
    }

    /* Print styles */
    @media print {

                /* Hide screen-only content when printing */
                .screen-only {
            display: none !important;
        }




        .print-only {
            display: block !important;
            background: white !important;
            color: black !important;
            padding: 2rem;
        }

        /* Ensure tables print cleanly */
        .print-only table {
            width: 100%;
            border-collapse: collapse;
        }

        .print-only th, .print-only td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        .print-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .print-header h1 {
            font-size: 24pt;
            margin-bottom: 10px;
        }

        .print-header h2 {
            font-size: 18pt;
            margin-bottom: 10px;
        }

        /* Keep table headers on new pages */
        thead {
            display: table-header-group;
        }

        /* Prevent awkward table row splitting */
        tr {
            page-break-inside: avoid;
        }

        /* Page settings */
        @page {
            margin: 2cm;
            size: portrait;
        }
    }
</style>
@endpush

@endsection 