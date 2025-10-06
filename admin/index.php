<?php
require_once '../config.php';

// Redirect if already logged in as admin
if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'admin') {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }
        .admin-badge {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card login-card border-0 shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-shield-lock-fill text-primary" style="font-size: 3rem;"></i>
                            <h2 class="fw-bold mt-3"><?php echo APP_NAME; ?></h2>
                            <span class="admin-badge">
                                <i class="bi bi-shield-check"></i> Admin Panel
                            </span>
                        </div>

                        <?php
                        if (isset($_SESSION['error'])) {
                            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill"></i> ' . $_SESSION['error'] . '
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                  </div>';
                            unset($_SESSION['error']);
                        }
                        if (isset($_SESSION['success'])) {
                            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bi bi-check-circle-fill"></i> ' . $_SESSION['success'] . '
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                  </div>';
                            unset($_SESSION['success']);
                        }
                        ?>

                        <form action="login-process.php" method="POST">
                            <div class="mb-4">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope"></i> Admin Email
                                </label>
                                <input type="email" class="form-control form-control-lg" 
                                       id="email" name="email" 
                                       placeholder="admin@example.com" 
                                       required autofocus>
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

                            <div class="mb-4 form-check">
                                <input type="checkbox" class="form-check-input" id="remember">
                                <label class="form-check-label" for="remember">
                                    Remember me
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                <i class="bi bi-box-arrow-in-right"></i> Login to Dashboard
                            </button>
                        </form>

                        <hr>

                        <div class="text-center">
                            <p class="mb-2">
                                <a href="register.php" class="text-decoration-none">
                                    <i class="bi bi-person-plus"></i> Create Admin Account
                                </a>
                            </p>
                            <p class="mb-0 text-muted">
                                <i class="bi bi-arrow-left"></i> 
                                <a href="../index.php" class="text-decoration-none">Back to User Login</a>
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
