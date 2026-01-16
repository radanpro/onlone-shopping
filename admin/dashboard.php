<?php
require_once '../config.php';
require_once '../includes/functions.php';
checkAdmin();

$user_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$product_count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$category_count = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$order_count = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();

include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="fw-bold"><i class="fas fa-tachometer-alt me-2 text-primary"></i>Admin Dashboard</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                </ol>
            </nav>
        </div>
        <hr>
    </div>
</div>

<div class="row g-4">
    <!-- Users Stat Card -->
    <div class="col-md-4">
        <div class="card stat-card h-100 shadow-sm border-0"
            style="background: linear-gradient(45deg, #4e73df 0%, #224abe 100%);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 text-uppercase fw-bold mb-1">Total Users</h6>
                        <h2 class="text-white fw-bold mb-0"><?php echo number_format($user_count); ?></h2>
                    </div>
                    <div class="bg-white-50 rounded-circle p-3" style="background: rgba(255,255,255,0.2);">
                        <i class="fas fa-users fa-2x text-white"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0">
                <a class="small text-white text-decoration-none d-flex align-items-center" href="users.php">
                    View All Users <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Products Stat Card -->
    <div class="col-md-4">
        <div class="card stat-card h-100 shadow-sm border-0"
            style="background: linear-gradient(45deg, #1cc88a 0%, #13855c 100%);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 text-uppercase fw-bold mb-1">Total Products</h6>
                        <h2 class="text-white fw-bold mb-0"><?php echo number_format($product_count); ?></h2>
                    </div>
                    <div class="bg-white-50 rounded-circle p-3" style="background: rgba(255,255,255,0.2);">
                        <i class="fas fa-box fa-2x text-white"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0">
                <a class="small text-white text-decoration-none d-flex align-items-center" href="products.php">
                    Manage Inventory <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>
    <!-- Category Stat Card -->
    <div class="col-md-4">
        <div class="card stat-card h-100 shadow-sm border-0"
            style="background: linear-gradient(45deg, #1cc88a 0%, #13855c 100%);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 text-uppercase fw-bold mb-1">Total Categories</h6>
                        <h2 class="text-white fw-bold mb-0"><?php echo number_format($category_count); ?></h2>
                    </div>
                    <div class="bg-white-50 rounded-circle p-3" style="background: rgba(255,255,255,0.2);">
                        <i class="fas fa-tags fa-2x text-white"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0">
                <a class="small text-white text-decoration-none d-flex align-items-center" href="categories.php">
                    Manage Categories <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Orders Stat Card -->
    <div class="col-md-4">
        <div class="card stat-card h-100 shadow-sm border-0"
            style="background: linear-gradient(45deg, #f6c23e 0%, #dda20a 100%);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 text-uppercase fw-bold mb-1">Total Orders</h6>
                        <h2 class="text-white fw-bold mb-0"><?php echo number_format($order_count); ?></h2>
                    </div>
                    <div class="bg-white-50 rounded-circle p-3" style="background: rgba(255,255,255,0.2);">
                        <i class="fas fa-shopping-cart fa-2x text-white"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0">
                <a class="small text-white text-decoration-none d-flex align-items-center" href="orders.php">
                    View Order History <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions Section -->
<div class="row mt-5">
    <div class="col-md-12 mb-4">
        <h4 class="fw-bold">Quick Actions</h4>
    </div>
    <div class="col-md-3">
        <a href="products.php?action=add"
            class="btn btn-white shadow-sm w-100 py-3 border rounded-3 text-decoration-none text-dark">
            <i class="fas fa-plus-circle text-success mb-2 d-block fs-3"></i>
            Add New Product
        </a>
    </div>
    <div class="col-md-3">
        <a href="users.php" class="btn btn-white shadow-sm w-100 py-3 border rounded-3 text-decoration-none text-dark">
            <i class="fas fa-user-shield text-primary mb-2 d-block fs-3"></i>
            Manage Admins
        </a>
    </div>
    <div class="col-md-3">
        <a href="../index.php"
            class="btn btn-white shadow-sm w-100 py-3 border rounded-3 text-decoration-none text-dark">
            <i class="fas fa-eye text-info mb-2 d-block fs-3"></i>
            View Storefront
        </a>
    </div>
    <div class="col-md-3">
        <a href="settings.php"
            class="btn btn-white shadow-sm w-100 py-3 border rounded-3 text-decoration-none text-dark">
            <i class="fas fa-cog text-secondary mb-2 d-block fs-3"></i>
            System Settings
        </a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>