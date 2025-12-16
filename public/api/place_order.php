<?php
include_once '../db_connect.php';
include_once 'common.php';

$input = json_decode(file_get_contents("php://input"), true); // Decode as array

if (!empty($input['items']) && !empty($input['total_amount'])) {
    try {
        $conn->beginTransaction();

        // Generate Order ID: ORD-001 (Daily Sequential)
        $stmt = $conn->query("SELECT MAX(CAST(SUBSTRING_INDEX(order_number, '-', -1) AS UNSIGNED)) FROM orders WHERE DATE(created_at) = CURDATE()");
        $last_num = $stmt->fetchColumn() ?: 0;
        $next_num = $last_num + 1;
        $order_id = 'ORD-' . str_pad($next_num, 3, '0', STR_PAD_LEFT);

        // SQL Insert
        $stmt = $conn->prepare("INSERT INTO orders (order_number, customer_name, table_number, total_amount, payment_method, order_type, order_status, payment_status) VALUES (?, ?, ?, ?, ?, ?, 'Received', 'Pending')");
        $stmt->execute([
            $order_id,
            $input['customer_name'] ?? 'Guest',
            $input['table_number'] ?? '',
            $input['total_amount'],
            $input['payment_method'] ?? 'Cash',
            $input['order_type'] ?? 'Dine-in'
        ]);
        $db_id = $conn->lastInsertId();

        $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, item_id, quantity, price) VALUES (?, ?, ?, ?)");

        foreach ($input['items'] as $item) {
            $itemStmt->execute([$db_id, $item['id'], $item['quantity'], $item['price']]);
        }

        $conn->commit();
        echo json_encode(['status' => 'success', 'order_id' => $order_id, 'message' => 'Order placed successfully']);

    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Order failed: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Incomplete data']);
}
?>
