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
        
        <!-- Custom CSS -->
        <style>
            * {
                font-family: 'Poppins', sans-serif;
            }

            body {
                background-color: rgba(214, 255, 236, 0.09);
                background-image: url("{{ asset('images/pshsbackground.jpg') }}");
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
                background-attachment: fixed;
                min-height: 100vh;
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

            /* Main content adjustment */
            body .main-content {
                margin-left: 320px;
                padding: 0 0 0 10px;
                transition: margin-left 0.3s ease;
                width: calc(100% - 320px);
                position: relative;
            }

            /* For collapsed sidebar */
            body .main-content.sidebar-collapsed {
                margin-left: 25px;
                width: calc(100% - 25px);
            }

            body .main-content .content-wrapper {
                padding: 2rem 3rem;
                margin: 0 auto;
                width: 100%;
                overflow-x: auto;
            }

            body .container, 
            body .container-fluid {
                max-width: 100%;
                padding-left: 15px;
                padding-right: 15px;
            }

            /* Responsive */
            @media (max-width: 768px) {
                body .main-content {
                    margin-left: 0;
                    padding-left: 0;
                    width: 100%;
                }
            }

            /* Added z-index adjustment for modals to appear above sidebar */
            .modal-backdrop {
                z-index: 1040 !important;
            }
            .modal {
                z-index: 1050 !important;
            }
            .modal-dialog {
                margin: 2rem auto;
                max-width: 95%;
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

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Script to update main-content margin when sidebar is toggled
                const sidebar = document.querySelector('.sidebar');
                const mainContent = document.querySelector('.main-content');
                const sidebarToggle = document.getElementById('sidebarToggle');
                
                if (sidebarToggle) {
                    sidebarToggle.addEventListener('click', function() {
                        if (sidebar.classList.contains('collapsed')) {
                            mainContent.classList.add('sidebar-collapsed');
                        } else {
                            mainContent.classList.remove('sidebar-collapsed');
                        }
                    });
                }
                
                // Check initial state on page load
                if (sidebar && sidebar.classList.contains('collapsed')) {
                    mainContent.classList.add('sidebar-collapsed');
                }
                
                // SweetAlert handling with rawjs
                @if(session('alert'))
                    var alertData = @json(session('alert'));
                    Swal.fire({
                        icon: alertData.type,
                        title: alertData.title,
                        text: alertData.text,
                        @if(isset(session('alert')['footer']))
                        footer: @json(session('alert')['footer']),
                        @endif
                        confirmButtonColor: alertData.type === 'success' ? '#4CAF50' : '#F44336'
                    });
                @endif
                
                @if(session('success'))
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: @json(session('success')),
                        confirmButtonColor: '#4CAF50'
                    });
                @endif
                
                @if(session('error'))
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: @json(session('error')),
                        confirmButtonColor: '#F44336'
                    });
                @endif
                
                @if(session('warning'))
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning',
                        text: @json(session('warning')),
                        confirmButtonColor: '#F9A825'
                    });
                @endif
                
                @if(session('info'))
                    Swal.fire({
                        icon: 'info',
                        title: 'Information',
                        text: @json(session('info')),
                        confirmButtonColor: '#2196F3'
                    });
                @endif
            });
        </script>

        @yield('scripts')
        @stack('scripts')
    </body>
</html>
