<?php
require_once 'config.php';

// Create Admin User
$admin_email = 'admin@shop.com';
$admin_pass = password_hash('admin123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$admin_email]);
if (!$stmt->fetch()) {
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, gender, user_type, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['Admin User', $admin_email, $admin_pass, 'male', 'admin', 1]);
    echo "Admin user created: admin@shop.com / admin123\n";
}

// Create Categories
$categories = ['Electronics', 'Clothing', 'Home & Garden'];
foreach ($categories as $cat) {
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
    $stmt->execute([$cat]);
    if (!$stmt->fetch()) {
        $pdo->prepare("INSERT INTO categories (name) VALUES (?)")->execute([$cat]);
    }
}
echo "Categories seeded.\n";
?>
