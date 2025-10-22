<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireLogin();

$user = getUser();
$user_id = $user['id'];

// Get payment statistics
$stats_query = "SELECT 
    COUNT(*) as total_count,
    COALESCE(SUM(amount), 0) as total_amount,
    COUNT(CASE WHEN payment_type = 'investment' THEN 1 END) as investment_payments,
    COUNT(CASE WHEN payment_type = 'loan_repayment' THEN 1 END) as loan_payments,
    COUNT(CASE WHEN payment_type = 'withdrawal' THEN 1 END) as withdrawal_count,
    COALESCE(SUM(CASE WHEN payment_type = 'investment' THEN amount ELSE 0 END), 0) as total_invested,
    COALESCE(SUM(CASE WHEN payment_type = 'loan_repayment' THEN amount ELSE 0 END), 0) as total_repaid,
    COALESCE(SUM(CASE WHEN payment_type = 'withdrawal' THEN amount ELSE 0 END), 0) as total_withdrawn,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count
    FROM payments 
    WHERE user_id = $user_id";
$stats_result = mysqli_query($conn, $stats_query);
$stats = $stats_result ? mysqli_fetch_assoc($stats_result) : [
    'total_count' => 0,
    'total_amount' => 0,
    'investment_payments' => 0,
    'loan_payments' => 0,
    'withdrawal_count' => 0,
    'total_invested' => 0,
    'total_repaid' => 0,
    'total_withdrawn' => 0,
    'pending_count' => 0
];

