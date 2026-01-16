<?php
session_start();
require_once 'config.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to manage your wishlist.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['product_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    exit;
}

$userId = $_SESSION['user_id'];
$productId = filter_var($_POST['product_id'], FILTER_SANITIZE_NUMBER_INT);

try {
    // Check if the item is already in the wishlist
    $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);
    $wishlistItem = $stmt->fetch();

    if ($wishlistItem) {
        // Remove from wishlist
        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE id = ?");
        $stmt->execute([$wishlistItem['id']]);
        echo json_encode(['status' => 'success', 'message' => 'Product removed from your wishlist.']);
    } else {
        // Add to wishlist
        $stmt = $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $stmt->execute([$userId, $productId]);
        echo json_encode(['status' => 'success', 'message' => 'Product added to your wishlist!']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Debug Error: ' . $e->getMessage()]);
}
