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
    $id = $_GET['delete'];
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    header("Location: products.php?success=Product deleted");
    exit;
}

// 2. منطق الإضافة والتعديل
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $price = $_POST['price'];
    $description = sanitize($_POST['description']);
    $stock = filter_var($_POST['stock'], FILTER_SANITIZE_NUMBER_INT);
    $id = isset($_POST['id']) && !empty($_POST['id']) ? $_POST['id'] : null;

    $base_dir = dirname(__DIR__);
    $upload_dir = $base_dir . '/uploads/products/';

    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $image = 'product-default.png';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $new_name = 'prod_' . time() . '.' . $ext;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $new_name)) {
            $image = 'products/' . $new_name;
        }
    } elseif ($id) {
        $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $image = $stmt->fetchColumn();
    }

    if ($id) {
        $stmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, description = ?, image = ?, stock = ? WHERE id = ?");
        $stmt->execute([$name, $price, $description, $image, $stock, $id]);
        $success = "Product updated successfully!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO products (name, price, description, image, stock) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $price, $description, $image, $stock]);
        $success = "Product added successfully!";
    }
}

// 3. منطق الـ Pagination (ترقيم الصفحات)
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

$total_stmt = $pdo->query("SELECT count(*) FROM products");
$total_products = $total_stmt->fetchColumn();
$total_pages = ceil($total_products / $limit);

$stmt = $pdo->prepare("SELECT * FROM products ORDER BY id DESC LIMIT ?, ?");
$stmt->bindValue(1, $start, PDO::PARAM_INT);
$stmt->bindValue(2, $limit, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="fw-bold"><i class="fas fa-box me-2 text-primary"></i> Manage Products</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="./dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Product</li>
                </ol>
            </nav>
        </div>
        <hr>
    </div>
</div>
<div class="container-fluid">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 position-sticky" style="top: 90px;">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4 text-primary" id="form-title"><i class="fas fa-plus-circle me-2"></i>Product
                        Details</h4>

                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                            <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="products.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="prod_id">

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Product Name</label>
                            <input type="text" name="name" id="prod_name"
                                class="form-control form-control-lg bg-light border-0" required>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold">Price ($)</label>
                                <input type="number" step="0.01" name="price" id="prod_price"
                                    class="form-control bg-light border-0" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold">Initial Stock</label>
                                <input type="number" name="stock" id="prod_stock" class="form-control bg-light border-0"
                                    required min="0">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Description</label>
                            <textarea name="description" id="prod_desc" class="form-control bg-light border-0"
                                rows="3"></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold">Upload Image</label>
                            <input type="file" name="image" class="form-control bg-light border-0">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary py-3 fw-bold rounded-3 shadow">Save
                                Product</button>
                            <button type="reset" onclick="resetForm()"
                                class="btn btn-link text-decoration-none text-muted">Reset Form</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0">
                    <h5 class="fw-bold mb-0">Inventory <span class="text-muted small fw-normal">(Total:
                            <?php echo $total_products; ?>)</span></h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0 ps-4">Product</th>
                                <th class="border-0">Price</th>
                                <th class="border-0">Stock</th>
                                <th class="border-0 text-end pe-4">Control</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $p): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-3 shadow-sm me-3 border overflow-hidden"
                                                style="width: 50px; height: 50px;">
                                                <img src="../uploads/<?php echo htmlspecialchars($p['image']); ?>"
                                                    class="w-100 h-100 object-fit-cover">
                                            </div>
                                            <div>
                                                <div class="fw-bold mb-0"><?php echo htmlspecialchars($p['name']); ?></div>
                                                <div class="text-muted small truncate" style="max-width: 150px;">
                                                    <?php echo substr($p['description'], 0, 30); ?>...</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span
                                            class="badge bg-soft-success text-success fw-bold p-2">$<?php echo number_format($p['price'], 2); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($p['stock'] > 10): ?>
                                            <span class="text-dark fw-bold"><i
                                                    class="fas fa-cubes text-muted me-1"></i><?php echo $p['stock']; ?></span>
                                        <?php elseif ($p['stock'] > 0): ?>
                                            <span class="text-warning fw-bold"><i
                                                    class="fas fa-exclamation-triangle me-1"></i>Low:
                                                <?php echo $p['stock']; ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Out of Stock</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-light rounded-pill border shadow-sm px-3"
                                            onclick='editProduct(<?php echo json_encode($p); ?>)'>
                                            <i class="fas fa-pen text-primary"></i>
                                        </button>
                                        <a href="products.php?delete=<?php echo $p['id']; ?>"
                                            class="btn btn-sm btn-light rounded-pill border shadow-sm px-3"
                                            onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-trash text-danger"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                    <div class="card-footer bg-white py-4 border-0">
                        <nav>
                            <ul class="pagination pagination-sm justify-content-center mb-0">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link border-0 shadow-sm mx-1 rounded-circle"
                                        href="?page=<?php echo $page - 1; ?>"><i class="fas fa-chevron-left"></i></a>
                                </li>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                        <a class="page-link border-0 shadow-sm mx-1 rounded-circle"
                                            href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link border-0 shadow-sm mx-1 rounded-circle"
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
    .bg-soft-success {
        background-color: #e8f5e9;
    }

    .object-fit-cover {
        object-fit: cover;
    }

    .page-link {
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
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
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    function resetForm() {
        document.getElementById('form-title').innerHTML = '<i class="fas fa-plus-circle me-2"></i>Product Details';
        document.getElementById('prod_id').value = "";
    }
</script>

<?php include '../includes/footer.php'; ?>