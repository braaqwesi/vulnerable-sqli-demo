<?php
// DEMO ONLY: This dashboard is intentionally insecure for SQLi demonstration. Do not use in production.
session_start();
if (!isset($_SESSION['auth'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/../src/db.php';

// Handle clear logs action
if (isset($_POST['action']) && $_POST['action'] === 'clear_logs') {
    file_put_contents('/var/www/logs/requests.log', '');
    $log_cleared = true;
}

// Handle logout action
if (isset($_POST['action']) && $_POST['action'] === 'logout') {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Get current user info from session
$current_user_id = $_SESSION['user_id'] ?? 1; // Default to admin if not set
$current_user_email = $_SESSION['user_email'] ?? 'admin@example.com';

// Check if current user is admin (ID 1 or admin@example.com)
$is_admin = ($current_user_id == 1 || $current_user_email === 'admin@example.com');

// Fetch users based on role
if ($is_admin) {
    // Admin sees all users
    $users = $mysqli->query('SELECT * FROM users ORDER BY id');
    $user_data = $mysqli->query('SELECT * FROM users WHERE id = ' . $current_user_id)->fetch_assoc();
} else {
    // Regular users only see their own data
    $users = $mysqli->query('SELECT * FROM users WHERE id = ' . $current_user_id);
    $user_data = $mysqli->query('SELECT * FROM users WHERE id = ' . $current_user_id)->fetch_assoc();
}

// Get credit cards for current user
$credit_cards = $mysqli->query('SELECT * FROM credit_cards WHERE user_id = ' . $current_user_id);

// Tail last 20 lines of log
$logfile = '/var/www/logs/requests.log';
$log_lines = [];
if (file_exists($logfile)) {
    $log_lines = array_slice(file($logfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES), -20);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_admin ? 'Admin Dashboard' : 'User Dashboard'; ?> - Vulnerable SQLi Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #64748b;
            --success-color: #059669;
            --warning-color: #d97706;
            --danger-color: #dc2626;
            --dark-color: #1e293b;
            --light-bg: #f8fafc;
            --card-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --card-shadow-hover: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%);
            box-shadow: var(--card-shadow);
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: var(--card-shadow-hover);
            transform: translateY(-2px);
        }
        
        .profile-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-left: 4px solid var(--primary-color);
        }
        
        .action-btn {
            border-radius: 8px;
            padding: 12px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
        }
        
        .action-btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--card-shadow-hover);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%);
        }
        
        .btn-warning {
            background: linear-gradient(135deg, var(--warning-color) 0%, #b45309 100%);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, var(--danger-color) 0%, #b91c1c 100%);
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--success-color) 0%, #047857 100%);
        }
        
        .stats-card {
            background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
        }
        
        .icon-circle {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
        }
        
        .icon-primary { background: var(--primary-color); }
        .icon-success { background: var(--success-color); }
        .icon-warning { background: var(--warning-color); }
        .icon-danger { background: var(--danger-color); }
        
        .table-modern {
            border-radius: 8px;
            overflow: hidden;
        }
        
        .table-modern thead th {
            background: var(--dark-color);
            color: white;
            border: none;
            font-weight: 600;
        }
        
        .table-modern tbody tr {
            transition: background-color 0.2s ease;
        }
        
        .table-modern tbody tr:hover {
            background-color: #f8fafc;
        }
        
        .alert-modern {
            border: none;
            border-radius: 8px;
            border-left: 4px solid;
        }
        
        .alert-danger {
            border-left-color: var(--danger-color);
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        }
        
        .alert-success {
            border-left-color: var(--success-color);
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        }
        
        .profile-section {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
        }
        
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            margin-top: 16px;
        }
        
        .feature-card {
            background: white;
            border-radius: 8px;
            padding: 16px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
        }
        
        .feature-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: var(--card-shadow-hover);
        }
        
        .feature-card h6 {
            margin-top: 8px;
            color: var(--dark-color);
            font-weight: 600;
            font-size: 14px;
        }
        
        .feature-card p {
            color: var(--secondary-color);
            font-size: 12px;
            margin-bottom: 0;
        }
        
        .icon-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: white;
        }
        
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            margin-bottom: 16px;
        }
        
        .card-header {
            padding: 12px 16px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .card-body {
            padding: 16px;
        }
        
        .table-modern {
            font-size: 13px;
        }
        
        .table-modern th {
            padding: 8px 12px;
            font-size: 12px;
        }
        
        .table-modern td {
            padding: 8px 12px;
        }
        
        .navbar {
            padding: 8px 0;
        }
        
        .navbar-brand {
            font-size: 18px;
        }
        
        .stats-card {
            padding: 12px !important;
        }
        
        .stats-card h5 {
            font-size: 20px;
            margin-bottom: 4px;
        }
        
        .stats-card small {
            font-size: 11px;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-header {
            padding: 12px 20px;
        }
        
        .modal-footer {
            padding: 12px 20px;
        }
        
        .alert-modern {
            padding: 8px 16px;
            margin-bottom: 16px;
        }
        
        .btn-sm {
            padding: 4px 8px;
            font-size: 12px;
        }
        
        .form-control {
            padding: 6px 12px;
            font-size: 14px;
        }
        
        .form-label {
            font-size: 13px;
            margin-bottom: 4px;
        }
        
        .mb-3 {
            margin-bottom: 12px !important;
        }
        
        .mb-4 {
            margin-bottom: 16px !important;
        }
        
        .mt-4 {
            margin-top: 16px !important;
        }
        
        .mt-5 {
            margin-top: 20px !important;
        }
        
        .p-3 {
            padding: 12px !important;
        }
        
        .p-0 {
            padding: 0 !important;
        }
        
        .textarea-log {
            font-size: 11px;
            line-height: 1.3;
        }
        
        .profile-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
        }
        
        .profile-section h2 {
            font-size: 20px;
            margin-bottom: 8px;
        }
        
        .profile-section p {
            font-size: 14px;
            margin-bottom: 4px;
        }
    </style>
</head>
<body>
    <!-- Modern Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <i class="bi bi-shield-check me-2"></i>
                <strong><?php echo $is_admin ? 'Admin Portal' : 'User Portal'; ?></strong>
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                        <div class="icon-circle icon-primary me-2">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <span><?php echo htmlspecialchars($user_data['first_name']); ?></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear me-2"></i>Settings</a></li>
                        <li><a class="dropdown-item" href="payments.php"><i class="bi bi-credit-card me-2"></i>Payment Details</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="" class="d-inline">
                                <input type="hidden" name="action" value="logout">
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Alert Messages -->
        <div class="alert alert-danger alert-modern text-center mb-4" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Demo environment — Do not use in production</strong>
        </div>
        
        <?php if (isset($log_cleared)): ?>
        <div class="alert alert-success alert-modern text-center mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <strong>Logs cleared successfully!</strong>
        </div>
        <?php endif; ?>

        <!-- Welcome Section -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="profile-section">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-1">
                                <i class="bi bi-<?php echo $is_admin ? 'shield-check' : 'person-circle'; ?> me-2"></i>
                                Welcome back, <?php echo htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name']); ?>!
                            </h2>
                            <p class="text-muted mb-0">
                                <i class="bi bi-envelope me-1"></i>
                                <?php echo htmlspecialchars($user_data['email']); ?>
                                <?php if ($is_admin): ?>
                                <span class="badge bg-primary ms-2">Admin</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="stats-card rounded">
                                <h5 class="mb-1"><?php echo $is_admin ? '12' : '1'; ?></h5>
                                <small class="text-muted"><?php echo $is_admin ? 'Total Users' : 'Account'; ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Main Content -->
        <div class="row">
            <div class="col-md-8">
                <?php if ($is_admin): ?>
                <!-- Admin View: All Users -->
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-people me-2"></i>All Users (Admin View)</h5>
                        <span class="badge bg-primary"><?php echo $users->num_rows; ?> Users</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-modern mb-0">
                                <thead>
                                    <tr>
                                        <th><i class="bi bi-hash me-1"></i>ID</th>
                                        <th><i class="bi bi-envelope me-1"></i>Email</th>
                                        <th><i class="bi bi-key me-1"></i>Password</th>
                                        <th><i class="bi bi-person me-1"></i>Name</th>
                                        <th><i class="bi bi-telephone me-1"></i>Phone</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($users) { while ($row = $users->fetch_assoc()) { ?>
                                    <tr>
                                        <td><span class="badge bg-secondary"><?=htmlspecialchars($row['id'])?></span></td>
                                        <td><?=htmlspecialchars($row['email'])?></td>
                                        <td><span class="text-danger fw-bold"><?=htmlspecialchars($row['password'])?></span></td>
                                        <td><?=htmlspecialchars($row['first_name'] . ' ' . $row['last_name'])?></td>
                                        <td><?=htmlspecialchars($row['phone'])?></td>
                                    </tr>
                                <?php }} ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Admin Actions -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Update Password</h6>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="update.php">
                                    <div class="mb-3">
                                        <label class="form-label">Target Email</label>
                                        <input type="text" name="email_target" class="form-control" placeholder="user@example.com" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">New Password</label>
                                        <input type="text" name="new_password" class="form-control" placeholder="New password" required>
                                    </div>
                                    <button class="btn btn-warning action-btn w-100" type="submit">
                                        <i class="bi bi-check-circle me-2"></i>Update Password
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="bi bi-trash me-2"></i>Delete User</h6>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="delete.php">
                                    <div class="mb-3">
                                        <label class="form-label">Target Email</label>
                                        <input type="text" name="email_target" class="form-control" placeholder="user@example.com" required>
                                    </div>
                                    <button class="btn btn-danger action-btn w-100" type="submit" onclick="return confirm('Are you sure you want to delete this user?')">
                                        <i class="bi bi-exclamation-triangle me-2"></i>Delete User
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <?php else: ?>
                <!-- Regular User View: Profile Management -->
                
                <!-- Profile Management Features -->
                <div class="feature-grid">
                    <div class="feature-card" onclick="showProfileModal()">
                        <div class="icon-circle icon-primary mx-auto">
                            <i class="bi bi-person"></i>
                        </div>
                        <h6>Update Profile</h6>
                        <p>Edit your personal information and contact details</p>
                    </div>
                    
                    <div class="feature-card" onclick="showPasswordModal()">
                        <div class="icon-circle icon-warning mx-auto">
                            <i class="bi bi-key"></i>
                        </div>
                        <h6>Change Password</h6>
                        <p>Update your account password for better security</p>
                    </div>
                    
                    <div class="feature-card" onclick="showCardModal()">
                        <div class="icon-circle icon-danger mx-auto">
                            <i class="bi bi-credit-card"></i>
                        </div>
                        <h6>Change Card PIN</h6>
                        <p>Update your credit card PIN numbers</p>
                    </div>
                </div>

                <!-- Profile Information Card -->
                <div class="card profile-card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-person-circle me-2"></i>Your Profile Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="profile-info-grid">
                            <div>
                                <h6 class="text-primary"><i class="bi bi-person me-2"></i>Personal Information</h6>
                                <div class="mb-2">
                                    <label class="form-label text-muted">Full Name</label>
                                    <p class="fw-bold mb-1"><?=htmlspecialchars($user_data['first_name'] . ' ' . $user_data['last_name'])?></p>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label text-muted">Email Address</label>
                                    <p class="fw-bold mb-1"><?=htmlspecialchars($user_data['email'])?></p>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label text-muted">Phone Number</label>
                                    <p class="fw-bold mb-1"><?=htmlspecialchars($user_data['phone'])?></p>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label text-muted">Date of Birth</label>
                                    <p class="fw-bold mb-1"><?=htmlspecialchars($user_data['date_of_birth'])?></p>
                                </div>
                            </div>
                            <div>
                                <h6 class="text-primary"><i class="bi bi-geo-alt me-2"></i>Address Information</h6>
                                <div class="mb-2">
                                    <label class="form-label text-muted">Street Address</label>
                                    <p class="fw-bold mb-1"><?=htmlspecialchars($user_data['address_line1'] ?? '')?></p>
                                    <?php if ($user_data['address_line2']): ?>
                                    <p class="fw-bold mb-1"><?=htmlspecialchars($user_data['address_line2'] ?? '')?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label text-muted">City, State ZIP</label>
                                    <p class="fw-bold mb-1"><?=htmlspecialchars($user_data['city'] . ', ' . $user_data['state'] . ' ' . $user_data['zip_code'])?></p>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label text-muted">Country</label>
                                    <p class="fw-bold mb-1"><?=htmlspecialchars($user_data['country'])?></p>
                                </div>
                            </div>
                            <div>
                                <h6 class="text-success"><i class="bi bi-currency-dollar me-2"></i>Financial Information</h6>
                                <div class="mb-2">
                                    <label class="form-label text-muted">Annual Income</label>
                                    <p class="fw-bold text-success mb-1">$<?=number_format($user_data['annual_income'], 2)?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php endif; ?>
            </div>
            <?php if ($is_admin): ?>
            <div class="col-md-4">
                <!-- Activity Logs Card (Admin Only) -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Activity Logs</h6>
                        <form method="POST" action="" class="d-inline">
                            <input type="hidden" name="action" value="clear_logs">
                            <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Clear all logs?')">
                                <i class="bi bi-trash me-1"></i>Clear
                            </button>
                        </form>
                    </div>
                    <div class="card-body p-0">
                        <div class="p-2">
                            <small class="text-muted">Last 20 entries</small>
                        </div>
                        <textarea class="form-control border-0 textarea-log" rows="18" readonly style="resize: none; font-family: 'Courier New', monospace;"><?php echo htmlspecialchars(implode("\n", $log_lines)); ?></textarea>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modals for User Actions -->
    <!-- Profile Update Modal -->
    <div class="modal fade" id="profileModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person me-2"></i>Update Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="update.php">
                        <input type="hidden" name="email_target" value="<?=htmlspecialchars($user_data['email'])?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">First Name</label>
                                    <input type="text" name="first_name" class="form-control" value="<?=htmlspecialchars($user_data['first_name'])?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" name="last_name" class="form-control" value="<?=htmlspecialchars($user_data['last_name'])?>">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="<?=htmlspecialchars($user_data['phone'] ?? '')?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address Line 1</label>
                            <input type="text" name="address_line1" class="form-control" value="<?=htmlspecialchars($user_data['address_line1'] ?? '')?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address Line 2</label>
                            <input type="text" name="address_line2" class="form-control" value="<?=htmlspecialchars($user_data['address_line2'] ?? '')?>">
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">City</label>
                                    <input type="text" name="city" class="form-control" value="<?=htmlspecialchars($user_data['city'] ?? '')?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">State</label>
                                    <input type="text" name="state" class="form-control" value="<?=htmlspecialchars($user_data['state'] ?? '')?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">ZIP Code</label>
                                    <input type="text" name="zip_code" class="form-control" value="<?=htmlspecialchars($user_data['zip_code'] ?? '')?>">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary action-btn">
                        <i class="bi bi-check-circle me-2"></i>Update Profile
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Password Change Modal -->
    <div class="modal fade" id="passwordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-key me-2"></i>Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="update.php">
                        <input type="hidden" name="email_target" value="<?=htmlspecialchars($user_data['email'])?>">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-control" value="<?=htmlspecialchars($user_data['password'])?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="text" name="new_password" class="form-control" placeholder="Enter new password" required>
                        </div>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Note:</strong> This form is vulnerable to SQL injection for demonstration purposes.
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning action-btn">
                        <i class="bi bi-check-circle me-2"></i>Change Password
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Card PIN Change Modal -->
    <div class="modal fade" id="cardModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-credit-card me-2"></i>Change Card PIN</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Feature Coming Soon:</strong> Card PIN management will be available in the next update.
                    </div>
                    <div class="row">
                        <?php 
                        $credit_cards->data_seek(0); // Reset result pointer
                        while ($card = $credit_cards->fetch_assoc()): 
                        ?>
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title"><?=htmlspecialchars($card['card_type'])?> •••• <?=htmlspecialchars($card['last_four_digits'])?></h6>
                                    <p class="card-text">
                                        <small class="text-muted">Expires: <?=htmlspecialchars($card['expiry_month'] . '/' . $card['expiry_year'])?></small><br>
                                        <small class="text-muted">Processor: <?=htmlspecialchars($card['payment_processor'])?></small>
                                    </p>
                                    <button class="btn btn-outline-primary btn-sm" disabled>
                                        <i class="bi bi-gear me-1"></i>Update PIN
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        function showProfileModal() {
            new bootstrap.Modal(document.getElementById('profileModal')).show();
        }
        
        function showPasswordModal() {
            new bootstrap.Modal(document.getElementById('passwordModal')).show();
        }
        
        function showCardModal() {
            new bootstrap.Modal(document.getElementById('cardModal')).show();
        }
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                if (alert.classList.contains('alert-success')) {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.remove();
                    }, 500);
                }
            });
        }, 5000);
    </script>
</body>
</html>

