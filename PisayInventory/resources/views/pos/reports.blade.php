@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center mb-4">
        <div class="col-12 text-center">
            <h2 class="fw-bold mb-4">POS Reports</h2>
            <p class="text-muted mb-0">Select a report to view detailed analytics and transactions.</p>
        </div>
    </div>
    <div class="row justify-content-center">
        <!-- Sales Report Card -->
        <div class="col-md-4 mb-4">
            <a href="{{ route('pos.reports.sales') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 border-primary hover-shadow transition">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bi bi-bar-chart-line" style="font-size: 3rem; color: #0d6efd;"></i>
                        </div>
                        <h4 class="fw-bold text-primary">Sales Report</h4>
                        <p class="text-muted">View sales trends, top items, and transaction details.</p>
                    </div>
                </div>
            </a>
        </div>
        <!-- Deposits Report Card -->
        <div class="col-md-4 mb-4">
            <a href="{{ route('pos.reports.deposits') }}" class="text-decoration-none">
                <div class="card shadow-sm h-100 border-success hover-shadow transition">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bi bi-wallet2" style="font-size: 3rem; color: #198754;"></i>
                        </div>
                        <h4 class="fw-bold text-success">Deposits Report</h4>
                        <p class="text-muted">Monitor deposit transactions and student balances.</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<style>
    .hover-shadow:hover {
        box-shadow: 0 0.5rem 2rem 0 rgba(13, 110, 253, 0.15) !important;
        transform: translateY(-2px) scale(1.03);
        transition: all 0.2s;
    }
    .transition {
        transition: all 0.2s;
    }
</style>
@endpush 