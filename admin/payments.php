<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireAdmin();

$user = getUser();

// Get payment statistics
$stats_query = "SELECT 
    COUNT(*) as total_count,
    COALESCE(SUM(amount), 0) as total_amount,
    COUNT(CASE WHEN payment_type = 'investment' THEN 1 END) as investment_payments,
    COUNT(CASE WHEN payment_type = 'loan_repayment' THEN 1 END) as loan_payments,
    COUNT(CASE WHEN payment_type = 'withdrawal' THEN 1 END) as withdrawals,
    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
    COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_count,
    COUNT(CASE WHEN DATE(payment_date) = CURDATE() THEN 1 END) as today_payments,
    COALESCE(SUM(CASE WHEN DATE(payment_date) = CURDATE() THEN amount ELSE 0 END), 0) as today_amount
    FROM payments";
$stats_result = mysqli_query($conn, $stats_query);
$stats = $stats_result ? mysqli_fetch_assoc($stats_result) : [
    'total_count' => 0,
    'total_amount' => 0,
    'investment_payments' => 0,
    'loan_payments' => 0,
    'withdrawals' => 0,
    'completed_count' => 0,
    'pending_count' => 0,
    'failed_count' => 0,
    'today_payments' => 0,
    'today_amount' => 0
];

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$type_filter = isset($_GET['type']) ? $_GET['type'] : 'all';
$date_filter = isset($_GET['date']) ? $_GET['date'] : 'all';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Build query with filters
$payments_query = "SELECT p.*, u.name as user_name, u.email as user_email 
                   FROM payments p 
                   LEFT JOIN users u ON p.user_id = u.id 
                   WHERE 1=1";

if ($status_filter != 'all') {
    $payments_query .= " AND p.status = '$status_filter'";
}

if ($type_filter != 'all') {
    $payments_query .= " AND p.payment_type = '$type_filter'";
}

if ($date_filter == 'today') {
    $payments_query .= " AND DATE(p.payment_date) = CURDATE()";
} elseif ($date_filter == 'week') {
    $payments_query .= " AND DATE(p.payment_date) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
} elseif ($date_filter == 'month') {
    $payments_query .= " AND DATE(p.payment_date) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
}

if (!empty($search)) {
    $payments_query .= " AND (u.name LIKE '%$search%' OR u.email LIKE '%$search%' OR p.transaction_id LIKE '%$search%')";
}

