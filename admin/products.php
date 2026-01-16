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

// جلب الفئات لعرضها في القائمة المنسدلة (Dropdown)
$catStmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories_list = $catStmt->fetchAll();

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    header("Location: products.php?success=Product deleted");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $price = $_POST['price'];
    $description = sanitize($_POST['description']);
    $stock = filter_var($_POST['stock'], FILTER_SANITIZE_NUMBER_INT);
    $category_id = $_POST['category_id']; // القيمة الجديدة
    $id = isset($_POST['id']) && !empty($_POST['id']) ? $_POST['id'] : null;

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
        // تحديث مع category_id
        $stmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, description = ?, image = ?, stock = ?, category_id = ? WHERE id = ?");
        $stmt->execute([$name, $price, $description, $image, $stock, $category_id, $id]);
        $success = "Product updated successfully!";
    } else {
        // إضافة مع category_id
        $stmt = $pdo->prepare("INSERT INTO products (name, price, description, image, stock, category_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $price, $description, $image, $stock, $category_id]);
        $success = "Product added successfully!";
    }
}

$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

$total_stmt = $pdo->query("SELECT count(*) FROM products");
$total_products = $total_stmt->fetchColumn();
$total_pages = ceil($total_products / $limit);

// جلب المنتجات مع اسم الفئة باستخدام JOIN لعرضها في الجدول
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name 
                       FROM products p 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       ORDER BY p.id DESC LIMIT ?, ?");
$stmt->bindValue(1, $start, PDO::PARAM_INT);
$stmt->bindValue(2, $limit, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 position-sticky" style="top: 90px;">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4 text-primary" id="form-title"><i class="fas fa-plus-circle me-2"></i>Product
                        Details</h4>

                    <form action="products.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="prod_id">

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Product Name</label>
                            <input type="text" name="name" id="prod_name" class="form-control bg-light border-0"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Category</label>
                            <select name="category_id" id="prod_cat" class="form-select bg-light border-0" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories_list as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold">Price ($)</label>
                                <input type="number" step="0.01" name="price" id="prod_price"
                                    class="form-control bg-light border-0" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold">Stock</label>
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
                                class="btn btn-link text-decoration-none text-muted small">Clear Fields</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0 ps-4">Product</th>
                                <th class="border-0">Category</th>
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
                                            <img src="../uploads/<?php echo htmlspecialchars($p['image']); ?>"
                                                class="rounded-2 me-3"
                                                style="width: 40px; height: 40px; object-fit: cover;">
                                            <div class="fw-bold small"><?php echo htmlspecialchars($p['name']); ?></div>
                                        </div>
                                    </td>
                                    <td><span
                                            class="badge bg-light text-dark border"><?php echo htmlspecialchars($p['category_name'] ?? 'Uncategorized'); ?></span>
                                    </td>
                                    <td class="small fw-bold">$<?php echo number_format($p['price'], 2); ?></td>
                                    <td><span class="small"><?php echo $p['stock']; ?></span></td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-primary"
                                            onclick='editProduct(<?php echo json_encode($p); ?>)'>
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        <a href="products.php?delete=<?php echo $p['id']; ?>"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Delete this product?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function editProduct(p) {
        document.getElementById('form-title').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Product';
        document.getElementById('prod_id').value = p.id;
        document.getElementById('prod_name').value = p.name;
        document.getElementById('prod_price').value = p.price;
        document.getElementById('prod_desc').value = p.description;
        document.getElementById('prod_stock').value = p.stock;
        document.getElementById('prod_cat').value = p.category_id; // تعبئة الفئة عند التعديل
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