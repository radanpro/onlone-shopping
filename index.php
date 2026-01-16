<?php
require_once 'config.php';
require_once 'includes/functions.php';
include 'includes/header.php';

$userId = isLoggedIn() ? $_SESSION['user_id'] : 0;

$catStmt = $pdo->query("SELECT DISTINCT c.* FROM categories c 
                        INNER JOIN products p ON c.id = p.category_id");
$categories = $catStmt->fetchAll();

$limit = 12;
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

<!-- ========== تنسيقات CSS المحسنة والمضافة ========== -->
<style>
    :root {
        --primary-color: #4f46e5;
        --primary-hover: #4338ca;
        --bg-body: #f8fafc;
        --text-dark: #1e293b;
        --text-gray: #64748b;
        --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        --card-hover-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        --accent-color: #f59e0b;
        /* لون النجوم */
    }

    /* --- 1. تصميم الشريط المتحرك (Ticker) --- */
    .ticker-wrap {
        width: 100%;
        height: 50px;
        overflow: hidden;
        background-color: var(--text-dark);
        /* خلفية داكنة */
        color: white;
        position: relative;
        transform: skewY(-2deg);
        /* الميل */
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        z-index: 1000;
        margin-top: -2px;
        /* للتأكد من عدم وجود فجوة */
    }

    .ticker {
        display: inline-block;
        white-space: nowrap;
        padding-right: 100%;
        animation: ticker 25s linear infinite;
    }

    .ticker-item {
        display: inline-block;
        padding: 0 2rem;
        font-size: 1.1rem;
        font-weight: 600;
        color: #fbbf24;
        /* لون ذهبي للنص */
    }

    .ticker-item i {
        margin: 0 10px;
        font-size: 1.2rem;
    }

    @keyframes ticker {
        0% {
            transform: translate3d(100%, 0, 0);
        }

        100% {
            transform: translate3d(0, 0, 0);
        }

        /* في العربية العربية تتحرك من اليمين لليسار */
    }

    /* --- تصميم الهيرو سكشن --- */
    .hero-modern {
        background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
        border-radius: 1.5rem;
        padding: 3rem 2rem;
        margin-bottom: 4rem;
        box-shadow: var(--card-shadow);
        border: 1px solid rgba(255, 255, 255, 0.5);
        position: relative;
        overflow: hidden;
    }

    .hero-modern::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: rgba(79, 70, 229, 0.05);
        border-radius: 50%;
        z-index: 0;
    }

    .hero-modern h1 {
        font-weight: 800;
        letter-spacing: -1px;
        color: var(--text-dark);
    }

    .btn-modern {
        background-color: var(--primary-color);
        color: white;
        padding: 0.75rem 2rem;
        border-radius: 50px;
        border: none;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 14px 0 rgba(79, 70, 229, 0.3);
    }

    .btn-modern:hover {
        transform: translateY(-2px);
        background-color: var(--primary-hover);
        color: white;
        box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
    }

    /* --- قسم التصنيفات --- */
    .cat-scroll-container {
        display: flex;
        overflow-x: auto;
        gap: 1.5rem;
        padding: 1rem 0.5rem;
        scroll-behavior: smooth;
        scrollbar-width: none;
    }

    .cat-scroll-container::-webkit-scrollbar {
        display: none;
    }

    .cat-wrapper {
        position: relative;
        margin-bottom: 4rem;
    }

    .scroll-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 45px;
        height: 45px;
        background: white;
        border-radius: 50%;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 5;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-dark);
        transition: all 0.3s ease;
        opacity: 0;
    }

    .cat-wrapper:hover .scroll-btn {
        opacity: 1;
    }

    .scroll-btn:hover {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    .btn-left {
        left: -20px;
    }

    .btn-right {
        right: -20px;
    }

    /* --- تحسينات الكروت (Option 1 & 3) --- */
    .cat-item,
    .product-col {
        opacity: 0;
        animation: fadeInUp 0.6s ease forwards;
    }

    .modern-card {
        border: none;
        background: #fff;
        border-radius: 1rem;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        box-shadow: var(--card-shadow);
        height: 100%;
        position: relative;
    }

    .modern-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--card-hover-shadow);
    }

    .card-img-wrapper {
        position: relative;
        overflow: hidden;
        padding-top: 65%;
    }

    .modern-card .card-img-top {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .modern-card:hover .card-img-top {
        transform: scale(1.08);
    }

    /* Overlay للإجراءات السريعة (Option 1) */
    .card-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.4);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        gap: 10px;
        opacity: 0;
        transition: all 0.3s ease;
        z-index: 1;
    }

    .modern-card:hover .card-overlay {
        opacity: 1;
    }

    .btn-quick-add {
        background: white;
        color: var(--text-dark);
        padding: 0.6rem 1.5rem;
        border-radius: 30px;
        font-weight: 600;
        font-size: 0.9rem;
        border: none;
        transform: translateY(20px);
        transition: all 0.3s ease;
        cursor: pointer;
        width: 80%;
    }

    .modern-card:hover .btn-quick-add {
        transform: translateY(0);
    }

    .btn-quick-add:hover {
        background: var(--primary-color);
        color: white;
    }

    /* شارة New (Option 3) */
    .badge-new {
        position: absolute;
        top: 10px;
        left: 10px;
        background: var(--primary-color);
        color: white;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 700;
        z-index: 2;
        text-transform: uppercase;
    }

    /* أيقونة القلب */
    .wishlist-icon {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 35px;
        height: 35px;
        background: rgba(255, 255, 255, 0.9);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        z-index: 2;
        cursor: pointer;
        transition: transform 0.2s;
    }

    .wishlist-icon:hover {
        transform: scale(1.1);
    }

    .wishlist-icon i {
        font-size: 0.9rem;
        transition: color 0.3s;
    }

    @keyframes heartBeat {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.3);
        }

        100% {
            transform: scale(1);
        }
    }

    .wishlist-icon.active i {
        animation: heartBeat 0.4s ease;
    }

    .card-body-custom {
        padding: 1.25rem;
        text-align: center;
    }

    .card-title-custom {
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
        font-size: 1rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* التقييمات (Option 3) */
    .rating-stars {
        color: var(--accent-color);
        font-size: 0.8rem;
        margin-bottom: 0.5rem;
    }

    .card-price {
        color: var(--primary-color);
        font-weight: 800;
        font-size: 1.1rem;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    #products .row {
        margin-top: 1rem;
    }
</style>
<!-- ============================================== -->



<!-- Hero Section -->
<div class="container">
    <div class="hero-modern d-flex align-items-center">
        <div class="col-lg-8">
            <span
                style="color: var(--primary-color); font-weight: 600; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px;">New
                Arrivals</span>
            <h1 class="display-4 fw-bold mb-3 mt-2">Shop By Category</h1>
            <p class="lead text-muted mb-4" style="max-width: 500px;">Explore our wide range of products organized just
                for you with the best quality guaranteed.</p>
            <a href="#all-products" class="btn-modern">Browse All Products</a>
        </div>
    </div>
</div>

<?php foreach ($categories as $index => $category): ?>
    <?php
    // جلب منتجات هذه الفئة
    $pStmt = $pdo->prepare("SELECT p.*, (SELECT COUNT(*) FROM wishlist w WHERE w.product_id = p.id AND w.user_id = ?) as is_wishlisted FROM products p WHERE category_id = ? LIMIT 10");
    $pStmt->execute([$userId, $category['id']]);
    $catProducts = $pStmt->fetchAll();

    if (empty($catProducts)) continue;
    ?>

    <div class="container category-section">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold m-0" style="position: relative; padding-right: 15px;">
                <?php echo htmlspecialchars($category['name']); ?>
                <span
                    style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); width: 4px; height: 20px; background: var(--primary-color); border-radius: 2px;"></span>
            </h3>
        </div>

        <div class="cat-wrapper">
            <button class="scroll-btn btn-right"
                onclick="this.nextElementSibling.scrollBy({left: 300, behavior: 'smooth'})"><i
                    class="fas fa-chevron-right"></i></button>

            <div class="cat-scroll-container">
                <?php foreach ($catProducts as $p): ?>
                    <div class="cat-item" style="min-width: 240px; max-width: 240px;">
                        <div class="modern-card">
                            <div class="card-img-wrapper">
                                <span class="badge-new">New</span> <!-- Badge -->
                                <img src="uploads/<?php echo $p['image'] ?: 'product-default.png'; ?>" class="card-img-top"
                                    alt="<?php echo htmlspecialchars($p['name']); ?>">

                                <div class="wishlist-icon <?php echo $p['is_wishlisted'] ? 'active text-danger' : ''; ?>"
                                    onclick="toggleWishlist(this, '<?php echo $p['id']; ?>')">
                                    <i class="<?php echo $p['is_wishlisted'] ? 'fas' : 'far'; ?> fa-heart"></i>
                                </div>

                                <!-- Quick Action Overlay -->
                                <div class="card-overlay">
                                    <button class="btn-quick-add" onclick="addToCart(<?php echo $p['id']; ?>)">
                                        <i class="fas fa-shopping-cart"></i> Add to Cart
                                    </button>
                                </div>
                            </div>
                            <div class="card-body-custom">
                                <!-- Mock Stars -->
                                <div class="rating-stars">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                                        class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                                </div>
                                <h6 class="card-title-custom text-truncate"><?php echo htmlspecialchars($p['name']); ?></h6>
                                <p class="card-price mb-3">$<?php echo number_format($p['price'], 2); ?></p>
                                <a href="product_details.php?id=<?php echo $p['id']; ?>"
                                    class="btn btn-outline-modern w-100">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <button class="scroll-btn btn-left"
                onclick="this.previousElementSibling.scrollBy({left: -300, behavior: 'smooth'})"><i
                    class="fas fa-chevron-left"></i></button>
        </div>
    </div>
