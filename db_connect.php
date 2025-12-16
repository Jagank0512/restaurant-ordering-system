<?php
try {
    $databaseUrl = getenv("DATABASE_URL");

    if (!$databaseUrl) {
        die("Database configuration not found");
    }

    $pdo = new PDO($databaseUrl, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (Exception $e) {
    die("Database connection failed");
}
