<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'POS System') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,600,700" rel="stylesheet">

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.8/dist/sweetalert2.min.css" rel="stylesheet">
    
    <!-- Custom Styles -->
    <style>
    body {
        background-color: #f5f6fa;
        color: #333;
        font-family: 'Nunito', sans-serif;
    }
    </style>

    @stack('styles')
</head>
<body>
    <main>
        <!-- No sidebar here, just the main content -->
        <div class="container-fluid py-3">
            <!-- Small Navbar for POS -->
            <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm mb-4 rounded-3">
                <div class="container-fluid">
                    <a class="navbar-brand" href="{{ route('pos.index') }}">
                        <img src="{{ asset('images/logo.png') }}" alt="Logo" height="30" class="me-2">
                        PSHS-CViscSC POS
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav me-auto">
                            <li class="nav-item">
                                <a class="nav-link {{ Request::is('inventory/pos') ? 'active' : '' }}" href="{{ route('pos.index') }}">
                                    <i class="bi bi-list-ul me-1"></i> Orders
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ Request::is('inventory/pos/create') ? 'active' : '' }}" href="{{ route('pos.create') }}">
                                    <i class="bi bi-plus-circle me-1"></i> New Order
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ Request::is('inventory/pos/cashiering') ? 'active' : '' }}" href="{{ route('pos.cashiering') }}">
                                    <i class="bi bi-cash-coin me-1"></i> Cashiering
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ Request::is('inventory/pos/cashdeposit') ? 'active' : '' }}" href="{{ route('pos.cashdeposit') }}">
                                    <i class="bi bi-wallet2 me-1"></i> Deposits
                                </a>
                            </li>
                        </ul>
                        <div class="d-flex align-items-center">
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm me-2">
                                <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                            </a>
                            <span class="text-muted me-3">{{ Auth::user()->name }}</span>
                        </div>
                    </div>
                </div>
            </nav>
            
            @yield('content')
        </div>
    </main>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.8/dist/sweetalert2.all.min.js"></script>

    <script>
        // Set CSRF token for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

    @stack('scripts')
</body>
</html> 