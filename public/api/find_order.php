<?php
header('Content-Type: application/json');
require_once '../db_connect.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Support JSON or GET params
    $name = $data['name'] ?? $_GET['name'] ?? '';
    $table = $data['table'] ?? $_GET['table'] ?? '';
    
    if (empty($name) && empty($table)) {
        echo json_encode(['status' => 'error', 'message' => 'Please provide Name or Table Number']);
        exit;
    }
    
    // Build Query - search today's orders
    $sql = "SELECT id, order_number, customer_name, table_number, total_amount, order_status, created_at 
            FROM orders 
            WHERE DATE(created_at) = CURDATE()";
            
            
    $params = [];
    
    if (!empty($name)) {
        $sql .= " AND customer_name LIKE ?";
        $params[] = "%$name%";
    }
    
    if (!empty($table)) {
        $sql .= " AND table_number = ?";
        $params[] = $table;
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($orders) {
        echo json_encode(['status' => 'success', 'data' => $orders]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No orders found for today with these details.']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
