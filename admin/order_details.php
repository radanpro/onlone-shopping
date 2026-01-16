<?php
require_once '../config.php';
require_once '../includes/functions.php';
checkAdmin();

$order_id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;

if (!$order_id) {
    header("Location: orders.php");
    exit;
}

// 1. جلب بيانات الطلب والزبون
$stmt = $pdo->prepare("SELECT o.*, u.name as user_name, u.email as user_email, u.created_at as user_since 
                       FROM orders o 
                       JOIN users u ON o.user_id = u.id 
                       WHERE o.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: orders.php");
    exit;
}

// 2. جلب المنتجات الموجودة داخل هذا الطلب
$itemsStmt = $pdo->prepare("SELECT oi.*, p.name as product_name, p.image 
                            FROM order_items oi 
                            JOIN products p ON oi.product_id = p.id 
                            WHERE oi.order_id = ?");
$itemsStmt->execute([$order_id]);
$items = $itemsStmt->fetchAll();

// 3. جلب إحصائيات الزبون
$statsStmt = $pdo->prepare("SELECT COUNT(*) as total_orders, SUM(total_price) as total_spent 
                            FROM orders WHERE user_id = ?");
$statsStmt->execute([$order['user_id']]);
$userStats = $statsStmt->fetch();

include '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded-4 shadow-sm">
            <h2 class="fw-bold mb-0 h4 text-primary"><i class="fas fa-file-invoice me-2"></i>Order Details
                #<?php echo $order['id']; ?></h2>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-light btn-sm rounded-pill px-3 border">
                    <i class="fas fa-print me-1"></i> Print
                </button>
                <a href="orders.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-user-circle me-2 text-primary"></i>Customer
                    Information</h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-soft-primary rounded-circle p-3 me-3 text-primary fw-bold"
                        style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                        <?php echo strtoupper(substr($order['user_name'], 0, 1)); ?>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($order['user_name']); ?></h6>
                        <small class="text-muted"><?php echo htmlspecialchars($order['user_email']); ?></small>
                    </div>
                </div>
                <hr class="text-muted opacity-25">
                <div class="mb-2 d-flex justify-content-between">
                    <small class="text-muted">Customer Since:</small>
                    <small class="fw-bold"><?php echo date('M Y', strtotime($order['user_since'])); ?></small>
                </div>
                <div class="mb-2 d-flex justify-content-between">
                    <small class="text-muted">Total Orders:</small>
                    <span
                        class="badge bg-soft-info text-info rounded-pill px-2"><?php echo $userStats['total_orders']; ?>
                        Orders</span>
                </div>
                <div class="mb-0 d-flex justify-content-between">
                    <small class="text-muted">Lifetime Spent:</small>
                    <small
                        class="fw-bold text-success">$<?php echo number_format($userStats['total_spent'], 2); ?></small>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-tasks me-2 text-primary"></i>Order Management</h6>
            </div>
            <div class="card-body">
                <form action="orders.php" method="POST">
                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                    <div class="mb-3">
                        <label class="small text-muted mb-2 fw-bold">Current Status</label>
                        <select name="status" class="form-select bg-light border-0 small shadow-none">
                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>
                                Pending</option>
                            <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>
                                Completed</option>
                            <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>
                                Cancelled</option>
                        </select>
                    </div>
                    <button type="submit" name="update_status" class="btn btn-primary w-100 rounded-3 fw-bold">
                        Update Order Status
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-box-open me-2 text-primary"></i>Items in this Order
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light small text-muted">
                            <tr>
                                <th class="ps-4 py-3 border-0">PRODUCT</th>
                                <th class="border-0">UNIT PRICE</th>
                                <th class="border-0 text-center">QTY</th>
                                <th class="text-end pe-4 border-0">TOTAL PER ITEM</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $calculated_total = 0;
                            foreach ($items as $item):
                                $item_total = $item['price'] * $item['quantity'];
                                $calculated_total += $item_total;
                            ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <img src="../uploads/<?php echo $item['image']; ?>"
                                                class="rounded-3 me-3 border shadow-sm"
                                                style="width: 45px; height: 45px; object-fit: cover;">
                                            <div>
                                                <div class="small fw-bold text-dark">
                                                    <?php echo htmlspecialchars($item['product_name']); ?></div>
                                                <div class="text-muted" style="font-size: 10px;">ID:
                                                    #<?php echo $item['product_id']; ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="small fw-semibold">$<?php echo number_format($item['price'], 2); ?></td>
                                    <td class="small text-center"><span
                                            class="badge bg-light text-dark border px-3"><?php echo $item['quantity']; ?></span>
                                    </td>
                                    <td class="text-end pe-4 fw-bold text-dark small">
                                        $<?php echo number_format($item_total, 2); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="border-top">
                            <tr class="bg-light">
                                <td colspan="3" class="text-end py-3 fw-bold text-muted small">Subtotal:</td>
                                <td class="text-end pe-4 py-3 fw-bold text-dark small">
                                    $<?php echo number_format($calculated_total, 2); ?></td>
                            </tr>
                            <tr class="bg-white">
                                <td colspan="3" class="text-end py-3 fw-bold align-middle">ORDER GRAND TOTAL:</td>
                                <td class="text-end pe-4 py-3 fw-bold text-primary h4 mb-0 align-middle">
                                    $<?php echo number_format($order['total_price'], 2); ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-3 ps-2">
            <p class="text-muted small">
                <i class="far fa-clock me-1"></i> Order placed on:
                <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?>
            </p>
        </div>
    </div>
</div>

<style>
    .bg-soft-primary {
        background-color: #e7f1ff;
    }

    .bg-soft-info {
        background-color: #e1f5fe;
    }

    .text-info {
        color: #0288d1 !important;
    }

    @media print {

        .btn,
        .card-header select,
        .btn-primary,
        .breadcrumb,
        nav {
            display: none !important;
        }

        .card {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
        }

        body {
            background: white !important;
        }
    }
</style>

<?php include '../includes/footer.php'; ?>