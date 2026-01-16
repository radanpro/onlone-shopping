<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Radan Market - Modern Shopping</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- خطوط جوجل (Poppins) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <?php
    // Determine the correct path for assets based on current directory
    $base_path = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false || strpos($_SERVER['PHP_SELF'], '/user/') !== false) ? '../' : '';
    ?>

    <!-- تنسيقات CSS للهيدر العام -->
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --text-dark: #1e293b;
            --text-gray: #64748b;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
            color: var(--text-dark);
            padding-top: 80px;
            /* مسافة للهيدر الثابت */
        }

        /* تصميم النافبار العائم */
        .navbar-custom {
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            padding: 0.8rem 0;
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-weight: 800;
            color: var(--text-dark) !important;
            font-size: 1.5rem;
            letter-spacing: -0.5px;
        }

        .navbar-brand i {
            color: var(--primary-color);
        }

        .nav-link {
            font-weight: 500;
            color: var(--text-dark) !important;
            margin: 0 5px;
            position: relative;
            transition: color 0.3s;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--primary-color) !important;
        }

        /* تأثير الخط أسفل الرابط */
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 50%;
            background-color: var(--primary-color);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-link:hover::after {
            width: 50%;
        }

        /* تنسيق القوائم المنسدلة */
        .dropdown-menu {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 0.5rem 0;
        }

        .dropdown-item {
            padding: 0.6rem 1.2rem;
            font-size: 0.9rem;
            color: var(--text-dark);
            transition: all 0.2s;
        }

        .dropdown-item:hover {
            background-color: #f1f5f9;
            color: var(--primary-color);
            padding-left: 1.5rem;
        }

        /* الأزرار */
        .btn-custom-primary {
            background-color: var(--primary-color);
            color: white;
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
        }

        .btn-custom-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .btn-outline-custom {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            border-radius: 50px;
            padding: 0.4rem 1.3rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-outline-custom:hover {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-user {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0.4rem 1rem;
            border-radius: 50px;
            background: #f1f5f9;
            color: var(--text-dark);
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-user:hover {
            background: #e2e8f0;
        }

        /* تجميل زر الهمبرغر */
        .navbar-toggler {
            border: none;
            padding: 0;
        }

        .navbar-toggler:focus {
            box-shadow: none;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="<?php echo $base_path; ?>index.php">
                <i class="fas fa-shopping-bag me-2"></i>Radan Market
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mx-auto">
                    <li class="nav-item"><a class="nav-link" href="<?php echo $base_path; ?>index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#all-products">Shop</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo $base_path; ?>user/my_orders.php">My
                            Orders</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo $base_path; ?>user/wishlist.php">Wishlist
                            <span class="badge bg-danger rounded-pill ms-1" style="font-size: 0.6rem;">New</span></a>
                    </li>
                </ul>

                <ul class="navbar-nav align-items-center gap-2">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle btn-user" href="#" id="userDropdown" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle text-primary"></i>
                                <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="<?php echo $base_path; ?>user/profile.php"><i
                                            class="fas fa-id-card me-2 text-muted"></i> Profile</a></li>
                                <?php if ($_SESSION['user_type'] == 'admin'): ?>
                                    <li><a class="dropdown-item" href="<?php echo $base_path; ?>admin/dashboard.php"><i
                                                class="fas fa-tachometer-alt me-2 text-muted"></i> Admin Panel</a></li>
                                <?php endif; ?>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-danger" href="<?php echo $base_path; ?>logout.php"><i
                                            class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo $base_path; ?>login.php">Login</a></li>
                        <li class="nav-item">
                            <a class="btn btn-custom-primary" href="<?php echo $base_path; ?>register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Container Start for Main Content -->
    <div class="container py-4 main-content">