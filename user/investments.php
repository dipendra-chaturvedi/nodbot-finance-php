<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireLogin();

$user = getUser();
$user_id = $user['id'];

// Get investment statistics with error checking
$stats_query = "SELECT 
                COUNT(*) as total_deposits,
                COALESCE(SUM(amount), 0) as total_invested,
                MIN(investment_date) as first_deposit,
                MAX(investment_date) as last_deposit
                FROM investments 
                WHERE user_id = $user_id";
$stats_result = mysqli_query($conn, $stats_query);

// Check if query succeeded
if ($stats_result === false) {
    // If query fails, set default values
    $stats = [
        'total_deposits' => 0,
        'total_invested' => 0,
        'first_deposit' => null,
        'last_deposit' => null
    ];
    // Optionally display error (uncomment to debug)
    // echo "Error: " . mysqli_error($conn);
} else {
    $stats = mysqli_fetch_assoc($stats_result);
}

// Calculate interest (example: 8% annual interest)
$interest_rate = 8;
$total_interest = ($stats['total_invested'] * $interest_rate) / 100;
$maturity_amount = $stats['total_invested'] + $total_interest;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Investments - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            overflow-x: hidden;
        }
        #sidebar {
            min-height: 100vh;
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
            transition: transform 0.3s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .deposit-form-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
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
        <!-- Sidebar -->
        <nav id="sidebar" class="bg-dark">
            <div class="sidebar-header">
                <h3><?php echo APP_NAME; ?></h3>
                <small class="text-white-50">User Portal</small>
            </div>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
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
                    <h4 class="mb-0">My Investments</h4>
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

                <!-- Investment Summary Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card stats-card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1 small">TOTAL INVESTED</p>
                                        <h3 class="mb-0"><?php echo formatCurrency($stats['total_invested']); ?></h3>
                                    </div>
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                        <i class="bi bi-piggy-bank text-primary fs-2"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card stats-card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1 small">ESTIMATED INTEREST</p>
                                        <h3 class="mb-0 text-success"><?php echo formatCurrency($total_interest); ?></h3>
                                        <small class="text-muted"><?php echo $interest_rate; ?>% p.a.</small>
                                    </div>
                                    <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                        <i class="bi bi-graph-up text-success fs-2"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card stats-card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1 small">MATURITY VALUE</p>
                                        <h3 class="mb-0 text-info"><?php echo formatCurrency($maturity_amount); ?></h3>
                                    </div>
                                    <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                        <i class="bi bi-trophy text-info fs-2"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card stats-card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1 small">TOTAL DEPOSITS</p>
                                        <h3 class="mb-0"><?php echo $stats['total_deposits']; ?></h3>
                                        <small class="text-muted">Transactions</small>
                                    </div>
                                    <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                        <i class="bi bi-receipt text-warning fs-2"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Add New Investment Form -->
                    <div class="col-md-4 mb-4">
                        <div class="card deposit-form-card border-0 shadow-lg h-100">
                            <div class="card-body p-4">
                                <h5 class="mb-3"><i class="bi bi-plus-circle"></i> Add New Deposit</h5>
                                <p class="mb-4 opacity-75">Make a regular deposit to grow your investment</p>

                                <form action="add-investment.php" method="POST">
                                    <div class="mb-3">
                                        <label for="amount" class="form-label">Deposit Amount *</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">₹</span>
                                            <input type="number" class="form-control form-control-lg" 
                                                   id="amount" name="amount" 
                                                   placeholder="0.00" 
                                                   step="0.01" 
                                                   min="100" 
                                                   required>
                                        </div>
                                        <small class="text-white-50">Minimum: ₹100</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="plan" class="form-label">Investment Plan *</label>
                                        <select class="form-select form-select-lg" id="plan" name="plan" required>
                                            <option value="">Select Plan</option>
                                            <option value="Daily Savings">Daily Savings</option>
                                            <option value="Monthly Recurring">Monthly Recurring</option>
                                            <option value="Fixed Deposit">Fixed Deposit</option>
                                            <option value="Flexible">Flexible Deposit</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="duration" class="form-label">Duration (Months) *</label>
                                        <input type="number" class="form-control form-control-lg" 
                                               id="duration" name="duration" 
                                               placeholder="12" 
                                               min="1" 
                                               max="120" 
                                               required>
                                        <small class="text-white-50">1 to 120 months</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="interest_rate" class="form-label">Interest Rate (%) *</label>
                                        <input type="number" class="form-control form-control-lg" 
                                               id="interest_rate" name="interest_rate" 
                                               value="8.00" 
                                               step="0.01" 
                                               min="0" 
                                               readonly>
                                        <small class="text-white-50">Annual interest rate</small>
                                    </div>

                                    <div class="mb-4">
                                        <label for="notes" class="form-label">Notes (Optional)</label>
                                        <textarea class="form-control" id="notes" name="notes" 
                                                  rows="2" placeholder="Add any notes..."></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-light btn-lg w-100">
                                        <i class="bi bi-check-circle"></i> Add Deposit
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Investment History -->
                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Investment History</h5>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary active">All</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary">This Month</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary">This Year</button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Plan</th>
                                                <th>Amount</th>
                                                <th>Duration</th>
                                                <th>Interest</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $history_query = "SELECT * FROM investments 
                                                            WHERE user_id = $user_id 
                                                            ORDER BY investment_date DESC";
                                            $history_result = mysqli_query($conn, $history_query);
                                            
                                            // FIXED: Check if query succeeded first
                                            if ($history_result === false) {
                                                // Query failed - show error
                                                echo '<tr><td colspan="7" class="text-center text-danger py-4">';
                                                echo '<i class="bi bi-exclamation-triangle fs-3"></i><br>';
                                                echo '<strong>Database Error:</strong><br>';
                                                echo mysqli_error($conn);
                                                echo '</td></tr>';
                                            } elseif (mysqli_num_rows($history_result) > 0) {
                                                // Query succeeded and has results
                                                while ($row = mysqli_fetch_assoc($history_result)) {
                                                    $status_class = 'info';
                                                    if ($row['status'] == 'active') $status_class = 'success';
                                                    if ($row['status'] == 'completed') $status_class = 'primary';
                                                    if ($row['status'] == 'cancelled') $status_class = 'danger';
                                                    
                                                    echo '<tr>';
                                                    echo '<td>' . date('M d, Y', strtotime($row['investment_date'])) . '<br><small class="text-muted">' . date('h:i A', strtotime($row['investment_date'])) . '</small></td>';
                                                    echo '<td><strong>' . htmlspecialchars($row['plan']) . '</strong></td>';
                                                    echo '<td><strong class="text-primary">' . formatCurrency($row['amount']) . '</strong></td>';
                                                    echo '<td>' . $row['duration_months'] . ' months</td>';
                                                    echo '<td><span class="badge bg-success">' . $row['interest_rate'] . '%</span></td>';
                                                    echo '<td><span class="badge bg-' . $status_class . '">' . ucfirst($row['status']) . '</span></td>';
                                                    echo '<td>
                                                            <a href="view-investment.php?id=' . $row['id'] . '" class="btn btn-sm btn-outline-primary" title="View Details">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                            <a href="delete-investment.php?id=' . $row['id'] . '" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm(\'Are you sure?\')">
                                                                <i class="bi bi-trash"></i>
                                                            </a>
                                                        </td>';
                                                    echo '</tr>';
                                                }
                                            } else {
                                                // Query succeeded but no results
                                                echo '<tr><td colspan="7" class="text-center py-5">
                                                        <i class="bi bi-inbox fs-1 text-muted"></i>
                                                        <p class="text-muted mt-2">No investments yet. Start investing today!</p>
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
