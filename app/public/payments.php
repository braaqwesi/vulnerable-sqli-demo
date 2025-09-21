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

// Get credit cards for current user
$credit_cards = $mysqli->query('SELECT * FROM credit_cards WHERE user_id = ' . $current_user_id);

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action']) && $_POST['action'] === 'change_card_pin') {
        $card_id = $_POST['card_id'] ?? '';
        $new_cvv = $_POST['new_cvv'] ?? '';
        
        // Vulnerable SQL - no prepared statements
        $sql = "UPDATE credit_cards SET cvv = '$new_cvv' WHERE id = $card_id AND user_id = $current_user_id";
        
        if ($mysqli->query($sql)) {
            $message = "Card PIN (CVV) updated successfully!";
            // Refresh credit cards data
            $credit_cards = $mysqli->query('SELECT * FROM credit_cards WHERE user_id = ' . $current_user_id);
        } else {
            $error = "Error updating card PIN: " . $mysqli->error;
        }
    }
}

// Mask card number
function maskCardNumber($card_number) {
    $cleaned = preg_replace('/[^0-9]/', '', $card_number);
    if (strlen($cleaned) >= 4) {
        return '****-****-****-' . substr($cleaned, -4);
    }
    return $card_number;
}

// Mask CVV
function maskCVV($cvv) {
    return str_repeat('*', strlen($cvv));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Details - Vulnerable SQLi Demo</title>
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
        
        .payment-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-left: 4px solid var(--primary-color);
            margin-bottom: 16px;
        }
        
        .card-type-badge {
            font-size: 11px;
            padding: 4px 8px;
        }
        
        .debit-badge { background: var(--success-color); }
        .credit-badge { background: var(--warning-color); }
        .prepaid-badge { background: var(--danger-color); }
        
        .masked-text {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: var(--danger-color);
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
        
        .icon-primary { background: var(--primary-color); }
        .icon-success { background: var(--success-color); }
        .icon-warning { background: var(--warning-color); }
        .icon-danger { background: var(--danger-color); }
    </style>
</head>
<body>
    <!-- Modern Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
                <i class="bi bi-credit-card me-2"></i>
                <strong>Payment Details</strong>
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

        <!-- Payment Cards -->
        <div class="row">
            <?php if ($credit_cards) { while ($card = $credit_cards->fetch_assoc()) { ?>
            <div class="col-md-4">
                <div class="card payment-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="bi bi-credit-card me-2"></i>
                            <?php echo htmlspecialchars($card['card_type']); ?>
                        </h6>
                        <span class="badge card-type-badge <?php echo strtolower($card['card_type']); ?>-badge">
                            <?php echo htmlspecialchars($card['card_type']); ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label text-muted">Card Number</label>
                            <p class="masked-text mb-1"><?php echo maskCardNumber($card['card_number']); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Cardholder Name</label>
                            <p class="fw-bold mb-1"><?php echo htmlspecialchars($card['cardholder_name']); ?></p>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Expiry</label>
                                    <p class="fw-bold mb-1"><?php echo htmlspecialchars($card['expiry_month'] . '/' . $card['expiry_year']); ?></p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">CVV</label>
                                    <p class="masked-text mb-1"><?php echo maskCVV($card['cvv']); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Payment Processor</label>
                            <p class="fw-bold mb-1"><?php echo htmlspecialchars($card['payment_processor']); ?></p>
                        </div>
                        
                        <!-- Change Card PIN Form -->
                        <form method="POST" class="mt-3">
                            <input type="hidden" name="action" value="change_card_pin">
                            <input type="hidden" name="card_id" value="<?php echo $card['id']; ?>">
                            <div class="mb-3">
                                <label class="form-label">New CVV/PIN</label>
                                <input type="text" name="new_cvv" class="form-control" placeholder="Enter new CVV" required>
                            </div>
                            <button type="submit" class="btn btn-warning w-100 btn-sm">
                                <i class="bi bi-gear me-1"></i>Change PIN
                            </button>
                        </form>
                        
                        <div class="alert alert-warning mt-3" style="font-size: 11px; padding: 8px;">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            <strong>Note:</strong> This form is vulnerable to SQL injection for demonstration purposes.
                        </div>
                    </div>
                </div>
            </div>
            <?php }} ?>
        </div>
        
        <!-- Summary -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Payment Summary</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="icon-circle icon-success mx-auto mb-2">
                            <i class="bi bi-credit-card"></i>
                        </div>
                        <h6>Debit Cards</h6>
                        <p class="text-muted"><?php 
                        $debit_count = $mysqli->query("SELECT COUNT(*) as count FROM credit_cards WHERE user_id = $current_user_id AND card_type = 'Debit'")->fetch_assoc()['count'];
                        echo $debit_count; 
                        ?> Active</p>
                    </div>
                    <div class="col-md-4">
                        <div class="icon-circle icon-warning mx-auto mb-2">
                            <i class="bi bi-credit-card"></i>
                        </div>
                        <h6>Credit Cards</h6>
                        <p class="text-muted"><?php 
                        $credit_count = $mysqli->query("SELECT COUNT(*) as count FROM credit_cards WHERE user_id = $current_user_id AND card_type = 'Credit'")->fetch_assoc()['count'];
                        echo $credit_count; 
                        ?> Active</p>
                    </div>
                    <div class="col-md-4">
                        <div class="icon-circle icon-danger mx-auto mb-2">
                            <i class="bi bi-credit-card"></i>
                        </div>
                        <h6>Prepaid Cards</h6>
                        <p class="text-muted"><?php 
                        $prepaid_count = $mysqli->query("SELECT COUNT(*) as count FROM credit_cards WHERE user_id = $current_user_id AND card_type = 'Prepaid'")->fetch_assoc()['count'];
                        echo $prepaid_count; 
                        ?> Active</p>
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

