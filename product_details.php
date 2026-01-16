<?php
require_once 'config.php';
require_once 'includes/functions.php';
include 'includes/header.php';

if (!isset($_GET['id'])) {
    redirect('index.php');
}

$id = $_GET['id'];
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
    </div>

    <!-- Product Info -->
    <div class="col-md-6">
        <h1 class="display-5 fw-bold text-dark mb-2"><?php echo htmlspecialchars($product['name']); ?></h1>
        <div class="mb-4">
            <span class="h2 fw-bold text-primary">$<?php echo number_format($product['price'], 2); ?></span>
        </div>
        <div class="card border-0 bg-light rounded-3 mb-4">
            <div class="card-body">
                <h6 class="fw-bold mb-2"><i class="fas fa-info-circle me-2"></i>Product Description</h6>
                <p class="text-muted mb-0"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <label for="quantity" class="form-label fw-bold small text-uppercase">Quantity</label>
                <div class="input-group mb-3" style="max-width: 140px;">
                    <button class="btn btn-outline-secondary" type="button" id="decrease-qty">-</button>
                    <input type="text" class="form-control text-center" value="1" id="quantity" name="quantity"
                        readonly>
                    <button class="btn btn-outline-secondary" type="button" id="increase-qty">+</button>
                </div>
            </div>
        </div>

        <div class="d-grid gap-3 d-md-flex">
            <?php if (isLoggedIn()): ?>
                <button class="btn btn-primary btn-lg px-5 rounded-pill shadow-sm" type="button" id="add-to-cart-btn"
                    data-product-id="<?php echo $product['id']; ?>">
                    <i class="fas fa-cart-plus me-2"></i>Add to Cart
                </button>
                <button class="btn btn-outline-danger btn-lg rounded-pill" type="button" id="add-to-wishlist-btn"
                    data-product-id="<?php echo $product['id']; ?>">
                    <i class="far fa-heart"></i>
                </button>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary btn-lg px-5 rounded-pill shadow-sm">
                    <i class="fas fa-sign-in-alt me-2"></i>Login to Purchase
                </a>
            <?php endif; ?>
        </div>
        <div id="alert-placeholder" class="mt-3"></div>
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
                    })
                    .catch(error => {
                        showAlert(`An error occurred. Please try again. ${error}`, 'danger');
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
                            this.querySelector('i').classList.toggle('far');
                            this.querySelector('i').classList.toggle('fas');
                        }
                    })
                    .catch(error => {
                        showAlert('An error occurred. Please try again.', 'danger');
                    });
            });
        }

        function showAlert(message, type) {
            alertPlaceholder.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>`;
        }
    });
</script>