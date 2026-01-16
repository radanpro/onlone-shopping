<?php
require_once '../config.php';
require_once '../includes/functions.php';
checkAdmin();

$error = '';
$success = '';

$upload_dir = '../uploads/products/';

if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    redirect('products.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $price = $_POST['price'];
    $description = sanitize($_POST['description']);
    // --- إضافة حقل الكمية هنا ---
    $stock = filter_var($_POST['stock'], FILTER_SANITIZE_NUMBER_INT);
    $id = isset($_POST['id']) ? $_POST['id'] : null;

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
        // --- تحديث استعلام UPDATE ليشمل stock ---
        $stmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, description = ?, image = ?, stock = ? WHERE id = ?");
        $stmt->execute([$name, $price, $description, $image, $stock, $id]);
        $success = "Product updated.";
    } else {
        // --- تحديث استعلام INSERT ليشمل stock ---
        $stmt = $pdo->prepare("INSERT INTO products (name, price, description, image, stock) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $price, $description, $image, $stock]);
        $success = "Product added.";
    }
}

$products = $pdo->query("SELECT * FROM products")->fetchAll();
include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-4">
        <h3>Add/Edit Product</h3>
        <?php if ($success): ?> <div class="alert alert-success"><?php echo $success; ?></div> <?php endif; ?>
        <form action="products.php" method="POST" enctype="multipart/form-data" class="card p-3 shadow-sm">
            <input type="hidden" name="id" id="prod_id">
            <div class="mb-3">
                <label>Name</label>
                <input type="text" name="name" id="prod_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Price</label>
                <input type="number" step="0.01" name="price" id="prod_price" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Stock (Quantity)</label>
                <input type="number" name="stock" id="prod_stock" class="form-control" required min="0">
            </div>
            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" id="prod_desc" class="form-control"></textarea>
            </div>
            <div class="mb-3">
                <label>Image</label>
                <input type="file" name="image" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Save Product</button>
            <button type="reset" class="btn btn-secondary mt-2">Clear</button>
        </form>
    </div>
    <div class="col-md-8">
        <h3>Product List</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td><img src="../uploads/<?php echo htmlspecialchars($p['image']); ?>" width="50"
                                class="img-thumbnail"></td>
                        <td><?php echo htmlspecialchars($p['name']); ?></td>
                        <td>$<?php echo htmlspecialchars($p['price']); ?></td>
                        <td><?php echo htmlspecialchars($p['stock']); ?></td>
                        <td>
                            <button class="btn btn-sm btn-info"
                                onclick='editProduct(<?php echo json_encode($p); ?>)'>Edit</button>
                            <a href="products.php?delete=<?php echo $p['id']; ?>" class="btn btn-sm btn-danger"
                                onclick="return confirm('Delete?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function editProduct(p) {
        document.getElementById('prod_id').value = p.id;
        document.getElementById('prod_name').value = p.name;
        document.getElementById('prod_price').value = p.price;
        document.getElementById('prod_desc').value = p.description;
        document.getElementById('prod_stock').value = p.stock;
    }
</script>

<?php include '../includes/footer.php'; ?>