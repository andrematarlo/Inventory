@extends('layouts.app')

@section('title', 'Report')



@section('content')

<div class="report-container">
    <!-- Header for screen view -->
<div class="mb-4 no-print">
        <!-- Back button and title section -->
        <div class="d-flex align-items-center mb-3">
            <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary me-3">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div class="report-header">
                <h2 class="mb-0">@yield('report-title')</h2>
                <p class="text-muted mb-0">@yield('report-subtitle')</p>
            </div>
        </div>
        
                <!-- Action buttons section -->
<div class="d-flex align-items-center mb-3">
    <div class="flex-grow-1">
        @yield('report-actions')
    </div>
    <div class="action-buttons">
        <a href="{{ route('reports.inventory.pdf', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d'), 'report_type' => $reportType]) }}" 
        class="btn btn-outline-secondary btn-icon-only" 
        data-toggle="tooltip" 
        data-placement="top" 
        title="Download PDF">
            <i class="bi bi-download"></i>  <!-- Changed from bi-download -->
        </a>
        <button onclick="printReport()" 
                class="btn btn-outline-secondary btn-icon-only"
                data-toggle="tooltip" 
                data-placement="top" 
                title="Print Report">
            <i class="bi bi-printer"></i>
        </button>
    </div>
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

        /* Header styling */
    .report-header h2 {
        font-size: 1.5rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .report-header p {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Icon-only buttons */
.btn-outline-secondary.btn-icon-only {
    width: 38px;
    height: 38px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-outline-secondary.btn-icon-only i {
    margin: 0;
    line-height: 1;
    font-size: 1.1rem;
}

/* Action buttons container */
.action-buttons {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

    /* Spacing utilities */
    .gap-2 {
        gap: 0.5rem !important;
    }


    /* Back button styling */
    .btn-outline-secondary {
        padding: 0.5rem;
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .screen-only {
    display: block;
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