$payments_query .= " ORDER BY p.payment_date DESC";
$payments_result = mysqli_query($conn, $payments_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Management - <?php echo APP_NAME; ?></title>
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
                    <a class="nav-link active" href="payments.php">
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
                    <h4 class="mb-0">Payment Management</h4>
                    <div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                            <i class="bi bi-plus-circle"></i> Add Payment
                        </button>
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
                                        <p class="text-muted mb-1 small">TOTAL PAYMENTS</p>
                                        <h3 class="mb-0"><?php echo formatCurrency($stats['total_amount']); ?></h3>
                                        <small class="text-muted"><?php echo $stats['total_count']; ?> transactions</small>
                                    </div>
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                        <i class="bi bi-wallet2 text-primary fs-2"></i>
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
                                        <p class="text-muted mb-1 small">TODAY'S PAYMENTS</p>
                                        <h3 class="mb-0 text-success"><?php echo formatCurrency($stats['today_amount']); ?></h3>
                                        <small class="text-muted"><?php echo $stats['today_payments']; ?> transactions</small>
                                    </div>
                                    <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                        <i class="bi bi-calendar-check text-success fs-2"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="card stats-card border-0 shadow-sm" style="border-left-color: #198754 !important;">
                            <div class="card-body text-center">
                                <i class="bi bi-check-circle text-success fs-1"></i>
                                <h2 class="mb-0"><?php echo $stats['completed_count']; ?></h2>
                                <small class="text-muted">Completed</small>
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
                        <div class="card stats-card border-0 shadow-sm" style="border-left-color: #dc3545 !important;">
                            <div class="card-body text-center">
                                <i class="bi bi-x-circle text-danger fs-1"></i>
                                <h2 class="mb-0"><?php echo $stats['failed_count']; ?></h2>
                                <small class="text-muted">Failed</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Type Breakdown -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="text-muted mb-3">Payment Types Breakdown</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span><i class="bi bi-graph-up text-primary"></i> Investments</span>
                                    <strong><?php echo $stats['investment_payments']; ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span><i class="bi bi-cash text-warning"></i> Loan Repayments</span>
                                    <strong><?php echo $stats['loan_payments']; ?></strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span><i class="bi bi-arrow-down-circle text-danger"></i> Withdrawals</span>
                                    <strong><?php echo $stats['withdrawals']; ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <form method="GET" class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label small">Search</label>
                                        <input type="text" class="form-control form-control-sm" name="search" 
                                               value="<?php echo htmlspecialchars($search); ?>" 
                                               placeholder="User or Transaction ID...">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small">Status</label>
                                        <select class="form-select form-select-sm" name="status">
                                            <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All</option>
                                            <option value="completed" <?php echo $status_filter == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="failed" <?php echo $status_filter == 'failed' ? 'selected' : ''; ?>>Failed</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small">Type</label>
                                        <select class="form-select form-select-sm" name="type">
                                            <option value="all">All Types</option>
                                            <option value="investment" <?php echo $type_filter == 'investment' ? 'selected' : ''; ?>>Investment</option>
                                            <option value="loan_repayment" <?php echo $type_filter == 'loan_repayment' ? 'selected' : ''; ?>>Loan Repayment</option>
                                            <option value="withdrawal" <?php echo $type_filter == 'withdrawal' ? 'selected' : ''; ?>>Withdrawal</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small">Date Range</label>
                                        <select class="form-select form-select-sm" name="date">
                                            <option value="all">All Time</option>
                                            <option value="today" <?php echo $date_filter == 'today' ? 'selected' : ''; ?>>Today</option>
                                            <option value="week" <?php echo $date_filter == 'week' ? 'selected' : ''; ?>>Last 7 Days</option>
                                            <option value="month" <?php echo $date_filter == 'month' ? 'selected' : ''; ?>>Last 30 Days</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end gap-2">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="bi bi-search"></i> Filter
                                        </button>
                                        <a href="payments.php" class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-x"></i> Reset
                                        </a>
                                        <button type="button" class="btn btn-success btn-sm" onclick="exportToExcel()">
                                            <i class="bi bi-download"></i> Export
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payments Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-table"></i> All Payments</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Transaction ID</th>
                                                <th>User</th>
                                                <th>Amount</th>
                                                <th>Type</th>
                                                <th>Method</th>
                                                <th>Status</th>
                                                <th>Date & Time</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if ($payments_result && mysqli_num_rows($payments_result) > 0) {
                                                while ($payment = mysqli_fetch_assoc($payments_result)) {
                                                    $status_colors = [
                                                        'completed' => 'success',
                                                        'pending' => 'warning',
                                                        'failed' => 'danger'
                                                    ];
                                                    $status_class = $status_colors[$payment['status']] ?? 'secondary';
                                                    
                                                    $type_icons = [
                                                        'investment' => 'graph-up',
                                                        'loan_repayment' => 'cash',
                                                        'withdrawal' => 'arrow-down-circle'
                                                    ];
                                                    $type_icon = $type_icons[$payment['payment_type']] ?? 'currency-dollar';
                                                    
                                                    echo '<tr>';
                                                    echo '<td><strong>#' . htmlspecialchars($payment['transaction_id'] ?? 'TXN' . str_pad($payment['id'], 6, '0', STR_PAD_LEFT)) . '</strong></td>';
                                                    echo '<td>
                                                            <strong>' . htmlspecialchars($payment['user_name']) . '</strong><br>
                                                            <small class="text-muted">' . htmlspecialchars($payment['user_email']) . '</small>
                                                          </td>';
                                                    echo '<td><strong class="text-primary">' . formatCurrency($payment['amount']) . '</strong></td>';
                                                    echo '<td>
                                                            <i class="bi bi-' . $type_icon . '"></i> 
                                                            ' . ucwords(str_replace('_', ' ', $payment['payment_type'])) . '
                                                          </td>';
                                                    echo '<td>' . htmlspecialchars($payment['payment_method'] ?? 'N/A') . '</td>';
                                                    echo '<td><span class="badge bg-' . $status_class . '">' . ucfirst($payment['status']) . '</span></td>';
                                                    echo '<td>
                                                            ' . date('M d, Y', strtotime($payment['payment_date'])) . '<br>
                                                            <small class="text-muted">' . date('h:i A', strtotime($payment['payment_date'])) . '</small>
                                                          </td>';
                                                    echo '<td>
                                                            <div class="btn-group btn-group-sm">';
                                                    
                                                    if ($payment['status'] == 'pending') {
                                                        echo '<button class="btn btn-success" onclick="updatePaymentStatus(' . $payment['id'] . ', \'completed\')" title="Mark Completed">
                                                                <i class="bi bi-check"></i>
                                                              </button>
                                                              <button class="btn btn-danger" onclick="updatePaymentStatus(' . $payment['id'] . ', \'failed\')" title="Mark Failed">
                                                                <i class="bi bi-x"></i>
                                                              </button>';
                                                    } else {
                                                        echo '<button class="btn btn-outline-primary" onclick="viewPayment(' . $payment['id'] . ')" title="View Details">
                                                                <i class="bi bi-eye"></i>
                                                              </button>';
                                                    }
                                                    
                                                    echo '      <button class="btn btn-outline-danger" onclick="deletePayment(' . $payment['id'] . ')" title="Delete">
                                                                <i class="bi bi-trash"></i>
                                                              </button>
                                                            </div>
                                                          </td>';
                                                    echo '</tr>';
                                                }
                                            } else {
                                                echo '<tr><td colspan="8" class="text-center py-5 text-muted">
                                                        <i class="bi bi-inbox fs-1"></i><br>
                                                        No payments found
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

    <!-- Add Payment Modal -->
    <div class="modal fade" id="addPaymentModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Add New Payment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="add-payment.php" method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="user_id" class="form-label">Select User *</label>
                                <select class="form-select" id="user_id" name="user_id" required>
                                    <option value="">Choose user...</option>
                                    <?php
                                    $users_list = mysqli_query($conn, "SELECT id, name, email FROM users WHERE user_type = 'user' ORDER BY name");
                                    while ($usr = mysqli_fetch_assoc($users_list)) {
                                        echo '<option value="' . $usr['id'] . '">' . htmlspecialchars($usr['name']) . ' (' . htmlspecialchars($usr['email']) . ')</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="amount" class="form-label">Amount *</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="1" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="payment_type" class="form-label">Payment Type *</label>
                                <select class="form-select" id="payment_type" name="payment_type" required>
                                    <option value="">Choose type...</option>
                                    <option value="investment">Investment</option>
                                    <option value="loan_repayment">Loan Repayment</option>
                                    <option value="withdrawal">Withdrawal</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="payment_method" class="form-label">Payment Method *</label>
                                <select class="form-select" id="payment_method" name="payment_method" required>
                                    <option value="">Choose method...</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                    <option value="UPI">UPI</option>
                                    <option value="Credit Card">Credit Card</option>
                                    <option value="Debit Card">Debit Card</option>
                                    <option value="Cheque">Cheque</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="transaction_id" class="form-label">Transaction ID</label>
                                <input type="text" class="form-control" id="transaction_id" name="transaction_id" 
                                       placeholder="Optional - Auto generated if empty">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="completed">Completed</option>
                                    <option value="pending">Pending</option>
                                    <option value="failed">Failed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Add Payment</button>
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

        // Update payment status
        function updatePaymentStatus(id, status) {
            if (confirm('Are you sure you want to mark this payment as ' + status + '?')) {
                window.location.href = 'update-payment-status.php?id=' + id + '&status=' + status;
            }
        }

        // Delete payment
        function deletePayment(id) {
            if (confirm('Are you sure you want to delete this payment record?')) {
                window.location.href = 'delete-payment.php?id=' + id;
            }
        }

        // View payment
        function viewPayment(id) {
            alert('View payment details #' + id + ' (Feature coming soon)');
        }

        // Export to Excel
        function exportToExcel() {
            alert('Export feature coming soon!');
        }
    </script>
</body>
</html>
