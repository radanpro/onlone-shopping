index.php:

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
        <p class="lead text-muted mb-4">Shop the latest trends with confidence. Quality products, fast shipping, and
            amazing prices all in one place.</p>
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
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown"
                data-bs-toggle="dropdown" aria-expanded="false">
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
                <img src="uploads/<?php echo $product['image'] ?: 'product-default.png'; ?>" class="card-img-top"
                    alt="<?php echo htmlspecialchars($product['name']); ?>">
                <span class="position-absolute top-0 end-0 m-3 badge rounded-pill bg-danger">New</span>
            </div>
            <div class="card-body d-flex flex-column">
                <h5 class="card-title text-truncate"><?php echo htmlspecialchars($product['name']); ?></h5>
                <p class="card-text text-muted small mb-3">
                    <?php echo htmlspecialchars(substr($product['description'], 0, 60)) . '...'; ?>
                </p>
                <div class="mt-auto d-flex justify-content-between align-items-center">
                    <span class="price">$<?php echo number_format($product['price'], 2); ?></span>
                    <a href="product_details.php?id=<?php echo $product['id']; ?>"
                        class="btn btn-outline-primary btn-sm rounded-pill px-3">
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

---

product_details.php:

<?php
require_once 'config.php';
require_once 'includes/functions.php';
include 'includes/header.php';

if (!isset($_GET['id'])) {
    redirect('index.php');
}

$id = $_GET['id'];
// Using a safe query that doesn't depend on categories table if it doesn't exist
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
} catch (Exception $e) {
    $product = false;
}

if (!$product) {
    echo "
    <div class='row justify-content-center py-5'>
        <div class='col-md-6 text-center'>
            <div class='card border-0 shadow-sm p-5'>
                <i class='fas fa-exclamation-triangle fa-4x text-warning mb-3'></i>
                <h2 class='fw-bold'>Product Not Found</h2>
                <p class='text-muted'>The product you are looking for might have been removed or is temporarily unavailable.</p>
                <a href='index.php' class='btn btn-primary rounded-pill px-4 mt-3'>Back to Shop</a>
            </div>
        </div>
    </div>";
    include 'includes/footer.php';
    exit;
}
?>

<div class="row mt-4 g-5">
    <!-- Product Image Gallery -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <img src="uploads/<?php echo $product['image'] ?: 'product-default.png'; ?>" class="img-fluid w-100"
                alt="<?php echo htmlspecialchars($product['name']); ?>"
                style="max-height: 500px; object-fit: contain; background: #fff;">
        </div>
        <div class="row mt-3 g-2">
            <div class="col-3">
                <img src="uploads/<?php echo $product['image'] ?: 'product-default.png'; ?>"
                    class="img-thumbnail rounded-3 cursor-pointer opacity-75" alt="thumb">
            </div>
        </div>
    </div>

    <!-- Product Info -->
    <div class="col-md-6">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Product Details</li>
            </ol>
        </nav>

        <h1 class="display-5 fw-bold text-dark mb-2"><?php echo htmlspecialchars($product['name']); ?></h1>

        <div class="d-flex align-items-center mb-4">
            <div class="text-warning me-2">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star-half-alt"></i>
            </div>
            <span class="text-muted small">(4.5/5 based on 120 reviews)</span>
        </div>

        <div class="mb-4">
            <span class="h2 fw-bold text-primary">$<?php echo number_format($product['price'], 2); ?></span>
            <span
                class="text-muted text-decoration-line-through ms-2 small">$<?php echo number_format($product['price'] * 1.2, 2); ?></span>
            <span class="badge bg-success ms-2">Save 20%</span>
        </div>

        <div class="card border-0 bg-light rounded-3 mb-4">
            <div class="card-body">
                <h6 class="fw-bold mb-2"><i class="fas fa-info-circle me-2"></i>Product Description</h6>
                <p class="text-muted mb-0"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <label class="form-label fw-bold small text-uppercase">Quantity</label>
                <div class="input-group mb-3" style="max-width: 140px;">
                    <button class="btn btn-outline-secondary" type="button">-</button>
                    <input type="text" class="form-control text-center" value="1">
                    <button class="btn btn-outline-secondary" type="button">+</button>
                </div>
            </div>
        </div>

        <div class="d-grid gap-3 d-md-flex">
            <?php if (isLoggedIn()): ?>
            <button class="btn btn-primary btn-lg px-5 rounded-pill shadow-sm" type="button">
                <i class="fas fa-cart-plus me-2"></i>Add to Cart
            </button>
            <button class="btn btn-outline-danger btn-lg rounded-pill" type="button">
                <i class="far fa-heart"></i>
            </button>
            <?php else: ?>
            <a href="login.php" class="btn btn-primary btn-lg px-5 rounded-pill shadow-sm">
                <i class="fas fa-sign-in-alt me-2"></i>Login to Purchase
            </a>
            <?php endif; ?>
        </div>

        <div class="mt-5 pt-4 border-top">
            <div class="row g-3">
                <div class="col-6">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-truck text-primary me-3 fa-lg"></i>
                        <div>
                            <p class="mb-0 fw-bold small">Free Shipping</p>
                            <p class="mb-0 text-muted extra-small" style="font-size: 0.75rem;">On orders over $100</p>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-undo text-primary me-3 fa-lg"></i>
                        <div>
                            <p class="mb-0 fw-bold small">30 Days Return</p>
                            <p class="mb-0 text-muted extra-small" style="font-size: 0.75rem;">Easy money back guarantee
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Related Products Placeholder -->
<div class="row mt-5 pt-5">
    <div class="col-12 mb-4">
        <h3 class="fw-bold">You May Also Like</h3>
    </div>
    <!-- This would normally be a loop of related products -->
    <div class="col-md-12 text-center py-4 bg-light rounded-4">
        <p class="text-muted mb-0">More products coming soon!</p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
