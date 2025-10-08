<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireAdmin();

$user = getUser();

// Get loan statistics
$stats_query = "SELECT 
    COUNT(*) as total_count,
    COALESCE(SUM(amount), 0) as total_amount,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
    COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_count,
    COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_count,
    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_count
    FROM loans";
$stats_result = mysqli_query($conn, $stats_query);
$stats = $stats_result ? mysqli_fetch_assoc($stats_result) : [
    'total_count' => 0,
    'total_amount' => 0,
    'pending_count' => 0,
    'approved_count' => 0,
    'rejected_count' => 0,
    'active_count' => 0
];

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Build query
$loans_query = "SELECT l.*, u.name as user_name, u.email as user_email 
                FROM loans l 
                LEFT JOIN users u ON l.user_id = u.id 
                WHERE 1=1";

if ($status_filter != 'all') {
    $loans_query .= " AND l.status = '$status_filter'";
}

if (!empty($search)) {
    $loans_query .= " AND (u.name LIKE '%$search%' OR u.email LIKE '%$search%' OR l.purpose LIKE '%$search%')";
}

$loans_query .= " ORDER BY l.created_at DESC";
$loans_result = mysqli_query($conn, $loans_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Loans - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            overflow-x: hidden;
        }
        #sidebar {
            min-height: 100vh;
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
        .stats-card {
            border-left: 4px solid;
            transition: transform 0.3s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
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
        <!-- Sidebar (same as investments page) -->
        <nav id="sidebar" class="bg-dark">
            <div class="sidebar-header">
                <h3><?php echo APP_NAME; ?></h3>
                <span class="badge bg-warning text-dark">Admin Panel</span>
            </div>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
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
                    <a class="nav-link active" href="loans.php">
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
                    <h4 class="mb-0">Loan Management</h4>
                    <div>
                        <span class="badge bg-warning text-dark fs-6">
                            <?php echo $stats['pending_count']; ?> Pending Approvals
                        </span>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <div class="container-fluid p-4">
                <?php showFlashMessage(); ?>

                <!-- Statistics Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-2">
                        <div class="card stats-card border-0 shadow-sm" style="border-left-color: #0d6efd !important;">
                            <div class="card-body">
                                <p class="text-muted mb-1 small">TOTAL LOANS</p>
                                <h4 class="mb-0"><?php echo formatCurrency($stats['total_amount']); ?></h4>
                                <small class="text-muted"><?php echo $stats['total_count']; ?> applications</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="card stats-card border-0 shadow-sm" style="border-left-color: #ffc107 !important;">
                            <div class="card-body text-center">
                                <i class="bi bi-clock-history text-warning fs-1"></i>
                                <h2 class="mb-0"><?php echo $stats['pending_count']; ?></h2>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="card stats-card border-0 shadow-sm" style="border-left-color: #198754 !important;">
                            <div class="card-body text-center">
                                <i class="bi bi-check-circle text-success fs-1"></i>
                                <h2 class="mb-0"><?php echo $stats['approved_count']; ?></h2>
                                <small class="text-muted">Approved</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="card stats-card border-0 shadow-sm" style="border-left-color: #dc3545 !important;">
                            <div class="card-body text-center">
                                <i class="bi bi-x-circle text-danger fs-1"></i>
                                <h2 class="mb-0"><?php echo $stats['rejected_count']; ?></h2>
                                <small class="text-muted">Rejected</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="card stats-card border-0 shadow-sm" style="border-left-color: #0dcaf0 !important;">
                            <div class="card-body text-center">
                                <i class="bi bi-activity text-info fs-1"></i>
                                <h2 class="mb-0"><?php echo $stats['active_count']; ?></h2>
                                <small class="text-muted">Active</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="card stats-card border-0 shadow-sm" style="border-left-color: #6c757d !important;">
                            <div class="card-body text-center">
                                <i class="bi bi-percent text-secondary fs-1"></i>
                                <h2 class="mb-0"><?php echo $stats['approved_count'] > 0 ? round(($stats['approved_count'] / $stats['total_count']) * 100) : 0; ?>%</h2>
                                <small class="text-muted">Approval Rate</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <form method="GET" class="row g-3">
                                    <div class="col-md-5">
                                        <label class="form-label">Search User/Purpose</label>
                                        <input type="text" class="form-control" name="search" 
                                               value="<?php echo htmlspecialchars($search); ?>" 
                                               placeholder="Search...">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Status Filter</label>
                                        <select class="form-select" name="status">
                                            <option value="all">All Status</option>
                                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>Approved</option>
                                            <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                            <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-search"></i> Search
                                        </button>
                                        <a href="loans.php" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-circle"></i> Reset
                                        </a>
                                        <button type="button" class="btn btn-success" onclick="exportToExcel()">
                                            <i class="bi bi-download"></i> Export
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loans Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-table"></i> All Loan Applications</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Loan ID</th>
                                                <th>User</th>
                                                <th>Purpose</th>
                                                <th>Amount</th>
                                                <th>Interest</th>
                                                <th>Duration</th>
                                                <th>EMI</th>
                                                <th>Status</th>
                                                <th>Applied On</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if ($loans_result && mysqli_num_rows($loans_result) > 0) {
                                                while ($loan = mysqli_fetch_assoc($loans_result)) {
                                                    $status_colors = [
                                                        'pending' => 'warning',
                                                        'approved' => 'success',
                                                        'rejected' => 'danger',
                                                        'active' => 'primary',
                                                        'completed' => 'info'
                                                    ];
                                                    $status_class = $status_colors[$loan['status']] ?? 'secondary';
                                                    
                                                    // Calculate EMI
                                                    $monthly_emi = ($loan['amount'] * $loan['interest_rate'] / 100 / 12 * $loan['duration_months'] + $loan['amount']) / $loan['duration_months'];
                                                    
                                                    echo '<tr>';
                                                    echo '<td><strong>#LN' . str_pad($loan['id'], 5, '0', STR_PAD_LEFT) . '</strong></td>';
                                                    echo '<td>
                                                            <strong>' . htmlspecialchars($loan['user_name']) . '</strong><br>
                                                            <small class="text-muted">' . htmlspecialchars($loan['user_email']) . '</small>
                                                          </td>';
                                                    echo '<td>' . htmlspecialchars($loan['purpose']) . '</td>';
                                                    echo '<td><strong>' . formatCurrency($loan['amount']) . '</strong></td>';
                                                    echo '<td>' . $loan['interest_rate'] . '%</td>';
                                                    echo '<td>' . $loan['duration_months'] . ' months</td>';
                                                    echo '<td>' . formatCurrency($monthly_emi) . '</td>';
                                                    echo '<td><span class="badge bg-' . $status_class . '">' . ucfirst($loan['status']) . '</span></td>';
                                                    echo '<td>' . date('M d, Y', strtotime($loan['created_at'])) . '</td>';
                                                    echo '<td>
                                                            <div class="btn-group btn-group-sm">';
                                                    
                                                    if ($loan['status'] == 'pending') {
                                                        echo '<button class="btn btn-success" onclick="updateLoanStatus(' . $loan['id'] . ', \'approved\')" title="Approve">
                                                                <i class="bi bi-check"></i>
                                                              </button>
                                                              <button class="btn btn-danger" onclick="updateLoanStatus(' . $loan['id'] . ', \'rejected\')" title="Reject">
                                                                <i class="bi bi-x"></i>
                                                              </button>';
                                                    } else {
                                                        echo '<button class="btn btn-outline-primary" onclick="viewLoanDetails(' . $loan['id'] . ')" title="View">
                                                                <i class="bi bi-eye"></i>
                                                              </button>';
                                                    }
                                                    
                                                    echo '      <button class="btn btn-outline-danger" onclick="deleteLoan(' . $loan['id'] . ')" title="Delete">
                                                                <i class="bi bi-trash"></i>
                                                              </button>
                                                            </div>
                                                          </td>';
                                                    echo '</tr>';
                                                }
                                            } else {
                                                echo '<tr><td colspan="10" class="text-center py-5 text-muted">No loan applications found</td></tr>';
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
        document.getElementById('sidebarCollapse')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        function updateLoanStatus(id, status) {
            if (confirm('Are you sure you want to ' + status + ' this loan?')) {
                window.location.href = 'update-loan-status.php?id=' + id + '&status=' + status;
            }
        }

        function deleteLoan(id) {
            if (confirm('Are you sure you want to delete this loan?')) {
                window.location.href = 'delete-loan.php?id=' + id;
            }
        }

        function viewLoanDetails(id) {
            alert('View loan details #' + id + ' (Feature coming soon)');
        }

        function exportToExcel() {
            alert('Export feature coming soon!');
        }
    </script>
</body>
</html>
