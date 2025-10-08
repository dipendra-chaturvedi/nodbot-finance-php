<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireLogin();

$user = getUser();

// Get user statistics
$user_id = $user['id'];

// Get investments with error handling
$investments_query = "SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total FROM investments WHERE user_id = $user_id";
$investments_result = mysqli_query($conn, $investments_query);

if ($investments_result) {
    $investments_data = mysqli_fetch_assoc($investments_result);
} else {
    // Set default values if query fails
    $investments_data = ['count' => 0, 'total' => 0];
}

// Get loans with error handling
$loans_query = "SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total FROM loans WHERE user_id = $user_id";
$loans_result = mysqli_query($conn, $loans_query);

if ($loans_result) {
    $loans_data = mysqli_fetch_assoc($loans_result);
} else {
    $loans_data = ['count' => 0, 'total' => 0];
}

// Get payments with error handling
$payments_query = "SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total FROM payments WHERE user_id = $user_id";
$payments_result = mysqli_query($conn, $payments_query);

if ($payments_result) {
    $payments_data = mysqli_fetch_assoc($payments_result);
} else {
    $payments_data = ['count' => 0, 'total' => 0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        #sidebar {
            min-height: 100vh;
            transition: all 0.3s;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .sidebar-header {
            padding: 20px;
            background: rgba(0, 0, 0, 0.1);
        }

        .sidebar-header h3 {
            color: white;
            margin: 0;
            font-weight: bold;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 15px 20px;
            border-left: 3px solid transparent;
            transition: all 0.3s;
        }

        .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            border-left-color: white;
        }

        .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.2);
            border-left-color: white;
        }

        .nav-link i {
            width: 25px;
            font-size: 1.2rem;
        }

        .user-info {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }

        /* Main Content */
        #content {
            width: 100%;
            padding: 0;
            min-height: 100vh;
            transition: all 0.3s;
        }

        .top-navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 15px 30px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            #sidebar {
                margin-left: -250px;
                position: fixed;
                z-index: 999;
            }
            #sidebar.active {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav id="sidebar" class="bg-dark">
            <div class="sidebar-header">
                <h3><?php echo APP_NAME; ?></h3>
                <small class="text-white-50">User Portal</small>
            </div>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="dashboard.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="investments.php">
                        <i class="bi bi-graph-up-arrow"></i> Investments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="loans.php">
                        <i class="bi bi-cash-stack"></i> Loans
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="payments.php">
                        <i class="bi bi-credit-card-2-front"></i> Payments
                    </a>
                </li>
            </ul>

            <div class="user-info">
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle fs-4 me-2"></i>
                        <div>
                            <strong><?php echo $user['name']; ?></strong>
                            <br>
                            <small class="text-white-50"><?php echo $user['email']; ?></small>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-person"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear"></i> Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div id="content">
            <!-- Top Navbar -->
            <nav class="top-navbar">
                <div class="d-flex justify-content-between align-items-center">
                    <button class="btn btn-outline-secondary d-md-none" id="sidebarCollapse">
                        <i class="bi bi-list"></i>
                    </button>
                    <h4 class="mb-0">Dashboard</h4>
                    <div>
                        <span class="text-muted">
                            <i class="bi bi-calendar3"></i> 
                            <?php echo date('l, F d, Y'); ?>
                        </span>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <div class="container-fluid p-4">
                <div class="row mb-4">
                    <div class="col">
                        <h2>Welcome , <?php echo $user['name']; ?></h2>
                        <p class="text-muted">Here's what's happening with your finances today.</p>
                    </div>
                </div>

                <?php showFlashMessage(); ?>

                <!-- Statistics Cards -->
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="text-muted mb-1">Total Investments</p>
                                        <h3 class="mb-0"><?php echo formatCurrency($investments_data['total']); ?></h3>
                                        <small class="text-success">
                                            <i class="bi bi-arrow-up"></i> Active: <?php echo $investments_data['count']; ?>
                                        </small>
                                    </div>
                                    <div class="bg-primary bg-opacity-10 rounded p-3">
                                        <i class="bi bi-graph-up-arrow text-primary fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="text-muted mb-1">Total Loans</p>
                                        <h3 class="mb-0"><?php echo formatCurrency($loans_data['total']); ?></h3>
                                        <small class="text-warning">
                                            <i class="bi bi-dash-circle"></i> Active: <?php echo $loans_data['count']; ?>
                                        </small>
                                    </div>
                                    <div class="bg-warning bg-opacity-10 rounded p-3">
                                        <i class="bi bi-cash-stack text-warning fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <p class="text-muted mb-1">Total Payments</p>
                                        <h3 class="mb-0"><?php echo formatCurrency($payments_data['total']); ?></h3>
                                        <small class="text-success">
                                            <i class="bi bi-check-circle"></i> Transactions: <?php echo $payments_data['count']; ?>
                                        </small>
                                    </div>
                                    <div class="bg-success bg-opacity-10 rounded p-3">
                                        <i class="bi bi-credit-card-2-front text-success fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Investments -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Investments</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Plan</th>
                                <th>Amount</th>
                                <th>Duration</th>
                                <th>Interest Rate</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // FIXED: Check if query succeeds before using mysqli_num_rows
                            $recent_query = "SELECT * FROM investments WHERE user_id = $user_id ORDER BY investment_date DESC LIMIT 5";
                            $recent_result = mysqli_query($conn, $recent_query);
                            
                            if ($recent_result === false) {
                                // Query failed - show error message
                                echo '<tr><td colspan="6" class="text-center text-danger py-4">';
                                echo '<i class="bi bi-exclamation-triangle"></i><br>';
                                echo '<strong>Database Error:</strong> ' . mysqli_error($conn);
                                echo '<br><small>Please make sure the investments table exists</small>';
                                echo '</td></tr>';
                            } elseif (mysqli_num_rows($recent_result) > 0) {
                                // Has results
                                while ($row = mysqli_fetch_assoc($recent_result)) {
                                    $status_class = $row['status'] == 'active' ? 'success' : ($row['status'] == 'pending' ? 'warning' : 'info');
                                    echo '<tr>';
                                    echo '<td><strong>' . htmlspecialchars($row['plan']) . '</strong></td>';
                                    echo '<td>' . formatCurrency($row['amount']) . '</td>';
                                    echo '<td>' . $row['duration_months'] . ' months</td>';
                                    echo '<td><span class="badge bg-primary">' . $row['interest_rate'] . '%</span></td>';
                                    echo '<td><span class="badge bg-' . $status_class . '">' . ucfirst($row['status']) . '</span></td>';
                                    echo '<td>' . date('M d, Y', strtotime($row['investment_date'])) . '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                // No results
                                echo '<tr><td colspan="6" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-3"></i><br>
                                        No investments yet. <a href="investments.php">Start investing today!</a>
                                      </td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar on mobile
        document.getElementById('sidebarCollapse')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>
