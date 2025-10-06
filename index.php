<?php
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_type'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: user/dashboard.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .login-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
        }
    </style>
</head>
<body class="d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card login-card border-0 shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-bank2 text-primary" style="font-size: 3rem;"></i>
                            <h2 class="fw-bold mt-3"><?php echo APP_NAME; ?></h2>
                            <p class="text-muted">Welcome back! Please login to your account</p>
                        </div>

                        <?php
                        if (isset($_SESSION['error'])) {
                            echo '<div class="alert alert-danger alert-dismissible fade show">
                                    <i class="bi bi-exclamation-triangle"></i> ' . $_SESSION['error'] . '
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                  </div>';
                            unset($_SESSION['error']);
                        }
                        if (isset($_SESSION['success'])) {
                            echo '<div class="alert alert-success alert-dismissible fade show">
                                    <i class="bi bi-check-circle"></i> ' . $_SESSION['success'] . '
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                  </div>';
                            unset($_SESSION['success']);
                        }
                        ?>

                        <form action="login-process.php" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope"></i> Email Address
                                </label>
                                <input type="email" class="form-control form-control-lg" 
                                       id="email" name="email" 
                                       placeholder="Enter your email" 
                                       required>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">
                                    <i class="bi bi-lock"></i> Password
                                </label>
                                <input type="password" class="form-control form-control-lg" 
                                       id="password" name="password" 
                                       placeholder="Enter your password" 
                                       required>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </button>
                        </form>

                        <hr>

                        <div class="text-center">
                            <p class="mb-2">Don't have an account? 
                                <a href="register.php" class="text-decoration-none fw-bold">Register here</a>
                            </p>
                            <p class="mb-0">
                                <a href="admin/" class="text-muted text-decoration-none">
                                    <i class="bi bi-shield-lock"></i> Admin Login
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
