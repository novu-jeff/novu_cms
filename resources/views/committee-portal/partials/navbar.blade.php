{{-- Committee Portal shared navigation --}}
<nav class="navbar navbar-expand-lg navbar-dark committee-portal-navbar">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('committee-portal.dashboard') }}">
            <i class="fas fa-landmark me-2"></i>
            Committee Portal
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#committeeNavbar"
                aria-controls="committeeNavbar" aria-expanded="false"
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="committeeNavbar">
            <ul class="navbar-nav me-auto ms-lg-4">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('committee-portal.dashboard') ? 'active fw-bold' : '' }}"
                       href="{{ route('committee-portal.dashboard') }}">
                        <i class="fas fa-chart-line me-1"></i> Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('committee-portal.live-session.*') ? 'active fw-bold' : '' }}"
                       href="{{ route('committee-portal.live-session.index') }}">
                        <i class="fas fa-users-rectangle me-1"></i> Live Session
                    </a>
                </li>

                <!--<li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-calendar-alt me-1"></i> Committee Meetings
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{ route(
                                        'committee-portal.referrals.show',
                                        $referral->id
                                    ) }}">
                        <i class="fas fa-file-alt me-1"></i> Referred Documents
                    </a>
                </li>-->

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('committee-portal.signing.*') ? 'active fw-bold' : '' }}"
                       href="{{ route('committee-portal.signing.index') }}">
                        <i class="fas fa-file-signature me-1"></i> Electronic Signing
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-3 mt-3 mt-lg-0">
                <span class="text-white-50 small">
                    <i class="fas fa-user-circle me-1"></i>
                    {{ $member->name ?? 'Committee Member' }}
                </span>

                <form method="POST" action="{{ route('committee-portal.logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
