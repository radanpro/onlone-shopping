<?php
require_once '../config.php';
require_once '../includes/functions.php';
checkAdmin();

// Handle Status Update
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $order_id]);
    redirect('orders.php');
}

$stmt = $pdo->query("SELECT o.*, u.name as user_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
$orders = $stmt->fetchAll();

include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="fw-bold"><i class="fas fa-shopping-cart me-2 text-primary"></i>Manage Orders</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Orders</li>
                </ol>
            </nav>
        </div>
        <hr>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-primary text-white py-3 border-0">
                <h5 class="mb-0 fw-bold"><i class="fas fa-list-ul me-2"></i>Orders List (<?php echo count($orders); ?>)
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($orders)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                        <p class="text-muted">No orders found in the system.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light border-bottom">
                                <tr>
                                    <th class="ps-4 fw-semibold">Order ID</th>
                                    <th class="fw-semibold">Customer</th>
                                    <th class="fw-semibold">Total Price</th>
                                    <th class="fw-semibold">Status</th>
                                    <th class="fw-semibold">Date</th>
                                    <th class="text-center fw-semibold pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <span class="fw-bold text-primary">#<?php echo $order['id']; ?></span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light rounded-circle p-2 me-2">
                                                    <i class="fas fa-user text-secondary"></i>
                                                </div>
                                                <span
                                                    class="fw-semibold"><?php echo htmlspecialchars($order['user_name']); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span
                                                class="fw-bold text-dark">$<?php echo number_format($order['total_price'], 2); ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $status_class = 'warning';
                                            $status_icon = 'clock';
                                            if ($order['status'] == 'completed') {
                                                $status_class = 'success';
                                                $status_icon = 'check-circle';
                                            }
                                            if ($order['status'] == 'cancelled') {
                                                $status_class = 'danger';
                                                $status_icon = 'times-circle';
                                            }
                                            ?>
                                            <span class="badge rounded-pill bg-<?php echo $status_class; ?> px-3 py-2">
                                                <i class="fas fa-<?php echo $status_icon; ?> me-1"></i>
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <i class="far fa-calendar-alt me-1"></i>
                                                <?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?>
                                            </small>
                                        </td>
                                        <td class="text-center pe-4">
                                            <form action="orders.php" method="POST"
                                                class="d-flex justify-content-center align-items-center gap-2">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                <select name="status"
                                                    class="form-select form-select-sm rounded-pill border-secondary-subtle"
                                                    style="width: 130px;">
                                                    <option value="pending"
                                                        <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending
                                                    </option>
                                                    <option value="completed"
                                                        <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>
                                                        Completed</option>
                                                    <option value="cancelled"
                                                        <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>
                                                        Cancelled</option>
                                                </select>
                                                <button type="submit" name="update_status"
                                                    class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                                                    <i class="fas fa-sync-alt me-1"></i> Update
                                                </button>
                                            </form>
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

<?php include '../includes/footer.php'; ?>