// Get active investments and loans for dropdown
$investments_list = mysqli_query($conn, "SELECT id, plan, amount FROM investments WHERE user_id = $user_id AND status = 'active' ORDER BY investment_date DESC");
$loans_list = mysqli_query($conn, "SELECT id, purpose, amount FROM loans WHERE user_id = $user_id AND status IN ('approved', 'active') ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Payments - <?php echo APP_NAME; ?></title>
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
        .payment-card {
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
                    <h4 class="mb-0">My Payments</h4>
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
                        <div class="card stats-card border-0 shadow-sm h-100">
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
                        <div class="card stats-card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted mb-1 small">INVESTMENT PAYMENTS</p>
                                        <h3 class="mb-0 text-success"><?php echo formatCurrency($stats['total_invested']); ?></h3>
                                        <small class="text-muted"><?php echo $stats['investment_payments']; ?> payments</small>
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
                                        <p class="text-muted mb-1 small">LOAN REPAYMENTS</p>
                                        <h3 class="mb-0 text-warning"><?php echo formatCurrency($stats['total_repaid']); ?></h3>
                                        <small class="text-muted"><?php echo $stats['loan_payments']; ?> payments</small>
                                    </div>
                                    <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                        <i class="bi bi-cash-coin text-warning fs-2"></i>
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
                                        <p class="text-muted mb-1 small">WITHDRAWALS</p>
                                        <h3 class="mb-0 text-danger"><?php echo formatCurrency($stats['total_withdrawn']); ?></h3>
                                        <small class="text-muted"><?php echo $stats['withdrawal_count']; ?> withdrawals</small>
                                    </div>
                                    <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                                        <i class="bi bi-arrow-down-circle text-danger fs-2"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Make Payment Form -->
                    <div class="col-md-4 mb-4">
                        <div class="card payment-card border-0 shadow-lg h-100">
                            <div class="card-body p-4">
                                <h5 class="mb-3"><i class="bi bi-plus-circle"></i> Make a Payment</h5>
                                <p class="mb-4 opacity-75">Process a new payment transaction</p>

                                <form action="process-payment.php" method="POST">
                                    <div class="mb-3">
                                        <label for="payment_type" class="form-label">Payment Type *</label>
                                        <select class="form-select form-select-lg" id="payment_type" name="payment_type" required>
                                            <option value="">Select Type</option>
                                            <option value="investment">Investment Payment</option>
                                            <option value="loan_repayment">Loan Repayment</option>
                                            <option value="withdrawal">Withdrawal</option>
                                        </select>
                                    </div>

                                    <div class="mb-3" id="investment_select_div" style="display:none;">
                                        <label for="investment_id" class="form-label">Select Investment</label>
                                        <select class="form-select" id="investment_id" name="investment_id">
                                            <option value="">Choose investment...</option>
                                            <?php
                                            if ($investments_list) {
                                                mysqli_data_seek($investments_list, 0);
                                                while ($inv = mysqli_fetch_assoc($investments_list)) {
                                                    echo '<option value="' . $inv['id'] . '">' . htmlspecialchars($inv['plan']) . ' - ' . formatCurrency($inv['amount']) . '</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="mb-3" id="loan_select_div" style="display:none;">
                                        <label for="loan_id" class="form-label">Select Loan</label>
                                        <select class="form-select" id="loan_id" name="loan_id">
                                            <option value="">Choose loan...</option>
                                            <?php
                                            if ($loans_list) {
                                                mysqli_data_seek($loans_list, 0);
                                                while ($loan = mysqli_fetch_assoc($loans_list)) {
                                                    echo '<option value="' . $loan['id'] . '">' . htmlspecialchars($loan['purpose']) . ' - ' . formatCurrency($loan['amount']) . '</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="amount" class="form-label">Amount *</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white">₹</span>
                                            <input type="number" class="form-control form-control-lg" 
                                                   id="amount" name="amount" 
                                                   placeholder="0.00" 
                                                   step="0.01" 
                                                   min="1" 
                                                   required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="payment_method" class="form-label">Payment Method *</label>
                                        <select class="form-select form-select-lg" id="payment_method" name="payment_method" required>
                                            <option value="">Select Method</option>
                                            <option value="Cash">Cash</option>
                                            <!-- <option value="Bank Transfer">Bank Transfer</option> -->
                                            <option value="UPI">UPI</option>
                                            <!-- <option value="Credit Card">Credit Card</option> -->
                                            <!-- <option value="Debit Card">Debit Card</option> -->
                                            <!-- <option value="Net Banking">Net Banking</option> -->
                                        </select>
                                    </div>

                                    <div class="mb-4">
                                        <label for="transaction_id" class="form-label">Transaction/Reference ID</label>
                                        <input type="text" class="form-control" id="transaction_id" name="transaction_id" 
                                               placeholder="Enter transaction reference (optional)">
                                    </div>

                                    <button type="submit" class="btn btn-light btn-lg w-100">
                                        <i class="bi bi-check-circle"></i> Process Payment
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Payment History -->
                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Payment History</h5>
                                <?php if ($stats['pending_count'] > 0): ?>
                                <span class="badge bg-warning"><?php echo $stats['pending_count']; ?> Pending</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Transaction ID</th>
                                                <th>Type</th>
                                                <th>Amount</th>
                                                <th>Method</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $history_query = "SELECT * FROM payments 
                                                            WHERE user_id = $user_id 
                                                            ORDER BY payment_date DESC";
                                            $history_result = mysqli_query($conn, $history_query);
                                            
                                            if ($history_result && mysqli_num_rows($history_result) > 0) {
                                                while ($payment = mysqli_fetch_assoc($history_result)) {
                                                    $status_colors = [
                                                        'completed' => 'success',
                                                        'pending' => 'warning',
                                                        'failed' => 'danger'
                                                    ];
                                                    $status_class = $status_colors[$payment['status']] ?? 'secondary';
                                                    
                                                    $type_colors = [
                                                        'investment' => 'primary',
                                                        'loan_repayment' => 'warning',
                                                        'withdrawal' => 'danger'
                                                    ];
                                                    $type_class = $type_colors[$payment['payment_type']] ?? 'secondary';
                                                    
                                                    echo '<tr>';
                                                    echo '<td><strong>#' . htmlspecialchars($payment['transaction_id'] ?? 'TXN' . str_pad($payment['id'], 6, '0', STR_PAD_LEFT)) . '</strong></td>';
                                                    echo '<td><span class="badge bg-' . $type_class . '">' . ucwords(str_replace('_', ' ', $payment['payment_type'])) . '</span></td>';
                                                    echo '<td><strong>' . formatCurrency($payment['amount']) . '</strong></td>';
                                                    echo '<td><small>' . htmlspecialchars($payment['payment_method'] ?? 'N/A') . '</small></td>';
                                                    echo '<td><span class="badge bg-' . $status_class . '">' . ucfirst($payment['status']) . '</span></td>';
                                                    echo '<td>
                                                            ' . date('M d, Y', strtotime($payment['payment_date'])) . '<br>
                                                            <small class="text-muted">' . date('h:i A', strtotime($payment['payment_date'])) . '</small>
                                                          </td>';
                                                    echo '</tr>';
                                                }
                                            } else {
                                                echo '<tr><td colspan="6" class="text-center py-5">
                                                        <i class="bi bi-inbox fs-1 text-muted"></i>
                                                        <p class="text-muted mt-2">No payment history yet</p>
                                                      </td></tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Methods Info -->
                        <div class="card border-0 shadow-sm mt-4">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Payment Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-primary"><i class="bi bi-bank"></i> Bank Transfer Details</h6>
                                        <p class="small mb-2"><strong>Bank Name:</strong> Nodbot Finance Bank</p>
                                        <p class="small mb-2"><strong>Account Number:</strong> 1234567890</p>
                                        <p class="small mb-3"><strong>IFSC Code:</strong> NFIN0001234</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-success"><i class="bi bi-phone"></i> UPI Details</h6>
                                        <p class="small mb-2"><strong>UPI ID:</strong> nodbot@upi</p>
                                        <p class="small mb-2"><strong>Phone:</strong> +91 98765 43210</p>
                                        <p class="small mb-3"><strong>QR Code:</strong> Available at branch</p>
                                    </div>
                                </div>
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-lightbulb"></i> 
                                    <strong>Note:</strong> All payments are processed within 24 hours. Please keep your transaction ID for reference.
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

        // Show/hide investment and loan dropdowns based on payment type
        document.getElementById('payment_type').addEventListener('change', function() {
            const investmentDiv = document.getElementById('investment_select_div');
            const loanDiv = document.getElementById('loan_select_div');
            const investmentSelect = document.getElementById('investment_id');
            const loanSelect = document.getElementById('loan_id');
            
            // Hide both first
            investmentDiv.style.display = 'none';
            loanDiv.style.display = 'none';
            investmentSelect.removeAttribute('required');
            loanSelect.removeAttribute('required');
            
            // Show relevant dropdown
            if (this.value === 'investment') {
                investmentDiv.style.display = 'block';
            } else if (this.value === 'loan_repayment') {
                loanDiv.style.display = 'block';
            }
        });
    </script>
</body>
</html>