<?php endforeach; ?>


<!-- الشريط المتحرك الجديد (Ticker) -->
<div class="ticker-wrap bx-5">
    <div class="ticker">
        <div class="ticker-item"><i class="fas fa-shipping-fast"></i> شحن سريع لجميع المناطق</div>
        <div class="ticker-item"><i class="fas fa-store"></i> Radan Commercial Market</div>
        <div class="ticker-item"><i class="fas fa-tags"></i> خصومات تصل إلى 50%</div>
        <div class="ticker-item"><i class="fas fa-headset"></i> دعم فني 24/7</div>
        <div class="ticker-item"><i class="fas fa-star"></i> أفضل جودة مضمونة</div>
        <div class="ticker-item"><i class="fas fa-shipping-fast"></i> شحن سريع لجميع المناطق</div>
        <div class="ticker-item"><i class="fas fa-store"></i> Radan Commercial Market</div>
    </div>
</div>

<hr class="my-5 mx-auto" style="width: 80%; border-top: 1px solid #e2e8f0; opacity: 0.5;">

<div class="container mb-5" id="all-products">
    <h3 class="fw-bold mb-4">All Products</h3>
    <div class="row">
        <?php foreach ($allProducts as $product): ?>
            <div class="col-sm-6 col-md-4 col-lg-3 mb-4 product-col">
                <div class="modern-card">
                    <div class="card-img-wrapper">
                        <span class="badge-new">Hot</span> <!-- Badge -->
                        <img src="uploads/<?php echo $product['image'] ?: 'product-default.png'; ?>" class="card-img-top"
                            alt="<?php echo htmlspecialchars($product['name']); ?>">

                        <div class="wishlist-icon <?php echo $product['is_wishlisted'] ? 'active text-danger' : ''; ?>"
                            onclick="toggleWishlist(this, '<?php echo $product['id']; ?>')">
                            <i class="<?php echo $product['is_wishlisted'] ? 'fas' : 'far'; ?> fa-heart"></i>
                        </div>

                        <!-- Quick Action Overlay -->
                        <div class="card-overlay">
                            <button class="btn-quick-add" onclick="addToCart(<?php echo $product['id']; ?>)">
                                <i class="fas fa-shopping-cart"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                    <div class="card-body-custom text-center">
                        <div class="rating-stars">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                                class="fas fa-star"></i><i class="far fa-star"></i>
                        </div>
                        <h5 class="card-title-custom text-truncate"><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p class="text-muted small mb-2 text-truncate" style="color: #94a3b8 !important;">
                            <?php echo htmlspecialchars(substr($product['description'], 0, 50)) . '...'; ?></p>
                        <div class="d-flex justify-content-between align-items-center mt-3 px-1">
                            <span class="card-price">$<?php echo number_format($product['price'], 2); ?></span>
                            <a href="product_details.php?id=<?php echo $product['id']; ?>"
                                class="btn btn-outline-modern btn-sm">Details</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Pagination (Modern Style) -->
