<?php
header('Content-Type: application/json');
include_once '../db_connect.php';
include_once 'common.php';

$orderId = $_GET['order_id'] ?? '';

if ($orderId) {
    $stmt = $conn->prepare("SELECT order_number, order_status, created_at, total_amount FROM orders WHERE order_number = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();

    if ($order) {
        // Fetch Items
        $itemStmt = $conn->prepare("
            SELECT oi.quantity, oi.price, m.name 
            FROM order_items oi
            JOIN items m ON oi.item_id = m.id
            WHERE oi.order_id = (SELECT id FROM orders WHERE order_number = ?)
        ");
        $itemStmt->execute([$orderId]);
        $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $order['items'] = $items;
        
        echo json_encode(['status' => 'success', 'data' => $order]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Order not found']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Order ID is required']);
}
?>
