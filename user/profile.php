<?php
require_once '../config.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $gender = $_POST['gender'];
    $birth_date = $_POST['birth_date'];
    
    if (empty($name)) {
        $error = "Name is required.";
    } else {
        $image = $user['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['image']['name'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if (in_array(strtolower($ext), $allowed)) {
                $new_name = time() . '_' . $filename;
                move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/' . $new_name);
                $image = $new_name;
            }
        }
        
        $stmt = $pdo->prepare("UPDATE users SET name = ?, gender = ?, birth_date = ?, image = ? WHERE id = ?");
        if ($stmt->execute([$name, $gender, $birth_date, $image, $user_id])) {
            $success = "Profile updated successfully.";
            $_SESSION['user_name'] = $name;
            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        } else {
            $error = "Failed to update profile.";
        }
    }
}

include '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-primary py-4 text-center border-0">
                <h3 class="text-white fw-bold mb-0">My Profile</h3>
            </div>
            <div class="card-body p-4 p-md-5">
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
                
                <div class="row align-items-center mb-5">
                    <div class="col-md-4 text-center mb-4 mb-md-0">
                        <div class="position-relative d-inline-block">
                            <img src="../uploads/<?php echo $user['image'] ?: 'default.png'; ?>" 
                                 class="rounded-circle shadow-sm border border-4 border-white" 
                                 style="width: 180px; height: 180px; object-fit: cover;">
                            <span class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-2 border border-3 border-white">
                                <i class="fas fa-camera"></i>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <h2 class="fw-bold mb-1"><?php echo htmlspecialchars($user['name']); ?></h2>
                        <p class="text-muted mb-3"><i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($user['email']); ?></p>
                        <div class="d-flex gap-2">
                            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                                <i class="fas fa-user-tag me-1 text-primary"></i> <?php echo ucfirst($user['user_type']); ?>
                            </span>
                            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                                <i class="fas fa-calendar-alt me-1 text-primary"></i> Joined <?php echo date('M Y', strtotime($user['created_at'] ?? 'now')); ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <hr class="my-5 opacity-25">
                
                <h5 class="fw-bold mb-4"><i class="fas fa-edit me-2 text-primary"></i>Edit Personal Information</h5>
                
                <form action="profile.php" method="POST" enctype="multipart/form-data">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-muted"></i></span>
                                <input type="text" name="name" class="form-control border-start-0 ps-0" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                                <input type="email" class="form-control border-start-0 ps-0 bg-white" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                            </div>
                            <small class="text-muted">Email cannot be changed for security reasons.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Gender</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-venus-mars text-muted"></i></span>
                                <select name="gender" class="form-select border-start-0 ps-0">
                                    <option value="male" <?php echo $user['gender'] == 'male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="female" <?php echo $user['gender'] == 'female' ? 'selected' : ''; ?>>Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Birth Date</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-calendar text-muted"></i></span>
                                <input type="date" name="birth_date" class="form-control border-start-0 ps-0" value="<?php echo $user['birth_date']; ?>">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Update Profile Image</label>
                            <input type="file" name="image" class="form-control">
                            <small class="text-muted">Recommended: Square image, max 2MB (JPG, PNG).</small>
                        </div>
                        <div class="col-md-12 mt-5">
                            <button type="submit" class="btn btn-primary px-5 py-2 rounded-pill shadow-sm">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                            <a href="../index.php" class="btn btn-link text-muted text-decoration-none ms-3">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
