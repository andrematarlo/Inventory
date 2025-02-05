<div class="sidebar">
    <div class="sidebar-header">
        <h3>PSHS Inventory</h3>
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <!-- Employee Management Dropdown -->
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle {{ request()->routeIs('employees.*', 'roles.*') ? 'active' : '' }}" 
               href="#" 
               id="employeeDropdown" 
               role="button" 
               data-bs-toggle="dropdown" 
               aria-expanded="false">
                <i class="bi bi-people"></i>
                <span>Employee Management</span>
                <i class="bi bi-chevron-right dropdown-arrow"></i>
            </a>
            <ul class="dropdown-menu" aria-labelledby="employeeDropdown">
                <li>
                    <a class="dropdown-item {{ request()->routeIs('employees.*') ? 'active' : '' }}" 
                       href="{{ route('employees.index') }}">
                        <i class="bi bi-person-badge"></i>
                        <span>Employees</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item {{ request()->routeIs('roles.index') ? 'active' : '' }}" 
                       href="{{ route('roles.index') }}">
                        <i class="bi bi-shield"></i>
                        <span>Roles</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item {{ request()->routeIs('roles.policies') ? 'active' : '' }}" 
                       href="{{ route('roles.policies') }}">
                        <i class="bi bi-key"></i>
                        <span>Role Policies</span>
                    </a>
                </li>
            </ul>
        </li>

        <li class="nav-item">
            <a href="{{ route('items.index') }}" class="nav-link {{ request()->routeIs('items.*') ? 'active' : '' }}">
                <i class="bi bi-box"></i>
                <span>Items</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('inventory.index') }}" class="nav-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                <i class="bi bi-clipboard-data"></i>
                <span>Inventory</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                <i class="bi bi-truck"></i>
                <span>Suppliers</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('classifications.index') }}" class="nav-link {{ request()->routeIs('classifications.*') ? 'active' : '' }}">
                <i class="bi bi-tags"></i>
                <span>Classifications</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('units.index') }}" class="nav-link {{ request()->routeIs('units.*') ? 'active' : '' }}">
                <i class="bi bi-rulers"></i>
                <span>Units</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text"></i>
                <span>Reports</span>
            </a>
        </li>

        <li class="nav-item mt-auto">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link btn btn-link">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </button>
            </form>
        </li>
    </ul>
</div> 