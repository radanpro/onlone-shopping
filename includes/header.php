<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopOnline - Modern Shopping Experience</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php
    // Determine the correct path for assets based on current directory
    $base_path = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false || strpos($_SERVER['PHP_SELF'], '/user/') !== false) ? '../' : '';
    ?>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/style.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo $base_path; ?>index.php">
                <i class="fas fa-shopping-bag me-2"></i>ShopOnline
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="<?php echo $base_path; ?>index.php">Home</a></li>
                </ul>
                <ul class="navbar-nav align-items-center">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="<?php echo $base_path; ?>user/profile.php"><i class="fas fa-id-card me-2"></i>Profile</a></li>
                                <?php if ($_SESSION['user_type'] == 'admin'): ?>
                                    <li><a class="dropdown-item" href="<?php echo $base_path; ?>admin/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Admin Panel</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?php echo $base_path; ?>logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo $base_path; ?>login.php">Login</a></li>
                        <li class="nav-item ms-lg-2">
                            <a class="btn btn-primary btn-sm px-4" href="<?php echo $base_path; ?>register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container py-5">
