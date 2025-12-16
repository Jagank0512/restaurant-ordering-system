<?php
require_once '../../db_connect.php';

$sql = "SELECT o.*, 
        GROUP_CONCAT(CONCAT(i.name, ' (x', oi.quantity, ')') SEPARATOR '<br>') as item_details
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id 
        LEFT JOIN items i ON oi.item_id = i.id 
        GROUP BY o.id 
        ORDER BY 
            CASE 
                WHEN o.order_status = 'Received' THEN 1
                WHEN o.order_status = 'Preparing' THEN 2
                WHEN o.order_status = 'Ready' THEN 3
                ELSE 4
            END,
            o.created_at DESC";

$stmt = $conn->query($sql);
$orders = $stmt->fetchAll();

if (count($orders) > 0) {
    foreach ($orders as $order) {
        $statusClass = '';
        switch($order['order_status']) {
            case 'Received': $statusClass = 'status-danger'; break;
            case 'Preparing': $statusClass = 'status-warning'; break;
            case 'Ready': $statusClass = 'bg-indigo-100'; break; // Custom style
            case 'Served': $statusClass = 'status-success'; break;
            case 'Cancelled': $statusClass = 'status-danger'; break;
        }

        $paymentClass = ($order['payment_status'] == 'Paid') ? 'status-success' : 'status-danger';
        
        echo "<tr>";
        echo "<td>#" . htmlspecialchars($order['order_number']) . "</td>";
        echo "<td>
                <div style='font-weight:600;'>" . htmlspecialchars($order['customer_name'] ?: 'Guest') . "</div>
                <div style='font-size:0.8rem; color:#666;'>Table: " . htmlspecialchars($order['table_number']) . "</div>
              </td>";
        echo "<td>" . $order['item_details'] . "</td>";
        echo "<td>₹" . number_format($order['total_amount'], 2);
        if ($order['refund_amount'] > 0) {
            echo "<br><small style='color:red;'>REFUND: -₹" . number_format($order['refund_amount'], 2) . "</small>";
        }
        echo "<br><small>" . $order['payment_method'] . "</small></td>";
        echo "<td><span class='status-badge $statusClass'>" . $order['order_status'] . "</span></td>";
        echo "<td><span class='status-badge $paymentClass'>" . $order['payment_status'] . "</span></td>";
        echo "<td>
                <button class='btn btn-sm btn-primary view-order-btn' data-id='" . $order['id'] . "' onclick='openOrderModal(" . json_encode($order) . ")'>
                    <i class='fas fa-eye'></i>
                </button>
                <a href='print_bill.php?id=" . $order['id'] . "' target='_blank' class='btn btn-sm' style='background:#ccc;'>
                    <i class='fas fa-print'></i>
                </a>
              </td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='7' class='text-center'>No active orders found.</td></tr>";
}
?>
