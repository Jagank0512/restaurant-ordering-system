<?php
$page_title = 'Food Items';
$active_page = 'items';
require_once 'includes/header.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
        $stmt->execute([$id]);
        echo "<script>window.location.href='items.php';</script>";
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle Status Toggle
if (isset($_GET['toggle']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $current_status = $_GET['toggle'];
    $new_status = $current_status ? 0 : 1;
    $stmt = $conn->prepare("UPDATE items SET is_available = ? WHERE id = ?");
    $stmt->execute([$new_status, $id]);
    echo "<script>window.location.href='items.php';</script>";
}

// Fetch Items with details
$sql = "SELECT i.*, s.name as sub_cat_name, c.name as cat_name 
        FROM items i 
        JOIN sub_categories s ON i.sub_category_id = s.id 
        JOIN categories c ON s.category_id = c.id 
        ORDER BY c.name, s.name, i.name";
$stmt = $conn->query($sql);
$items = $stmt->fetchAll();
?>

<div class="flex justify-between mb-4">
    <h2 class="page-title">All Food Items</h2>
    <div style="display:flex; gap:10px;">
        <input type="text" id="searchInput" placeholder="Search..." style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px;">
        <a href="manage_item.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Item
        </a>
    </div>
</div>

<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('.data-table tbody tr');
    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Combined Status</th> <!-- Renamed to avoid confusion -->
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td>
                    <?php if ($item['image_url']): ?>
                        <img src="../<?php echo htmlspecialchars($item['image_url']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                    <?php else: ?>
                        <div style="width: 50px; height: 50px; background: #eee; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-utensils" style="color: #ccc;"></i>
                        </div>
                    <?php endif; ?>
                </td>
                <td>
                    <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                    <div style="font-size: 0.75rem; color: #666;"><?php echo mb_strimwidth(htmlspecialchars($item['description']), 0, 50, "..."); ?></div>
                </td>
                <td>
                    <div style="font-size: 0.85rem; font-weight: 600;"><?php echo htmlspecialchars($item['cat_name']); ?></div>
                    <div style="font-size: 0.75rem; color: #888;"><?php echo htmlspecialchars($item['sub_cat_name']); ?></div>
                </td>
                <td>₹<?php echo number_format($item['price'], 2); ?></td>
                <td>
                    <a href="items.php?toggle=<?php echo $item['is_available']; ?>&id=<?php echo $item['id']; ?>" class="status-badge <?php echo $item['is_available'] ? 'status-success' : 'status-danger'; ?>" style="text-decoration: none;">
                        <?php echo $item['is_available'] ? 'Active' : 'Inactive'; ?>
                    </a>
                </td>
                <td>
                    <a href="manage_item.php?id=<?php echo $item['id']; ?>" class="btn btn-sm" style="background: #EEF2FF; color: var(--primary-color);">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="items.php?delete=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');">
                        <i class="fas fa-trash"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($items)): ?>
                <tr><td colspan="6" class="text-center" style="text-align:center;">No items found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>
