<aside class="sidebar shadow-lg border-1">
    <div class="sidebar-title">
        <img src="{{ asset('default/cms_nav.png') }}" alt="logo">
    </div>
    <ul class="sidebar-list">
        @php
            $user = auth()->user();
        @endphp

    @if ($user && $user->role && $user->role->name === 'secretary')
            {{-- SB Secretary menu --}}
            <li class="sidebar-item mt-1 {{ request()->is('secretary/documents*') ? 'active' : '' }}">
                <a class="sidebar-link pe-5" href="{{ route('secretary.documents') }}">
                    <i class="fa-solid fa-file-lines"></i> Documents
                </a>
            </li>

            <li class="sidebar-item mt-1 {{ request()->is('secretary/session-meetings*') ? 'active' : '' }}">
                <a class="sidebar-link pe-5" href="{{ route('session-meetings.index') }}">
                    <i class="fa-solid fa-handshake"></i> Session Meetings
                </a>
            </li>

            <li class="sidebar-item mt-1 {{ request()->is('secretary/settings/live-sessions*') ? 'active' : '' }}">
                <a class="sidebar-link pe-5" href="{{ route('settings.live.edit') }}">
                    <i class="fa-solid fa-video"></i>  Live Session Settings
                </a>
            </li>
        @else
        <!-- Members -->
        <li class="sidebar-item mt-1 {{ request()->is('members*') ? 'active' : '' }}">
            <a class="sidebar-link pe-5" href="{{ route('members.index') }}">
                <i class="fa-solid fa-user-group"></i> Members
            </a>
        </li>

        <!-- Standing Committee -->
        <li class="sidebar-item mt-1 {{ request()->is('standing-committee*') ? 'active' : '' }}">
            <a class="sidebar-link pe-5" href="{{ route('standing-committee.index') }}">
                <i class="fa-solid fa-people-line"></i> Standing Committee
            </a>
        </li>

        <!-- District Assignment -->
        <li class="sidebar-item mt-1 {{ request()->is('district-assignments*') ? 'active' : '' }}">
            <a class="sidebar-link pe-5" href="{{ route('district-assignments.index') }}">
                <i class="fa-solid fa-map-location-dot"></i> District Assignment
            </a>
        </li>

        <!-- Photo Journal -->
        <li class="sidebar-item mt-1 {{ request()->is('photo-journals*') ? 'active' : '' }}">
            <a class="sidebar-link pe-5" href="{{ route('photo-journals.index') }}">
                <i class="fa-solid fa-camera-retro"></i> Photo Journal
            </a>
        </li>

        <!-- Organizational Chart -->
        <li class="sidebar-item mt-1 {{ request()->is('organization*') ? 'active' : '' }}">
            <a class="sidebar-link pe-5" href="{{ route('organization.index') }}">
                <i class="fa-solid fa-sitemap"></i> Organizational Chart
            </a>
        </li>

        <!-- Calendar Events -->
        <li class="sidebar-item mt-1 {{ request()->is('calendar-event*') ? 'active' : '' }}">
            <a class="sidebar-link pe-5" href="{{ route('calendar-event.index') }}">
                <i class="fa-solid fa-calendar-days"></i> Calendar Events
            </a>
        </li>

        <!-- Barangay Officials -->
        <li class="sidebar-item mt-1 {{ request()->is('barangay-officials*') ? 'active' : '' }}">
            <a class="sidebar-link pe-5" href="{{ route('barangay-officials.index') }}">
                <i class="fa-solid fa-user-tie"></i> Barangay Officials
            </a>
        </li>

        <!-- ⚙️ Settings Section -->
        <li class="sidebar-item mt-3 {{ request()->is('settings*') || request()->is('galleries*') ? 'active' : '' }}">
            <a class="sidebar-link pe-5 d-flex justify-content-between align-items-center" 
               data-bs-toggle="collapse" href="#settingsMenu" role="button" 
               aria-expanded="{{ request()->is('settings*') || request()->is('galleries*') ? 'true' : 'false' }}" 
               aria-controls="settingsMenu">
                <span><i class="fa-solid fa-gear"></i> Settings</span>
                <i class="fa-solid fa-chevron-down small"></i>
            </a>

            <!-- Submenu -->
            <ul class="collapse list-unstyled ms-4 mt-1 {{ request()->is('settings*') || request()->is('galleries*') ? 'show' : '' }}" id="settingsMenu">
                <li class="sidebar-item {{ request()->is('galleries*') ? 'active' : '' }}">
                    <a class="sidebar-link pe-5" href="{{ route('gallery.index') }}">
                        <i class="fa-regular fa-image"></i> Photo Gallery
                    </a>
                </li>
                 <li class="sidebar-item {{ request()->is('users*') ? 'active' : '' }}">
                        <a class="sidebar-link pe-5" href="{{ url('/users') }}">
                            <i class="fa-solid fa-user-gear"></i> Users
                        </a>
                    </li>
                   <!-- <li class="sidebar-item {{ request()->is('roles*') ? 'active' : '' }}">
                        <a class="sidebar-link pe-5" href="{{ url('/roles') }}">
                            <i class="fa-solid fa-id-card-clip"></i> Roles
                        </a>
                    </li>-->
                @if(config('app.lis_url'))
                <li class="sidebar-item">
                    <a class="sidebar-link pe-5" href="{{ rtrim(config('app.lis_url'), '/') }}/photo-journals" target="_blank" rel="noopener">
                        <i class="fa-solid fa-external-link-alt"></i> View Photo Journal on LIS
                    </a>
                </li>
                @endif
                {{-- You can add more submenu items here later --}}
                {{-- <li><a class="sidebar-link" href="#">Website Info</a></li> --}}
            </ul>
        </li>
         @endif
    </ul>
</aside>
