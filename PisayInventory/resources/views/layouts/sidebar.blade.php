<div class="sidebar text-white">
    <div class="sidebar-header border-bottom border-secondary py-3 d-flex justify-content-between align-items-center">
        <button class="btn btn-link text-white ms-2 p-0 border-0 sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list fs-4 expand-icon"></i>
            <i class="bi bi-three-dots-vertical fs-4 collapse-icon" style="display: none;"></i>
        </button>
        <div class="d-flex align-items-center">
            <img src="{{ asset('images/pisaylogo.png') }}" alt="PSHS Logo" class="sidebar-logo ms-3 me-2">
            <h3 class="text-white m-0 sidebar-title">PSHS-CVisC Inventory</h3>
        </div>
    </div>

    @php
        use Illuminate\Support\Facades\Auth;
        
        // Get user roles and convert to array
        $userPermissions = null;
        if (Auth::check()) {
            $controller = app(\App\Http\Controllers\Controller::class);
            $hrPermissions = $controller->getUserPermissions('Employee Management');
            $purchasingPermissions = $controller->getUserPermissions('Purchasing Management');
            $receivingPermissions = $controller->getUserPermissions('Receiving Management'); 
            $inventoryPermissions = $controller->getUserPermissions('Inventory');
            $studentPermissions = $controller->getUserPermissions('Students');
            $laboratoryPermissions = $controller->getUserPermissions('Laboratory Management');
            $posPermissions = $controller->getUserPermissions('Point of Sale');
        }
        
        // Check permissions for each module
        $isAdmin = Auth::check() && Auth::user()->role === 'Admin';
        $isStudent = Auth::check() && Auth::user()->role === 'Students';
        $hasHRAccess = $hrPermissions && $hrPermissions->CanView;
        $hasPurchasingAccess = $purchasingPermissions && $purchasingPermissions->CanView;
        $hasReceivingAccess = $receivingPermissions && $receivingPermissions->CanView;
        $hasInventoryAccess = $inventoryPermissions && $inventoryPermissions->CanView;
        $hasStudentAccess = $studentPermissions && $studentPermissions->CanView || $isAdmin;
        $hasLaboratoryAccess = $laboratoryPermissions && $laboratoryPermissions->CanView || $isAdmin;
        $hasPOSAccess = $posPermissions && $posPermissions->CanView || $isAdmin || $isStudent;
    @endphp

    <ul class="nav flex-column py-2">
        @if(!$isStudent)
        <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link text-white {{ request()->routeIs('dashboard') ? 'active bg-primary' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </li>
        @endif

        @if($hasStudentAccess && !$isStudent)
        <li class="nav-item">
            <a href="{{ route('students.index') }}" class="nav-link text-white {{ request()->routeIs('students.*') ? 'active bg-primary' : '' }}">
                <i class="bi bi-mortarboard"></i>
                <span>Students</span>
            </a>
        </li>
        @endif

        @if($isAdmin)
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('modules.*') ? 'active' : '' }}" href="{{ route('modules.index') }}">
                <i class="bi bi-grid-3x3-gap me-2"></i>
                <span>Modules</span>
            </a>
        </li>
        @endif

        @if($hasHRAccess)
        <li class="nav-item dropdown">
            <a href="#" class="nav-link dropdown-toggle" id="employeeDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-people"></i>
                <span>Employee Management</span>
            </a>
            <ul class="dropdown-menu" aria-labelledby="employeeDropdown">
                <li>
                    <a class="dropdown-item {{ request()->routeIs('employees.index') ? 'active' : '' }}" href="{{ route('employees.index') }}">
                        <i class="bi bi-person"></i> Employees
                    </a>
                </li>
                <li>
                    <a class="dropdown-item {{ request()->routeIs('roles.index') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                        <i class="bi bi-person-badge"></i> Roles
                    </a>
                </li>
                <li>
                    <a class="dropdown-item {{ request()->routeIs('roles.policies') ? 'active' : '' }}" href="{{ route('roles.policies') }}">
                        <i class="bi bi-shield-check"></i> Role Policies
                    </a>
                </li>
            </ul>
        </li>
        @endif

        @if($hasPurchasingAccess)
        <li class="nav-item">
            <a href="{{ route('purchases.index') }}" class="nav-link text-white {{ request()->routeIs('purchases.*') ? 'active bg-primary' : '' }}">
                <i class="bi bi-cart4"></i>
                <span>Purchase Management</span>
            </a>
        </li>
        @endif

        @if($hasReceivingAccess)
        <li class="nav-item">
            <a href="{{ route('receiving.index') }}" class="nav-link text-white {{ request()->routeIs('receiving.*') ? 'active bg-primary' : '' }}">
                <i class="bi bi-box-seam"></i>
                <span>Receiving Management</span>
            </a>
        </li>
        @endif

        @if($hasInventoryAccess)
        <li class="nav-item">
            <a href="{{ route('items.index') }}" class="nav-link text-white {{ request()->routeIs('items.*') ? 'active bg-primary' : '' }}">
                <i class="bi bi-box"></i>
                <span>Items</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('inventory.index') }}" class="nav-link text-white {{ request()->routeIs('inventory.*') ? 'active bg-primary' : '' }}">
                <i class="bi bi-clipboard-data"></i>
                <span>Inventory</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('suppliers.index') }}" class="nav-link text-white {{ request()->routeIs('suppliers.*') ? 'active bg-primary' : '' }}">
                <i class="bi bi-truck"></i>
                <span>Suppliers</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('classifications.index') }}" class="nav-link text-white {{ request()->routeIs('classifications.*') ? 'active bg-primary' : '' }}">
                <i class="bi bi-tags"></i>
                <span>Classifications</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('units.index') }}" class="nav-link text-white {{ request()->routeIs('units.*') ? 'active bg-primary' : '' }}">
                <i class="bi bi-rulers"></i>
                <span>Units</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('reports.index') }}" class="nav-link text-white {{ request()->routeIs('reports.*') ? 'active bg-primary' : '' }}">
                <i class="bi bi-file-earmark-text"></i>
                <span>Reports</span>
            </a>
        </li>
        @endif

        @if($hasLaboratoryAccess || $isStudent)
        <li class="nav-item dropdown">
            <a href="#" class="nav-link dropdown-toggle" id="laboratoryDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-building"></i>
                <span>Laboratory Management</span>
            </a>
            <ul class="dropdown-menu" aria-labelledby="laboratoryDropdown">
                <li>
                    <a class="dropdown-item" href="{{ route('laboratories.index') }}">
                        <i class="bi bi-building"></i> Laboratories
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('equipment.index') }}">
                        <i class="bi bi-tools"></i> Equipment
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('laboratory.reservations') }}">
                        <i class="bi bi-calendar-check"></i> Reservations
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('equipment.borrowings') }}">
                        <i class="bi bi-box-arrow-right"></i> Equipment Borrowing
                    </a>
                </li>
            </ul>
        </li>
        @endif

        @if(Auth::check())
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#posCollapse" role="button" aria-expanded="false" aria-controls="posCollapse">
                <i class="bi bi-shop"></i>
                <span class="nav-text">
                    Point of Sale
                    <i class="bi bi-chevron-down dropdown-arrow"></i>
                </span>
            </a>
            <div class="collapse" id="posCollapse">
                <ul class="nav-content">
                    @if(Auth::user()->role === 'Students')
                    <li>
                        <a href="{{ route('pos.kiosk.index') }}" class="dropdown-item {{ request()->routeIs('pos.kiosk.*') ? 'active' : '' }}">
                            <i class="bi bi-person-workspace"></i>
                            <span>Student Kiosk</span>
                        </a>
                    </li>
                    @endif
                    
                    @if(in_array(Auth::user()->role, ['Admin', 'Cashier']))
                    <li>
                        <a href="{{ route('pos.orders.index') }}" class="dropdown-item {{ request()->routeIs('pos.orders.index') ? 'active' : '' }}">
                            <i class="bi bi-cart3"></i>
                            <span>Orders</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('pos.orders.create') }}" class="dropdown-item {{ request()->routeIs('pos.orders.create') ? 'active' : '' }}">
                            <i class="bi bi-plus-circle"></i>
                            <span>New Order</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('pos.cashier.index') }}" class="dropdown-item {{ request()->routeIs('pos.cashier.*') ? 'active' : '' }}">
                            <i class="bi bi-cash-register"></i>
                            <span>Cashiering</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('pos.deposits.index') }}" class="dropdown-item {{ request()->routeIs('pos.deposits.*') ? 'active' : '' }}">
                            <i class="bi bi-wallet2"></i>
                            <span>Cash Deposit</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('pos.reports.index') }}" class="dropdown-item {{ request()->routeIs('pos.reports.*') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-text"></i>
                            <span>Reports</span>
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
        </li>
        @endif

        <li class="nav-item mt-auto">
            <form method="POST" action="{{ route('logout') }}" onsubmit="return confirmLogout()">
                @csrf
                <button type="submit" class="nav-link btn btn-link text-white w-100 text-start px-3">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </button>
            </form>
        </li>
    </ul>
</div>

<style>
.sidebar {
    background-color: #2D2D2D !important;
    min-height: 100vh;
    width: 250px;
    box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    overflow-x: hidden;
    overflow-y: auto;
    transition: all 0.3s ease;
    z-index: 1030;
}

.sidebar.collapsed {
    width: 90px !important;
    background-color: #222222 !important;
}

.sidebar-header {
    display: flex;
    align-items: center;
    padding: 0.75rem 0.5rem;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar .nav-link {
    color: white !important;
    padding: 0.6rem 0.6rem !important;
    display: flex !important;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    border-radius: 0;
    margin-right: 0.25rem;
}

.sidebar .nav-link i {
    width: 18px;
    text-align: center;
    font-size: 1rem;
    min-width: 18px;
    color: #ffffff;
}

.sidebar .nav-link span {
    white-space: nowrap;
    opacity: 1;
    transition: opacity 0.3s ease;
    font-size: 0.85rem;
    overflow: visible;
    text-overflow: clip;
    max-width: 200px;
}

.sidebar .nav-link:hover {
    background-color: rgba(255,255,255,0.1);
}

/* Active state */
.sidebar .nav-link.active {
    background-color: rgba(255,255,255,0.1) !important;
    border-left: none;
    position: relative;
}

.sidebar .nav-link.active i {
    color: #fff;
}

.sidebar .nav-link.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background-color: #8ba8d9;
}

/* Collapsed state */
.sidebar.collapsed {
    width: 90px !important;
    background-color: #222222 !important;
}

.sidebar.collapsed .sidebar-header {
    height: auto;
    padding: 0.5rem 0;
    justify-content: center;
}

/* Make logo smaller in collapsed state */
.sidebar.collapsed .sidebar-logo {
    width: 16px !important;
    height: 16px !important;
    margin: 0 !important;
    display: block;
}

.sidebar.collapsed .sidebar-title,
.sidebar.collapsed .nav-link span,
.sidebar.collapsed .dropdown-menu,
.sidebar.collapsed .nav-item form span,
.sidebar.collapsed .dropdown-arrow {
    display: none;
}

.sidebar.collapsed .nav-link {
    padding: 0.85rem 0 !important;
    justify-content: center;
    width: 100%;
}

.sidebar.collapsed .nav-link i {
    margin: 0;
    font-size: 1.3rem;
    color: #ffffff;
}

.sidebar.collapsed .sidebar-toggle i {
    font-size: 0.8rem !important; /* Make toggle icon smaller too */
}

.sidebar.collapsed .nav-item {
    width: 100%;
    display: flex;
    justify-content: center;
}

.sidebar.collapsed ~ .main-content {
    margin-left: 90px;
}

.sidebar.collapsed .nav-item.dropdown .dropdown-menu {
    left: 90px !important;
}

.main-content.sidebar-collapsed {
    margin-left: 90px;
}

/* Hide the active indicator dot in very narrow sidebar */
.sidebar.collapsed .nav-link.active::after {
    display: none;
}

/* Adjust hover tooltip to work with very narrow sidebar */
.sidebar.collapsed .nav-item:hover .nav-link::after {
    left: 90px;
    font-size: 0.8rem;
    padding: 4px 8px;
}

/* Dropdown styles */
.sidebar .dropdown-menu {
    background-color: #1e1e1e !important;
    border: none !important;
    border-radius: 0;
    margin: 0;
    width: 100%;
    position: static !important;
    padding: 0;
    transform: none !important;
    box-shadow: none;
}

.sidebar .dropdown-item {
    color: white !important;
    padding: 0.6rem 0.6rem 0.6rem 2rem;
    white-space: nowrap;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem !important; /* Smaller font size for dropdown items */
    overflow: visible;
    text-overflow: clip;
}

.sidebar .dropdown-item i {
    color: #ffffff;
    width: 16px;
    text-align: center;
    font-size: 0.9rem;
    min-width: 16px;
}

.sidebar .dropdown-item:hover {
    background-color: rgba(255,255,255,0.1) !important;
}

/* Scrollbar */
.sidebar::-webkit-scrollbar {
    width: 4px;
}

.sidebar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
}

.sidebar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 2px;
}

