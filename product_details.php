<?php
require_once 'config.php';
require_once 'includes/functions.php';
include 'includes/header.php';

if (!isset($_GET['id'])) {
    redirect('index.php');
}

$id = $_GET['id'];
try {
    $isWishlisted = false;
    if (isLoggedIn()) {
        $wishStmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $wishStmt->execute([$_SESSION['user_id'], $id]);
        if ($wishStmt->fetch()) {
            $isWishlisted = true;
        }
    }
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
} catch (Exception $e) {
    $product = false;
}

if (!$product) {
    // تصميم رسالة الخطأ بشكل أفضل
    echo "
    <div class='container py-5'>
        <div class='row justify-content-center'>
            <div class='col-md-6 text-center'>
                <div class='p-5' style='background: #fff; border-radius: 2rem; box-shadow: 0 10px 25px rgba(0,0,0,0.05);'>
                    <div style='width: 80px; height: 80px; background: #fee2e2; color: #ef4444; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 2rem; margin-bottom: 1.5rem;'>
                        <i class='fas fa-exclamation-triangle'></i>
                    </div>
                    <h2 class='fw-bold mb-3' style='color: #1e293b;'>Product Not Found</h2>
                    <p class='text-muted mb-4'>The product you are looking for might have been removed or is temporarily unavailable.</p>
                    <a href='index.php' class='btn-modern'>Back to Shop</a>
                </div>
            </div>
        </div>
    </div>";
    include 'includes/footer.php';
    exit;
}
?>

<!-- تنسيقات CSS إضافية لصفحة التفاصيل -->
<style>
    :root {
        --primary-color: #4f46e5;
        --primary-hover: #4338ca;
        --text-dark: #1e293b;
        --text-gray: #64748b;
        --bg-gray: #f8fafc;
    }

    /* زر العودة */
    .btn-back {
        color: var(--text-gray);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        transition: all 0.3s;
        padding: 0.5rem 0;
    }

    .btn-back:hover {
        color: var(--primary-color);
        transform: translateX(-5px);
    }

    /* حاوية الصورة */
    .product-image-container {
        background: #fff;
        border-radius: 1.5rem;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
    }

    .product-image-container img {
        max-height: 450px;
        width: 100%;
        object-fit: contain;
        transition: transform 0.3s ease;
    }

    /* التحكم بالكمية بشكل مخصص */
    .qty-control-wrapper {
        display: inline-flex;
        align-items: center;
        background: var(--bg-gray);
        border-radius: 50px;
        padding: 5px;
        border: 1px solid #e2e8f0;
    }

    .qty-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: none;
        background: white;
        color: var(--text-dark);
        font-weight: bold;
        font-size: 1.2rem;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .qty-btn:hover {
        background: var(--primary-color);
        color: white;
        box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);
    }

    .qty-input {
        width: 50px;
        border: none;
        background: transparent;
        text-align: center;
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--text-dark);
        outline: none;
    }

    /* زر الإضافة للسلة */
    .btn-cart-action {
        background-color: var(--primary-color);
        color: white;
        border: none;
        padding: 1rem 2.5rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1.1rem;
        box-shadow: 0 4px 14px 0 rgba(79, 70, 229, 0.4);
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .btn-cart-action:hover {
        background-color: var(--primary-hover);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(79, 70, 229, 0.5);
        color: white;
    }

    /* زر المفضلة */
    .btn-wishlist-action {
        width: 55px;
        height: 55px;
        border-radius: 50%;
        border: 2px solid #e2e8f0;
        background: white;
        color: var(--text-gray);
        font-size: 1.2rem;
        cursor: pointer;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-wishlist-action:hover {
        border-color: #ef4444;
        color: #ef4444;
        background: #fef2f2;
    }

    .btn-wishlist-action.active {
        background: #ef4444;
        border-color: #ef4444;
        color: white;
        animation: heartBeat 0.4s ease;
    }

    @keyframes heartBeat {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.1);
        }

        100% {
            transform: scale(1);
        }
    }

    .product-title {
        font-weight: 800;
        font-size: 2.5rem;
        color: var(--text-dark);
        line-height: 1.2;
        margin-bottom: 0.5rem;
    }

    .product-price {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 2rem;
        display: inline-block;
    }

    .product-desc-card {
        background: var(--bg-gray);
        border-radius: 1rem;
        padding: 1.5rem;
        border: 1px solid #f1f5f9;
        color: var(--text-gray);
        line-height: 1.7;
    }

    /* تنسيق الـ Alert */
    .custom-alert {
        border-radius: 0.75rem;
        border: none;
        padding: 1rem 1.5rem;
    }
