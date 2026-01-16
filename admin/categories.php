<?php
require_once '../config.php';
require_once '../includes/functions.php';
checkAdmin();

$error = '';
$success = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
    redirect('categories.php');
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $id = isset($_POST['id']) ? $_POST['id'] : null;

    if ($id) {
        $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $description, $id]);
        $success = "Category updated successfully.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->execute([$name, $description]);
        $success = "Category added successfully.";
    }
}

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="fw-bold"><i class="fas fa-list me-2 text-primary"></i>Manage Categories</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Categories</li>
                </ol>
            </nav>
        </div>
        <hr>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Add/Edit Form -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-primary text-white py-3 rounded-top-4 border-0">
                <h5 class="mb-0 fw-bold"><i class="fas fa-plus-circle me-2"></i>Add/Edit Category</h5>
            </div>
            <div class="card-body p-4">
                <form action="categories.php" method="POST">
                    <input type="hidden" name="id" id="cat_id">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Name</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i
                                    class="fas fa-tag text-muted"></i></span>
                            <input type="text" name="name" id="cat_name" class="form-control border-start-0 ps-0"
                                placeholder="Enter category name" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" id="cat_desc" class="form-control"
                            placeholder="Enter category description" rows="4"></textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary py-2 rounded-pill fw-semibold">
                            <i class="fas fa-save me-2"></i>Save Category
                        </button>
                        <button type="reset" class="btn btn-outline-secondary py-2 rounded-pill fw-semibold"
                            onclick="resetForm()">
                            <i class="fas fa-redo me-2"></i>Clear
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Categories List -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-primary text-white py-3 rounded-top-4 border-0">
                <h5 class="mb-0 fw-bold"><i class="fas fa-th-list me-2"></i>Categories List
                    (<?php echo count($categories); ?>)</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($categories)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                        <p class="text-muted">No categories found. Create your first category!</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light border-bottom">
                                <tr>
                                    <th class="ps-4 fw-semibold">ID</th>
                                    <th class="fw-semibold">Name</th>
                                    <th class="fw-semibold">Description</th>
                                    <th class="text-center fw-semibold pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $c): ?>
                                    <tr class="align-middle">
                                        <td class="ps-4">
                                            <span class="badge bg-light text-dark"><?php echo $c['id']; ?></span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($c['name']); ?></strong>
                                        </td>
                                        <td>
                                            <span
                                                class="text-muted small"><?php echo htmlspecialchars(substr($c['description'], 0, 50)); ?><?php echo strlen($c['description']) > 50 ? '...' : ''; ?></span>
                                        </td>
                                        <td class="text-center pe-4">
                                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                                onclick="editCategory(<?php echo htmlspecialchars(json_encode($c)); ?>)"
                                                title="Edit">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </button>
                                            <a href="categories.php?delete=<?php echo $c['id']; ?>"
                                                class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                                onclick="return confirm('Are you sure you want to delete this category?')"
                                                title="Delete">
                                                <i class="fas fa-trash me-1"></i>Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    function editCategory(c) {
        document.getElementById('cat_id').value = c.id;
        document.getElementById('cat_name').value = c.name;
        document.getElementById('cat_desc').value = c.description;

        // Scroll to form
        document.querySelector('.col-md-4').scrollIntoView({
            behavior: 'smooth'
        });
    }

    function resetForm() {
        document.getElementById('cat_id').value = '';
        document.getElementById('cat_name').value = '';
        document.getElementById('cat_desc').value = '';
    }
</script>

<?php include '../includes/footer.php'; ?>