<?php
require_once '../db_connect.php';

$id = $_GET['id'] ?? null;
if (!$id) die("Invalid Order ID");

// Fetch Order
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) die("Order not found");

// Fetch Items
$stmt = $conn->prepare("SELECT oi.*, i.name FROM order_items oi JOIN items i ON oi.item_id = i.id WHERE oi.order_id = ?");
$stmt->execute([$id]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bill #<?php echo $order['order_number']; ?></title>
    <style>
        @page { size: 80mm auto; margin: 0; }
        body { 
            font-family: 'Courier New', monospace; 
            width: 78mm; /* Slightly less than 80 to avoid clipping */
            margin: 0; 
            padding: 5px; 
            font-size: 12px;
            color: black;
        }
        .header { text-align: center; margin-bottom: 10px; }
        .header h2 { margin: 0; font-size: 16px; }
        .header p { margin: 5px 0; font-size: 12px; }
        .divider { border-top: 1px dashed #000; margin: 5px 0; }
        .item-row { display: flex; justify-content: space-between; margin-bottom: 2px; }
        .total-row { display: flex; justify-content: space-between; font-weight: bold; margin-top: 5px; }
        .footer { text-align: center; margin-top: 10px; font-size: 10px; }
        @media print {
            body { width: 100%; margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h2>Restaurant Name</h2>
        <p>Order Receipt</p>
    </div>
    
    <div>
        Order #: <?php echo $order['order_number']; ?><br>
        Date: <?php echo $order['created_at']; ?><br>
        Table: <?php echo $order['table_number']; ?>
    </div>
    
    <div class="divider"></div>
    
    <?php foreach ($items as $item): ?>
    <div class="item-row">
        <span><?php echo $item['name']; ?> x<?php echo $item['quantity']; ?></span>
        <span><?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
    </div>
    <?php endforeach; ?>
    
    <div class="divider"></div>
    
    <div class="divider"></div>
    
    <?php 
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    $cgst = $subtotal * 0.025;
    $sgst = $subtotal * 0.025;
    $grand_total = $subtotal + $cgst + $sgst;
    
    // In case the stored total is different (e.g. older orders), we might want to respect the stored total
    // But for new orders, if we update the frontend to include tax, stored total should match.
    // Let's use the calculated one for the breakdown, but maybe check against stored.
    // For now, simple breakdown:
    ?>

    <div class="total-row" style="font-weight: normal;">
        <span>Subtotal</span>
        <span><?php echo number_format($subtotal, 2); ?></span>
    </div>
    <div class="total-row" style="font-weight: normal; font-size: 0.9em;">
        <span>CGST (2.5%)</span>
        <span><?php echo number_format($cgst, 2); ?></span>
    </div>
    <div class="total-row" style="font-weight: normal; font-size: 0.9em;">
        <span>SGST (2.5%)</span>
        <span><?php echo number_format($sgst, 2); ?></span>
    </div>

    <div class="divider"></div>

    <div class="total-row" style="font-size: 1.1rem;">
        <span>TOTAL</span>
        <span><?php echo number_format($grand_total, 2); ?></span>
    </div>
    
    <div class="footer">
        <p>Thank you for dining with us!</p>
    </div>
</body>
</html>
