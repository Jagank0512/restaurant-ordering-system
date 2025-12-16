<?php
$page_title = 'Analytics & Reports';
$active_page = 'reports';
require_once 'includes/header.php';

// Date Filter Logic
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'today';
$start_date = date('Y-m-d');
$end_date = date('Y-m-d');

if ($filter == 'today') {
    $start_date = date('Y-m-d');
    $end_date = date('Y-m-d');
    $period_label = "Today's Sales";
} elseif ($filter == 'week') {
    $start_date = date('Y-m-d', strtotime('monday this week'));
    $end_date = date('Y-m-d', strtotime('sunday this week'));
    $period_label = "This Week's Sales";
} elseif ($filter == 'month') {
    $start_date = date('Y-m-01');
    $end_date = date('Y-m-t');
    $period_label = "This Month's Sales";
}

// 1. Total Stats
$stats_sql = "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN order_status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled_count,
    SUM(CASE WHEN order_status != 'Cancelled' THEN total_amount ELSE 0 END) as total_sales,
    SUM(refund_amount) as total_refunds
FROM orders 
WHERE DATE(created_at) BETWEEN ? AND ?";
$stmt = $conn->prepare($stats_sql);
$stmt->execute([$start_date, $end_date]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

$total_sales = $stats['total_sales'] ?: 0;
$total_refunds = $stats['total_refunds'] ?: 0;
$net_total = $total_sales - $total_refunds;
$cancelled_count = $stats['cancelled_count'] ?: 0;

// 2. Item-wise Sales
$items_sql = "SELECT i.name, SUM(oi.quantity) as qty, SUM(oi.price * oi.quantity) as revenue
FROM order_items oi
JOIN orders o ON oi.order_id = o.id
JOIN items i ON oi.item_id = i.id
WHERE DATE(o.created_at) BETWEEN ? AND ? 
AND o.order_status != 'Cancelled'
GROUP BY i.id
ORDER BY qty DESC";
$stmt = $conn->prepare($items_sql);
$stmt->execute([$start_date, $end_date]);
$item_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Cancelled Orders List
$cancel_sql = "SELECT * FROM orders WHERE order_status = 'Cancelled' AND DATE(created_at) BETWEEN ? AND ?";
$stmt = $conn->prepare($cancel_sql);
$stmt->execute([$start_date, $end_date]);
$cancelled_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<style>
    @media print {
        @page { size: 80mm auto; margin: 0; }
        body * { visibility: hidden; }
        #printable-area, #printable-area * { visibility: visible; }
        #printable-area { 
            position: absolute; 
            left: 0; 
            top: 0; 
            width: 78mm; 
            font-family: 'Courier New', monospace;
            padding: 5px;
            color: black;
        }
        .no-print { display: none !important; }
        .main-content { margin: 0; padding: 0; }
        .sidebar { display: none; }
        
        /* Thermal Specific Overrides */
        h2 { font-size: 16px; margin: 0 0 5px 0; }
        h3 { font-size: 14px; margin: 10px 0 5px 0; border-bottom: 1px dashed #000; }
        p { font-size: 12px; margin: 5px 0; }
        
        /* Grid to Single Column */
        .stats-grid { 
            display: block; 
            grid-template-columns: 1fr; 
            gap: 10px;
        }
        .stat-card {
            border: 1px dashed #ccc;
            box-shadow: none;
            padding: 8px;
            margin-bottom: 8px;
        }
        .stat-card h3 { 
            font-size: 12px; 
            border: none; 
            margin: 0; 
        }
        .stat-card .value { font-size: 14px; font-weight: bold; }
        
        /* Tables */
        .table-container { box-shadow: none; border: none; }
        table.data-table { 
            width: 100%; 
            font-size: 10px; 
            border-collapse: collapse; 
        }
        table.data-table th, table.data-table td {
            padding: 4px 2px;
            text-align: left;
            border-bottom: 1px dotted #ccc;
        }
        table.data-table th { border-bottom: 1px solid #000; }
    }
</style>

<div class="flex justify-between mb-4 no-print">
    <h2 class="page-title">Sales Report</h2>
    <div style="display:flex; gap:10px;">
        <select onchange="window.location.href='reports.php?filter='+this.value" style="padding: 8px; border-radius: 6px; border: 1px solid #ddd;">
            <option value="today" <?php echo $filter == 'today' ? 'selected' : ''; ?>>Today</option>
            <option value="week" <?php echo $filter == 'week' ? 'selected' : ''; ?>>This Week</option>
            <option value="month" <?php echo $filter == 'month' ? 'selected' : ''; ?>>This Month</option>
        </select>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>
</div>

<div id="printable-area">
    <div style="text-align:center; margin-bottom: 20px;">
        <h2>Gourmet Bites - Sales Report</h2>
        <p>Period: <?php echo date('d M Y', strtotime($start_date)); ?> - <?php echo date('d M Y', strtotime($end_date)); ?></p>
    </div>

    <!-- Summary Cards -->
    <div class="stats-grid mb-6">
        <div class="stat-card">
            <h3>Total Sales</h3>
            <div style="font-size:1.5rem; font-weight:700; color:#10B981;">₹<?php echo number_format($total_sales, 2); ?></div>
        </div>
        <div class="stat-card">
            <h3>Refunds</h3>
            <div style="font-size:1.5rem; font-weight:700; color:#EF4444;">₹<?php echo number_format($total_refunds, 2); ?></div>
        </div>
        <div class="stat-card">
            <h3>Net Profit</h3>
            <div style="font-size:1.5rem; font-weight:700; color:#4F46E5;">₹<?php echo number_format($net_total, 2); ?></div>
        </div>
        <div class="stat-card">
            <h3>Cancelled Orders</h3>
            <div style="font-size:1.5rem; font-weight:700; color:#F59E0B;"><?php echo $cancelled_count; ?></div>
        </div>
    </div>

    <!-- Item Sales Table -->
    <div class="table-container mb-6">
        <h3 style="padding: 15px; border-bottom: 1px solid #eee; margin: 0;">Item-wise Sales Breakdown</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Count Sold</th>
                    <th>Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($item_sales as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo $item['qty']; ?> count</td>
                    <td>₹<?php echo number_format($item['revenue'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($item_sales)): ?>
                    <tr><td colspan="3" class="text-center">No sales in this period.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Cancelled Orders Table -->
    <?php if (!empty($cancelled_orders)): ?>
    <div class="table-container">
        <h3 style="padding: 15px; border-bottom: 1px solid #eee; margin: 0; color: #EF4444;">Cancelled Orders</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Reason/Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cancelled_orders as $order): ?>
                <tr>
                    <td><?php echo $order['order_number']; ?></td>
                    <td><?php echo date('d M H:i', strtotime($order['created_at'])); ?></td>
                    <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                    <td>Cancelled</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
