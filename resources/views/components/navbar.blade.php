<nav class="app-navbar">
    <div class="navbar-left">
        <button type="button"
                class="navbar-toggle"
                id="sidebarMobileToggle"
                aria-label="Open navigation menu">
            <i class="fa-solid fa-bars"></i>
        </button>

        <button type="button"
                class="navbar-desktop-toggle"
                id="sidebarNavbarCollapse"
                aria-label="Toggle sidebar">
            <i class="fa-solid fa-bars-staggered"></i>
        </button>

        <div class="navbar-heading">
            <span class="navbar-company">
                {{ $organizationName ?? config('app.client', 'CMS') }}
            </span>
            <span class="navbar-caption">Content Management Portal</span>
        </div>
    </div>

    <div class="dropdown user-dropdown-wrapper">
        <button class="user-menu"
                type="button"
                id="userDropdown"
                data-bs-toggle="dropdown"
                data-bs-auto-close="true"
                aria-expanded="false"
                aria-label="Open user menu">
            <span class="user-avatar">
                {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
            </span>

            <span class="user-details">
                <span class="user-name">{{ Auth::user()->name }}</span>
                <span class="user-role">
                    {{ Auth::user()->role->name ?? 'User' }}
                </span>
            </span>

            <i class="fa-solid fa-chevron-down user-chevron"></i>
        </button>

        <ul class="dropdown-menu dropdown-menu-end user-dropdown-menu"
            aria-labelledby="userDropdown">
            <li>
                <h6 class="dropdown-header">
                    Signed in as {{ Auth::user()->name }}
                </h6>
            </li>

            <li>
                <a class="dropdown-item" href="#">
                    <i class="fa-regular fa-user me-2"></i>
                    Profile
                </a>
            </li>

            <li><hr class="dropdown-divider"></li>

            <li>
                <button type="button"
                        class="dropdown-item text-danger"
                        id="logoutButton">
                    <i class="fa-solid fa-right-from-bracket me-2"></i>
                    Logout
                </button>
            </li>
        </ul>

        <form id="logout-form"
              action="{{ route('logout') }}"
              method="POST"
              class="d-none">
            @csrf
        </form>
    </div>
</nav>

