<?php
$page_title = 'Categories';
$active_page = 'categories';
require_once 'includes/header.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $msg = "Category deleted successfully!";
        // Redirect to remove query param
        echo "<script>window.location.href='categories.php';</script>";
    } catch (Exception $e) {
        $error = "Error deleting category: " . $e->getMessage();
    }
}

$stmt = $conn->query("SELECT * FROM categories ORDER BY display_order ASC, created_at DESC");
$categories = $stmt->fetchAll();
?>

<div class="flex justify-between mb-4">
    <h2 class="page-title">All Categories</h2>
    <div style="display:flex; gap:10px;">
        <input type="text" id="searchInput" placeholder="Search..." style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px;">
        <a href="manage_category.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Category
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
                <th>Order</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $cat): ?>
            <tr>
                <td>
                    <?php if ($cat['image_url']): ?>
                        <img src="../<?php echo htmlspecialchars($cat['image_url']); ?>" alt="<?php echo htmlspecialchars($cat['name']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                    <?php else: ?>
                        <div style="width: 50px; height: 50px; background: #eee; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #aaa;">
                            <i class="fas fa-image"></i>
                        </div>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($cat['name']); ?></td>
                <td><?php echo $cat['display_order']; ?></td>
                <td>
                    <a href="manage_category.php?id=<?php echo $cat['id']; ?>" class="btn btn-sm" style="background: #EEF2FF; color: var(--primary-color);">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="categories.php?delete=<?php echo $cat['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure? This will delete all subcategories and items under this category.');">
                        <i class="fas fa-trash"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($categories)): ?>
                <tr><td colspan="4" class="text-center" style="text-align:center;">No categories found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>
