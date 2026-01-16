<?php
require_once '../config.php';
require_once '../includes/functions.php';
checkAdmin();

// Handle activation/deactivation
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    if ($_GET['action'] == 'activate') {
        $pdo->prepare("UPDATE users SET status = 1 WHERE id = ?")->execute([$id]);
    } elseif ($_GET['action'] == 'deactivate') {
        $pdo->prepare("UPDATE users SET status = 0 WHERE id = ?")->execute([$id]);
    } elseif ($_GET['action'] == 'delete') {
        $pdo->prepare("DELETE FROM users WHERE id = ? AND user_type != 'admin'")->execute([$id]);
    }
    redirect('users.php');
}

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <h3>Manage Users</h3>
        <table class="table table-bordered table-striped mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><img src="../uploads/<?php echo $user['image']; ?>" width="40" height="40" class="rounded-circle"></td>
                    <td><?php echo $user['name']; ?></td>
                    <td><?php echo $user['email']; ?></td>
                    <td><?php echo $user['user_type']; ?></td>
                    <td>
                        <?php if ($user['status'] == 1): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($user['user_type'] != 'admin'): ?>
                            <?php if ($user['status'] == 0): ?>
                                <a href="users.php?action=activate&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-success">Activate</a>
                            <?php else: ?>
                                <a href="users.php?action=deactivate&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning">Deactivate</a>
                            <?php endif; ?>
                            <a href="users.php?action=delete&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
