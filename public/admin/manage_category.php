<?php
$page_title = 'Manage Category';
$active_page = 'categories';
require_once 'includes/header.php';

$id = $_GET['id'] ?? null;
$name = '';
$display_order = 0;
$image_url = '';
$error = '';

if ($id) {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $category = $stmt->fetch();
    if ($category) {
        $name = $category['name'];
        $display_order = $category['display_order'];
        $image_url = $category['image_url'];
    } else {
        echo "<div class='alert alert-danger'>Category not found</div>";
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $display_order = (int)$_POST['display_order'];
    
    // Image Upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../assets/images/categories/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Remove old image if exists and we are updating
            if ($id && $image_url && file_exists("../" . $image_url)) {
                unlink("../" . $image_url);
            }
            $image_url = "assets/images/categories/" . $new_filename;
        } else {
            $error = "Sorry, there was an error uploading your file.";
        }
    }

    if (empty($name)) {
        $error = "Name is required.";
    }

    if (!$error) {
        try {
            if ($id) {
                // Update
                $stmt = $conn->prepare("UPDATE categories SET name = ?, display_order = ?, image_url = ? WHERE id = ?");
                $stmt->execute([$name, $display_order, $image_url, $id]);
            } else {
                // Insert
                $stmt = $conn->prepare("INSERT INTO categories (name, display_order, image_url) VALUES (?, ?, ?)");
                $stmt->execute([$name, $display_order, $image_url]);
            }
            echo "<script>window.location.href='categories.php';</script>";
            exit;
        } catch (Exception $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}
?>

<div class="stat-card" style="max-width: 600px; margin: 0 auto; display: block;">
    <div class="flex justify-between mb-4">
        <h2><?php echo $id ? 'Edit Category' : 'Add New Category'; ?></h2>
        <a href="categories.php" class="btn btn-sm" style="background: #eee;">Cancel</a>
    </div>

    <?php if ($error): ?>
        <div style="background: #FEE2E2; color: #991B1B; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label class="form-label">Category Name</label>
            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Display Order</label>
            <input type="number" name="display_order" class="form-control" value="<?php echo $display_order; ?>">
            <small style="color: grey;">Lower numbers show first.</small>
        </div>

        <div class="form-group">
            <label class="form-label">Category Image</label>
            <input type="file" name="image" class="form-control" accept="image/*">
            <?php if ($image_url): ?>
                <div style="margin-top: 10px;">
                    <p class="form-label">Current Image:</p>
                    <img src="../<?php echo $image_url; ?>" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                </div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%;">
            <?php echo $id ? 'Update Category' : 'Create Category'; ?>
        </button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
