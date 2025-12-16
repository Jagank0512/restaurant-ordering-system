<?php
include_once '../db_connect.php';
include_once 'common.php';

$data = json_decode(file_get_contents("php://input"));

// Default hardcoded for fallback if DB is empty or for testing
// admin / admin123
if (isset($data->username) && isset($data->password)) {
    // Check DB
    $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->execute([$data->username]);
    $user = $stmt->fetch();

    if ($user && password_verify($data->password, $user['password'])) {
         // Start session or return token. For this simple app, we'll return a 'token' which is just the user ID lol
         echo json_encode(['status' => 'success', 'token' => $user['id'], 'message' => 'Login successful']);
    } else {
        // Fallback for demo if users didn't seed DB properly
        if ($data->username === 'admin' && $data->password === 'admin123') {
             echo json_encode(['status' => 'success', 'token' => 'demo_token', 'message' => 'Login successful (Demo)']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
        }
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing credentials']);
}
?>
