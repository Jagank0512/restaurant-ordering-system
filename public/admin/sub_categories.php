<?php
$page_title = 'Sub Categories';
$active_page = 'sub_categories';
require_once 'includes/header.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $conn->prepare("DELETE FROM sub_categories WHERE id = ?");
        $stmt->execute([$id]);
        echo "<script>window.location.href='sub_categories.php';</script>";
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch Sub Categories with Parent Category Name
$sql = "SELECT s.*, c.name as category_name 
        FROM sub_categories s 
        JOIN categories c ON s.category_id = c.id 
        ORDER BY c.display_order, s.name";
$stmt = $conn->query($sql);
$sub_categories = $stmt->fetchAll();
?>

<div class="flex justify-between mb-4">
    <h2 class="page-title">All Sub Categories</h2>
    <div style="display:flex; gap:10px;">
        <input type="text" id="searchInput" placeholder="Search..." style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px;">
        <a href="manage_sub_category.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Sub Category
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
                <th>Parent Category</th>
                <th>Sub Category Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sub_categories as $item): ?>
            <tr>
                <td><span class="status-badge bg-indigo-100"><?php echo htmlspecialchars($item['category_name']); ?></span></td>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td>
                    <a href="manage_sub_category.php?id=<?php echo $item['id']; ?>" class="btn btn-sm" style="background: #EEF2FF; color: var(--primary-color);">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="sub_categories.php?delete=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');">
                        <i class="fas fa-trash"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($sub_categories)): ?>
                <tr><td colspan="3" class="text-center" style="text-align:center;">No sub-categories found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>
