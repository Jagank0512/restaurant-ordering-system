            </div> <!-- End content-wrapper -->
        </div> <!-- End main-content -->
    </div> <!-- End admin-container -->

    <script>
        // Sidebar Toggle for Mobile
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.querySelector('.main-content');
        
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('open');
            });
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 1024) {
                if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target) && sidebar.classList.contains('open')) {
                    sidebar.classList.remove('open');
                }
            }
        });
    </script>
</body>
</html>
