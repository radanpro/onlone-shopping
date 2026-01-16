<?php
require_once 'config.php';
require_once 'includes/functions.php';
include 'includes/header.php';

$userId = isLoggedIn() ? $_SESSION['user_id'] : 0;

$catStmt = $pdo->query("SELECT * FROM categories");
$categories = $catStmt->fetchAll();

$limit = 8;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$countStmt = $pdo->query("SELECT COUNT(*) FROM products");
$totalProducts = $countStmt->fetchColumn();
$totalPages = ceil($totalProducts / $limit);

$sql = "SELECT p.*, (SELECT COUNT(*) FROM wishlist w WHERE w.product_id = p.id AND w.user_id = ?) as is_wishlisted 
        FROM products p 
        ORDER BY p.id DESC 
        LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$allProducts = $stmt->fetchAll();
?>

<style>
    .cat-scroll-container {
        display: flex;
        overflow-x: auto;
        gap: 1.5rem;
        padding: 1rem 0;
        scroll-behavior: smooth;
        scrollbar-width: none;
    }

    .cat-scroll-container::-webkit-scrollbar {
        display: none;
    }

    .cat-wrapper {
        position: relative;
        margin-bottom: 3rem;
    }

    .scroll-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 35px;
        height: 35px;
        background: white;
        border-radius: 50%;
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        z-index: 5;
    }

    .btn-left {
        left: -15px;
    }

    .btn-right {
        right: -15px;
    }

    .cat-item {
        min-width: 220px;
        max-width: 220px;
    }
</style>

<div class="row mb-5 align-items-center bg-white rounded-4 p-4 p-md-5 shadow-sm mx-0">
    <div class="col-md-6">
        <h1 class="display-4 fw-bold text-primary mb-3">Shop By Category</h1>
        <p class="lead text-muted">Explore our wide range of products organized just for you.</p>
        <a href="#all-products" class="btn btn-primary rounded-pill px-4">Browse All</a>
    </div>
</div>

<?php foreach ($categories as $category): ?>
    <div class="category-section mb-5">
        <h3 class="fw-bold mb-3"><?php echo htmlspecialchars($category['name']); ?></h3>
        <div class="cat-wrapper">
            <button class="scroll-btn btn-left"
                onclick="this.nextElementSibling.scrollBy({left: -300, behavior: 'smooth'})"><i
                    class="fas fa-chevron-left"></i></button>
            <div class="cat-scroll-container">
                <?php
                $pStmt = $pdo->prepare("SELECT p.*, (SELECT COUNT(*) FROM wishlist w WHERE w.product_id = p.id AND w.user_id = ?) as is_wishlisted FROM products p WHERE category_id = ? LIMIT 10");
                $pStmt->execute([$userId, $category['id']]);
                $catProducts = $pStmt->fetchAll();

                if (empty($catProducts)):
                    echo "<p class='text-muted ps-2'>No products in this category yet.</p>";
                else:
                    foreach ($catProducts as $p):
                ?>
                        <div class="cat-item">
                            <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden">
                                <div class="position-relative">
                                    <img src="uploads/<?php echo $p['image'] ?: 'product-default.png'; ?>" class="card-img-top"
                                        style="height: 150px; object-fit: cover;">
                                    <div class="position-absolute top-0 start-0 m-2">
                                        <i
                                            class="<?php echo $p['is_wishlisted'] ? 'fas' : 'far'; ?> fa-heart text-danger bg-white p-2 rounded-circle shadow-sm small"></i>
                                    </div>
                                </div>
                                <div class="card-body p-3 text-center">
                                    <h6 class="text-truncate fw-bold mb-1 small"><?php echo htmlspecialchars($p['name']); ?></h6>
                                    <p class="text-primary fw-bold mb-2 small">$<?php echo number_format($p['price'], 2); ?></p>
                                    <a href="product_details.php?id=<?php echo $p['id']; ?>"
                                        class="btn btn-sm btn-outline-primary w-100 rounded-pill">View</a>
                                </div>
                            </div>
                        </div>
                <?php endforeach;
                endif; ?>
            </div>
            <button class="scroll-btn btn-right"
                onclick="this.previousElementSibling.scrollBy({left: 300, behavior: 'smooth'})"><i
                    class="fas fa-chevron-right"></i></button>
        </div>
    </div>
<?php endforeach; ?>

<hr class="my-5" id="all-products">

<div id="products" class="row">
    <div class="col-12 mb-4">
        <h2 class="fw-bold"><i class="fas fa-th-large me-2 text-secondary"></i>All Products</h2>
    </div>

    <?php foreach ($allProducts as $product): ?>
        <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
            <div class="card product-card h-100 shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="position-relative">
                    <img src="uploads/<?php echo $product['image'] ?: 'product-default.png'; ?>" class="card-img-top"
                        style="height: 200px; object-fit: cover;">
                    <div class="position-absolute top-0 start-0 m-3">
                        <i
                            class="<?php echo $product['is_wishlisted'] ? 'fas' : 'far'; ?> fa-heart text-danger bg-white p-2 rounded-circle shadow-sm"></i>
                    </div>
                </div>
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title text-truncate fw-bold"><?php echo htmlspecialchars($product['name']); ?></h5>
                    <p class="card-text text-muted small mb-3">
                        <?php echo htmlspecialchars(substr($product['description'], 0, 60)) . '...'; ?></p>
                    <div class="mt-auto d-flex justify-content-between align-items-center">
                        <span class="price fw-bold text-primary">$<?php echo number_format($product['price'], 2); ?></span>
                        <a href="product_details.php?id=<?php echo $product['id']; ?>"
                            class="btn btn-outline-primary btn-sm rounded-pill px-3">Details</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php if ($totalPages > 1): ?>
    <nav class="mt-5">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link rounded-circle mx-1" href="?page=<?php echo $page - 1; ?>#all-products"><i
                        class="fas fa-chevron-left"></i></a>
            </li>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                    <a class="page-link rounded-circle mx-1" href="?page=<?php echo $i; ?>#all-products"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                <a class="page-link rounded-circle mx-1" href="?page=<?php echo $page + 1; ?>#all-products"><i
                        class="fas fa-chevron-right"></i></a>
            </li>
        </ul>
    </nav>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>