<div class="container ">
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <li class="page-item disabled">
                <a class="page-link rounded-circle mx-1 border-0"
                    style="width:40px; height:40px; display:flex; align-items:center; justify-content:center;" href="#"
                    tabindex="-1"><i class="fas fa-chevron-left"></i></a>
            </li>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                    <a class="page-link rounded-circle mx-1 border-0 shadow-sm <?php echo ($i == $page) ? 'bg-primary text-white border-primary' : ''; ?>"
                        style="width:40px; height:40px; display:flex; align-items:center; justify-content:center; transition:all 0.2s;"
                        href="?page=<?php echo $i; ?>#all-products"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item">
                <a class="page-link rounded-circle mx-1 border-0"
                    style="width:40px; height:40px; display:flex; align-items:center; justify-content:center;"
                    href="#"><i class="fas fa-chevron-right"></i></a>
            </li>
        </ul>
    </nav>
</div>

<!-- Toast Container -->
<div id="toast-container" style="position: fixed; bottom: 20px; left: 20px; z-index: 1050;"></div>

<script>
    // Wishlist Toggle
    function toggleWishlist(element, productId) {
        const icon = element.querySelector('i');
        const isWishlisted = element.classList.contains('active');

        fetch('add_to_wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `product_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                showToast(data.message, data.status);
                if (data.status === 'success') {
                    element.classList.toggle('active');
                    element.classList.toggle('text-danger');
                    icon.classList.toggle('fas');
                    icon.classList.toggle('far');
                }
            })
            .catch(error => {
                showToast('Error occurred.', 'danger');
            });
    }

    // Add to Cart from Grid
    function addToCart(productId) {
        fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `product_id=${productId}&quantity=1`
            })
            .then(response => response.json())
            .then(data => {
                showToast(data.message, data.status);
            })
            .catch(error => {
                showToast('Error adding to cart.', 'danger');
            });
    }

    // Toast Notification Function
    function showToast(message, type) {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `alert alert-${type} shadow-sm`;
        toast.style.minWidth = '250px';
        toast.style.animation = 'fadeInUp 0.3s ease';
        toast.innerText = message;

        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
</script>

<?php include 'includes/footer.php'; ?>