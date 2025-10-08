<?php
require_once '../config.php';
require_once '../includes/functions.php';

requireAdmin();

$user = getUser();

// Get user statistics
$stats_query = "SELECT 
    COUNT(*) as total_users,
    COUNT(CASE WHEN user_type = 'user' THEN 1 END) as regular_users,
    COUNT(CASE WHEN user_type = 'admin' THEN 1 END) as admin_users,
    COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today_registrations,
    COUNT(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as week_registrations
    FROM users";
$stats_result = mysqli_query($conn, $stats_query);
$stats = $stats_result ? mysqli_fetch_assoc($stats_result) : [
    'total_users' => 0,
    'regular_users' => 0,
    'admin_users' => 0,
    'today_registrations' => 0,
    'week_registrations' => 0
];

// Get filter parameters
$user_type_filter = isset($_GET['user_type']) ? $_GET['user_type'] : 'all';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Build query with filters
$users_query = "SELECT u.*,
    (SELECT COUNT(*) FROM investments WHERE user_id = u.id) as investment_count,
    (SELECT COALESCE(SUM(amount), 0) FROM investments WHERE user_id = u.id) as total_invested,
    (SELECT COUNT(*) FROM loans WHERE user_id = u.id) as loan_count,
    (SELECT COALESCE(SUM(amount), 0) FROM loans WHERE user_id = u.id) as total_loans
    FROM users u 
    WHERE 1=1";

if ($user_type_filter != 'all') {
    $users_query .= " AND u.user_type = '$user_type_filter'";
}

if (!empty($search)) {
    $users_query .= " AND (u.name LIKE '%$search%' OR u.email LIKE '%$search%' OR u.phone LIKE '%$search%')";
}

$users_query .= " ORDER BY u.created_at DESC";
$users_result = mysqli_query($conn, $users_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - <?php echo APP_NAME; ?></title>
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
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
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
                    <a class="nav-link active" href="users.php">
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
                    <h4 class="mb-0">User Management</h4>
                    <div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="bi bi-plus-circle"></i> Add New User
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
                                        <p class="text-muted mb-1 small">TOTAL USERS</p>
                                        <h2 class="mb-0"><?php echo $stats['total_users']; ?></h2>
                                        <small class="text-muted">All registered users</small>
                                    </div>
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                        <i class="bi bi-people text-primary fs-2"></i>
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
                                        <p class="text-muted mb-1 small">REGULAR USERS</p>
                                        <h2 class="mb-0"><?php echo $stats['regular_users']; ?></h2>
                                        <small class="text-muted">User accounts</small>
                                    </div>
                                    <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                        <i class="bi bi-person-check text-success fs-2"></i>
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
                                        <p class="text-muted mb-1 small">ADMINISTRATORS</p>
                                        <h2 class="mb-0"><?php echo $stats['admin_users']; ?></h2>
                                        <small class="text-muted">Admin accounts</small>
                                    </div>
                                    <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                        <i class="bi bi-shield-check text-warning fs-2"></i>
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
                                        <p class="text-muted mb-1 small">NEW THIS WEEK</p>
                                        <h2 class="mb-0"><?php echo $stats['week_registrations']; ?></h2>
                                        <small class="text-muted">Today: <?php echo $stats['today_registrations']; ?></small>
                                    </div>
                                    <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                        <i class="bi bi-calendar-plus text-info fs-2"></i>
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
                                    <div class="col-md-5">
                                        <label class="form-label">Search Users</label>
                                        <input type="text" class="form-control" name="search" 
                                               value="<?php echo htmlspecialchars($search); ?>" 
                                               placeholder="Search by name, email, or phone...">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">User Type</label>
                                        <select class="form-select" name="user_type">
                                            <option value="all" <?php echo $user_type_filter == 'all' ? 'selected' : ''; ?>>All Types</option>
                                            <option value="user" <?php echo $user_type_filter == 'user' ? 'selected' : ''; ?>>Regular Users</option>
                                            <option value="admin" <?php echo $user_type_filter == 'admin' ? 'selected' : ''; ?>>Administrators</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-search"></i> Search
                                        </button>
                                        <a href="users.php" class="btn btn-outline-secondary">
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

                <!-- Users Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0"><i class="bi bi-table"></i> All Users</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>User</th>
                                                <th>Contact</th>
                                                <th>Type</th>
                                                <th>Investments</th>
                                                <th>Loans</th>
                                                <th>Registered</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if ($users_result && mysqli_num_rows($users_result) > 0) {
                                                while ($usr = mysqli_fetch_assoc($users_result)) {
                                                    $initials = strtoupper(substr($usr['name'], 0, 2));
                                                    $type_badge = $usr['user_type'] == 'admin' ? 'warning' : 'primary';
                                                    
                                                    echo '<tr>';
                                                    echo '<td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="user-avatar me-3">' . $initials . '</div>
                                                                <div>
                                                                    <strong>' . htmlspecialchars($usr['name']) . '</strong>
                                                                    <br>
                                                                    <small class="text-muted">ID: #' . str_pad($usr['id'], 4, '0', STR_PAD_LEFT) . '</small>
                                                                </div>
                                                            </div>
                                                          </td>';
                                                    echo '<td>
                                                            <small><i class="bi bi-envelope"></i> ' . htmlspecialchars($usr['email']) . '</small><br>
                                                            <small><i class="bi bi-phone"></i> ' . htmlspecialchars($usr['phone'] ?? 'N/A') . '</small>
                                                          </td>';
                                                    echo '<td><span class="badge bg-' . $type_badge . '">' . strtoupper($usr['user_type']) . '</span></td>';
                                                    echo '<td>
                                                            <strong>' . formatCurrency($usr['total_invested']) . '</strong><br>
                                                            <small class="text-muted">' . $usr['investment_count'] . ' investments</small>
                                                          </td>';
                                                    echo '<td>
                                                            <strong>' . formatCurrency($usr['total_loans']) . '</strong><br>
                                                            <small class="text-muted">' . $usr['loan_count'] . ' loans</small>
                                                          </td>';
                                                    echo '<td>' . date('M d, Y', strtotime($usr['created_at'])) . '</td>';
                                                    echo '<td>
                                                            <div class="btn-group btn-group-sm">
                                                                <button class="btn btn-outline-primary" onclick="viewUser(' . $usr['id'] . ')" title="View Details">
                                                                    <i class="bi bi-eye"></i>
                                                                </button>
                                                                <button class="btn btn-outline-warning" onclick="editUser(' . $usr['id'] . ')" title="Edit User">
                                                                    <i class="bi bi-pencil"></i>
                                                                </button>';
                                                    
                                                    // Don't allow deleting current admin
                                                    if ($usr['id'] != $_SESSION['user_id']) {
                                                        echo '      <button class="btn btn-outline-danger" onclick="deleteUser(' . $usr['id'] . ', \'' . htmlspecialchars($usr['name']) . '\')" title="Delete User">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>';
                                                    }
                                                    
                                                    echo '      </div>
                                                          </td>';
                                                    echo '</tr>';
                                                }
                                            } else {
                                                echo '<tr><td colspan="7" class="text-center py-5 text-muted">No users found</td></tr>';
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

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-person-plus"></i> Add New User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="add-user.php" method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="user_type" class="form-label">User Type *</label>
                                <select class="form-select" id="user_type" name="user_type" required>
                                    <option value="user">Regular User</option>
                                    <option value="admin">Administrator</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="password" name="password" minlength="8" required>
                                <small class="text-muted">Minimum 8 characters</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="edit-user.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="edit_user_id" name="user_id">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="edit_email" name="email" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="edit_phone" name="phone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_user_type" class="form-label">User Type *</label>
                                <select class="form-select" id="edit_user_type" name="user_type" required>
                                    <option value="user">Regular User</option>
                                    <option value="admin">Administrator</option>
                                </select>
                            </div>
                        </div>

                        <hr>
                        <p class="text-muted"><small>Leave password fields empty to keep current password</small></p>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="edit_password" name="password" minlength="8">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="edit_confirm_password" name="confirm_password">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning"><i class="bi bi-check-circle"></i> Update User</button>
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

        // View user details
        function viewUser(id) {
            window.location.href = 'view-user.php?id=' + id;
        }

        // Edit user
        function editUser(id) {
            // Fetch user data via AJAX
            fetch('get-user.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_user_id').value = data.id;
                    document.getElementById('edit_name').value = data.name;
                    document.getElementById('edit_email').value = data.email;
                    document.getElementById('edit_phone').value = data.phone || '';
                    document.getElementById('edit_user_type').value = data.user_type;
                    
                    new bootstrap.Modal(document.getElementById('editUserModal')).show();
                })
                .catch(error => alert('Error loading user data'));
        }

        // Delete user
        function deleteUser(id, name) {
            if (confirm('Are you sure you want to delete user: ' + name + '?\n\nThis will also delete all their investments and loans!')) {
                window.location.href = 'delete-user.php?id=' + id;
            }
        }

        // Export to Excel
        function exportToExcel() {
            alert('Export feature coming soon!');
        }

        // Password match validation for add form
        document.querySelector('#addUserModal form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });

        // Password match validation for edit form
        document.querySelector('#editUserModal form').addEventListener('submit', function(e) {
            const password = document.getElementById('edit_password').value;
            const confirmPassword = document.getElementById('edit_confirm_password').value;
            
            if (password && password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });
    </script>
</body>
</html>
