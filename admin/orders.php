<?php
require_once '../config.php';
require_once '../includes/functions.php';
checkAdmin();

$error = '';
$success = '';

// 1. منطق تحديث الحالة مع Validation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = filter_var($_POST['order_id'], FILTER_VALIDATE_INT);
    $status = sanitize($_POST['status']);

    $allowed_statuses = ['pending', 'completed', 'cancelled'];

    if ($order_id && in_array($status, $allowed_statuses)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);
        $success = "Order #$order_id status updated to $status.";
    } else {
        $error = "Invalid order or status.";
    }
}

// 2. منطق الترقيم (Pagination)
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$start = ($page - 1) * $limit;

$total_stmt = $pdo->query("SELECT count(*) FROM orders");
$total_orders = $total_stmt->fetchColumn();
$total_pages = ceil($total_orders / $limit);

// 3. جلب الطلبات مع بيانات المستخدم
$stmt = $pdo->prepare("SELECT o.*, u.name as user_name, u.email as user_email 
                       FROM orders o 
                       JOIN users u ON o.user_id = u.id 
                       ORDER BY o.created_at DESC 
                       LIMIT ?, ?");
$stmt->bindValue(1, $start, PDO::PARAM_INT);
$stmt->bindValue(2, $limit, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded-4 shadow-sm">
            <h2 class="fw-bold mb-0 h4"><i class="fas fa-shopping-cart me-2 text-primary"></i>Manage Orders</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="dashboard.php" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Orders</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="container-fluid px-0">
    <?php if ($success): ?>
        <div class="alert alert-success border-0 shadow-sm rounded-3 mb-4 small"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4 small"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="fw-bold mb-0 fs-6 text-dark">Recent Orders <span
                            class="text-muted fw-normal small">(Total: <?php echo $total_orders; ?>)</span></h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted small">
                                <tr>
                                    <th class="ps-4 py-3 border-0">ORDER ID</th>
                                    <th class="border-0">CUSTOMER</th>
                                    <th class="border-0">TOTAL</th>
                                    <th class="border-0">STATUS</th>
                                    <th class="border-0">DATE</th>
                                    <th class="text-end pe-4 border-0">UPDATE STATUS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($orders)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">No orders found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <a href="order_details.php?id=<?php echo $order['id']; ?>"
                                                    class="text-decoration-none">
                                                    <span class="fw-bold text-primary small">#<?php echo $order['id']; ?></span>
                                                    <i class="fas fa-external-link-alt ms-1 small" style="font-size: 10px;"></i>
                                                </a>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-soft-primary rounded-circle p-2 me-2">
                                                        <i class="fas fa-user text-primary small"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark small">
                                                            <?php echo htmlspecialchars($order['user_name']); ?></div>
                                                        <div class="text-muted small" style="font-size: 11px;">
                                                            <?php echo htmlspecialchars($order['user_email']); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span
                                                    class="fw-bold text-dark small">$<?php echo number_format($order['total_price'], 2); ?></span>
                                            </td>
                                            <td>
                                                <?php
                                                $status_cfg = [
                                                    'pending' => ['class' => 'warning', 'icon' => 'clock'],
                                                    'completed' => ['class' => 'success', 'icon' => 'check-circle'],
                                                    'cancelled' => ['class' => 'danger', 'icon' => 'times-circle']
                                                ];
                                                $curr = $status_cfg[$order['status']] ?? $status_cfg['pending'];
                                                ?>
                                                <span
                                                    class="badge bg-soft-<?php echo $curr['class']; ?> text-<?php echo $curr['class']; ?> rounded-pill px-3 py-2 small">
                                                    <i class="fas fa-<?php echo $curr['icon']; ?> me-1"></i>
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="text-dark small" style="font-size: 12px;">
                                                    <i class="far fa-calendar-alt me-1 text-muted"></i>
                                                    <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                                                </div>
                                                <div class="text-muted small" style="font-size: 11px;">
                                                    <?php echo date('H:i A', strtotime($order['created_at'])); ?>
                                                </div>
                                            </td>
                                            <td class="text-end pe-4">
                                                <form action="orders.php" method="POST"
                                                    class="d-flex justify-content-end align-items-center gap-2">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <select name="status"
                                                        class="form-select form-select-sm bg-light border-0 small"
                                                        style="width: 120px;">
                                                        <option value="pending"
                                                            <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>
                                                            Pending</option>
                                                        <option value="completed"
                                                            <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>
                                                            Completed</option>
                                                        <option value="cancelled"
                                                            <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>
                                                            Cancelled</option>
                                                    </select>
                                                    <button type="submit" name="update_status"
                                                        class="btn btn-primary btn-sm rounded-3">
                                                        <i class="fas fa-save"></i>
                                                    </button>
                                                </form>
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
    .bg-soft-primary {
        background-color: #e7f1ff;
    }

    .bg-soft-warning {
        background-color: #fff3cd;
    }

    .bg-soft-success {
        background-color: #d1e7dd;
    }

    .bg-soft-danger {
        background-color: #f8d7da;
    }

    .text-warning {
        color: #856404 !important;
    }

    .text-success {
        color: #0f5132 !important;
    }

    .text-danger {
        color: #842029 !important;
    }

    .form-select:focus {
        box-shadow: none;
        border-color: #0d6efd;
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

<?php include '../includes/footer.php'; ?>