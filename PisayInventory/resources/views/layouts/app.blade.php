<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title') - PSHS Inventory</title>
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Bootstrap Icons -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <!-- DataTables CSS -->
        <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
        <!-- Custom CSS -->
        <style>
            * {
                font-family: 'Poppins', sans-serif;
            }

            :root {
                --primary: #4f46e5;
                --primary-dark: #4338ca;
                --secondary: #1e293b;
                --success: #22c55e;
                --danger: #ef4444;
                --warning: #f59e0b;
                --info: #3b82f6;
            }

            body {
                background-color: #f1f5f9;
                font-size: 0.875rem;
                line-height: 1.5;
            }

            h1 { font-size: 1.75rem; }
            h2 { font-size: 1.5rem; }
            h3 { font-size: 1.25rem; }
            h4 { font-size: 1.125rem; }

            /* Sidebar Styles */
            .sidebar {
                position: fixed;
                left: 0;
                top: 0;
                height: 100vh;
                width: 280px;
                background: white;
                box-shadow: 4px 0 10px rgba(0, 0, 0, 0.05);
                z-index: 1000;
                transition: all 0.3s ease;
            }

            .sidebar-header {
                padding: 1.5rem;
                text-align: center;
                border-bottom: 1px solid #e2e8f0;
            }

            .sidebar-header h4 {
                color: var(--primary);
                font-weight: 600;
                margin: 0;
                font-size: 1.25rem;
            }

            .menu-item {
                display: flex;
                align-items: center;
                padding: 0.75rem 1.5rem;
                color: #64748b;
                text-decoration: none;
                transition: all 0.3s ease;
                border-radius: 0 50px 50px 0;
                margin: 0.25rem 0;
                margin-right: 1rem;
                font-size: 0.875rem;
            }

            .menu-item:hover {
                background-color: #f8fafc;
                color: var(--primary);
            }

            .menu-item.active {
                background-color: #ebe9fe;
                color: var(--primary);
                font-weight: 500;
            }

            .menu-item i {
                font-size: 1.125rem;
                margin-right: 1rem;
                width: 24px;
                text-align: center;
            }

            /* Main Content Styles */
            .main-content {
                margin-left: 280px;
                padding: 2rem;
                min-height: 100vh;
            }

            /* Card Styles */
            .card {
                border: none;
                border-radius: 16px;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                background: white;
                transition: all 0.3s ease;
            }

            .card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            }

            .table-card {
                padding: 1rem;
            }

            /* Button Styles */
            .btn {
                padding: 0.5rem 1rem;
                border-radius: 8px;
                font-weight: 500;
                transition: all 0.3s ease;
                font-size: 0.875rem;
            }

            .btn-primary {
                background-color: var(--primary);
                border-color: var(--primary);
            }

            .btn-primary:hover {
                background-color: var(--primary-dark);
                border-color: var(--primary-dark);
                transform: translateY(-2px);
            }

            .btn-add {
                background-color: var(--success);
                color: white;
                border: none;
                padding: 0.5rem 1rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .btn-add:hover {
                background-color: #16a34a;
                color: white;
            }

            /* Table Styles */
            .table {
                width: 100% !important;
                margin-bottom: 0;
                font-size: 0.875rem;
            }

            .table th {
                font-weight: 600;
                color: #475569;
                background-color: #f8fafc;
                padding: 0.75rem;
                white-space: nowrap;
            }

            .table td {
                padding: 0.75rem;
                color: #334155;
                vertical-align: middle;
            }

            /* Status Badge Styles */
            .status-badge {
                padding: 0.25rem 0.75rem;
                border-radius: 50px;
                font-size: 0.75rem;
                font-weight: 500;
            }

            .status-low {
                background-color: #fef2f2;
                color: var(--danger);
            }

            .status-good {
                background-color: #f0fdf4;
                color: var(--success);
            }

            /* Action Buttons */
            .action-buttons {
                display: flex;
                gap: 0.5rem;
            }

            .action-buttons .btn {
                padding: 0.25rem;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                width: 32px;
                height: 32px;
            }

            /* Form Styles */
            .form-control, .form-select {
                border-radius: 8px;
                padding: 0.5rem 0.75rem;
                border-color: #e2e8f0;
                font-size: 0.875rem;
            }

            .form-control:focus, .form-select:focus {
                border-color: var(--primary);
                box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            }

            /* Modal Styles */
            .modal-content {
                border: none;
                border-radius: 16px;
            }

            .modal-header {
                border-bottom: 1px solid #e2e8f0;
                padding: 1.5rem;
            }

            .modal-body {
                padding: 1.5rem;
                font-size: 0.875rem;
            }

            .modal-footer {
                border-top: 1px solid #e2e8f0;
                padding: 1.5rem;
            }

            /* Alerts */
            .alert {
                border: none;
                border-radius: 12px;
                padding: 0.75rem 1rem;
                margin-bottom: 1.5rem;
                font-size: 0.875rem;
            }

            .alert-success {
                background-color: #f0fdf4;
                color: #166534;
            }

            .alert-danger {
                background-color: #fef2f2;
                color: #991b1b;
            }

            /* DataTables Customization */
            .dataTables_wrapper .dataTables_length select {
                padding: 0.25rem 1.5rem 0.25rem 0.5rem;
                border-radius: 8px;
                height: 2rem;
                font-size: 0.875rem;
            }

            .dataTables_wrapper .dataTables_filter input {
                border-radius: 8px;
                padding: 0.25rem 0.5rem;
                height: 2rem;
                font-size: 0.875rem;
            }

            .dataTables_wrapper .dataTables_paginate .paginate_button {
                border-radius: 8px;
                margin: 0 0.25rem;
            }

            /* Responsive Styles */
            @media (max-width: 768px) {
                .sidebar {
                    transform: translateX(-100%);
                }
                .sidebar.active {
                    transform: translateX(0);
                }
                .main-content {
                    margin-left: 0;
                }
                .table-responsive {
                    overflow-x: auto;
                }
                .table {
                    min-width: 800px;
                }
            }

            /* Animation */
            .fade-in {
                animation: fadeIn 0.5s ease-in;
            }

            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(10px); }
                to { opacity: 1; transform: translateY(0); }
            }

            /* Table Card Layout */
            .card.table-card {
                margin-bottom: 2rem;
            }

            .card.table-card .card-body {
                padding: 1rem;
            }

            /* Ensure tables fill their containers */
            .table-responsive {
                min-height: 400px;
            }

            .dataTables_wrapper {
                width: 100%;
            }

            /* Adjust table header and cell padding */
            .table > :not(caption) > * > * {
                padding: 0.5rem;
            }

            /* Improve table header appearance */
            .table thead th {
                background-color: #f8fafc;
                font-weight: 600;
                border-bottom: 2px solid #e2e8f0;
            }

            /* Add horizontal scroll for mobile */
            @media (max-width: 768px) {
                .table-responsive {
                    overflow-x: auto;
                }
                
                .table {
                    min-width: 800px;
                }
            }

            /* Employee Management Dropdown Styles */
            .menu-item .cursor-pointer {
                cursor: pointer;
            }

            .menu-item .bi-chevron-right,
            .menu-item .bi-chevron-down {
                transition: transform 0.3s ease;
            }

            .menu-item .bi-chevron-down {
                transform: rotate(90deg);
            }

            /* Submenu Styles */
            .menu-item .ms-4 {
                margin-left: 1rem !important;
            }

            .menu-item .ms-4 .menu-item {
                padding: 0.5rem 1rem;
                font-size: 0.8rem;
            }

            .menu-item .ms-4 .menu-item i {
                font-size: 0.9rem;
            }

            /* Animation for dropdown */
            [x-cloak] {
                display: none !important;
            }

            .menu-item [x-show] {
                transition: all 0.3s ease;
            }

            .nav-item.dropdown .dropdown-menu {
                background: #f8f9fa;
                border: none;
                border-radius: 0.5rem;
                margin-top: 0.5rem;
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            }

            .dropdown-item {
                padding: 0.5rem 1.5rem;
                color: #6c757d;
                transition: all 0.3s ease;
            }

            .dropdown-item:hover {
                background: #e9ecef;
                color: #0d6efd;
            }

            .dropdown-item i {
                width: 1.2rem;
                text-align: center;
            }

            .nav-link.dropdown-toggle::after {
                margin-left: auto;
                vertical-align: middle;
            }
        </style>
        @yield('additional_styles')
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    </head>
    <body>
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h4>PSHS Inventory</h4>
            </div>
            <div class="sidebar-menu mt-4">
                <a href="{{ route('dashboard') }}" class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>

                @if(Auth::user()->role === 'Admin')
                    <!-- Employee Management dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="employeeDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-people me-2"></i>
                            <span>Employee Management</span>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="employeeDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('roles.index') }}">
                                    <i class="bi bi-shield me-2"></i>Roles
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('roles.policies') }}">
                                    <i class="bi bi-key me-2"></i>Role Policies
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('employees.index') }}">
                                    <i class="bi bi-person-badge me-2"></i>Employees
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                <!-- Menu items for both Admin and Inventory Manager -->
                @if(Auth::user()->role === 'Admin' || Auth::user()->role === 'Inventory Manager')
                    <a href="{{ route('items.index') }}" class="menu-item {{ request()->routeIs('items.*') ? 'active' : '' }}">
                        <i class="bi bi-box"></i>
                        <span>Items</span>
                    </a>
                    <a href="{{ route('inventory.index') }}" class="menu-item {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                        <i class="bi bi-box-seam"></i>
                        <span>Inventory</span>
                    </a>
                    <a href="{{ route('suppliers.index') }}" class="menu-item {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                        <i class="bi bi-truck"></i>
                        <span>Suppliers</span>
                    </a>
                    <a href="{{ route('classifications.index') }}" class="menu-item {{ request()->routeIs('classifications.*') ? 'active' : '' }}">
                        <i class="bi bi-diagram-3"></i>
                        <span>Classifications</span>
                    </a>
                    <a href="{{ route('units.index') }}" class="menu-item {{ request()->routeIs('units.*') ? 'active' : '' }}">
                        <i class="bi bi-rulers"></i>
                        <span>Units</span>
                    </a>
                    <a href="{{ route('reports.index') }}" class="menu-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>Reports</span>
                    </a>
                @endif

                <form method="POST" action="{{ route('logout') }}" class="mt-4">
                    @csrf
                    <button type="submit" class="menu-item w-100 text-start" style="background: none; border: none;">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content fade-in">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
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

        <!-- Scripts -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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
