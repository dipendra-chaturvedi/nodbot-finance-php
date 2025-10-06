<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireLogin();

$user = getUser();
$user_id = $user['id'];

// Get user's total investments (savings)
$savings_query = "SELECT COALESCE(SUM(amount), 0) as total_savings FROM investments WHERE user_id = $user_id AND status = 'active'";
$savings_result = mysqli_query($conn, $savings_query);
$savings_data = $savings_result ? mysqli_fetch_assoc($savings_result) : ['total_savings' => 0];
$total_savings = $savings_data['total_savings'];

// Calculate loan eligibility based on savings
$max_loan_amount = $total_savings * 2; // Can borrow up to 2x of savings
$credit_score = min(850, 300 + ($total_savings / 1000) * 10); // Simple credit score calculation

// Get existing loans
$loans_query = "SELECT COUNT(*) as count, COALESCE(SUM(amount), 0) as total FROM loans WHERE user_id = $user_id";
$loans_result = mysqli_query($conn, $loans_query);
$loans_data = $loans_result ? mysqli_fetch_assoc($loans_result) : ['count' => 0, 'total' => 0];

// Define loan offers based on savings
$loan_offers = [];

if ($total_savings >= 5000) {
    $loan_offers[] = [
        'name' => 'Personal Loan',
        'amount' => min(50000, $max_loan_amount * 0.4),
        'interest' => 10.5,
        'duration' => 24,
        'description' => 'Quick approval for personal needs',
        'icon' => 'person-fill',
        'color' => 'primary'
    ];
}

if ($total_savings >= 10000) {
    $loan_offers[] = [
        'name' => 'Business Loan',
        'amount' => min(100000, $max_loan_amount * 0.6),
        'interest' => 12.0,
        'duration' => 36,
        'description' => 'Grow your business with flexible terms',
        'icon' => 'briefcase-fill',
        'color' => 'success'
    ];
}

if ($total_savings >= 20000) {
    $loan_offers[] = [
        'name' => 'Home Loan',
        'amount' => min(500000, $max_loan_amount),
        'interest' => 8.5,
        'duration' => 120,
        'description' => 'Lowest rates for home purchase',
        'icon' => 'house-fill',
        'color' => 'info'
    ];
}

if ($total_savings >= 15000) {
    $loan_offers[] = [
        'name' => 'Education Loan',
        'amount' => min(200000, $max_loan_amount * 0.7),
        'interest' => 9.0,
        'duration' => 60,
        'description' => 'Invest in your future',
        'icon' => 'mortarboard-fill',
        'color' => 'warning'
    ];
}

