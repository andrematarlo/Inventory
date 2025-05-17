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
            // Check for kiosk permissions specifically
            $kioskPermissions = $controller->getUserPermissions('Kiosk');
        }
        
        // Check permissions for each module
        $isAdmin = Auth::check() && Auth::user()->role === 'Admin';
        $isStudent = Auth::check() && Auth::user()->role === 'Students';
        $isCashier = Auth::check() && Auth::user()->role === 'Cashier';
        
        $hasHRAccess = $hrPermissions && $hrPermissions->CanView;
        $hasPurchasingAccess = $purchasingPermissions && $purchasingPermissions->CanView;
        $hasReceivingAccess = $receivingPermissions && $receivingPermissions->CanView;
        $hasInventoryAccess = $inventoryPermissions && $inventoryPermissions->CanView;
        $hasStudentAccess = ($studentPermissions && $studentPermissions->CanView) || $isAdmin;
        $hasLaboratoryAccess = ($laboratoryPermissions && $laboratoryPermissions->CanView) || $isAdmin;
        $hasPOSAccess = ($posPermissions && $posPermissions->CanView) || $isAdmin || $isCashier;
        $hasKioskAccess = ($kioskPermissions && $kioskPermissions->CanView) || $isAdmin || $isStudent;
        
        $canManagePOS = ($posPermissions && $posPermissions->CanAdd) || $isAdmin || $isCashier;
        $canViewReports = ($posPermissions && $posPermissions->CanView) || $isAdmin;
    @endphp

    <ul class="nav flex-column py-2">
        @if(!$isStudent && Auth::user()->role !== 'Teacher')
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
                @if($hasLaboratoryAccess && !$isStudent)
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
                @endif
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
                <li>
                    <a class="dropdown-item" href="{{ route('laboratory.accountability.index') }}">
                        <i class="bi bi-file-text"></i> Accountability Records
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('laboratory.reagent.index') }}">
                        <i class="bi bi-flask"></i> Laboratory Reagent Requests
                    </a>
                </li>
            </ul>
        </li>
        @endif

        @if($hasKioskAccess)
        <li class="nav-item">
            <a href="{{ route('pos.orders.create') }}" class="nav-link text-white {{ request()->routeIs('pos.orders.create') ? 'active bg-primary' : '' }}">
                <i class="bi bi-person-workspace"></i>
                <span>Student Kiosk</span>
            </a>
        </li>
        @endif

        @if($hasPOSAccess)
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#posCollapse" role="button" aria-expanded="false" aria-controls="posCollapse">
                <i class="bi bi-shop"></i>
                <span class="nav-text">Point of Sale</span>
            </a>
            <div class="collapse" id="posCollapse">
                <ul class="nav-content">
                    @if($isStudent)
                    <li>
                        <a href="{{ route('pos.orders.create') }}" class="dropdown-item {{ request()->routeIs('pos.orders.create') ? 'active' : '' }}">
                            <i class="bi bi-person-workspace"></i>
                            <span>Student Kiosk</span>
                        </a>
                    </li>
                    @endif
                    
                    @if($canManagePOS)
                    <li>
                        <a href="{{ route('pos.menu-items.index') }}" class="dropdown-item {{ request()->routeIs('pos.menu-items.*') ? 'active' : '' }}">
                            <i class="bi bi-list-check"></i>
                            <span>Manage Menu Items</span>
                        </a>
                    </li>
                    
                    <li>
                        <a href="{{ route('pos.dashboard') }}" class="dropdown-item {{ request()->routeIs('pos.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2"></i>
                            <span>Orders Dashboard</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('pos.orders.index') }}" class="dropdown-item {{ request()->routeIs('pos.orders.*') ? 'active' : '' }}">
                            <i class="bi bi-cart3"></i>
                            <span>Orders</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('pos.deposits.index') }}" class="dropdown-item {{ request()->routeIs('pos.deposits.*') ? 'active' : '' }}">
                            <i class="bi bi-wallet2"></i>
                            <span>Cash Deposit</span>
                        </a>
                    </li>
                    @endif
                    
                    @if($canViewReports)
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
                <button type="submit" class="nav-link btn btn-link text-white w-100 text-start px-3 logout-button">
                    <i class="bi bi-box-arrow-right"></i>
                    <span class="logout-text">Logout</span>
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
    color: #fff;
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
    width: 24px !important;
    height: 24px !important;
    margin: 0 !important;
    display: block;
}

.sidebar.collapsed .sidebar-title,
.sidebar.collapsed .nav-link span:not(.logout-text),
.sidebar.collapsed .dropdown-menu,
.sidebar.collapsed .nav-item form span:not(.logout-text),
.sidebar.collapsed .dropdown-arrow {
    display: none;
}

