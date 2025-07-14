<nav class="navbar navbar-expand-md navbar-dark bg-primary sticky-top shadow" style="height: 50px">
    <div class="container d-flex justify-content-md-between">
        <div></div>
        <div class="text-white">
            <h5 class="m-0 text-capitalize">{{ Auth::user()->organization->company_name ?? '' }}</h5>
        </div>
        <div class="dropdown d-none d-lg-block">
            <a id="navbarDropdown " class="nav-link dropdown-toggle text-white text-capitalize" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                Hi, {{ Auth::user()->name }}
            </a>

            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <a class="dropdown-item" href="">
                    <i class="fas fa-user-circle me-2"></i> Profile
                </a>

                <a class="dropdown-item" href="{{ route('logout') }}"
                    onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt me-2"></i> {{ __('Logout') }}
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </div>

        <!-- Hamburger Links -->
        <div class="hamburger d-lg-none">
            <input class="checkbox" type="checkbox" id="toggleSidebar" />
            <div class="hamburger-lines">
                <span class="line line1 bg-light"></span>
                <span class="line line2 bg-light"></span>
                <span class="line line3 bg-light"></span>
            </div>
        </div>
    </div>
</nav>
