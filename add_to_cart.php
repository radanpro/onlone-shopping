<?php
session_start();
require_once 'config.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to add items to the cart.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    exit;
}

$userId = $_SESSION['user_id'];
$productId = filter_var($_POST['product_id'], FILTER_SANITIZE_NUMBER_INT);
$quantity = filter_var($_POST['quantity'], FILTER_SANITIZE_NUMBER_INT);

if ($quantity <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid quantity.']);
    exit;
}

try {
    // Find product price
    $stmt = $pdo->prepare("SELECT price, stock FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        echo json_encode(['status' => 'error', 'message' => 'Product not found.']);
        exit;
    }

    if ($product['stock'] < $quantity) {
        echo json_encode(['status' => 'error', 'message' => 'Not enough stock available.']);
        exit;
    }

    // Find pending order for the user or create a new one
    $stmt = $pdo->prepare("SELECT id FROM orders WHERE user_id = ? AND status = 'pending'");
    $stmt->execute([$userId]);
    $order = $stmt->fetch();

    $orderId = null;
    if ($order) {
        $orderId = $order['id'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_price) VALUES (?, 0)");
        $stmt->execute([$userId]);
        $orderId = $pdo->lastInsertId();
    }

    // Check if item already in cart
    $stmt = $pdo->prepare("SELECT id, quantity FROM order_items WHERE order_id = ? AND product_id = ?");
    $stmt->execute([$orderId, $productId]);
    $orderItem = $stmt->fetch();

    if ($orderItem) {
        // Update quantity
        $newQuantity = $orderItem['quantity'] + $quantity;
        $stmt = $pdo->prepare("UPDATE order_items SET quantity = ? WHERE id = ?");
        $stmt->execute([$newQuantity, $orderItem['id']]);
    } else {
        // Insert new item
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$orderId, $productId, $quantity, $product['price']]);
    }

    // Update total price of the order
    $stmt = $pdo->prepare("
        UPDATE orders o SET total_price = (
            SELECT SUM(oi.quantity * oi.price) 
            FROM order_items oi 
            WHERE oi.order_id = o.id
        ) WHERE o.id = ?
    ");
    $stmt->execute([$orderId]);


    echo json_encode(['status' => 'success', 'message' => 'Product added to cart successfully!']);
} catch (Exception $e) {
    // In a real app, you would log the error
    echo json_encode(['status' => 'error', 'message' => 'Debug Error: ' . $e->getMessage()]);
}
