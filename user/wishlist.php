<?php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';
include '../includes/header.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$userId = $_SESSION['user_id'];

// جلب منتجات المفضلة
$stmt = $pdo->prepare("
    SELECT p.* FROM products p
    JOIN wishlist w ON p.id = w.product_id
    WHERE w.user_id = ?
");
$stmt->execute([$userId]);
$wishlist = $stmt->fetchAll();
?>

<h2 class="fw-bold mb-4"><i class="fas fa-heart me-2 text-danger"></i>My Wishlist</h2>

<div class="row">
    <?php if (empty($wishlist)): ?>
        <div class="col-12 text-center py-5 bg-white rounded-4 shadow-sm">
            <i class="far fa-heart fa-4x text-muted mb-3"></i>
            <h4>Your wishlist is empty</h4>
            <p class="text-muted">Save items you like to see them here.</p>
            <a href="../index.php" class="btn btn-outline-primary rounded-pill mt-2">Explore Products</a>
        </div>
    <?php else: ?>
        <?php foreach ($wishlist as $product): ?>
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card product-card h-100 shadow-sm border-0 rounded-4">
                    <img src="../uploads/<?php echo $product['image']; ?>" class="card-img-top rounded-top-4" alt="...">
                    <div class="card-body">
                        <h6 class="fw-bold text-truncate"><?php echo htmlspecialchars($product['name']); ?></h6>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="text-primary fw-bold">$<?php echo number_format($product['price'], 2); ?></span>
                            <a href="../product_details.php?id=<?php echo $product['id']; ?>"
                                class="btn btn-sm btn-primary rounded-pill px-3">View</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>