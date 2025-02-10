<div class="sidebar bg-dark text-white">
    <div class="sidebar-header border-bottom border-secondary py-3">
        <h3 class="text-white m-0 ps-3">PSHS Inventory</h3>
    </div>

    <ul class="nav flex-column py-2">
        <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link text-white {{ request()->routeIs('dashboard') ? 'active bg-primary' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </li>


        <!-- Employee Management Dropdown -->
        <li class="nav-item dropdown {{ request()->routeIs('employees.*', 'roles.*') ? 'show' : '' }}">
            <a class="nav-link dropdown-toggle text-white" 
            href="#" 
            id="employeeDropdown" 
            role="button" 
            data-bs-toggle="dropdown" 
                aria-expanded="{{ request()->routeIs('employees.*', 'roles.*') ? 'true' : 'false' }}">
        <div class="d-flex w-100 align-items-center">
            <i class="bi bi-people me-2"></i>
            <span class="flex-grow-1">Employee Management</span>
            <i class="bi bi-chevron-down ms-1"></i>
        </div>
    </a>
    <ul class="dropdown-menu dropdown-menu-dark {{ request()->routeIs('employees.*', 'roles.*') ? 'show' : '' }}" 
        aria-labelledby="employeeDropdown">
        <li>
            <a class="dropdown-item text-white {{ request()->routeIs('employees.*') ? 'active' : '' }}" 
               href="{{ route('employees.index') }}">
                <i class="bi bi-person-badge"></i>
                <span>Employees</span>
            </a>
        </li>
        <li>
            <a class="dropdown-item text-white {{ request()->routeIs('roles.index') ? 'active' : '' }}" 
               href="{{ route('roles.index') }}">
                <i class="bi bi-shield"></i>
                <span>Roles</span>
            </a>
        </li>
        <li>
            <a class="dropdown-item text-white {{ request()->routeIs('roles.policies') ? 'active' : '' }}" 
               href="{{ route('roles.policies') }}">
                <i class="bi bi-key"></i>
                <span>Role Policies</span>
            </a>
        </li>
    </ul>
</li>
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
            <a href="{{ route('purchases.index') }}" class="nav-link text-white {{ request()->routeIs('purchases.*') ? 'active bg-primary' : '' }}">
                <i class="bi bi-cart4"></i>
                <span>Point of Sale</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('reports.index') }}" class="nav-link text-white {{ request()->routeIs('reports.*') ? 'active bg-primary' : '' }}">
                <i class="bi bi-file-earmark-text"></i>
                <span>Reports</span>
            </a>
        </li>

        <li class="nav-item mt-auto">
            <form method="POST" action="{{ route('logout') }}">
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
    min-height: 100vh;
    width: 320px;
    box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    overflow-x: hidden; /* Prevent horizontal scroll */
    overflow-y: auto;
}

.sidebar .nav-link {
    color: white !important;
}

.sidebar .nav-link:hover {
    background-color: rgba(255,255,255,0.1);
}

/* Active state styles */
.sidebar .nav-link.active,
.sidebar .dropdown-item.active,
.sidebar .nav-link.dropdown-toggle.active {
    background-color: #198754 !important;
    font-weight: 500;
    color: white !important;  
}

/* Dropdown specific styles */
.sidebar .dropdown-menu {
    background-color: rgb(48, 50, 53) !important;
    border: none !important;
    border-radius: 0;
    margin: 0;
    width: 320px; /* Match sidebar width */
    position: static !important;
    padding: 0.75rem; /* Match nav-link margin */
    transform: none !important;
    box-shadow: none;
}
.sidebar .nav-item.dropdown.show .dropdown-menu {
    display: block;
}


.sidebar .dropdown-item {
    color: white !important;
    padding: 0.75rem; /* Match nav-link padding */
    white-space: nowrap;
    border-radius: 0.375rem; /* Match nav-link border-radius */
}

.sidebar .dropdown-item:focus,
.sidebar .dropdown-item:active {
    background-color: #198754 !important;
    color: white !important;
    outline: none !important;
    box-shadow: none !important;
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
</style>

<script>

/* Script rani para sa dropdown */

document.addEventListener('DOMContentLoaded', function() {
    const dropdownToggle = document.querySelector('#employeeDropdown');
    const parentLi = dropdownToggle.closest('.nav-item');
    
    // Toggle dropdown
    dropdownToggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation(); // Prevent event bubbling
        
        // Toggle the show class regardless of active state
        parentLi.classList.toggle('show');
        this.setAttribute('aria-expanded', 
            parentLi.classList.contains('show') ? 'true' : 'false'
        );
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        // Only close if click is outside the dropdown
        if (!parentLi.contains(e.target)) {
            parentLi.classList.remove('show');
            dropdownToggle.setAttribute('aria-expanded', 'false');
        }
    });
});
</script>