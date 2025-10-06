<?php
require_once '../config.php';

// Secret registration code for admin signup (change this to your own secret code)
define('ADMIN_REGISTRATION_CODE', 'NODBOT2025');

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
    <title>Admin Registration - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }
        .register-card {
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
        .form-control:focus {
            border-color: #2a5298;
            box-shadow: 0 0 0 0.2rem rgba(42, 82, 152, 0.25);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card register-card border-0 shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-person-plus-fill text-primary" style="font-size: 3rem;"></i>
                            <h2 class="fw-bold mt-3">Admin Registration</h2>
                            <span class="admin-badge">
                                <i class="bi bi-shield-lock"></i> Secure Access Required
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
                        ?>

                        <div class="alert alert-warning">
                            <i class="bi bi-info-circle"></i> 
                            <strong>Note:</strong> Admin registration requires a secret registration code. 
                            Contact system administrator to obtain this code.
                        </div>

                        <form action="register-process.php" method="POST" id="adminRegisterForm">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="name" class="form-label">
                                        <i class="bi bi-person"></i> Full Name *
                                    </label>
                                    <input type="text" class="form-control" 
                                           id="name" name="name" 
                                           placeholder="Enter full name" 
                                           required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">
                                        <i class="bi bi-envelope"></i> Email Address *
                                    </label>
                                    <input type="email" class="form-control" 
                                           id="email" name="email" 
                                           placeholder="admin@example.com" 
                                           required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">
                                        <i class="bi bi-telephone"></i> Phone Number *
                                    </label>
                                    <input type="tel" class="form-control" 
                                           id="phone" name="phone" 
                                           placeholder="1234567890" 
                                           pattern="[0-9]{10}" 
                                           required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">
                                        <i class="bi bi-lock"></i> Password *
                                    </label>
                                    <input type="password" class="form-control" 
                                           id="password" name="password" 
                                           placeholder="Min 8 characters" 
                                           minlength="8" 
                                           required>
                                    <small class="text-muted">Must be at least 8 characters</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">
                                        <i class="bi bi-lock-fill"></i> Confirm Password *
                                    </label>
                                    <input type="password" class="form-control" 
                                           id="confirm_password" name="confirm_password" 
                                           placeholder="Re-enter password" 
                                           required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="registration_code" class="form-label">
                                    <i class="bi bi-key-fill"></i> Admin Registration Code *
                                </label>
                                <input type="text" class="form-control" 
                                       id="registration_code" name="registration_code" 
                                       placeholder="Enter secret registration code" 
                                       required>
                                <small class="text-danger">
                                    This code is required to create admin accounts
                                </small>
                            </div>

                            <div class="mb-4 form-check">
                                <input type="checkbox" class="form-check-input" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the terms and conditions *
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                <i class="bi bi-person-check"></i> Create Admin Account
                            </button>
                        </form>

                        <hr>

                        <div class="text-center">
                            <p class="mb-0">
                                Already have an account? 
                                <a href="index.php" class="text-decoration-none fw-bold">Login here</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password match validation
        document.getElementById('adminRegisterForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
        });
    </script>
</body>
</html>
