<?php
// DEMO ONLY: This page is intentionally insecure for SQL injection demonstration purposes.
// DO NOT USE IN PRODUCTION. See README for details.
// This page shows all database data without authentication - demonstrates data exposure vulnerability.

require_once '/var/www/src/db.php';

// Get all users data
$users_result = $mysqli->query("SELECT * FROM users ORDER BY id");
$users_data = [];
while ($row = $users_result->fetch_assoc()) {
    $users_data[] = $row;
}

// Get all credit cards data
$cards_result = $mysqli->query("SELECT * FROM credit_cards ORDER BY user_id, id");
$cards_data = [];
while ($row = $cards_result->fetch_assoc()) {
    $cards_data[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vulnerable SQLi Demo - All Database Data</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="alert alert-danger text-center" role="alert">
                    <strong>⚠️ DEMO ENVIRONMENT — DO NOT USE IN PRODUCTION</strong>
                </div>
                
                <div class="card shadow">
                    <div class="card-body">
                        <h3 class="card-title text-center text-danger mb-4">⚠️ All Database Data (No Authentication Required)</h3>
                        
                        <div class="alert alert-warning" role="alert">
                            <strong>🚨 Security Warning:</strong> This demonstrates the severe impact of data exposure vulnerabilities - 
                            all sensitive data (passwords, SSNs, credit cards, CVVs) is accessible without any authentication!
                        </div>
                        
                        <!-- Users Data -->
                        <h4 class="mt-4">Users Table (<?php echo count($users_data); ?> records)</h4>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Email</th>
                                        <th>Password</th>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>SSN</th>
                                        <th>Address</th>
                                        <th>DOB</th>
                                        <th>Tax ID</th>
                                        <th>Income</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users_data as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td class="text-danger fw-bold"><?php echo htmlspecialchars($user['password']); ?></td>
                                        <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                        <td class="text-danger fw-bold"><?php echo htmlspecialchars($user['ssn']); ?></td>
                                        <td><?php echo htmlspecialchars($user['address_line1'] . ', ' . $user['city'] . ', ' . $user['state'] . ' ' . $user['zip_code']); ?></td>
                                        <td><?php echo htmlspecialchars($user['date_of_birth']); ?></td>
                                        <td class="text-danger fw-bold"><?php echo htmlspecialchars($user['tax_id']); ?></td>
                                        <td>$<?php echo number_format($user['annual_income'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Credit Cards Data -->
                        <h4 class="mt-5">Credit Cards Table (<?php echo count($cards_data); ?> records)</h4>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>User ID</th>
                                        <th>Card Number</th>
                                        <th>Cardholder</th>
                                        <th>Expiry</th>
                                        <th>CVV</th>
                                        <th>Type</th>
                                        <th>Processor</th>
                                        <th>Last 4</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cards_data as $card): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($card['id']); ?></td>
                                        <td><?php echo htmlspecialchars($card['user_id']); ?></td>
                                        <td class="text-danger fw-bold"><?php echo htmlspecialchars($card['card_number']); ?></td>
                                        <td><?php echo htmlspecialchars($card['cardholder_name']); ?></td>
                                        <td><?php echo htmlspecialchars($card['expiry_month'] . '/' . $card['expiry_year']); ?></td>
                                        <td class="text-danger fw-bold"><?php echo htmlspecialchars($card['cvv']); ?></td>
                                        <td><?php echo htmlspecialchars($card['card_type']); ?></td>
                                        <td><?php echo htmlspecialchars($card['payment_processor']); ?></td>
                                        <td><?php echo htmlspecialchars($card['last_four_digits']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-danger mt-4" role="alert">
                            <h5 class="alert-heading">🚨 Critical Security Impact</h5>
                            <p><strong>This demonstrates the real-world impact of SQL injection and data exposure vulnerabilities:</strong></p>
                            <ul class="mb-0">
                                <li><strong>Authentication Bypass:</strong> Sensitive data accessible without login</li>
                                <li><strong>PII Exposure:</strong> Social Security Numbers, addresses, phone numbers</li>
                                <li><strong>Financial Data:</strong> Credit card numbers, CVVs, payment processors</li>
                                <li><strong>Identity Theft Risk:</strong> Complete personal information available</li>
                                <li><strong>Compliance Violations:</strong> GDPR, PCI-DSS, SOX violations</li>
                            </ul>
                        </div>

                        <div class="text-center mt-4">
                            <a href="index.php" class="btn btn-primary">Back to Login</a>
                            <form method="POST" action="index.php" class="d-inline">
                                <input type="hidden" name="action" value="reset_db">
                                <button type="submit" class="btn btn-warning" onclick="return confirm('Reset database to original state?')">Reset Database</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

