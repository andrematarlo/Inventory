@extends('layouts.app')

@section('title', 'Report')

@section('additional_styles')
<style>
    .report-container {
        background-color: white;
        border-radius: 10px;
        padding: 2rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    @media print {
        .sidebar {
            display: none;
        }
        .main-content {
            margin-left: 0;
        }
        .no-print {
            display: none;
        }
    }
</style>
@endsection

@section('content')
<div class="report-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>PSHS Inventory System</h2>
        <div class="no-print">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> Print Report
            </button>
            <a href="{{ route('reports.index') }}" class="btn btn-secondary">
                Back to Reports
            </a>
        </div>
    </div>
    
    @yield('report-content')
</div>
@endsection 