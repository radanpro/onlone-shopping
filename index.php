<?php
require_once 'config.php';
require_once 'includes/functions.php';
include 'includes/header.php';

$stmt = $pdo->query("SELECT * FROM products");
$products = $stmt->fetchAll();
?>

<!-- Hero Section -->
<div class="row mb-5 align-items-center bg-white rounded-4 p-4 p-md-5 shadow-sm mx-0">
    <div class="col-md-6">
        <h1 class="display-4 fw-bold text-primary mb-3">Discover the Best Deals Online</h1>
        <p class="lead text-muted mb-4">Shop the latest trends with confidence. Quality products, fast shipping, and amazing prices all in one place.</p>
        <a href="#products" class="btn btn-primary btn-lg px-5 py-3 rounded-pill">Shop Now</a>
    </div>
    <div class="col-md-6 d-none d-md-block text-center">
        <i class="fas fa-shopping-cart text-primary" style="font-size: 10rem; opacity: 0.1;"></i>
    </div>
</div>

<div id="products" class="row">
    <div class="col-12 d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fas fa-th-large me-2 text-secondary"></i>Our Products</h2>
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                Sort By
            </button>
            <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                <li><a class="dropdown-item" href="#">Newest</a></li>
                <li><a class="dropdown-item" href="#">Price: Low to High</a></li>
                <li><a class="dropdown-item" href="#">Price: High to Low</a></li>
            </ul>
        </div>
    </div>

    <?php if (empty($products)): ?>
        <div class="col-12 text-center py-5">
            <div class="card border-0 shadow-sm p-5">
                <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No products available yet.</h4>
                <p>Check back later for exciting new arrivals!</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($products as $product): ?>
            <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
                <div class="card product-card h-100 shadow-sm">
                    <div class="position-relative">
                        <img src="uploads/<?php echo $product['image'] ?: 'product-default.png'; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <span class="position-absolute top-0 end-0 m-3 badge rounded-pill bg-danger">New</span>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title text-truncate"><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p class="card-text text-muted small mb-3">
                            <?php echo htmlspecialchars(substr($product['description'], 0, 60)) . '...'; ?>
                        </p>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <span class="price">$<?php echo number_format($product['price'], 2); ?></span>
                            <a href="product_details.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Features Section -->
<div class="row mt-5 py-5 border-top">
    <div class="col-md-4 text-center mb-4">
        <div class="mb-3"><i class="fas fa-shipping-fast fa-3x text-primary"></i></div>
        <h5>Fast Delivery</h5>
        <p class="text-muted">Get your orders delivered to your doorstep in no time.</p>
    </div>
    <div class="col-md-4 text-center mb-4">
        <div class="mb-3"><i class="fas fa-shield-alt fa-3x text-primary"></i></div>
        <h5>Secure Payment</h5>
        <p class="text-muted">Your transactions are safe and encrypted with us.</p>
    </div>
    <div class="col-md-4 text-center mb-4">
        <div class="mb-3"><i class="fas fa-headset fa-3x text-primary"></i></div>
        <h5>24/7 Support</h5>
        <p class="text-muted">Our team is always here to help you with any queries.</p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
