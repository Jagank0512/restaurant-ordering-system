<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gourmet Bites - Menu</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="container">
        <!-- Header Box -->
        <div style="background:var(--card-bg); padding:16px 20px; border-radius:16px; display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; box-shadow:var(--shadow-sm); border:1px solid var(--border-color);">
            <div class="logo">Gourmet Bites 🍔</div>
            <a href="track_order.php" class="track-btn">Track Order</a>
        </div>

        <!-- Search Bar (Simple & Centered) -->
        <div class="search-wrapper">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="search-input" class="search-input" placeholder="Search..." onkeyup="filterMenu()">
        </div>

        <!-- Categories -->
        <div class="category-wrapper">
            <div class="category-scroll" id="category-scroll">
                <!-- Injected by JS -->
                <div class="category-pill active">Loading...</div>
            </div>
        </div>

        <!-- Menu List -->
        <div id="menu-list">
            <!-- Injected by JS -->
            <div style="text-align:center; padding: 40px; color: #777;">Loading deliciousness...</div>
        </div>
    </div>




    <!-- Bottom Cart Bar -->
    <div class="cart-bar" id="cart-bar" style="display: none;" onclick="openCartModal()">
        <div class="cart-info">
            <span id="cart-count">0 ITEMS</span>
            <div class="cart-total" id="cart-total">₹0</div>
        </div>
        <div class="view-cart-btn">
            View Cart &rarr;
        </div>
    </div>

    <!-- Cart Modal -->
    <div class="modal" id="cart-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Your Cart</h2>
                <button class="close-modal" onclick="closeCartModal()">&times;</button>
            </div>
            <div id="cart-items-container">
                <!-- Cart items -->
            </div>
        </div>
    </div>

    <script src="assets/js/script.js?v=<?php echo time(); ?>"></script>
</body>
</html>
