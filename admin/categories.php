<?php
require_once '../config.php';
require_once '../includes/functions.php';
checkAdmin();

$error = '';
$success = '';

if (isset($_GET['delete'])) {
    $id = filter_var($_GET['delete'], FILTER_VALIDATE_INT);
    if ($id) {
        $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
        header("Location: categories.php?success=Category deleted successfully");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) && !empty($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT) : null;
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);

    if (empty($name)) {
        $error = "Category name is required.";
    } else {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $description, $id]);
            $success = "Category updated successfully!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $description]);
            $success = "Category added successfully!";
        }
    }
}

$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$start = ($page - 1) * $limit;

$total_stmt = $pdo->query("SELECT count(*) FROM categories");
$total_categories = $total_stmt->fetchColumn();
$total_pages = ceil($total_categories / $limit);

$stmt = $pdo->prepare("SELECT * FROM categories ORDER BY id DESC LIMIT ?, ?");
$stmt->bindValue(1, $start, PDO::PARAM_INT);
$stmt->bindValue(2, $limit, PDO::PARAM_INT);
$stmt->execute();
$categories = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded-4 shadow-sm">
            <h2 class="fw-bold mb-0 h4"><i class="fas fa-list me-2 text-primary"></i>Manage Categories</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Categories</li>
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
                    <h5 class="mb-0 fs-6" id="form-title"><i class="fas fa-plus-circle me-2"></i>Category Details</h5>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger border-0 small shadow-sm"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if ($success || isset($_GET['success'])): ?>
                        <div class="alert alert-success border-0 small shadow-sm">
                            <?php echo $success ?: $_GET['success']; ?></div>
                    <?php endif; ?>

                    <form action="categories.php" method="POST" id="categoryForm">
                        <input type="hidden" name="id" id="cat_id">

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Category Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i
                                        class="fas fa-tag text-muted"></i></span>
                                <input type="text" name="name" id="cat_name" class="form-control bg-light border-0"
                                    placeholder="e.g. Electronics" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Description</label>
                            <textarea name="description" id="cat_desc" class="form-control bg-light border-0" rows="5"
                                placeholder="Describe this category..."></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary py-2 fw-bold rounded-3 shadow-sm border-0">
                                <i class="fas fa-save me-2"></i>Save Category
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
                                    <th class="ps-4 py-3 border-0">ID</th>
                                    <th class="border-0">NAME</th>
                                    <th class="border-0">DESCRIPTION</th>
                                    <th class="text-end pe-4 border-0">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($categories)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">No categories found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($categories as $c): ?>
                                        <tr>
                                            <td class="ps-4"><span class="text-muted small">#<?php echo $c['id']; ?></span></td>
                                            <td>
                                                <div class="fw-bold text-dark small"><?php echo htmlspecialchars($c['name']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-muted small text-truncate" style="max-width: 250px;">
                                                    <?php echo htmlspecialchars($c['description']) ?: '<em>No description</em>'; ?>
                                                </div>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="btn-group shadow-sm rounded-3 overflow-hidden">
                                                    <button class="btn btn-white btn-sm px-3 border-end"
                                                        onclick='editCategory(<?php echo json_encode($c); ?>)'>
                                                        <i class="fas fa-edit text-primary"></i>
                                                    </button>
                                                    <a href="categories.php?delete=<?php echo $c['id']; ?>"
                                                        class="btn btn-white btn-sm px-3"
                                                        onclick="return confirm('Delete this category? This might affect products linked to it.')">
                                                        <i class="fas fa-trash text-danger"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
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
    .btn-white {
        background: white;
        border: 1px solid #eee;
    }

    .btn-white:hover {
        background: #f8f9fa;
    }

    .form-control:focus {
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
    function editCategory(c) {
        document.getElementById('form-title').innerHTML = '<i class="fas fa-edit me-2"></i>Editing: ' + c.name;
        document.getElementById('cat_id').value = c.id;
        document.getElementById('cat_name').value = c.name;
        document.getElementById('cat_desc').value = c.description;
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    function resetForm() {
        document.getElementById('form-title').innerHTML = '<i class="fas fa-plus-circle me-2"></i>Category Details';
        document.getElementById('cat_id').value = "";
    }
</script>

<?php include '../includes/footer.php'; ?>