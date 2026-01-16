<?php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';
include '../includes/header.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$userId = $_SESSION['user_id'];

// جلب الطلبات مع تفاصيل المنتجات
$stmt = $pdo->prepare("
    SELECT o.id as order_id, o.status, o.total_price, o.created_at,
           oi.quantity, oi.price as unit_price, p.name, p.image
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
");
$stmt->execute([$userId]);
$results = $stmt->fetchAll(PDO::FETCH_GROUP); // تجميع العناصر حسب رقم الطلب
?>

<h2 class="fw-bold mb-4"><i class="fas fa-box me-2 text-primary"></i>My Orders & Cart</h2>

<?php if (empty($results)): ?>
    <div class="text-center py-5 bg-white rounded-4 shadow-sm">
        <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
        <h4>Your cart is empty</h4>
        <a href="../index.php" class="btn btn-primary rounded-pill mt-3">Start Shopping</a>
    </div>
<?php else: ?>
    <?php foreach ($results as $orderId => $items): ?>
        <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <div>
                    <span class="fw-bold text-muted">Order #<?php echo $orderId; ?></span>
                    <span class="ms-2 badge <?php echo $items[0]['status'] == 'pending' ? 'bg-warning' : 'bg-success'; ?>">
                        <?php echo strtoupper($items[0]['status']); ?>
                    </span>
                </div>
                <span class="text-muted small"><?php echo $items[0]['created_at']; ?></span>
            </div>
            <div class="card-body">
                <?php foreach ($items as $item): ?>
                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom-dashed">
                        <img src="../uploads/<?php echo $item['image']; ?>" class="rounded-3 me-3" width="60" height="60"
                            style="object-fit: cover;">
                        <div class="flex-grow-1">
                            <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($item['name']); ?></h6>
                            <small class="text-muted">Qty: <?php echo $item['quantity']; ?> ×
                                $<?php echo number_format($item['unit_price'], 2); ?></small>
                        </div>
                        <div class="fw-bold">$<?php echo number_format($item['quantity'] * $item['unit_price'], 2); ?></div>
                    </div>
                <?php endforeach; ?>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <h5 class="fw-bold mb-0">Total: $<?php echo number_format($items[0]['total_price'], 2); ?></h5>
                    <?php if ($items[0]['status'] == 'pending'): ?>
                        <button class="btn btn-success btn-sm rounded-pill px-4">Checkout Now</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>