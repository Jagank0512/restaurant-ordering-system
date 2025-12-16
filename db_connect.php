<?php
$whitelist = array('127.0.0.1', '::1', 'localhost');

if(in_array($_SERVER['HTTP_HOST'], $whitelist)){
    // Localhost Settings
    $host = 'localhost';
    $db_name = 'restaurant_qr_db';
    $username = 'root';
    $password = 'Jagan@143';
} else {
    // InfinityFree / Live Settings
    
    // IMPORTANT: InfinityFree rarely uses 'localhost'. 
    // If this fails, check your "MySQL Hostname" in the Control Panel (e.g., sql300.infinityfree.com)
    $host = 'localhost'; 
    $db_name = 'if0_40690108_restaurant_qr_db';
    $username = 'if0_40690108';
    $password = '143Jagan';
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit;
    } else {
        echo "Connection failed: " . $e->getMessage();
        die();
    }
}
?>
