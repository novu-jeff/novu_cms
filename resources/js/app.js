import './bootstrap';
import './toast'

$('#toggleSidebar').on('change', function() {
    var sidebar = $('.sidebar');
    sidebar.toggleClass('show', this.checked);
});