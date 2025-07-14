<aside class="sidebar shadow-lg border-1">
    <div class="sidebar-title">
        <img src="{{ asset('default/cms_nav.png') }}" alt="logo">
    </div>
    <ul class="sidebar-list">
        <!-- Members -->
        <li class="sidebar-item mt-1 {{ request()->is('reports*') ? 'active' : '' }}">
            <a class="sidebar-link pe-5" href="#">
                <i class="fa-solid fa-user-group"></i> Members
            </a>
        </li>

        <!-- Standing Committee -->
        <li class="sidebar-item mt-1 {{ request()->is('standing-committee*') ? 'active' : '' }}">
            <a class="sidebar-link pe-5" href="#">
                <i class="fa-solid fa-people-line"></i> Standing Committee
            </a>
        </li>

        <!-- District Assignment -->
        <li class="sidebar-item mt-1 {{ request()->is('district-assignment*') ? 'active' : '' }}">
            <a class="sidebar-link pe-5" href="#">
                <i class="fa-solid fa-map-location-dot"></i> District Assignment
            </a>
        </li>

        <!-- Photo Journal -->
        <li class="sidebar-item mt-1 {{ request()->is('photo-journal*') ? 'active' : '' }}">
            <a class="sidebar-link pe-5" href="#">
                <i class="fa-solid fa-camera-retro"></i> Photo Journal
            </a>
        </li>

        <!-- Organizational Chart -->
        <li class="sidebar-item mt-1 {{ request()->is('organizational-chart*') ? 'active' : '' }}">
            <a class="sidebar-link pe-5" href="#">
                <i class="fa-solid fa-sitemap"></i> Organizational Chart
            </a>
        </li>

        <!-- Calendar Events -->
        <li class="sidebar-item mt-1 {{ request()->is('calendar-events*') ? 'active' : '' }}">
            <a class="sidebar-link pe-5" href="#">
                <i class="fa-solid fa-calendar-days"></i> Calendar Events
            </a>
        </li>

        <!-- Barangay Officials -->
        <li class="sidebar-item mt-1 {{ request()->is('barangay-officials*') ? 'active' : '' }}">
            <a class="sidebar-link pe-5" href="#">
                <i class="fa-solid fa-user-tie"></i> Barangay Officials
            </a>
        </li>
    </ul>

</aside>