</style>

<div class="container py-5">
    <!-- زر العودة -->
    <a href="javascript:history.back()" class="btn-back mb-4">
        <i class="fas fa-arrow-right ms-2"></i> Back to Shop
    </a>

    <div class="row align-items-center g-5">
        <!-- Product Image Gallery -->
        <div class="col-lg-6">
            <div class="product-image-container">
                <img src="uploads/<?php echo $product['image'] ?: 'product-default.png'; ?>"
                    alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
        </div>

        <!-- Product Info -->
        <div class="col-lg-6">
            <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>

            <div class="mb-4">
                <span class="product-price">$<?php echo number_format($product['price'], 2); ?></span>
            </div>

            <div class="product-desc-card mb-4">
                <p class="mb-0"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>

            <!-- Quantity & Actions -->
            <div class="d-flex flex-wrap align-items-center gap-4 mb-5">
                <div class="mb-3">
                    <label class="form-label fw-bold small text-uppercase text-muted mb-2 d-block">Quantity</label>
                    <div class="qty-control-wrapper">
                        <button class="qty-btn" type="button" id="decrease-qty">-</button>
                        <input type="text" class="qty-input" value="1" id="quantity" name="quantity" readonly>
                        <button class="qty-btn" type="button" id="increase-qty">+</button>
                    </div>
                </div>

                <div class="d-flex gap-3 align-items-end mb-3 mt-auto">
                    <?php if (isLoggedIn()): ?>
                        <button class="btn-cart-action" type="button" id="add-to-cart-btn"
                            data-product-id="<?php echo $product['id']; ?>">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                        <button class="btn-wishlist-action <?php echo $isWishlisted ? 'active' : ''; ?>" type="button"
                            id="add-to-wishlist-btn" data-product-id="<?php echo $product['id']; ?>">
                            <i class="fas fa-heart"></i>
                        </button>
                    <?php else: ?>
                        <a href="login.php" class="btn-cart-action">
                            <i class="fas fa-sign-in-alt"></i> Login to Purchase
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Alert Placeholder -->
            <div id="alert-placeholder" class="mt-3"></div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const decreaseQtyBtn = document.getElementById('decrease-qty');
        const increaseQtyBtn = document.getElementById('increase-qty');
        const quantityInput = document.getElementById('quantity');
        const addToCartBtn = document.getElementById('add-to-cart-btn');
        const addToWishlistBtn = document.getElementById('add-to-wishlist-btn');
        const alertPlaceholder = document.getElementById('alert-placeholder');

        // Quantity controls
        decreaseQtyBtn.addEventListener('click', function() {
            let currentValue = parseInt(quantityInput.value);
            if (currentValue > 1) {
                quantityInput.value = currentValue - 1;
            }
        });

        increaseQtyBtn.addEventListener('click', function() {
            let currentValue = parseInt(quantityInput.value);
            quantityInput.value = currentValue + 1;
        });

        // Add to Cart
        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', function() {
                // أضف تأثير تحميل بسيط (اختياري)
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';

                const productId = this.dataset.productId;
                const quantity = quantityInput.value;

                fetch('add_to_cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `product_id=${productId}&quantity=${quantity}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        showAlert(data.message, data.status);
                        this.innerHTML = originalText; // استعادة النص الأصلي
                    })
                    .catch(error => {
                        showAlert(`An error occurred. Please try again.`, 'danger');
                        this.innerHTML = originalText;
                    });
            });
        }

        // Add to Wishlist
        if (addToWishlistBtn) {
            addToWishlistBtn.addEventListener('click', function() {
                const productId = this.dataset.productId;

                fetch('add_to_wishlist.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `product_id=${productId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        showAlert(data.message, data.status);
                        if (data.status === 'success') {
                            this.classList.toggle('active');
                        }
                    })
                    .catch(error => {
                        showAlert('An error occurred. Please try again.', 'danger');
                    });
            });
        }

        function showAlert(message, type) {
            let bgClass = type === 'success' ? 'bg-light-success' : 'bg-light-danger';
            let icon = type === 'success' ? 'fa-check-circle text-success' : 'fa-times-circle text-danger';

            // تخصيص شكل الـ Alert ليناسب التصميم
            alertPlaceholder.innerHTML = `
                <div class="custom-alert alert alert-${type} alert-dismissible fade show d-flex align-items-center" role="alert" style="border-left: 5px solid ${type === 'success' ? '#198754' : '#dc3545'};">
                    <i class="fas ${icon} me-3 fs-5"></i>
                    <div>${message}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
        }
    });
</script>