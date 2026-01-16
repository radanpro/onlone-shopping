<?php
require_once 'config.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] == 0 && $user['user_type'] != 'admin') {
            $error = "Your account is not activated yet.";
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_type'] = $user['user_type'];

            setcookie('user_email', $email, time() + (86400 * 30), "/");

            if ($user['user_type'] == 'admin') {
                redirect('admin/dashboard.php');
            } else {
                redirect('index.php');
            }
        }
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ShopOnline</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
        }
        .auth-card {
            width: 100%;
            max-width: 400px;
            background: white;
            padding: 2rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card auth-card shadow-lg">
                    <div class="text-center mb-4">
                        <a href="index.php" class="text-decoration-none">
                            <h2 class="fw-bold text-primary"><i class="fas fa-shopping-bag me-2"></i>ShopOnline</h2>
                        </a>
                        <p class="text-muted">Welcome back! Please login to your account.</p>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="login.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                                <input type="email" name="email" class="form-control border-start-0 ps-0"
                                    placeholder="name@example.com"
                                    value="<?php echo isset($_COOKIE['user_email']) ? htmlspecialchars($_COOKIE['user_email']) : ''; ?>"
                                    required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between">
                                <label class="form-label fw-semibold">Password</label>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-lock text-muted"></i></span>
                                <input type="password" name="password" class="form-control border-start-0 ps-0" 
                                    placeholder="Enter your password" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </form>
                    
                    <div class="text-center">
                        <p class="mb-0 text-muted">Don't have an account? <a href="register.php" class="text-primary fw-bold text-decoration-none">Register here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
