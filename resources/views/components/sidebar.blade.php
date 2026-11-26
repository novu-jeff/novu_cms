<aside class="sidebar" id="appSidebar">
    <div class="sidebar-title">
        @php
        $organizationSetting = \App\Models\OrganizationSetting::first();

        $logoUrl = $organizationSetting?->logo_path
            ? \Illuminate\Support\Facades\Storage::url($organizationSetting->logo_path)
            : asset('default/cms_nav.png');
    @endphp

    <a href="{{ route('dashboard') }}" class="sidebar-brand">
        <img
            src="{{ $logoUrl }}"
            alt="{{ $organizationSetting?->organization_name ?? 'CMS Logo' }}"
        >
        <span class="sidebar-brand-text"></span>
    </a>

        <!--<button type="button"
                class="sidebar-collapse-btn"
                id="sidebarCollapse"
                aria-label="Collapse sidebar"
                title="Collapse sidebar">
            <i class="fa-solid fa-angles-left"></i>
        </button>-->
    </div>

    <div class="sidebar-scroll">
        <ul class="sidebar-list">
            @php
                $user = auth()->user();
        @endphp

    @if ($user && $user->role && $user->role->name === 'secretary')
            {{-- SB Secretary Dashboard --}}
            
            <li class="sidebar-item mt-1 {{ request()->routeIs('secretary.dashboard') ? 'active' : '' }}">
                <a class="sidebar-link"
                href="{{ route('secretary.dashboard') }}"
                data-label="Dashboard"
                title="Dashboard">
                    <span class="sidebar-icon">
                        <i class="fa-solid fa-gauge-high"></i>
                    </span>
                        <span class="sidebar-text">Dashboard</span> 
                </a>
            </li>

            {{-- Document Review --}}
            <li class="sidebar-item mt-1 {{ request()->is('secretary/documents*') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('secretary.documents') }}"
                data-label="Documents"
                title="Documents">
                    <span class="sidebar-icon">
                        <i class="fa-solid fa-file-lines"></i>
                    </span>
                    <span class="sidebar-text">Documents</span>
                </a>
            </li>

            {{-- Session Management --}}
            <li class="sidebar-item mt-1 {{ request()->is('secretary/session-meetings*') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('session-meetings.index') }}"
                data-label="Session Management"
                title="Session Management">
                    <span class="sidebar-icon">
                        <i class="fa-solid fa-handshake"></i>
                    </span>
                    <span class="sidebar-text">Session Management</span>
                </a>
            </li>

            {{-- Review Results Minutes --}}
            <li class="sidebar-item mt-1 {{ request()->is('minutes/review-results*') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('minutes.review-results') }}"
                data-label="Review Results"
                title="Review Results">
                    <span class="sidebar-icon">
                        <i class="fas fa-clipboard-check"></i>
                    </span>
                    <span class="sidebar-text">Review Results</span>
                </a>
            </li>

            {{-- Committee Referrals --}}
            <li class="sidebar-item mt-1 {{ request()->is('secretary/committee-referrals*') ? 'active' : '' }}">
                <a class="sidebar-link"
                href="{{ route('secretary.committee-referrals.index') }}"
                data-label="Committee Referrals"
                title="Committee Referrals">

                    <span class="sidebar-icon">
                        <i class="fa-solid fa-file-circle-check"></i>
                    </span>

                    <span class="sidebar-text">Committee Referrals</span>
                </a>
            </li>

            {{-- Committee Meetings --}}
            <li class="sidebar-item mt-1 {{ request()->is('secretary/committee-meetings*') ? 'active' : '' }}">
                <a class="sidebar-link"
                href="{{ route('secretary.committee-meetings.index') }}"
                data-label="Committee Meetings"
                title="Committee Meetings">

                    <span class="sidebar-icon">
                        <i class="fa-solid fa-calendar-days"></i>
                    </span>

                    <span class="sidebar-text">Committee Meetings</span>
                </a>
            </li>



            {{-- Live Session Settings --}}
            <li class="sidebar-item mt-1 {{ request()->is('secretary/settings/live-session*') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('settings.live.edit') }}"
                data-label="Live Session Settings"
                title="Live Session Settings">
                    <span class="sidebar-icon">
                        <i class="fa-solid fa-video"></i>
                    </span>
                    <span class="sidebar-text">Live Session Settings</span>
                </a>
            </li>
        @elseif($user && $user->role && $user->role->name === 'presiding_officer')
            {{-- Presiding Officer Dashboard --}}

            <li class="sidebar-item mt-1 {{ request()->routeIs('presiding-officer.dashboard') ? 'active' : '' }}">
                <a class="sidebar-link"
                   href="{{ route('presiding-officer.dashboard') }}"
                   data-label="Dashboard"
                   title="Dashboard">
                    <span class="sidebar-icon">
                        <i class="fa-solid fa-video"></i>
                    </span>
                    <span class="sidebar-text">Dashboard</span>
                </a>
            </li>

            <li class="sidebar-item mt-1 {{ request()->routeIs('presiding-officer.sessions.*') ? 'active' : '' }}">
                <a class="sidebar-link"
                href="{{ route('presiding-officer.sessions.index') }}"
                data-label="Live Deliberation"
                title="Live Deliberation">
                    <span class="sidebar-icon">
                        <i class="fa-solid fa-gavel"></i>
                    </span>
                    <span class="sidebar-text">Live Deliberation</span>
                </a>
            </li>

            <li class="sidebar-item mt-1 {{ request()->routeIs('presiding-officer.minutes.index') ? 'active' : '' }}">
                <a class="sidebar-link"
                   href="{{ route('presiding-officer.minutes.index') }}"
                   data-label="Dashboard"
                   title="Dashboard">
                    <span class="sidebar-icon">
                        <i class="fa-solid fa-file-circle-check"></i>
                    </span>
                    <span class="sidebar-text">Minutes for Review</span>
                </a>
            </li>

            {{-- Electronic Document Signing --}}
            <li class="sidebar-item mt-1 {{ request()->routeIs('document-signing.*') ? 'active' : '' }}">
                <a class="sidebar-link"
                href="{{ route('document-signing.index') }}"
                data-label="Electronic Signing"
                title="Electronic Signing">

                    <span class="sidebar-icon">
                        <i class="fas fa-file-signature"></i>
                    </span>

                    <span class="sidebar-text">Electronic Signing</span>
                </a>
            </li>
        @else
        {{-- CMS Admin Dashboard --}}
        <li class="sidebar-item mt-1 {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a class="sidebar-link"
            href="{{ route('dashboard') }}"
            data-label="Dashboard"
            title="Dashboard">
                <span class="sidebar-icon">
                    <i class="fa-solid fa-gauge-high"></i>
                </span>
                <span class="sidebar-text">Dashboard</span>
            </a>
        </li>

        <!-- Members -->
        <li class="sidebar-item mt-1 {{ request()->is('members*') ? 'active' : '' }}">
            <a class="sidebar-link"
               href="{{ route('members.index') }}"
               data-label="Members"
               title="Members">
                <span class="sidebar-icon">
                    <i class="fa-solid fa-user-group"></i>
                </span>
                <span class="sidebar-text">Members</span>
            </a>
        </li>

        <!-- Standing Committee -->
        <li class="sidebar-item mt-1 {{ request()->is('standing-committee*') ? 'active' : '' }}">
            <a class="sidebar-link"
               href="{{ route('standing-committee.index') }}"
               data-label="Standing Committee"
               title="Standing Committee">
                <span class="sidebar-icon">
                    <i class="fa-solid fa-people-line"></i>
                </span>
                <span class="sidebar-text">Standing Committee</span>
            </a>
        </li>

        <!-- District Assignment -->
        <li class="sidebar-item mt-1 {{ request()->is('district-assignments*') ? 'active' : '' }}">
            <a class="sidebar-link"
               href="{{ route('district-assignments.index') }}"
               data-label="District Assignment"
               title="District Assignment">
                <span class="sidebar-icon">
                    <i class="fa-solid fa-map-location-dot"></i>
                </span>
                <span class="sidebar-text">District Assignment</span>
            </a>
        </li>

        <!-- Photo Journal -->
        <li class="sidebar-item mt-1 {{ request()->is('photo-journals*') ? 'active' : '' }}">
            <a class="sidebar-link"
               href="{{ route('photo-journals.index') }}"
               data-label="Photo Journal"
               title="Photo Journal">
                <span class="sidebar-icon">
                    <i class="fa-solid fa-camera-retro"></i>
                </span>
                <span class="sidebar-text">Photo Journal</span>
            </a>
        </li>

        <!-- Organizational Chart -->
        <li class="sidebar-item mt-1 {{ request()->is('organization*') ? 'active' : '' }}">
            <a class="sidebar-link"
               href="{{ route('organization.index') }}"
               data-label="Organizational Chart"
               title="Organizational Chart">
                <span class="sidebar-icon">
                    <i class="fa-solid fa-sitemap"></i>
                </span>
                <span class="sidebar-text">Organizational Chart</span>
            </a>
        </li>

        <!-- Calendar Events -->
        <li class="sidebar-item mt-1 {{ request()->is('calendar-event*') ? 'active' : '' }}">
            <a class="sidebar-link"
               href="{{ route('calendar-event.index') }}"
               data-label="Calendar Events"
               title="Calendar Events">
                <span class="sidebar-icon">
                    <i class="fa-solid fa-calendar-days"></i>
                </span>
                <span class="sidebar-text">Calendar Events</span>
            </a>
        </li>

        <!-- Barangay Officials -->
        <li class="sidebar-item mt-1 {{ request()->is('barangay-officials*') ? 'active' : '' }}">
            <a class="sidebar-link"
               href="{{ route('barangay-officials.index') }}"
               data-label="Barangay Officials"
               title="Barangay Officials">
                <span class="sidebar-icon">
                    <i class="fa-solid fa-user-tie"></i>
                </span>
                <span class="sidebar-text">Barangay Officials</span>
            </a>
        </li>

        <!-- ⚙️ Settings Section -->
        <li class="sidebar-item mt-3 {{ request()->is('settings*') || request()->is('galleries*') ? 'active' : '' }}">
            <a class="sidebar-link pe-5 d-flex justify-content-between align-items-center" 
               data-bs-toggle="collapse" href="#settingsMenu" role="button" 
               aria-expanded="{{ request()->is('settings*') || request()->is('galleries*') ? 'true' : 'false' }}" 
               aria-controls="settingsMenu"
               data-label="Photo Gallery"
                   title="Photo Gallery">
                   <span class="sidebar-icon">
                            <i class="fa-solid fa-chevron-down small"></i>
                        </span>
                        <span class="sidebar-text">Settings</span>
            </a>

            <!-- Submenu -->
            <ul class="collapse list-unstyled ms-4 mt-1 {{ request()->is('settings*') || request()->is('galleries*') ? 'show' : '' }}" id="settingsMenu">
                <li class="sidebar-item {{ request()->is('galleries*') ? 'active' : '' }}">
                    <a class="sidebar-link" href="{{ route('gallery.index') }}"
                    data-label="Photo Gallery"
                   title="Photo Gallery">
                       <span class="sidebar-icon">
                            <i class="fa-regular fa-image"></i>
                        </span>
                        <span class="sidebar-text">Photo Gallery</span>
                    </a>
                </li>
                {{-- Barangays Settings --}}
                <li class="sidebar-item {{ request()->is('barangays*') ? 'active' : '' }}">
                    <a class="sidebar-link" href="{{ route('barangays.index') }}"
                    data-label="Barangay Settings" title="Barangay Settings">
                        <span class="sidebar-icon"><i class="fa-solid fa-map-location-dot"></i></span>
                        <span class="sidebar-text">Barangay Settings</span>
                    </a>
                </li>
                {{-- Organization Settings --}}
                <li class="sidebar-item {{ request()->routeIs('settings.organization.*') ? 'active' : '' }}">
                    <a class="sidebar-link"
                    href="{{ route('settings.organization.edit') }}"
                    data-label="Organization Settings"
                    title="Organization Settings">
                        <span class="sidebar-icon">
                            <i class="fa-solid fa-building me-1"></i>
                        </span>
                        <span class="sidebar-text">Organization Settings</span>
                    </a>
                </li>
                 <li class="sidebar-item {{ request()->is('users*') ? 'active' : '' }}">
                        <a class="sidebar-link pe-5" href="{{ url('/users') }}"
                        data-label="Users"
                        title="Users">
                            <span class="sidebar-icon">
                             <i class="fa-solid fa-user-gear"></i> 
                        </span>
                        <span class="sidebar-text">Users</span> 
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
    </div>
</aside>
