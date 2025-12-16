<?php
$page_title = 'Manage Sub Category';
$active_page = 'sub_categories';
require_once 'includes/header.php';

$id = $_GET['id'] ?? null;
$name = '';
$category_id = '';
$error = '';

if ($id) {
    $stmt = $conn->prepare("SELECT * FROM sub_categories WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if ($item) {
        $name = $item['name'];
        $category_id = $item['category_id'];
    } else {
        echo "<div class='alert alert-danger'>Item not found</div>";
        exit;
    }
}

// Fetch Categories for dropdown
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $category_id = $_POST['category_id'];

    if (empty($name) || empty($category_id)) {
        $error = "All fields are required.";
    }

    if (!$error) {
        try {
            if ($id) {
                $stmt = $conn->prepare("UPDATE sub_categories SET name = ?, category_id = ? WHERE id = ?");
                $stmt->execute([$name, $category_id, $id]);
            } else {
                $stmt = $conn->prepare("INSERT INTO sub_categories (name, category_id) VALUES (?, ?)");
                $stmt->execute([$name, $category_id]);
            }
            echo "<script>window.location.href='sub_categories.php';</script>";
            exit;
        } catch (Exception $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}
?>

<div class="stat-card" style="max-width: 600px; margin: 0 auto; display: block;">
    <div class="flex justify-between mb-4">
        <h2><?php echo $id ? 'Edit Sub Category' : 'Add New Sub Category'; ?></h2>
        <a href="sub_categories.php" class="btn btn-sm" style="background: #eee;">Cancel</a>
    </div>

    <?php if ($error): ?>
        <div style="background: #FEE2E2; color: #991B1B; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label class="form-label">Parent Category</label>
            <select name="category_id" class="form-control" required>
                <option value="">Select Category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo ($category_id == $cat['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Sub Category Name</label>
            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" required>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%;">
            <?php echo $id ? 'Update Sub Category' : 'Create Sub Category'; ?>
        </button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
