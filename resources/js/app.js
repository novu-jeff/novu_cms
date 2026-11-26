import './bootstrap';
import './toast'




document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;

    const desktopToggle = document.getElementById('sidebarCollapse');
    const navbarDesktopToggle = document.getElementById('sidebarNavbarCollapse');
    const mobileToggle = document.getElementById('sidebarMobileToggle');
    const backdrop = document.getElementById('sidebarBackdrop');

    const isMobile = () => window.matchMedia('(max-width: 991.98px)').matches;

    function closeMobileSidebar() {
        body.classList.remove('sidebar-mobile-open');
    }

    function toggleMobileSidebar() {
        body.classList.toggle('sidebar-mobile-open');
    }

    function toggleDesktopSidebar() {
        if (!isMobile()) {
            body.classList.toggle('sidebar-collapsed');
        }
    }

    desktopToggle?.addEventListener('click', toggleDesktopSidebar);
    navbarDesktopToggle?.addEventListener('click', toggleDesktopSidebar);
    mobileToggle?.addEventListener('click', toggleMobileSidebar);
    backdrop?.addEventListener('click', closeMobileSidebar);

    // Close the mobile drawer after selecting a navigation link.
    document.querySelectorAll('#appSidebar .sidebar-link').forEach(link => {
        link.addEventListener('click', () => {
            if (isMobile() && !link.hasAttribute('data-bs-toggle')) {
                closeMobileSidebar();
            }
        });
    });

    // Reset mobile drawer state when returning to desktop.
    window.addEventListener('resize', () => {
        if (!isMobile()) {
            closeMobileSidebar();
        }
    });

        const logoutButton = document.getElementById('logoutButton');
        const logoutForm = document.getElementById('logout-form');

        logoutButton?.addEventListener('click', () => {
            logoutForm?.requestSubmit();
        });
});
