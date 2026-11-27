
<nav class="app-navbar">
    {{-- LEFT: Sidebar toggles and heading --}}
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

    

    {{-- RIGHT: User profile --}}
    <div class="navbar-right">
        {{-- CENTER: Notifications --}}
    @auth
        @php
            $user = auth()->user();

            $unreadCount = $user->unreadNotifications()->count();

            $latestNotifications = $user
                ->unreadNotifications()
                ->latest()
                ->take(5)
                ->get();
        @endphp

        <div class="navbar-right">
            <div class="dropdown">
                <button
                    class="notification-bell"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    aria-label="Notifications"
                >
                    <i class="fa-solid fa-bell"></i>

                    @if($unreadCount > 0)
                        <span class="notification-badge">
                            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                        </span>
                    @endif
                </button>

                <div class="dropdown-menu dropdown-menu-end notification-menu">

                    <div class="notification-header">
                        <span>Notifications</span>

                        @if($unreadCount > 0)
                            <span class="notification-count">
                                {{ $unreadCount }} new
                            </span>
                        @endif
                    </div>

                    @forelse($latestNotifications as $notification)
                        <form
                            method="POST"
                            action="{{ route('secretary.notifications.read', $notification->id) }}"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="dropdown-item notification-item"
                            >
                                <span class="notification-item-icon">
                                    <i class="fa-solid fa-file-lines"></i>
                                </span>

                                <span class="notification-item-content">
                                    <span class="notification-item-title">
                                        New Document for Review
                                    </span>

                                    <span class="notification-item-description">
                                        {{ $notification->data['document_title'] ?? 'New legislative document' }}
                                    </span>

                                    <span class="notification-item-time">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </span>
                                </span>
                            </button>
                        </form>
                    @empty
                        <div class="notification-empty">
                            <i class="fa-regular fa-bell-slash"></i>
                            <span>No new notifications</span>
                        </div>
                    @endforelse

                    <a
                        href="{{ route('secretary.notifications.index') }}"
                        class="notification-footer"
                    >
                        View all notifications
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    @endauth
        @auth
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
        @endauth
    </div>
</nav>
