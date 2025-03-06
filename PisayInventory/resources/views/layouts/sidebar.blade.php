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
            $studentPermissions = $controller->getUserPermissions('Student Management');
        }
        
        // Check permissions for each module
        $isAdmin = Auth::check() && Auth::user()->role === 'Admin';
        $hasHRAccess = $hrPermissions && $hrPermissions->CanView;
        $hasPurchasingAccess = $purchasingPermissions && $purchasingPermissions->CanView;
        $hasReceivingAccess = $receivingPermissions && $receivingPermissions->CanView;
        $hasInventoryAccess = $inventoryPermissions && $inventoryPermissions->CanView;
        $hasStudentAccess = $studentPermissions && $studentPermissions->CanView || $isAdmin;
    @endphp

    <ul class="nav flex-column py-2">
        <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link text-white {{ request()->routeIs('dashboard') ? 'active bg-primary' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </li>
        @if($hasStudentAccess)
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
    background-color: #2D2D2D  !important;
    min-height: 100vh;
    width: 320px;
    box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    overflow-x: hidden; /* Prevent horizontal scroll */
    overflow-y: auto;
    transition: width 0.3s ease;
}

.sidebar.collapsed {
    width: 70px;
}

.sidebar.collapsed .sidebar-header {
    justify-content: center;
    padding: 0.75rem 0;
}

.sidebar.collapsed .sidebar-logo {
    margin: 0 !important;
    display: block;
}

.sidebar.collapsed .d-flex.align-items-center {
    margin: 0;
    padding: 0 5px;
    justify-content: center;
}

.sidebar.collapsed .sidebar-toggle {
    margin-right: 0;
}

.sidebar.collapsed .sidebar-title,
.sidebar.collapsed .nav-link span,
.sidebar.collapsed .dropdown-menu,
.sidebar.collapsed .nav-item form span,
.sidebar.collapsed .bi-chevron-down {
    display: none;
}

.sidebar.collapsed .nav-link {
    padding: 0.75rem;
    justify-content: center;
}

.sidebar.collapsed .nav-link i {
    margin: 0;
    font-size: 1.25rem;
}

.sidebar.collapsed ~ .main-content {
    margin-left: 70px;
}

.sidebar-toggle {
    margin-right: 2px;
    padding: 0;
    display: flex;
    align-items: center;
    cursor: pointer;
    transition: transform 0.3s;
}

.sidebar.collapsed .sidebar-toggle {
    transform: rotate(180deg);
}

.sidebar .nav-link {
    color: white !important;
    padding: 0.75rem 0.5rem !important;
    margin-left: 0.5rem;
}

.sidebar .nav-link:hover {
    background-color: rgba(255,255,255,0.1);
}

/* Active state styles */
    .sidebar .nav-link.active,
    .sidebar .dropdown-item.active,
    .sidebar .nav-link.dropdown-toggle.active {
        background-color:rgb(73, 77, 87) !important;
        font-weight: 500;
        border-left: 4px solid #ffffff;
        color: white !important;  
    }

/* Dropdown specific styles */
.sidebar .dropdown-menu {
    background-color: rgb(48, 50, 53) !important;
    border: none !important;
    border-radius: 0;
    margin: 0;
    width: 100%;
    position: static !important;
    padding: 0;
    transform: none !important;
    box-shadow: none;
}
.sidebar .nav-item.dropdown.show .dropdown-menu {
    display: block;
}


.sidebar .dropdown-item {
    color: white !important;
    padding: 0.75rem 1rem;
    white-space: nowrap;
    transition: all 0.2s ease;
}

.sidebar .dropdown-item:hover,
.sidebar .dropdown-item:focus {
    background-color: rgba(255,255,255,0.1) !important;
    color: white !important;
}

.sidebar .nav-link:focus,
.sidebar .nav-link:active {
    color: white !important;
    outline: none !important;
    box-shadow: none !important;
}
.nav-link.dropdown-toggle,
.dropdown-menu {
    margin: 0 !important;
}
.nav-link .bi-chevron-down {
    font-size: 10px; /* Adjust this value to your preferred size */
}

.nav-link.dropdown-toggle .bi-chevron-down {
    transition: transform 0.3s;
}

.show .nav-link.dropdown-toggle .bi-chevron-down {
    transform: rotate(180deg);
}
.sidebar .nav-item {
    width: 100%;
}

.sidebar .nav-link {
    white-space: nowrap;
}

.sidebar .nav.flex-column {
    width: 100%;
}
.sidebar .nav-item form {
    margin: 0;
    padding: 0;
}

.sidebar .nav-item button.nav-link {
    background: none;
    border: none;
    padding: 0.8rem 1rem;  /* Match other nav-links padding */
    width: 100%;
    text-align: left;
}

.sidebar .nav-item button.nav-link:hover {
    background-color: rgba(255,255,255,0.1);
}

.sidebar-logo {
    width: 30px;
    height: 30px;
    object-fit: contain;
    margin-right: 2px !important;
}

.sidebar-header {
    display: flex;
    align-items: center;
    padding: 0.5rem 0.25rem;
}

.sidebar-header .d-flex.align-items-center {
    gap: 2px;
}

/* Add these new styles */
.sidebar .collapse-icon {
    display: none;
}

.sidebar.collapsed .expand-icon {
    display: none !important;
}

.sidebar.collapsed .collapse-icon {
    display: inline-block !important;
}

/* Dropdown menu positioning when sidebar is collapsed */
.sidebar.collapsed .nav-item.dropdown .dropdown-menu {
    position: absolute !important;
    left: 70px !important;  /* Width of collapsed sidebar */
    top: 0 !important;
    width: 200px;  /* Adjust width of dropdown */
    background-color: #343a40 !important;
    border-radius: 0 4px 4px 0;
    box-shadow: 4px 2px 5px rgba(0,0,0,0.2);
    padding: 0.5rem;
    display: none;  /* Hide by default */
    z-index: 1000;  /* Ensure it appears above other content */
}

/* Make parent position relative for absolute positioning */
.sidebar.collapsed .nav-item.dropdown {
    position: relative;
}

/* Ensure dropdown items are visible */
.sidebar.collapsed .dropdown-menu .dropdown-item {
    display: block !important;
    color: white !important;
    padding: 8px 16px;
    margin: 2px 0;
}

/* Show dropdown menu on hover in collapsed state */
.sidebar.collapsed .nav-item.dropdown:hover > .dropdown-menu {
    display: block !important;
}

/* Style dropdown items on hover */
.sidebar.collapsed .dropdown-menu .dropdown-item:hover {
    background-color: rgba(255,255,255,0.1) !important;
    border-radius: 4px;
}

/* Remove the old hover rule that might conflict */
/* .sidebar.collapsed .nav-item.dropdown:hover .dropdown-menu {
    display: block;
} */

/* Add these styles */
.sidebar-title {
    font-size: 0.85rem;
    white-space: nowrap;
    letter-spacing: -0.5px;
    margin-right: 8px;
    padding-right: 10px;
}

/* Adjust the header layout */
.sidebar-header > .d-flex.align-items-center {
    flex: 1;
    justify-content: center;
    margin-right: 24px;
}

/* Remove the chevron from dropdown toggle */
.nav-link.dropdown-toggle::after {
    display: none !important;
}

/* Update dropdown styles */
.sidebar .dropdown-menu {
    background-color: rgb(48, 50, 53) !important;
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
    padding: 0.75rem 1rem;
    white-space: nowrap;
    transition: all 0.2s ease;
}

.sidebar .dropdown-item:hover,
.sidebar .dropdown-item:focus {
    background-color: rgba(255,255,255,0.1) !important;
    color: white !important;
}

/* Match hover effect with other nav items */
.sidebar .nav-link:hover,
.sidebar .dropdown-toggle:hover {
    background-color: rgba(255,255,255,0.1);
}

/* Active state for dropdown items */
.sidebar .dropdown-item.active,
.sidebar .dropdown-item:active {
    background-color: #495057 !important;
    font-weight: 500;
    border-left: 4px solid #ffffff;
    color: white !important;
}

/* Add padding-left to compensate for the border */
.sidebar .dropdown-item {
    padding-left: calc(1rem - 4px) !important;
}

.sidebar .dropdown-item.active {
    padding-left: 1rem !important;
}

/* Collapsed state dropdown */
.sidebar.collapsed .nav-item.dropdown .dropdown-menu {
    position: absolute !important;
    left: 70px !important;
    top: 0;
    width: 200px;
    background-color: #343a40 !important;
    border-radius: 0 4px 4px 0;
    box-shadow: 4px 2px 5px rgba(0,0,0,0.2);
    padding: 0.5rem;
}

.sidebar.collapsed .dropdown-item {
    padding: 0.75rem 1rem;
}

/* Remove old chevron styles */
.nav-link .bi-chevron-down,
.nav-link.dropdown-toggle .bi-chevron-down,
.show .nav-link.dropdown-toggle .bi-chevron-down {
    display: none !important;
}

/* Move regular nav links more to the left */
.sidebar .nav-link {
    color: white !important;
    padding: 0.75rem 0.5rem !important;
    margin-left: 0.5rem;
}

/* Keep Employee Management dropdown at original position */
.sidebar .nav-item.dropdown .nav-link {
    padding: 0.75rem 1rem !important;
    margin-left: 0;
}

/* Adjust icon spacing for regular nav items */
.sidebar .nav-link i {
    margin-right: 0.75rem;
    width: 20px;
    text-align: center;
}

/* Keep the collapsed state styling */
.sidebar.collapsed .nav-link {
    padding: 0.75rem !important;
    margin-left: 0;
    justify-content: center;
}

.sidebar.collapsed .nav-link i {
    margin-right: 0;
}

/* Adjust the logout button to match */
.sidebar .nav-item form button.nav-link {
    padding: 0.75rem 0.5rem !important;
    margin-left: 0.5rem;
}

.sidebar.collapsed .nav-item form button.nav-link {
    padding: 0.75rem !important;
    margin-left: 0;
}
/* Scrollbar styling */
.sidebar::-webkit-scrollbar {
    width: 6px;  /* Make scrollbar thinner */
}

.sidebar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);  /* Slightly lighter than sidebar */
    border-radius: 3px;
}