.sidebar.collapsed .nav-link {
    padding: 0.75rem 0 !important;
    justify-content: center;
    width: 100%;
}

.sidebar.collapsed .nav-link i {
    margin: 0;
    font-size: 1rem !important;
    color: #fff;
}

.sidebar.collapsed .sidebar-toggle i {
    font-size: 1rem !important;
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
    background-color: #000000 !important;
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
    color: #fff;
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
    background-color: #000000;
    padding: 0.5rem 0;
    border-radius: 0 4px 4px 0;
    box-shadow: 2px 2px 5px rgba(0,0,0,0.2);
    min-width: 200px;
    z-index: 1031;
    top: auto;
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

/* Add specific styles for POS dropdown items */
.sidebar #posCollapse .dropdown-item {
    font-size: 0.75rem !important;
    padding: 0.4rem 0.6rem !important;
    margin: 1px 0;
}

.sidebar #posCollapse .dropdown-item.active {
    background-color: transparent !important;
}

.sidebar #posCollapse .dropdown-item:hover {
    background-color: rgba(255,255,255,0.1) !important;
}

/* Override any Bootstrap active states */
.sidebar .dropdown-item.active,
.sidebar .dropdown-item:active {
    background-color: transparent !important;
    color: white !important;
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

/* Add position relative to the parent nav-item */
.sidebar .nav-item {
    position: relative;
}

/* Ensure the dropdown appears next to its parent */
.sidebar.collapsed .nav-item .collapse {
    position: absolute;
    top: 0;
    left: 100%;
    display: none;
}

.sidebar.collapsed .nav-item .collapse.show {
    display: block;
}

/* Update text colors in sidebar */
.sidebar .nav-link,
.sidebar .nav-link span,
.sidebar .nav-link i,
.sidebar .dropdown-item,
.sidebar .dropdown-item i,
.sidebar-title,
.sidebar .nav-content .dropdown-item {
    color: #FFFFFF !important;
}

/* Keep white text in dropdowns */
.sidebar .dropdown-menu {
    background-color: #000000 !important;
}

.sidebar .dropdown-menu .dropdown-item {
    color: #FFFFFF !important;
}

/* Make sure active items are still visible */
.sidebar .nav-link.active,
.sidebar .dropdown-item.active {
    background-color: rgba(255,255,255,0.1) !important;
    color: #FFFFFF !important;
}

/* Ensure hover states maintain white text */
.sidebar .nav-link:hover,
.sidebar .dropdown-item:hover {
    background-color: rgba(255,255,255,0.1) !important;
    color: #FFFFFF !important;
}

/* Update dropdown styles for Point of Sale */
.sidebar #posCollapse {
    background-color: transparent !important;
}

.sidebar #posCollapse .nav-content {
    background-color: transparent !important;
}

.sidebar #posCollapse .dropdown-item,
.sidebar #posCollapse .dropdown-item i,
.sidebar #posCollapse .dropdown-item span {
    color: #FFFFFF !important;
    font-weight: normal;
}

/* Make sure the dropdown items are visible */
.sidebar .collapse .nav-content .dropdown-item {
    color: #FFFFFF !important;
    background-color: transparent !important;
}

/* Hover effect for dropdown items */
.sidebar .collapse .nav-content .dropdown-item:hover {
    background-color: rgba(255,255,255,0.1) !important;
    color: #FFFFFF !important;
}

/* Active state for dropdown items */
.sidebar .collapse .nav-content .dropdown-item.active {
    background-color: rgba(255,255,255,0.1) !important;
    color: #FFFFFF !important;
}

/* Update text colors for Point of Sale dropdown */
.sidebar #posCollapse .nav-content .dropdown-item,
.sidebar #posCollapse .nav-content .dropdown-item i,
.sidebar #posCollapse .nav-content .dropdown-item span {
    color: #FFFFFF !important;
    font-weight: normal;
}

/* Make sure active and hover states maintain white text */
.sidebar #posCollapse .nav-content .dropdown-item:hover,
.sidebar #posCollapse .nav-content .dropdown-item.active {
    color: #FFFFFF !important;
    background-color: rgba(255,255,255,0.1) !important;
}

/* Update close button color */
.btn-close {
    background: transparent url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z'/%3e%3c/svg%3e") center/1em auto no-repeat !important;
    opacity: 1 !important;
}

.btn-close:hover {
    opacity: 0.75 !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mainContent = document.querySelector('.main-content');
    const navLinks = document.querySelectorAll('.sidebar .nav-link:not(.logout-button)');
    
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