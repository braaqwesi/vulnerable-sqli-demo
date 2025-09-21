<?php
session_start();
require_once '/var/www/src/db.php';

// Check if user is logged in
if (!isset($_SESSION['auth'])) {
    header('Location: index.php');
    exit;
}

// Get current user info
$current_user_id = $_SESSION['user_id'] ?? 1;
$user_data = $mysqli->query('SELECT * FROM users WHERE id = ' . $current_user_id)->fetch_assoc();

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        $first_name = $_POST['first_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $address_line1 = $_POST['address_line1'] ?? '';
        $address_line2 = $_POST['address_line2'] ?? '';
        $city = $_POST['city'] ?? '';
        $state = $_POST['state'] ?? '';
        $zip_code = $_POST['zip_code'] ?? '';
        
        // Vulnerable SQL - no prepared statements
        $sql = "UPDATE users SET 
                first_name = '$first_name', 
                last_name = '$last_name', 
                phone = '$phone', 
                address_line1 = '$address_line1', 
                address_line2 = '$address_line2', 
                city = '$city', 
                state = '$state', 
                zip_code = '$zip_code' 
                WHERE id = $current_user_id";
        
        if ($mysqli->query($sql)) {
            $message = "Profile updated successfully!";
            // Refresh user data
            $user_data = $mysqli->query('SELECT * FROM users WHERE id = ' . $current_user_id)->fetch_assoc();
        } else {
            $error = "Error updating profile: " . $mysqli->error;
        }
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
        $new_password = $_POST['new_password'] ?? '';
        
        // Vulnerable SQL - no prepared statements
        $sql = "UPDATE users SET password = '$new_password' WHERE id = $current_user_id";
        
        if ($mysqli->query($sql)) {
            $message = "Password changed successfully!";
            // Refresh user data
            $user_data = $mysqli->query('SELECT * FROM users WHERE id = ' . $current_user_id)->fetch_assoc();
        } else {
            $error = "Error changing password: " . $mysqli->error;
        }
    }
}

// Mask sensitive data
function maskPassword($password) {
    return str_repeat('*', strlen($password));
}

function maskSSN($ssn) {
    return '***-**-' . substr($ssn, -4);
}

function maskTaxId($tax_id) {
    return 'TAX' . str_repeat('*', strlen($tax_id) - 3);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Vulnerable SQLi Demo</title>
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
            border-radius: 8px;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            margin-bottom: 16px;
        }
        
        .card:hover {
            box-shadow: var(--card-shadow-hover);
            transform: translateY(-2px);
        }
        
        .card-header {
            padding: 12px 16px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .card-body {
            padding: 16px;
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
        
        .security-info {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border-left: 4px solid var(--danger-color);
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 16px;
        }
        
        .masked-text {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: var(--danger-color);
        }
    </style>
</head>
<body>
    <!-- Modern Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
                <i class="bi bi-shield-check me-2"></i>
                <strong>Settings</strong>
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                        <div class="icon-circle icon-primary me-2" style="width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; color: white; background: var(--primary-color);">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <span><?php echo htmlspecialchars($user_data['first_name']); ?></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="dashboard.php"><i class="bi bi-house me-2"></i>Dashboard</a></li>
                        <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear me-2"></i>Settings</a></li>
                        <li><a class="dropdown-item" href="payments.php"><i class="bi bi-credit-card me-2"></i>Payment Details</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="dashboard.php" class="d-inline">
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
        
        <?php if (isset($message)): ?>
        <div class="alert alert-success alert-modern text-center mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <strong><?php echo htmlspecialchars($message); ?></strong>
        </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-modern text-center mb-4" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong><?php echo htmlspecialchars($error); ?></strong>
        </div>
        <?php endif; ?>

        <!-- Security Information -->
        <div class="security-info">
            <h5 class="text-danger mb-3"><i class="bi bi-shield-exclamation me-2"></i>Security Information</h5>
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-2">
                        <label class="form-label text-muted">Password</label>
                        <p class="masked-text mb-1"><?php echo maskPassword($user_data['password']); ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-2">
                        <label class="form-label text-muted">Social Security Number</label>
                        <p class="masked-text mb-1"><?php echo maskSSN($user_data['ssn']); ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-2">
                        <label class="form-label text-muted">Tax ID</label>
                        <p class="masked-text mb-1"><?php echo maskTaxId($user_data['tax_id']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Update Profile -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-person me-2"></i>Update Profile</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="update_profile">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">First Name</label>
                                        <input type="text" name="first_name" class="form-control" value="<?=htmlspecialchars($user_data['first_name'])?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" name="last_name" class="form-control" value="<?=htmlspecialchars($user_data['last_name'])?>" required>
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
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-check-circle me-2"></i>Update Profile
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Change Password -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-key me-2"></i>Change Password</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="change_password">
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" class="form-control" value="<?php echo maskPassword($user_data['password']); ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="text" name="new_password" class="form-control" placeholder="Enter new password" required>
                            </div>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Note:</strong> This form is vulnerable to SQL injection for demonstration purposes.
                            </div>
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="bi bi-check-circle me-2"></i>Change Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
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
