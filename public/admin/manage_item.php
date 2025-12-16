<?php
$page_title = 'Manage Item';
$active_page = 'items';
require_once 'includes/header.php';

$id = $_GET['id'] ?? null;
// Defaults
$name = '';
$sub_category_id = '';
$description = '';
$price = '';
$image_url = '';
$is_available = 1;

$error = '';

if ($id) {
    $stmt = $conn->prepare("SELECT * FROM items WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if ($item) {
        $name = $item['name'];
        $sub_category_id = $item['sub_category_id'];
        $description = $item['description'];
        $price = $item['price'];
        $image_url = $item['image_url'];
        $is_available = $item['is_available'];
    } else {
        echo "<div class='alert alert-danger'>Item not found</div>";
        exit;
    }
}

// Fetch Subcategories grouped by Category
$sql = "SELECT s.id, s.name as sub_name, c.name as cat_name 
        FROM sub_categories s 
        JOIN categories c ON s.category_id = c.id 
        ORDER BY c.display_order, s.name";
$stmt = $conn->query($sql);
$sub_options = $stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $sub_category_id = $_POST['sub_category_id'];
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $is_available = isset($_POST['is_available']) ? 1 : 0; // Checkbox

    // Image Upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../assets/images/items/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
             if ($id && $image_url && file_exists("../" . $image_url)) {
                unlink("../" . $image_url);
            }
            $image_url = "assets/images/items/" . $new_filename;
        } else {
            $error = "Error uploading image.";
        }
    }

    if (empty($name) || empty($sub_category_id) || empty($price)) {
        $error = "Name, Category and Price are required.";
    }

    if (!$error) {
        try {
            if ($id) {
                $stmt = $conn->prepare("UPDATE items SET name=?, sub_category_id=?, description=?, price=?, image_url=?, is_available=? WHERE id=?");
                $stmt->execute([$name, $sub_category_id, $description, $price, $image_url, $is_available, $id]);
            } else {
                $stmt = $conn->prepare("INSERT INTO items (name, sub_category_id, description, price, image_url, is_available) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $sub_category_id, $description, $price, $image_url, $is_available]);
            }
            echo "<script>window.location.href='items.php';</script>";
            exit;
        } catch (Exception $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}
?>

<div class="stat-card" style="max-width: 800px; margin: 0 auto; display: block;">
    <div class="flex justify-between mb-4">
        <h2><?php echo $id ? 'Edit Food Item' : 'Add New Food Item'; ?></h2>
        <a href="items.php" class="btn btn-sm" style="background: #eee;">Cancel</a>
    </div>

    <?php if ($error): ?>
        <div style="background: #FEE2E2; color: #991B1B; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="flex" style="gap: 20px;">
            <div style="flex: 1;">
                <div class="form-group">
                    <label class="form-label">Item Name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="sub_category_id" class="form-control" required>
                        <option value="">Select Category</option>
                        <?php 
                        $current_cat = '';
                        foreach ($sub_options as $opt) {
                            if ($current_cat != $opt['cat_name']) {
                                if ($current_cat != '') echo '</optgroup>';
                                echo '<optgroup label="' . htmlspecialchars($opt['cat_name']) . '">';
                                $current_cat = $opt['cat_name'];
                            }
                            $selected = ($sub_category_id == $opt['id']) ? 'selected' : '';
                            echo '<option value="' . $opt['id'] . '" ' . $selected . '>' . htmlspecialchars($opt['sub_name']) . '</option>';
                        }
                        if ($current_cat != '') echo '</optgroup>';
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Price (₹)</label>
                    <input type="number" step="0.01" name="price" class="form-control" value="<?php echo $price; ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="is_available" <?php echo $is_available ? 'checked' : ''; ?>>
                        Item is Available (Active)
                    </label>
                </div>
            </div>

            <div style="flex: 1;">
                 <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($description); ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Food Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    <?php if ($image_url): ?>
                        <div style="margin-top: 10px;">
                            <img src="../<?php echo $image_url; ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px;">
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 20px;">
            <?php echo $id ? 'Update Item' : 'Save Item'; ?>
        </button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
