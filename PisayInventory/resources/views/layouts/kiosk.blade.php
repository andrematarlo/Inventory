<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        <title>@yield('title', 'PSHS Kiosk')</title>
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
                background-color: #F5F5DC !important;
                color: #000000 !important;
            }

            /* Main content adjustment for kiosk - no sidebar */
            body .main-content {
                margin: 0 auto;
                padding: 20px;
                width: 100%;
                max-width: 1800px; /* Increased from 1400px */
            }

            body .main-content .content-wrapper {
                padding: 0;
                margin: 0;
                width: 100%;
            }

            body .container, 
            body .container-fluid {
                max-width: 1800px; /* Increased from 1400px */
                margin: 0 auto;
                padding: 0 20px;
            }

            /* Card grid adjustments */
            .menu-items-grid .row {
                margin: 0 -10px;
            }

            .menu-items-grid .col-lg-3 {
                padding: 10px;
            }

            /* Card styling */
            .card {
                border-radius: 12px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }

            .card:hover {
                transform: translateY(-5px);
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            }

            /* Header card specific styling */
            .header-card {
                background: linear-gradient(to right, #28a745, #20c997);
                border: none;
                border-radius: 15px;
            }

            .header-card .card-body {
                padding: 1.5rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
                width: 100%;
            }

            .cart-icon {
                background: none;
                border: none;
                position: relative;
                font-size: 1.5rem;
                color: white;
                padding: 0.5rem;
            }

            .cart-count {
                position: absolute;
                top: 0;
                right: 0;
                transform: translate(25%, -25%);
            }

            /* Search input styling */
            .search-input-container {
                position: relative;
                flex-grow: 1;
                margin-right: 1rem;
            }

            .search-input {
                padding-left: 2.5rem;
                border-radius: 20px;
            }

            .search-icon {
                position: absolute;
                left: 1rem;
                top: 50%;
                transform: translateY(-50%);
                color: #6c757d;
            }
            
            /* Add some overlay to make content more readable */
            body::before {
                content: '';
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(255, 255, 255, 0.7);
                z-index: -1;
            }

            /* Category buttons styling */
            .categories-scroll {
                overflow-x: auto;
                white-space: nowrap;
                padding-bottom: 1rem;
            }

            .category-btn {
                padding: 8px 16px;
                border-radius: 20px;
                font-weight: 500;
                transition: all 0.2s ease;
            }

            .category-btn.active {
                background-color: #27AE60;
                border-color: #27AE60;
                color: white;
            }

            /* Alert styling */
            .alert {
                border-radius: 12px;
                margin: 20px 0;
            }

            /* Responsive adjustments */
            @media (max-width: 1200px) {
                .menu-items-grid .col-lg-3 {
                    width: 33.333%;
                }
            }

            @media (max-width: 992px) {
                .menu-items-grid .col-lg-3 {
                    width: 50%;
                }
            }

            @media (max-width: 576px) {
                .menu-items-grid .col-lg-3 {
                    width: 100%;
                }

                body .main-content {
                    padding: 10px;
                }

                body .container,
                body .container-fluid {
                    padding: 0 10px;
                }
            }
        </style>
        @yield('additional_styles')
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        @yield('styles')
        @stack('styles')
    </head>
    <body>
        <div id="app">
            <main>
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
            </main>
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

        @stack('scripts')

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

        <script>
            // Add CSRF token to all AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        </script>
    </body>
</html> 