<?php
include_once '../db_connect.php';
include_once 'common.php';

try {
    // Fetch all structure
    $query = "
        SELECT 
            c.id as cat_id, c.name as cat_name, c.image_url as cat_image,
            s.id as sub_id, s.name as sub_name,
            i.id as item_id, i.name as item_name, i.description, i.price, i.image_url as item_image, i.is_available
        FROM categories c
        LEFT JOIN sub_categories s ON c.id = s.category_id
        LEFT JOIN items i ON s.id = i.sub_category_id AND i.is_available = 1
        ORDER BY c.display_order, c.id, s.id, i.id
    ";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $menu = [];
    foreach ($rows as $row) {
        $catId = $row['cat_id'];
        $subId = $row['sub_id'];
        
        if (!isset($menu[$catId])) {
            $menu[$catId] = [
                'id' => $catId,
                'name' => $row['cat_name'],
                'image' => $row['cat_image'],
                'sub_categories' => []
            ];
        }

        if ($subId && !isset($menu[$catId]['sub_categories'][$subId])) {
            $menu[$catId]['sub_categories'][$subId] = [
                'id' => $subId,
                'name' => $row['sub_name'],
                'items' => []
            ];
        }

        if ($row['item_id']) {
            $menu[$catId]['sub_categories'][$subId]['items'][] = [
                'id' => $row['item_id'],
                'name' => $row['item_name'],
                'description' => $row['description'],
                'price' => $row['price'],
                'image' => $row['item_image']
            ];
        }
    }

    // Re-index array to remove keys
    $finalMenu = [];
    foreach ($menu as $cat) {
        $cat['sub_categories'] = array_values($cat['sub_categories']);
        $finalMenu[] = $cat;
    }

    echo json_encode(['status' => 'success', 'data' => $finalMenu]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
