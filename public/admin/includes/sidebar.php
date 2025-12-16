<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="index.php" class="logo">
            <i class="fas fa-qrcode"></i> QR Menu Admin
        </a>
    </div>
    
    <nav class="sidebar-nav">
        <a href="index.php" class="nav-item <?php echo ($active_page == 'dashboard') ? 'active' : ''; ?>">
            <i class="fas fa-home"></i> Dashboard
        </a>
        
        <div class="nav-section-label" style="padding: 0.75rem 1rem; font-size: 0.75rem; color: #9CA3AF; text-transform: uppercase; font-weight: 600;">Menu Management</div>
        
        <a href="categories.php" class="nav-item <?php echo ($active_page == 'categories') ? 'active' : ''; ?>">
            <i class="fas fa-tags"></i> Categories
        </a>
        <a href="sub_categories.php" class="nav-item <?php echo ($active_page == 'sub_categories') ? 'active' : ''; ?>">
            <i class="fas fa-layer-group"></i> Sub Categories
        </a>
        <a href="items.php" class="nav-item <?php echo ($active_page == 'items') ? 'active' : ''; ?>">
            <i class="fas fa-utensils"></i> Food Items
        </a>

        <div class="nav-section-label" style="padding: 0.75rem 1rem; font-size: 0.75rem; color: #9CA3AF; text-transform: uppercase; font-weight: 600;">Orders & Sales</div>

        <a href="orders.php" class="nav-item <?php echo ($active_page == 'orders') ? 'active' : ''; ?>">
            <i class="fas fa-clipboard-list"></i> Live Orders
        </a>
        <a href="reports.php" class="nav-item <?php echo ($active_page == 'reports') ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i> Analytics & Reports
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="logout.php" class="nav-item" style="color: var(--danger-color);">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</aside>
