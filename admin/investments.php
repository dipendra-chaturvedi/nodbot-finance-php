<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireAdmin();

$user = getUser();

// Get investment statistics
$total_investments_query = "SELECT 
    COUNT(*) as total_count,
    COALESCE(SUM(amount), 0) as total_amount,
    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_count,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count
    FROM investments";
$total_result = mysqli_query($conn, $total_investments_query);
$stats = $total_result ? mysqli_fetch_assoc($total_result) : [
    'total_count' => 0,
    'total_amount' => 0,
    'active_count' => 0,
    'pending_count' => 0,
    'completed_count' => 0
];

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Build query with filters
$investments_query = "SELECT i.*, u.name as user_name, u.email as user_email 
                      FROM investments i 
                      LEFT JOIN users u ON i.user_id = u.id 
                      WHERE 1=1";

if ($status_filter != 'all') {
    $investments_query .= " AND i.status = '$status_filter'";
}

if (!empty($search)) {
    $investments_query .= " AND (u.name LIKE '%$search%' OR u.email LIKE '%$search%' OR i.plan LIKE '%$search%')";
}

$investments_query .= " ORDER BY i.investment_date DESC";
$investments_result = mysqli_query($conn, $investments_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Investments - <?php echo APP_NAME; ?></title>
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
        <!-- Admin Sidebar -->
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
                    <a class="nav-link active" href="investments.php">
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
                    <h4 class="mb-0">Investment Management</h4>
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
                <?php showFlashMessage(); ?>

                <!-- Statistics Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card stats-card border-0 shadow-sm" style="border-left-color: #0d6efd !important;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1 small">TOTAL INVESTMENTS</p>
                                        <h3 class="mb-0"><?php echo formatCurrency($stats['total_amount']); ?></h3>
                                        <small class="text-muted">Count: <?php echo $stats['total_count']; ?></small>
                                    </div>
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                        <i class="bi bi-graph-up text-primary fs-2"></i>
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
                                        <p class="text-muted mb-1 small">ACTIVE</p>
                                        <h2 class="mb-0"><?php echo $stats['active_count']; ?></h2>
                                    </div>
                                    <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                        <i class="bi bi-check-circle text-success fs-2"></i>
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
                                        <p class="text-muted mb-1 small">PENDING</p>
                                        <h2 class="mb-0"><?php echo $stats['pending_count']; ?></h2>
                                    </div>
                                    <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                        <i class="bi bi-clock-history text-warning fs-2"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card stats-card border-0 shadow-sm" style="border-left-color: #0dcaf0 !important;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1 small">COMPLETED</p>
                                        <h2 class="mb-0"><?php echo $stats['completed_count']; ?></h2>
                                    </div>
                                    <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                        <i class="bi bi-check-all text-info fs-2"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters and Search -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <form method="GET" class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Search User/Plan</label>
                                        <input type="text" class="form-control" name="search" 
                                               value="<?php echo htmlspecialchars($search); ?>" 
                                               placeholder="Search by name, email, or plan...">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Status Filter</label>
                                        <select class="form-select" name="status">
                                            <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Status</option>
                                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="bi bi-search"></i> Search
                                        </button>
                                        <a href="investments.php" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-circle"></i> Reset
                                        </a>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-success w-100" onclick="exportToExcel()">
                                            <i class="bi bi-download"></i> Export
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Investments Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-table"></i> All Investments</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0" id="investmentsTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ID</th>
                                                <th>User</th>
                                                <th>Plan</th>
                                                <th>Amount</th>
                                                <th>Duration</th>
                                                <th>Interest</th>
                                                <th>Maturity Value</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if ($investments_result && mysqli_num_rows($investments_result) > 0) {
                                                while ($inv = mysqli_fetch_assoc($investments_result)) {
                                                    $status_colors = [
                                                        'pending' => 'warning',
                                                        'active' => 'success',
                                                        'completed' => 'info',
                                                        'cancelled' => 'danger'
                                                    ];
                                                    $status_class = $status_colors[$inv['status']] ?? 'secondary';
                                                    
                                                    // Calculate maturity value
                                                    $interest = ($inv['amount'] * $inv['interest_rate'] * $inv['duration_months']) / (12 * 100);
                                                    $maturity = $inv['amount'] + $interest;
                                                    
                                                    echo '<tr>';
                                                    echo '<td><strong>#' . str_pad($inv['id'], 4, '0', STR_PAD_LEFT) . '</strong></td>';
                                                    echo '<td>
                                                            <strong>' . htmlspecialchars($inv['user_name']) . '</strong><br>
                                                            <small class="text-muted">' . htmlspecialchars($inv['user_email']) . '</small>
                                                          </td>';
                                                    echo '<td><span class="badge bg-primary">' . htmlspecialchars($inv['plan']) . '</span></td>';
                                                    echo '<td><strong>' . formatCurrency($inv['amount']) . '</strong></td>';
                                                    echo '<td>' . $inv['duration_months'] . ' months</td>';
                                                    echo '<td>' . $inv['interest_rate'] . '%</td>';
                                                    echo '<td><strong class="text-success">' . formatCurrency($maturity) . '</strong></td>';
                                                    echo '<td><span class="badge bg-' . $status_class . '">' . ucfirst($inv['status']) . '</span></td>';
                                                    echo '<td>' . date('M d, Y', strtotime($inv['investment_date'])) . '</td>';
                                                    echo '<td>
                                                            <div class="btn-group btn-group-sm">
                                                                <button class="btn btn-outline-primary" onclick="viewDetails(' . $inv['id'] . ')" title="View">
                                                                    <i class="bi bi-eye"></i>
                                                                </button>
                                                                <button class="btn btn-outline-warning" onclick="updateStatus(' . $inv['id'] . ', \'' . $inv['status'] . '\')" title="Update Status">
                                                                    <i class="bi bi-pencil"></i>
                                                                </button>
                                                                <button class="btn btn-outline-danger" onclick="deleteInvestment(' . $inv['id'] . ')" title="Delete">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </div>
                                                          </td>';
                                                    echo '</tr>';
                                                }
                                            } else {
                                                echo '<tr><td colspan="10" class="text-center py-5 text-muted">No investments found</td></tr>';
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

    <!-- Update Status Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Investment Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="update-investment-status.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="investment_id" id="modal_investment_id">
                        <div class="mb-3">
                            <label class="form-label">Select Status</label>
                            <select class="form-select form-select-lg" name="status" id="modal_status" required>
                                <option value="pending">Pending</option>
                                <option value="active">Active</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar
        document.getElementById('sidebarCollapse')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // Update status
        function updateStatus(id, currentStatus) {
            document.getElementById('modal_investment_id').value = id;
            document.getElementById('modal_status').value = currentStatus;
            new bootstrap.Modal(document.getElementById('statusModal')).show();
        }

        // Delete investment
        function deleteInvestment(id) {
            if (confirm('Are you sure you want to delete this investment?')) {
                window.location.href = 'delete-investment.php?id=' + id;
            }
        }

        // View details
        function viewDetails(id) {
            alert('View investment details #' + id + ' (Feature coming soon)');
        }

        // Export to Excel
        function exportToExcel() {
            alert('Export feature coming soon!');
        }
    </script>
</body>
</html>
