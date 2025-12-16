<?php
try {
    $databaseUrl = getenv("postgresql://restaurant_user:04aNRfs2pzNDB7LNe44EHr9h0imNGNz1@dpg-d50mjj6r433s73dd37t0-a/restaurant_db_vks8");

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