// Default offer for everyone
if (empty($loan_offers)) {
    $loan_offers[] = [
        'name' => 'Micro Loan',
        'amount' => 5000,
        'interest' => 15.0,
        'duration' => 6,
        'description' => 'Small loan for emergency needs',
        'icon' => 'cash-coin',
        'color' => 'secondary'
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loans - <?php echo APP_NAME; ?></title>
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
        .eligibility-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .loan-offer-card {
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }
        .loan-offer-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .credit-score-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
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
                <h3><i class="bi bi-bank2"></i> <?php echo APP_NAME; ?></h3>
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
                    <h4 class="mb-0">Loan Offers</h4>
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

                <!-- Eligibility Banner -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card eligibility-card border-0 shadow-lg">
                            <div class="card-body p-4">
                                <div class="row align-items-center">
                                    <div class="col-md-3 text-center">
                                        <div class="credit-score-circle mx-auto">
                                            <?php echo round($credit_score); ?>
                                        </div>
                                        <p class="mb-0 mt-2">Credit Score</p>
                                    </div>
                                    <div class="col-md-3 text-center border-start">
                                        <h3 class="mb-1"><?php echo formatCurrency($total_savings); ?></h3>
                                        <p class="mb-0">Total Savings</p>
                                    </div>
                                    <div class="col-md-3 text-center border-start">
                                        <h3 class="mb-1"><?php echo formatCurrency($max_loan_amount); ?></h3>
                                        <p class="mb-0">Max Loan Eligible</p>
                                    </div>
                                    <div class="col-md-3 text-center border-start">
                                        <h3 class="mb-1"><?php echo $loans_data['count']; ?></h3>
                                        <p class="mb-0">Active Loans</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loan Offers -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="mb-3">
                            <i class="bi bi-star-fill text-warning"></i> 
                            Personalized Loan Offers for You
                        </h5>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <?php foreach ($loan_offers as $offer): ?>
                    <div class="col-md-6 col-lg-3">
                        <div class="card loan-offer-card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="text-center mb-3">
                                    <div class="bg-<?php echo $offer['color']; ?> bg-opacity-10 rounded-circle p-3 d-inline-block">
                                        <i class="bi bi-<?php echo $offer['icon']; ?> text-<?php echo $offer['color']; ?> fs-1"></i>
                                    </div>
                                </div>
                                <h5 class="card-title text-center"><?php echo $offer['name']; ?></h5>
                                <p class="text-center text-muted small"><?php echo $offer['description']; ?></p>
                                
                                <hr>
                                
                                <div class="mb-2">
                                    <small class="text-muted">Loan Amount</small>
                                    <h4 class="text-<?php echo $offer['color']; ?>"><?php echo formatCurrency($offer['amount']); ?></h4>
                                </div>
                                
                                <div class="d-flex justify-content-between mb-2">
                                    <div>
                                        <small class="text-muted">Interest Rate</small>
                                        <p class="mb-0 fw-bold"><?php echo $offer['interest']; ?>% p.a.</p>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted">Duration</small>
                                        <p class="mb-0 fw-bold"><?php echo $offer['duration']; ?> months</p>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <?php
                                    $monthly_emi = ($offer['amount'] * $offer['interest'] / 100 / 12 * $offer['duration'] + $offer['amount']) / $offer['duration'];
                                    ?>
                                    <small class="text-muted">Monthly EMI</small>
                                    <p class="mb-0 fw-bold text-success"><?php echo formatCurrency($monthly_emi); ?></p>
                                </div>
                                
                                <button class="btn btn-<?php echo $offer['color']; ?> w-100" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#applyLoanModal"
                                        data-loan-name="<?php echo $offer['name']; ?>"
                                        data-loan-amount="<?php echo $offer['amount']; ?>"
                                        data-loan-interest="<?php echo $offer['interest']; ?>"
                                        data-loan-duration="<?php echo $offer['duration']; ?>">
                                    <i class="bi bi-check-circle"></i> Apply Now
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- My Loans -->
                <div class="row mt-5">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-list-ul"></i> My Loan Applications</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Loan ID</th>
                                                <th>Purpose</th>
                                                <th>Amount</th>
                                                <th>Interest</th>
                                                <th>Duration</th>
                                                <th>Status</th>
                                                <th>Applied On</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $my_loans_query = "SELECT * FROM loans WHERE user_id = $user_id ORDER BY created_at DESC";
                                            $my_loans_result = mysqli_query($conn, $my_loans_query);
                                            
                                            if ($my_loans_result && mysqli_num_rows($my_loans_result) > 0) {
                                                while ($loan = mysqli_fetch_assoc($my_loans_result)) {
                                                    $status_colors = [
                                                        'pending' => 'warning',
                                                        'approved' => 'success',
                                                        'rejected' => 'danger',
                                                        'active' => 'primary',
                                                        'completed' => 'info'
                                                    ];
                                                    $status_class = $status_colors[$loan['status']] ?? 'secondary';
                                                    
                                                    echo '<tr>';
                                                    echo '<td><strong>#LN' . str_pad($loan['id'], 5, '0', STR_PAD_LEFT) . '</strong></td>';
                                                    echo '<td>' . htmlspecialchars($loan['purpose']) . '</td>';
                                                    echo '<td><strong>' . formatCurrency($loan['amount']) . '</strong></td>';
                                                    echo '<td>' . $loan['interest_rate'] . '%</td>';
                                                    echo '<td>' . $loan['duration_months'] . ' months</td>';
                                                    echo '<td><span class="badge bg-' . $status_class . '">' . ucfirst($loan['status']) . '</span></td>';
                                                    echo '<td>' . date('M d, Y', strtotime($loan['created_at'])) . '</td>';
                                                    echo '<td>
                                                            <a href="#" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                                                          </td>';
                                                    echo '</tr>';
                                                }
                                            } else {
                                                echo '<tr><td colspan="8" class="text-center py-5">
                                                        <i class="bi bi-inbox fs-1 text-muted"></i>
                                                        <p class="text-muted mt-2">No loan applications yet</p>
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

    <!-- Apply Loan Modal -->
    <div class="modal fade" id="applyLoanModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-file-earmark-text"></i> Apply for Loan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="apply-loan.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="loan_type" class="form-label">Loan Type</label>
                            <input type="text" class="form-control" id="loan_type" name="purpose" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label for="loan_amount" class="form-label">Loan Amount *</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control" id="loan_amount" name="amount" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label for="interest_rate" class="form-label">Interest Rate (%)</label>
                                <input type="text" class="form-control" id="interest_rate" name="interest_rate" readonly>
                            </div>
                            <div class="col-6 mb-3">
                                <label for="duration" class="form-label">Duration (Months)</label>
                                <input type="number" class="form-control" id="duration" name="duration_months" readonly>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="additional_notes" class="form-label">Additional Notes</label>
                            <textarea class="form-control" id="additional_notes" name="notes" rows="3" placeholder="Explain your loan purpose..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Submit Application</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar on mobile
        document.getElementById('sidebarCollapse')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // Populate modal with loan data
        const modal = document.getElementById('applyLoanModal');
        modal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            document.getElementById('loan_type').value = button.getAttribute('data-loan-name');
            document.getElementById('loan_amount').value = button.getAttribute('data-loan-amount');
            document.getElementById('interest_rate').value = button.getAttribute('data-loan-interest');
            document.getElementById('duration').value = button.getAttribute('data-loan-duration');
        });
    </script>
</body>
</html>
