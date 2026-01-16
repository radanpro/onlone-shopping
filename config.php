<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'shop');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Base URL
define('BASE_URL', '/online-shopping-system/');
