<?php
// admin/includes/footer.php
?>
        </div> <!-- End of main content area -->
    </div> <!-- End of flex container -->

    <script>
        // JavaScript for sidebar toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
            // Adjust main content margin when sidebar is toggled for smaller screens
            if (window.innerWidth < 768) { // md breakpoint in Tailwind is 768px
                if (sidebar.classList.contains('-translate-x-full')) {
                    mainContent.classList.remove('ml-64');
                } else {
                    mainContent.classList.add('ml-64');
                }
            }
        });

        // Ensure sidebar is visible and main content has margin on larger screens
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) {
                sidebar.classList.remove('-translate-x-full');
                mainContent.classList.add('md:ml-64'); // Apply margin on md and larger screens
            } else {
                // On small screens, always start with sidebar hidden, unless toggled
                if (!sidebar.classList.contains('-translate-x-full')) {
                     sidebar.classList.add('-translate-x-full');
                }
                mainContent.classList.remove('md:ml-64'); // Remove margin on smaller screens
            }
        });

        // Initialize sidebar state on load for larger screens
        window.onload = function() {
            if (window.innerWidth >= 768) {
                sidebar.classList.remove('-translate-x-full');
                if (mainContent) { // Check if mainContent exists before adding class
                    mainContent.classList.add('md:ml-64');
                }
            } else {
                 if (mainContent) { // Check if mainContent exists before removing class
                    mainContent.classList.remove('md:ml-64');
                }
            }
        };

    </script>
</body>
</html>
