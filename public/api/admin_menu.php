<?php
include_once '../db_connect.php';
include_once 'common.php';

$data = json_decode(file_get_contents("php://input"));
$action = $_GET['action'] ?? '';

if ($action === 'add_category') {
    $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
    $stmt->execute([$data->name]);
    echo json_encode(['status' => 'success', 'message' => 'Category added']);
} elseif ($action === 'add_subcategory') {
    $stmt = $conn->prepare("INSERT INTO sub_categories (category_id, name) VALUES (?, ?)");
    $stmt->execute([$data->category_id, $data->name]);
    echo json_encode(['status' => 'success', 'message' => 'Sub-Category added']);
} elseif ($action === 'add_item') {
    $stmt = $conn->prepare("INSERT INTO items (sub_category_id, name, price, description, image_url) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$data->sub_category_id, $data->name, $data->price, $data->description, $data->image_url]);
    echo json_encode(['status' => 'success', 'message' => 'Item added']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
?>
