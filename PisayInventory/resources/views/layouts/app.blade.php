<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', 'PSHS Inventory')</title>
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Bootstrap Icons -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
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
                background-color: #f8fafc;
            }

            /* Sidebar Styles */
            .sidebar {
                position: fixed;
                left: 0;
                top: 0;
                height: 100vh;
                width: 250px;
                background: white;
                box-shadow: 0 4px 12px 0 rgba(0, 0, 0, 0.05);
                overflow-y: auto;
                z-index: 1000;
            }

            .sidebar-header {
                padding: 1.5rem;
                border-bottom: 1px solid #f1f5f9;
            }

            .sidebar-header h3 {
                color: #0f172a;
                margin: 0;
                font-size: 1.25rem;
                font-weight: 600;
            }

            .nav-item {
                margin: 0.25rem 0;
            }

            .nav-link {
                display: flex;
                align-items: center;
                padding: 0.75rem 1.5rem;
                color: #64748b;
                text-decoration: none;
                transition: all 0.2s;
                border-radius: 0.375rem;
                margin: 0 0.5rem;
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
            .main-content {
    margin-left: 280px;
    min-height: 100vh;
}

.content-wrapper {
    padding: 2rem;  /* This adds the padding around your content */
}

/* Responsive Styles */
@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
    }
    .content-wrapper {
        padding: 1rem;
    }
}
        </style>
        @yield('additional_styles')
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    </head>
    <body>
        @include('layouts.sidebar')
        
        <div class="main-content">
    <div class="content-wrapper">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
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

        <!-- Scripts -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
        <script>
            // Initialize DataTables
            $(document).ready(function() {
                $('.table').DataTable({
                    responsive: true,
                    pageLength: 10,
                    order: [[0, 'asc']],
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search..."
                    }
                });

                // Auto-hide alerts
                setTimeout(function() {
                    $('.alert').alert('close');
                }, 5000);

                $('.dropdown-toggle').dropdown();
            });
        </script>
        @yield('scripts')
    </body>
</html>