/* Main content adjustment */
.main-content {
    margin-left: 250px;
    transition: margin-left 0.3s ease;
}

.main-content.sidebar-collapsed {
    margin-left: 30px;
}

/* Also adjust the sidebar-logo size for normal state */
.sidebar-logo {
    width: 24px;
    height: 24px;
    object-fit: contain;
    margin-right: 0.5rem !important;
    transition: all 0.3s ease;
}

/* Fixed dropdown styles for POS submenu */
.sidebar .nav-content {
    list-style-type: none !important;
    padding-left: 0.5rem !important;
    margin-left: 0 !important;
}

.sidebar .collapse .nav-content li {
    padding-left: 0;
    margin-left: 0;
    list-style: none;
    position: relative;
}

/* Add small dot indicators for dropdown items instead of bullets */
.sidebar .collapse .nav-content li::before {
    display: none !important; /* Remove all bullet points */
}

/* Make submenus appear properly when collapsed */
.sidebar.collapsed .collapse.show {
    position: absolute;
    left: 90px;
    top: 0;
    width: 180px;
    background-color: #222222;
    padding: 0;
    border-radius: 0 4px 4px 0;
    box-shadow: 0 3px 6px rgba(0,0,0,0.16);
}

/* Style dropdown items */
.sidebar .collapse .dropdown-item {
    padding-left: 1rem !important;
    margin: 1px 0;
}

