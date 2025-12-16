<?php
include_once '../db_connect.php';
include_once 'common.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Fetch live orders
    $stmt = $conn->prepare("
        SELECT o.*, GROUP_CONCAT(CONCAT(i.name, ' (x', oi.quantity, ')') SEPARATOR ', ') as items_summary 
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN items i ON oi.item_id = i.id
        GROUP BY o.id
        ORDER BY o.created_at DESC
    ");
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $orders]);

} elseif ($method === 'POST') {
    // Update status
    $data = json_decode(file_get_contents("php://input"));
    if (isset($data->order_id) && isset($data->status)) {
        $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE id = ?"); // Use internal ID for updates if possible, or order_number
        // Assuming order_id passed is the internal ID. If it's order_number, change query.
        $stmt->execute([$data->status, $data->order_id]);
        echo json_encode(['status' => 'success', 'message' => 'Order status updated']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Missing data']);
    }
}
?>