.sidebar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2) !important;  /* Light gray thumb */
    border-radius: 3px;
    transition: background 0.2s ease;
}

.sidebar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.3) !important;  /* Lighter on hover */
}

/* For Firefox */
.sidebar {
    scrollbar-width: thin;  /* "auto" or "thin" */
    scrollbar-color: rgba(255, 255, 255, 0.2) rgba(255, 255, 255, 0.1);  /* thumb track */
}
</style>

<script>
// Add confirmLogout function at the top of the script section
function confirmLogout() {
    return confirm('Are you sure you want to logout?');
}

/* Script for dropdown */
document.addEventListener('DOMContentLoaded', function() {
    const dropdownToggle = document.querySelector('#employeeDropdown');
    const parentLi = dropdownToggle.closest('.nav-item');
    const dropdownMenu = parentLi.querySelector('.dropdown-menu');

    // Check if we should open the dropdown on page load
    const shouldOpenDropdown = localStorage.getItem('employeeDropdownOpen') === 'true' || 
                             window.location.pathname.includes('/employees') ||
                             window.location.pathname.includes('/roles');
    
    if (shouldOpenDropdown && !document.querySelector('.sidebar').classList.contains('collapsed')) {
        parentLi.classList.add('show');
        dropdownToggle.setAttribute('aria-expanded', 'true');
    }

    dropdownToggle.addEventListener('click', function(e) {
        const sidebar = document.querySelector('.sidebar');
        e.preventDefault();
        e.stopPropagation();
       
        if (sidebar.classList.contains('collapsed')) {
            dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
        } else {
            parentLi.classList.toggle('show');
            dropdownToggle.setAttribute('aria-expanded', 
                parentLi.classList.contains('show') ? 'true' : 'false'
            );
            // Save state to localStorage
            localStorage.setItem('employeeDropdownOpen', parentLi.classList.contains('show'));
        }
    });

    // Add click handler for dropdown items to prevent closing
    dropdownMenu.addEventListener('click', function(e) {
        e.stopPropagation(); // Prevent event bubbling
        if (!document.querySelector('.sidebar').classList.contains('collapsed')) {
            parentLi.classList.add('show'); // Keep dropdown open
            dropdownToggle.setAttribute('aria-expanded', 'true');
            localStorage.setItem('employeeDropdownOpen', 'true');
        }
    });

    // Handle hover for collapsed state
    parentLi.addEventListener('mouseenter', function() {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar.classList.contains('collapsed')) {
            const dropdownMenu = parentLi.querySelector('.dropdown-menu');
            dropdownMenu.style.display = 'block';
            dropdownToggle.setAttribute('aria-expanded', 'true');
        }
    });

    parentLi.addEventListener('mouseleave', function() {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar.classList.contains('collapsed')) {
            const dropdownMenu = parentLi.querySelector('.dropdown-menu');
            dropdownMenu.style.display = 'none';
            dropdownToggle.setAttribute('aria-expanded', 'false');
        }
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const sidebar = document.querySelector('.sidebar');
        if (!parentLi.contains(e.target) && !sidebar.classList.contains('collapsed')) {
            parentLi.classList.remove('show');
            dropdownToggle.setAttribute('aria-expanded', 'false');
            localStorage.setItem('employeeDropdownOpen', 'false');
        }
    });
});

// Add this new code for sidebar toggle
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mainContent = document.querySelector('.main-content');
    
    // Set initial state to expanded (not collapsed)
    localStorage.setItem('sidebarCollapsed', 'false');
    sidebar.classList.remove('collapsed');
    mainContent.style.marginLeft = '320px';
    const expandIcon = sidebarToggle.querySelector('.expand-icon');
    const collapseIcon = sidebarToggle.querySelector('.collapse-icon');
    expandIcon.style.display = 'inline-block';
    collapseIcon.style.display = 'none';

    sidebarToggle.addEventListener('click', function(e) {
        e.preventDefault();
        sidebar.classList.toggle('collapsed');
        
        const expandIcon = this.querySelector('.expand-icon');
        const collapseIcon = this.querySelector('.collapse-icon');
        
        // Adjust main content margin and icons
        if (sidebar.classList.contains('collapsed')) {
            mainContent.style.marginLeft = '70px';
            expandIcon.style.display = 'none';
            collapseIcon.style.display = 'inline-block';
        } else {
            mainContent.style.marginLeft = '320px';
            expandIcon.style.display = 'inline-block';
            collapseIcon.style.display = 'none';
        }
        
        // Save the state
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    });
});
</script>