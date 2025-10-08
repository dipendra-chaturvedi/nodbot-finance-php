<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireAdmin();

$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get user data
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_query);

if (!$user_result || mysqli_num_rows($user_result) == 0) {
    $_SESSION['error'] = 'User not found!';
    header('Location: users.php');
    exit;
}

$usr = mysqli_fetch_assoc($user_result);

// Get user statistics
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM investments WHERE user_id = $user_id) as investment_count,
    (SELECT COALESCE(SUM(amount), 0) FROM investments WHERE user_id = $user_id) as total_invested,
    (SELECT COUNT(*) FROM loans WHERE user_id = $user_id) as loan_count,
    (SELECT COALESCE(SUM(amount), 0) FROM loans WHERE user_id = $user_id) as total_loans,
    (SELECT COUNT(*) FROM payments WHERE user_id = $user_id) as payment_count,
    (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE user_id = $user_id) as total_payments";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View User - <?php echo htmlspecialchars($usr['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row mb-4">
            <div class="col">
                <a href="users.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Users
                </a>
            </div>
        </div>

        <!-- User Profile Card -->
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <div class="bg-primary bg-opacity-10 rounded-circle p-4 d-inline-block mb-3">
                            <i class="bi bi-person-fill text-primary" style="font-size: 4rem;"></i>
                        </div>
                        <h3><?php echo htmlspecialchars($usr['name']); ?></h3>
                        <p class="text-muted"><?php echo htmlspecialchars($usr['email']); ?></p>
                        <span class="badge bg-<?php echo $usr['user_type'] == 'admin' ? 'warning' : 'primary'; ?> mb-3">
                            <?php echo strtoupper($usr['user_type']); ?>
                        </span>
                        
                        <hr>
                        
                        <div class="text-start">
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($usr['phone'] ?? 'N/A'); ?></p>
                            <p><strong>User ID:</strong> #<?php echo str_pad($usr['id'], 4, '0', STR_PAD_LEFT); ?></p>
                            <p><strong>Registered:</strong> <?php echo date('M d, Y', strtotime($usr['created_at'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <!-- Statistics -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted">Total Investments</h6>
                                <h3 class="text-primary"><?php echo formatCurrency($stats['total_invested']); ?></h3>
                                <small class="text-muted"><?php echo $stats['investment_count']; ?> investments</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted">Total Loans</h6>
                                <h3 class="text-warning"><?php echo formatCurrency($stats['total_loans']); ?></h3>
                                <small class="text-muted"><?php echo $stats['loan_count']; ?> loans</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted">Total Payments</h6>
                                <h3 class="text-success"><?php echo formatCurrency($stats['total_payments']); ?></h3>
                                <small class="text-muted"><?php echo $stats['payment_count']; ?> transactions</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted">Net Position</h6>
                                <?php $net = $stats['total_invested'] - $stats['total_loans']; ?>
                                <h3 class="<?php echo $net >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo formatCurrency(abs($net)); ?>
                                </h3>
                                <small class="text-muted"><?php echo $net >= 0 ? 'Positive' : 'Negative'; ?></small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Activity timeline coming soon...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
