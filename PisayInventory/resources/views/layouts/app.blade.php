<!DOCTYPE html>
<html lang="en">
    <head>
    

        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        <title>@yield('title', 'PSHS Inventory System')</title>
        <link rel="icon" href="{{ asset('images/pisaylogo.png') }}" type="image/x-icon">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/pisaylogo.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/pisaylogo.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/pisaylogo.png') }}">
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Bootstrap Icons -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <!-- DataTables CSS -->
        <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
        <!-- Custom CSS -->
        <style>
            * {
                font-family: 'Poppins', sans-serif;
            }

            body {
                background-color:rgba(214, 255, 236, 0.09);
                background-image: url('{{ asset('images/pshsbackground.jpg') }}');
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
                background-attachment: fixed;
                min-height: 100vh;
            }
            

            /* Sidebar Styles */
            .sidebar {
                position: fixed;
                left: 0;
                top: 0;
                height: 100vh;
                width: 320px;
                background: white;
                box-shadow: 0 4px 12px 0 rgba(0, 0, 0, 0.05);
                overflow-y: auto;
                z-index: 1000;
            }

            .sidebar-header {
                padding: 1rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
                border-bottom: 1px solid #f1f5f9;
            }

            .sidebar-header h3 {
                color: #0f172a;
                margin: 0;
                font-size: 1rem;
                font-weight: 600;
            }

            .sidebar-logo {
                width: 30px;
                height: auto;
                margin-right: 8px;
            }

            .nav-item {
                margin: 0.25rem 0;
            }

            .nav-link {
                display: flex;
                align-items: center;
                padding: 0.75rem 1.25rem; /* Adjusted padding */
                color: #64748b;
                text-decoration: none;
                transition: all 0.2s;
                border-radius: 0.375rem;
                margin: 0 0.75rem; /* Adjusted margin */
                gap: 0.75rem;
            }

            .nav-link i {
                font-size: 1.25rem;
                width: 1.5rem;
                text-align: center;
                flex-shrink: 0;
            }

            .nav-text {
                display: flex;
                flex-direction: column;
                flex-grow: 1;
                line-height: 1.2;
            }

            .nav-text span {
                font-size: 0.875rem;
            }

            /* Dropdown styles */
            .dropdown-toggle {
                position: relative;
            }

            .dropdown-toggle .dropdown-arrow {
                position: absolute;
                right: 1rem;
                transition: transform 0.2s;
            }

            .dropdown-toggle[aria-expanded="true"] .dropdown-arrow {
                transform: rotate(90deg);
            }

            .dropdown-menu {
                padding: 0.5rem;
                margin: 0;
                border: none;
                background-color: transparent;
                box-shadow: none;
            }

            .dropdown-item {
                padding: 0.5rem 1rem;
                margin: 0.25rem 0;
                color: #64748b;
                border-radius: 0.375rem;
                display: flex;
                align-items: center;
            }

            .dropdown-item:hover {
                color: #0f172a;
                background-color: #f1f5f9;
            }

            .dropdown-item.active {
                color: #0f172a;
                background-color: #f1f5f9;
                font-weight: 500;
            }

            .dropdown-item i {
                font-size: 1rem;
                margin-right: 0.75rem;
                width: 1.25rem;
                text-align: center;
            }

            /* Logout button */
            .nav-item.mt-auto {
                margin-top: auto !important;
                border-top: 1px solid #f1f5f9;
            }

            .nav-item.mt-auto .nav-link {
                color: #ef4444;
                gap: 0.5rem;
            }

            .nav-item.mt-auto .nav-link i {
                width: 1.25rem;
            }

            .nav-item.mt-auto button.nav-link {
                width: 100%;
                text-align: left;
                border: none;
                background: none;
                padding: 0.75rem 1.5rem;
            }

            .nav-item.mt-auto .nav-link:hover {
                background-color: #fef2f2;
            }

            /* Main content adjustment */
    body .main-content {
        margin-left: 320px;
        padding: 0 0 0 10px;
        transition: margin-left 0.3s ease;
    }

    body .main-content .content-wrapper {
        padding: 2rem 3rem;
        margin: 0 auto;
    }

    body .container, 
    body .container-fluid {
        max-width: 1800px;
        margin: 0 auto;
    }

    /* Responsive */
    @media (max-width: 768px) {
        body .main-content {
            margin-left: 0;
            padding-left: 0;
        }
    }

    /* Responsive styles */
    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        .sidebar.show {
            transform: translateX(0);
        }
    }
    
            /* Add some overlay to make content more readable */
            body::before {
                content: '';
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(255, 255, 255, 0.7); /* white overlay with 70% opacity */
                z-index: -1;
            }
        </style>
        @yield('additional_styles')
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        @yield('styles')
        @stack('styles')

        <!-- DateRangePicker CSS -->
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    </head>
    <body>
        @include('layouts.sidebar')
        
        <div class="main-content">
            <div class="content-wrapper">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show main-alert" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>

        <!-- Scripts in correct order -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

<!-- Additional Styles -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">

<!-- Application JavaScript -->
<script src="{{ asset('js/app.js') }}"></script>

@if(session('sweet_alert'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: "{{ session('sweet_alert.title') }}",
                    text: "{{ session('sweet_alert.message') }}",
                    icon: "{{ session('sweet_alert.type') }}",
                    confirmButtonText: 'OK'
                });
            });
        </script>
        @endif

        @yield('scripts')
        @stack('scripts')

    <!-- Scripts -->
    <!-- <script src="{{ asset('js/app.js') }}" defer></script> -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Display SweetAlert flash messages from session
            @if (session('alert'))
                Swal.fire({
                    icon: '{{ session('alert')['type'] }}',
                    title: '{{ session('alert')['title'] }}',
                    text: '{{ session('alert')['text'] }}',
                    @if (isset(session('alert')['footer']))
                    footer: '{{ session('alert')['footer'] }}',
                    @endif
                    confirmButtonColor: '{{ session('alert')['type'] === 'success' ? '#4CAF50' : '#F44336' }}'
                });
            @elseif (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: '{{ session('success') }}',
                    confirmButtonColor: '#4CAF50'
                });
            @elseif (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '{{ session('error') }}',
                    confirmButtonColor: '#F44336'
                });
            @elseif (session('warning'))
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning',
                    text: '{{ session('warning') }}',
                    confirmButtonColor: '#F9A825'
                });
            @elseif (session('info'))
                Swal.fire({
                    icon: 'info',
                    title: 'Information',
                    text: '{{ session('info') }}',
                    confirmButtonColor: '#2196F3'
                });
            @endif
        });
    </script>

    <!-- Moment.js -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <!-- DateRangePicker -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    </body>
</html>
