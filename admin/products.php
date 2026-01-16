<?php
require_once '../config.php';
require_once '../includes/functions.php';
checkAdmin();

$error = '';
$success = '';

$base_dir = dirname(__DIR__);
$upload_dir = $base_dir . '/uploads/products/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if (isset($_GET['delete'])) {
    $id = filter_var($_GET['delete'], FILTER_VALIDATE_INT);
    if ($id) {
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        header("Location: products.php?success=Product deleted successfully");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) && !empty($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT) : null;
    $name = sanitize($_POST['name']);
    $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
    $stock = filter_var($_POST['stock'], FILTER_VALIDATE_INT);
    $category_id = filter_var($_POST['category_id'], FILTER_VALIDATE_INT);
    $description = sanitize($_POST['description']);

    if (!$name || $price === false || $stock === false || !$category_id) {
        $error = "Please enter valid data in all required fields.";
    } else {
        $image = null;
        $image_required = !$id; // الصورة إجبارية فقط في حالة المنتج الجديد

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

            if (in_array($ext, $allowed_exts)) {
                $new_name = 'prod_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_name)) {
                    $image = 'products/' . $new_name;
                }
            } else {
                $error = "Invalid image format. Only JPG, PNG, and WebP are allowed.";
            }
        } elseif ($image_required) {
            $error = "Product image is required for new products.";
        }

        if (empty($error)) {
            if ($id) {
                if ($image) {
                    $stmt = $pdo->prepare("UPDATE products SET name=?, price=?, description=?, image=?, stock=?, category_id=? WHERE id=?");
                    $stmt->execute([$name, $price, $description, $image, $stock, $category_id, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE products SET name=?, price=?, description=?, stock=?, category_id=? WHERE id=?");
                    $stmt->execute([$name, $price, $description, $stock, $category_id, $id]);
                }
                $success = "Product updated successfully!";
            } else {
                $final_image = $image ?? 'product-default.png';
                $stmt = $pdo->prepare("INSERT INTO products (name, price, description, image, stock, category_id) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $price, $description, $final_image, $stock, $category_id]);
                $success = "Product added successfully!";
            }
        }
    }
}

$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$start = ($page - 1) * $limit;

$total_stmt = $pdo->query("SELECT count(*) FROM products");
$total_products = $total_stmt->fetchColumn();
$total_pages = ceil($total_products / $limit);

$stmt = $pdo->prepare("SELECT p.*, c.name as category_name 
                       FROM products p 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       ORDER BY p.id DESC LIMIT ?, ?");
$stmt->bindValue(1, $start, PDO::PARAM_INT);
$stmt->bindValue(2, $limit, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll();

$catStmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories_list = $catStmt->fetchAll();

include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded-4 shadow-sm">
            <h2 class="fw-bold mb-0 h4"><i class="fas fa-boxes me-2 text-primary"></i>Manage Products</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Products</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="container-fluid px-0">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden position-sticky" style="top: 20px;">
                <div class="card-header bg-primary text-white p-3 border-0">
                    <h5 class="mb-0 fs-6" id="form-title"><i class="fas fa-plus-circle me-2"></i>Product Details</h5>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger border-0 small shadow-sm"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if ($success || isset($_GET['success'])): ?>
                        <div class="alert alert-success border-0 small shadow-sm">
                            <?php echo $success ?: $_GET['success']; ?></div>
                    <?php endif; ?>

                    <form action="products.php" method="POST" enctype="multipart/form-data" id="productForm">
                        <input type="hidden" name="id" id="prod_id">

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Product Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i
                                        class="fas fa-tag text-muted"></i></span>
                                <input type="text" name="name" id="prod_name" class="form-control bg-light border-0"
                                    placeholder="Enter product name" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Category</label>
                            <select name="category_id" id="prod_cat" class="form-select bg-light border-0" required>
                                <option value="">Choose category...</option>
                                <?php foreach ($categories_list as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold text-muted">Price ($)</label>
                                <input type="number" step="0.01" name="price" id="prod_price"
                                    class="form-control bg-light border-0" placeholder="0.00" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold text-muted">Stock</label>
                                <input type="number" name="stock" id="prod_stock" class="form-control bg-light border-0"
                                    placeholder="0" required min="0">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Description</label>
                            <textarea name="description" id="prod_desc" class="form-control bg-light border-0" rows="3"
                                placeholder="Describe the product..."></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Product Image</label>
                            <input type="file" name="image" id="prod_image" class="form-control bg-light border-0">
                            <div id="image-hint" class="form-text small text-primary">Required for new products.</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary py-2 fw-bold rounded-3 shadow-sm border-0">
                                <i class="fas fa-save me-2"></i>Save Product
                            </button>
                            <button type="reset" onclick="resetForm()"
                                class="btn btn-outline-secondary py-2 btn-sm border-0">
                                <i class="fas fa-redo me-2"></i>Reset Form
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted small">
                                <tr>
                                    <th class="ps-4 py-3 border-0">PRODUCT</th>
                                    <th class="border-0">CATEGORY</th>
                                    <th class="border-0">PRICE</th>
                                    <th class="border-0">STOCK</th>
                                    <th class="text-end pe-4 border-0">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $p): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <img src="../uploads/<?php echo htmlspecialchars($p['image']); ?>"
                                                    class="rounded-3 shadow-sm border"
                                                    style="width: 45px; height: 45px; object-fit: cover;">
                                                <div class="ms-3">
                                                    <div class="fw-bold text-dark small">
                                                        <?php echo htmlspecialchars($p['name']); ?></div>
                                                    <div class="text-muted" style="font-size: 11px;">ID:
                                                        #<?php echo $p['id']; ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span
                                                class="badge bg-soft-primary text-primary px-3 py-2 rounded-pill"><?php echo htmlspecialchars($p['category_name'] ?? 'Uncategorized'); ?></span>
                                        </td>
                                        <td><span
                                                class="fw-bold text-dark small">$<?php echo number_format($p['price'], 2); ?></span>
                                        </td>
                                        <td>
                                            <?php if ($p['stock'] <= 5): ?>
                                                <span class="text-danger fw-bold small"><i
                                                        class="fas fa-exclamation-circle me-1"></i><?php echo $p['stock']; ?></span>
                                            <?php else: ?>
                                                <span class="text-muted small"><?php echo $p['stock']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group shadow-sm rounded-3 overflow-hidden">
                                                <button class="btn btn-white btn-sm px-3 border-end"
                                                    onclick='editProduct(<?php echo json_encode($p); ?>)'>
                                                    <i class="fas fa-edit text-primary"></i>
                                                </button>
                                                <a href="products.php?delete=<?php echo $p['id']; ?>"
                                                    class="btn btn-white btn-sm px-3"
                                                    onclick="return confirm('Delete this product permanently?')">
                                                    <i class="fas fa-trash text-danger"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if ($total_pages > 1): ?>
                    <div class="card-footer bg-white border-0 py-3">
                        <nav>
                            <ul class="pagination pagination-sm justify-content-center mb-0">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link border-0 shadow-sm mx-1 rounded-3"
                                        href="?page=<?php echo $page - 1; ?>"><i class="fas fa-chevron-left"></i></a>
                                </li>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                        <a class="page-link border-0 shadow-sm mx-1 rounded-3"
                                            href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link border-0 shadow-sm mx-1 rounded-3"
                                        href="?page=<?php echo $page + 1; ?>"><i class="fas fa-chevron-right"></i></a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-soft-primary {
        background-color: #e7f1ff;
    }

    .btn-white {
        background: white;
        border: 1px solid #eee;
    }

    .btn-white:hover {
        background: #f8f9fa;
    }

    .form-control:focus,
    .form-select:focus {
        box-shadow: none;
        border-color: #0d6efd;
        background-color: #fff !important;
    }

    .page-link {
        color: #555;
        padding: 8px 14px;
    }

    .page-item.active .page-link {
        background-color: #0d6efd;
        color: white;
    }
</style>



<script>
    function editProduct(p) {
        document.getElementById('form-title').innerHTML = '<i class="fas fa-edit me-2"></i>Editing: ' + p.name;
        document.getElementById('prod_id').value = p.id;
        document.getElementById('prod_name').value = p.name;
        document.getElementById('prod_price').value = p.price;
        document.getElementById('prod_desc').value = p.description;
        document.getElementById('prod_stock').value = p.stock;
        document.getElementById('prod_cat').value = p.category_id;

        // عند التعديل، الصورة ليست إجبارية
        document.getElementById('prod_image').required = false;
        document.getElementById('image-hint').innerHTML = "Leave empty to keep current image.";
        document.getElementById('image-hint').className = "form-text small text-muted";

        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    function resetForm() {
        document.getElementById('form-title').innerHTML = '<i class="fas fa-plus-circle me-2"></i>Product Details';
        document.getElementById('prod_id').value = "";
        document.getElementById('prod_image').required = true;
        document.getElementById('image-hint').innerHTML = "Required for new products.";
        document.getElementById('image-hint').className = "form-text small text-primary";
    }

    // Client-side validation for new product
    document.getElementById('productForm').onsubmit = function() {
        if (!document.getElementById('prod_id').value && !document.getElementById('prod_image').value) {
            alert("Please upload a product image.");
            return false;
        }
    };
</script>

<?php include '../includes/footer.php'; ?>