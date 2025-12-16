<?php
session_start();

$dsn = "postgresql://restaurant_user:PASSWORD@dpg-d50mjj6r433s73dd37t0-a.render.com:5432/restaurant_db_vks8";

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB error");
}

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Only handle POST requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $error = '';

    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Auto-create default admin if none exists (Dev helper)
        $checkAdmin = $conn->query("SELECT COUNT(*) FROM admin")->fetchColumn();
        if ($checkAdmin == 0) {
            $defPass = password_hash('admin123', PASSWORD_DEFAULT);
            $conn->exec("INSERT INTO admin (username, password) VALUES ('admin', '$defPass')");
        }

        $stmt = $conn->prepare("SELECT id, username, password FROM admin WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            if (password_verify($password, $row['password'])) {
                $_SESSION['admin_id'] = $row['id'];
                $_SESSION['admin_username'] = $row['username'];
                header("Location: index.php");
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "Username not found.";
        }
    }

    // If we're here, there was an error. Redirect back to login.html with error
    if ($error) {
        header("Location: login.html?error=" . urlencode($error));
        exit();
    }
} else {
    // If someone accesses login.php directly without POST, send them to the form
    header("Location: login.html");
    exit();
}
?>


