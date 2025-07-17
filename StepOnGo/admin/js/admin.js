document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content'); // Assuming you want main-content to also adjust

    if (sidebarToggle && sidebar && mainContent) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('sidebar-collapsed'); // Add/remove class from main content if needed
            // You might want to save this state to localStorage
            // if (sidebar.classList.contains('collapsed')) {
            //     localStorage.setItem('sidebarState', 'collapsed');
            // } else {
            //     localStorage.setItem('sidebarState', 'expanded');
            // }
        });

        // Optional: Restore sidebar state from localStorage on load
        // if (localStorage.getItem('sidebarState') === 'collapsed') {
        //     sidebar.classList.add('collapsed');
        //     mainContent.classList.add('sidebar-collapsed');
        // }
    }
});