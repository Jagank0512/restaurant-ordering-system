<?php
$page_title = 'Dashboard';
$active_page = 'dashboard';
require_once 'includes/header.php';

// Fetch Analytics Data (Only Paid/Completed orders for sales)
// Total Orders
$stmt = $conn->query("SELECT COUNT(*) FROM orders");
$total_orders = $stmt->fetchColumn();

// Total Sales
$stmt = $conn->query("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'Paid'");
$total_sales = $stmt->fetchColumn() ?: 0;

// Today's Sales
$stmt = $conn->query("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'Paid' AND DATE(created_at) = CURDATE()");
$today_sales = $stmt->fetchColumn() ?: 0;

// This Month's Sales
$stmt = $conn->query("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'Paid' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
$month_sales = $stmt->fetchColumn() ?: 0;

// Cancelled Orders
// Cancelled Orders (Today)
$stmt = $conn->query("SELECT COUNT(*) FROM orders WHERE order_status = 'Cancelled' AND DATE(created_at) = CURDATE()");
$total_cancelled = $stmt->fetchColumn();

// Total Refunded
// Total Refunded (Today)
$stmt = $conn->query("SELECT SUM(refund_amount) FROM orders WHERE DATE(created_at) = CURDATE()");
$total_refunded = $stmt->fetchColumn() ?: 0;

// Recent Orders
$stmt = $conn->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5");
$recent_orders = $stmt->fetchAll();
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <h3>Total Orders</h3>
            <div class="value"><?php echo number_format($total_orders); ?></div>
        </div>
        <div class="stat-icon bg-indigo-100">
            <i class="fas fa-shopping-bag"></i>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-info">
            <h3>Total Sales</h3>
            <div class="value">₹<?php echo number_format($total_sales, 2); ?></div>
        </div>
        <div class="stat-icon bg-emerald-100">
            <i class="fas fa-rupee-sign"></i>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-info">
            <h3>Today's Revenue</h3>
            <div class="value">₹<?php echo number_format($today_sales, 2); ?></div>
        </div>
        <div class="stat-icon bg-amber-100">
            <i class="fas fa-calendar-day"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <h3>Monthly Revenue</h3>
            <div class="value">₹<?php echo number_format($month_sales, 2); ?></div>
        </div>
        <div class="stat-icon bg-indigo-100">
            <i class="fas fa-calendar-alt"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <h3>Cancelled Orders (Today)</h3>
            <div class="value"><?php echo number_format($total_cancelled); ?></div>
        </div>
        <div class="stat-icon" style="background-color: #FEE2E2; color: #EF4444;">
            <i class="fas fa-times-circle"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <h3>Refunded (Today)</h3>
            <div class="value">₹<?php echo number_format($total_refunded, 2); ?></div>
        </div>
        <div class="stat-icon" style="background-color: #FEE2E2; color: #EF4444;">
            <i class="fas fa-undo"></i>
        </div>
    </div>
</div>

<div class="table-container">
    <div class="table-header">
        <h2>Recent Orders</h2>
        <a href="orders.php" class="btn btn-primary btn-sm">View All</a>
    </div>
    <div style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Table</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_orders as $order): ?>
                <tr>
                    <td>#<?php echo htmlspecialchars($order['order_number']); ?></td>
                    <td><?php echo htmlspecialchars($order['customer_name'] ?? 'Guest'); ?></td>
                    <td><?php echo htmlspecialchars($order['table_number']); ?></td>
                    <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                    <td>
                        <?php
                        $statusClass = 'status-warning';
                        if ($order['order_status'] == 'Served') $statusClass = 'status-success';
                        elseif ($order['order_status'] == 'Ready') $statusClass = 'bg-indigo-100';
                        elseif ($order['order_status'] == 'Cancelled') $statusClass = 'status-danger';
                        ?>
                        <span class="status-badge <?php echo $statusClass; ?>">
                            <?php echo $order['order_status']; ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-badge <?php echo ($order['payment_status'] == 'Paid') ? 'status-success' : 'status-danger'; ?>">
                            <?php echo $order['payment_status']; ?>
                        </span>
                    </td>
                    <td><?php echo date('h:i A', strtotime($order['created_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($recent_orders)): ?>
                    <tr><td colspan="7" style="text-align: center; color: var(--text-muted);">No recent orders found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
