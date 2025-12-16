<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="container">
        <header class="header">
            <div class="logo">Track Your Cravings 🍕</div>
            <a href="index.php" class="track-btn">
                <i class="fas fa-arrow-left"></i> Back to Menu
            </a>
        </header>

        <!-- Track by Order ID -->
        <div class="menu-item" style="max-width: 600px; margin: 100px auto 20px;">
            <div class="section-title" style="margin: 0 0 20px; font-size: 1.5rem;">Track by Order ID</div>
            <div class="form-group">
                <div style="display: flex; gap: 12px; margin-bottom: 8px;">
                    <input type="text" id="order-id-input" class="search-input" style="padding: 16px; border-radius: 12px;" placeholder="Enter Order ID (e.g. ORD-1234)">
                    <button class="add-btn" style="background: var(--primary-color); color:white; border:none; padding: 0 30px; display:flex; align-items:center; gap:8px;" onclick="checkStatus()">
                        <i class="fas fa-search"></i> Track
                    </button>
                </div>
                <p style="font-size: 0.85rem; color: var(--text-muted);">Found on your receipt or confirmation screen.</p>
            </div>
        </div>

        <!-- Forgot Order ID Link -->
        <div style="text-align:center; margin: 20px 0;">
            <a href="#" onclick="toggleForgotForm(event)" style="color:var(--primary-color); text-decoration:underline;">
                Forgot Order ID?
            </a>
        </div>

        <!-- Forgot Order ID Form (Hidden by default) -->
        <div id="forgot-form-container" class="menu-item" style="max-width: 600px; margin: 0 auto; display:none; border-color: var(--border-color);">
            <div class="section-title" style="margin: 0 0 15px; font-size: 1.2rem; color: var(--text-color);">
                <i class="fas fa-search"></i> Find My Order
            </div>
            <p style="color: var(--text-muted); margin-bottom: 16px; font-size: 0.9rem;">
                Enter the name or table number used for today's order.
            </p>
            
            <div class="form-group">
                <label class="form-label" style="color:var(--text-muted)">Name</label>
                <input type="text" id="find-name" class="search-input" style="padding: 12px; border-radius: 12px; margin-bottom: 12px;" placeholder="e.g. John">
                
                <label class="form-label" style="color:var(--text-muted)">Table Number</label>
                <input type="text" id="find-table" class="search-input" style="padding: 12px; border-radius: 12px; margin-bottom: 16px;" placeholder="e.g. 5">
                
                <button class="primary-btn" style="margin-top:0; padding: 12px;" onclick="findOrders()">Find Order</button>
            </div>

            <div id="find-results" style="margin-top: 20px;"></div>
        </div>

        <div id="status-result"></div>
    </div>
    <script src="assets/js/tracking.js"></script>
</body>

</html>