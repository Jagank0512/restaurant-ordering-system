<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.html");
    exit();
}
require_once '../db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Restaurant QR Menu</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Chart.js for Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <header class="top-header">
                <div class="header-left">
                    <button id="sidebar-toggle" class="btn btn-sm">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title"><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></h1>
                </div>
                
                <div class="user-profile">
                    <span>Welcome, Admin</span>
                    <div class="avatar" style="width: 35px; height: 35px; background: var(--primary-color); border-radius: 50%; color: white; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
            </header>
            
            <div class="content-wrapper">