/* Make sidebar title smaller */
.sidebar-title {
    font-size: 0.8rem !important;
    letter-spacing: -0.4px;
    white-space: nowrap;
    margin-right: 0.25rem !important;
}

/* Hide dropdown chevron icon specifically for POS */
.sidebar .nav-item .nav-link[href="#posCollapse"] .bi-chevron-down,
.sidebar .nav-item .nav-link[data-bs-toggle="collapse"][href="#posCollapse"] .bi-chevron-down,
.sidebar .nav-item .collapse-icon {
    display: none !important;
}

/* Add extra style to make POS menu items readable but compact */
.sidebar #posCollapse .dropdown-item {
    font-size: 0.75rem !important;
    padding: 0.4rem 0.6rem !important;
    margin: 1px 0;
}

/* Fix dropdown icon alignment for laboratory dropdown */
.sidebar .nav-item.dropdown .nav-link.dropdown-toggle::after {
    display: none !important;
}

/* Make lab management dropdown items smaller */
.sidebar .nav-item.dropdown .dropdown-menu .dropdown-item {
    font-size: 0.75rem !important;
    padding: 0.5rem 0.75rem 0.5rem 2rem !important;
}

/* Improve spacing in dropdown menus */
.sidebar .dropdown-menu {
    padding: 0.25rem 0 !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mainContent = document.querySelector('.main-content');
    const navLinks = document.querySelectorAll('.sidebar .nav-link');
    
    // Add data-title attribute to all nav links for tooltip
    navLinks.forEach(link => {
        const spanText = link.querySelector('span')?.textContent.trim();
        if (spanText) {
            link.setAttribute('data-title', spanText);
        }
    });
    
    // Check for saved state
    const sidebarState = localStorage.getItem('sidebarCollapsed');
    if (sidebarState === 'true') {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('sidebar-collapsed');
        
        // Toggle visibility of icons in collapsed state
        document.querySelector('.expand-icon').style.display = 'none';
        document.querySelector('.collapse-icon').style.display = 'block';
    }

    sidebarToggle.addEventListener('click', function(e) {
        e.preventDefault();
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('sidebar-collapsed');
        
        // Toggle icon visibility
        const isCollapsed = sidebar.classList.contains('collapsed');
        document.querySelector('.expand-icon').style.display = isCollapsed ? 'none' : 'block';
        document.querySelector('.collapse-icon').style.display = isCollapsed ? 'block' : 'none';
        
        // Update logo size
        const logo = document.querySelector('.sidebar-logo');
        if (logo) {
            logo.style.width = isCollapsed ? '16px' : '24px';
            logo.style.height = isCollapsed ? '16px' : '24px';
        }
        
        // Save state
        localStorage.setItem('sidebarCollapsed', isCollapsed);
    });

    // Handle dropdowns
    const dropdownToggles = document.querySelectorAll('.nav-link.dropdown-toggle');
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            if (sidebar.classList.contains('collapsed')) {
                e.preventDefault();
                return;
            }
            
            e.preventDefault();
            const parent = this.closest('.nav-item.dropdown');
            parent.classList.toggle('show');
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.nav-item.dropdown')) {
            document.querySelectorAll('.nav-item.dropdown').forEach(dropdown => {
                dropdown.classList.remove('show');
            });
        }
    });

    // Special handling for POS dropdown - remove arrow/indicator
    const posDropdownToggle = document.querySelector('.nav-link[href="#posCollapse"]');
    if (posDropdownToggle) {
        const arrowIcon = posDropdownToggle.querySelector('.bi-chevron-down');
        if (arrowIcon) {
            arrowIcon.style.display = 'none';
        }
    }
});

function confirmLogout() {
    return confirm('Are you sure you want to logout?');
}
</script>