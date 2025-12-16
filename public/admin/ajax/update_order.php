<?php
require_once '../../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = $_POST['order_id'];
    $order_status = $_POST['order_status'];
    $payment_status = $_POST['payment_status'];
    $refund_amount = isset($_POST['refund_amount']) && $_POST['refund_amount'] !== '' ? floatval($_POST['refund_amount']) : 0.00;

    try {
        $stmt = $conn->prepare("UPDATE orders SET order_status = ?, payment_status = ?, refund_amount = ? WHERE id = ?");
        $stmt->execute([$order_status, $payment_status, $refund_amount, $order_id]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
