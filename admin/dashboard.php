<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireAdmin();

$user = getUser();

// Get all statistics
$total_users_query = "SELECT COUNT(*) as count FROM users WHERE user_type = 'user'";
$total_users_result = mysqli_query($conn, $total_users_query);
$total_users = mysqli_fetch_assoc($total_users_result)['count'];

$total_investments_query = "SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total FROM investments";
$total_investments_result = mysqli_query($conn, $total_investments_query);
$total_investments = mysqli_fetch_assoc($total_investments_result);

$total_loans_query = "SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total FROM loans";
$total_loans_result = mysqli_query($conn, $total_loans_query);
$total_loans = mysqli_fetch_assoc($total_loans_result);

$total_payments_query = "SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total FROM payments";
$total_payments_result = mysqli_query($conn, $total_payments_query);
$total_payments = mysqli_fetch_assoc($total_payments_result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            overflow-x: hidden;
        }

        /* Admin Sidebar Styles */
        #sidebar {
            min-height: 100vh;
            transition: all 0.3s;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        }

        .sidebar-header {
            padding: 20px;
            background: rgba(0, 0, 0, 0.2);
        }

        .sidebar-header h3 {
            color: white;
            margin: 0;
            font-weight: bold;
        }

        .sidebar-header .badge {
            background: rgba(255, 255, 255, 0.2);
            color: white;
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
            border-left-color: #ffc107;
        }

        .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.2);
            border-left-color: #ffc107;
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
            background: #f8f9fa;
        }

        .top-navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 15px 30px;
        }

        /* Stats Cards */
        .stats-card {
            border-left: 4px solid;
            transition: transform 0.3s;
        }

        .stats-card:hover {
            transform: translateY(-5px);
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
        <!-- Admin Sidebar -->
        <nav id="sidebar" class="bg-dark">
            <div class="sidebar-header">
                <h3></i> <?php echo APP_NAME; ?></h3>
                <span class="badge">Admin Panel</span>
            </div>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="dashboard.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="users.php">
                        <i class="bi bi-people"></i> Manage Users
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
                <li class="nav-item mt-3">
                    <a class="nav-link" href="#">
                        <i class="bi bi-gear"></i> Settings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="bi bi-file-earmark-text"></i> Reports
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
                            <small class="text-white-50">Administrator</small>
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
                    <h4 class="mb-0">Admin Dashboard</h4>
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
                        <h2>Welcome, <?php echo $user['name']; ?>! 🎯</h2>
                        <p class="text-muted">Monitor and manage all platform activities from here.</p>
                    </div>
                </div>

                <?php showFlashMessage(); ?>

                <!-- Statistics Cards -->
                <div class="row g-4">
                    <div class="col-md-3">
                        <div class="card stats-card border-0 shadow-sm" style="border-left-color: #0dcaf0 !important;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1 small">TOTAL USERS</p>
                                        <h2 class="mb-0"><?php echo $total_users; ?></h2>
                                    </div>
                                    <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                        <i class="bi bi-people text-info fs-2"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card stats-card border-0 shadow-sm" style="border-left-color: #0d6efd !important;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1 small">INVESTMENTS</p>
                                        <h4 class="mb-0"><?php echo formatCurrency($total_investments['total']); ?></h4>
                                        <small class="text-muted">Count: <?php echo $total_investments['count']; ?></small>
                                    </div>
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                        <i class="bi bi-graph-up-arrow text-primary fs-2"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card stats-card border-0 shadow-sm" style="border-left-color: #ffc107 !important;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1 small">LOANS</p>
                                        <h4 class="mb-0"><?php echo formatCurrency($total_loans['total']); ?></h4>
                                        <small class="text-muted">Count: <?php echo $total_loans['count']; ?></small>
                                    </div>
                                    <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                        <i class="bi bi-cash-stack text-warning fs-2"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card stats-card border-0 shadow-sm" style="border-left-color: #198754 !important;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1 small">PAYMENTS</p>
                                        <h4 class="mb-0"><?php echo formatCurrency($total_payments['total']); ?></h4>
                                        <small class="text-muted">Count: <?php echo $total_payments['count']; ?></small>
                                    </div>
                                    <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                        <i class="bi bi-credit-card-2-front text-success fs-2"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Users Table -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Users</h5>
                                <a href="users.php" class="btn btn-sm btn-primary">View All</a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Phone</th>
                                                <th>Registered</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $users_query = "SELECT * FROM users WHERE user_type = 'user' ORDER BY created_at DESC LIMIT 10";
                                            $users_result = mysqli_query($conn, $users_query);
                                            
                                            if (mysqli_num_rows($users_result) > 0) {
                                                while ($row = mysqli_fetch_assoc($users_result)) {
                                                    echo '<tr>';
                                                    echo '<td><strong>#' . $row['id'] . '</strong></td>';
                                                    echo '<td>' . $row['name'] . '</td>';
                                                    echo '<td>' . $row['email'] . '</td>';
                                                    echo '<td>' . $row['phone'] . '</td>';
                                                    echo '<td>' . date('M d, Y', strtotime($row['created_at'])) . '</td>';
                                                    echo '<td>
                                                            <a href="#" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                                            <a href="#" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></a>
                                                          </td>';
                                                    echo '</tr>';
                                                }
                                            } else {
                                                echo '<tr><td colspan="6" class="text-center py-4 text-muted">No users registered yet</td></tr>';
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
