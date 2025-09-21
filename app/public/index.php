<?php
// DEMO ONLY: This page is intentionally insecure for SQL injection demonstration purposes.
// DO NOT USE IN PRODUCTION. See README for details.
// For demo: email input is type=text to allow SQLi payloads.

session_start();

// Redirect to dashboard if already logged in
if (isset($_SESSION['auth'])) {
    header('Location: dashboard.php');
    exit;
}

// Handle view all data action (no auth required) - demonstrates data exposure vulnerability
if (isset($_POST['action']) && $_POST['action'] === 'view_all_data') {
    header('Location: viewdata.php');
    exit;
}

// Handle reset DB action (no auth required)
if (isset($_POST['action']) && $_POST['action'] === 'reset_db') {
    // Reset DB by dropping and recreating all tables using direct MySQL connection
    require_once '/var/www/src/db.php';
    
    // Drop all tables (credit_cards first due to foreign key constraint)
    $mysqli->query("DROP TABLE IF EXISTS credit_cards");
    $mysqli->query("DROP TABLE IF EXISTS users");
    
    // Recreate users table with enhanced schema
    $mysqli->query("CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(200) NOT NULL,
        password VARCHAR(200) NOT NULL,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        ssn VARCHAR(11),
        address_line1 VARCHAR(200),
        address_line2 VARCHAR(200),
        city VARCHAR(100),
        state VARCHAR(50),
        zip_code VARCHAR(10),
        country VARCHAR(50),
        date_of_birth DATE,
        tax_id VARCHAR(20),
        annual_income DECIMAL(12,2),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Recreate credit_cards table
    $mysqli->query("CREATE TABLE credit_cards (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        card_number VARCHAR(19) NOT NULL,
        cardholder_name VARCHAR(200) NOT NULL,
        expiry_month VARCHAR(2) NOT NULL,
        expiry_year VARCHAR(4) NOT NULL,
        cvv VARCHAR(4) NOT NULL,
        card_type VARCHAR(20),
        billing_address_line1 VARCHAR(200),
        billing_address_line2 VARCHAR(200),
        billing_city VARCHAR(100),
        billing_state VARCHAR(50),
        billing_zip VARCHAR(10),
        billing_country VARCHAR(50),
        payment_processor VARCHAR(50),
        last_four_digits VARCHAR(4),
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    
    // Insert enhanced demo data
    $mysqli->query("INSERT INTO users (email, password, first_name, last_name, phone, ssn, address_line1, address_line2, city, state, zip_code, country, date_of_birth, tax_id, annual_income) VALUES
        ('admin@example.com', 'adminpass', 'John', 'Administrator', '(555) 123-4567', '123-45-6789', '123 Main St', 'Suite 100', 'New York', 'NY', '10001', 'USA', '1985-03-15', 'TAX123456789', 95000.00),
        ('alice@example.com', 'alice123', 'Alice', 'Johnson', '(555) 234-5678', '234-56-7890', '456 Oak Ave', NULL, 'Los Angeles', 'CA', '90210', 'USA', '1990-07-22', 'TAX234567890', 75000.00),
        ('bob@example.com', 'bobpass', 'Robert', 'Smith', '(555) 345-6789', '345-67-8901', '789 Pine St', 'Apt 2B', 'Chicago', 'IL', '60601', 'USA', '1988-11-08', 'TAX345678901', 82000.00),
        ('carol@example.com', 'carolpw', 'Carol', 'Williams', '(555) 456-7890', '456-78-9012', '321 Elm St', NULL, 'Houston', 'TX', '77001', 'USA', '1992-05-14', 'TAX456789012', 68000.00),
        ('dave@example.com', 'davepw', 'David', 'Brown', '(555) 567-8901', '567-89-0123', '654 Maple Dr', 'Unit 5', 'Phoenix', 'AZ', '85001', 'USA', '1987-09-30', 'TAX567890123', 78000.00),
        ('eve@example.com', 'evepw', 'Eve', 'Davis', '(555) 678-9012', '678-90-1234', '987 Cedar Ln', NULL, 'Philadelphia', 'PA', '19101', 'USA', '1991-12-03', 'TAX678901234', 71000.00),
        ('frank@example.com', 'frank123', 'Frank', 'Miller', '(555) 789-0123', '789-01-2345', '147 Birch St', 'Apt 3A', 'San Antonio', 'TX', '78201', 'USA', '1986-08-17', 'TAX789012345', 73000.00),
        ('grace@example.com', 'grace456', 'Grace', 'Wilson', '(555) 890-1234', '890-12-3456', '258 Spruce Ave', NULL, 'San Diego', 'CA', '92101', 'USA', '1989-04-25', 'TAX890123456', 79000.00),
        ('henry@example.com', 'henry789', 'Henry', 'Moore', '(555) 901-2345', '901-23-4567', '369 Walnut Rd', 'Suite 200', 'Dallas', 'TX', '75201', 'USA', '1984-01-12', 'TAX901234567', 85000.00),
        ('iris@example.com', 'iris000', 'Iris', 'Taylor', '(555) 012-3456', '012-34-5678', '741 Poplar St', NULL, 'San Jose', 'CA', '95101', 'USA', '1993-06-19', 'TAX012345678', 72000.00),
        ('jack@example.com', 'jack111', 'Jack', 'Anderson', '(555) 123-4567', '123-45-6789', '852 Hickory Dr', 'Apt 4B', 'Austin', 'TX', '73301', 'USA', '1985-10-07', 'TAX123456789', 76000.00),
        ('kate@example.com', 'kate222', 'Kate', 'Thomas', '(555) 234-5678', '234-56-7890', '963 Ash St', NULL, 'Jacksonville', 'FL', '32201', 'USA', '1990-02-28', 'TAX234567890', 74000.00)");
    
    // Insert credit card data
    $mysqli->query("INSERT INTO credit_cards (user_id, card_number, cardholder_name, expiry_month, expiry_year, cvv, card_type, billing_address_line1, billing_address_line2, billing_city, billing_state, billing_zip, billing_country, payment_processor, last_four_digits) VALUES
        (1, '4532-1234-5678-9012', 'John Administrator', '12', '2026', '123', 'Visa', '123 Main St', 'Suite 100', 'New York', 'NY', '10001', 'USA', 'Stripe', '9012'),
        (1, '5555-4444-3333-2222', 'John Administrator', '08', '2025', '456', 'Mastercard', '123 Main St', 'Suite 100', 'New York', 'NY', '10001', 'USA', 'PayPal', '2222'),
        (2, '4111-1111-1111-1111', 'Alice Johnson', '03', '2027', '789', 'Visa', '456 Oak Ave', NULL, 'Los Angeles', 'CA', '90210', 'USA', 'Square', '1111'),
        (3, '5555-5555-5555-4444', 'Robert Smith', '11', '2025', '321', 'Mastercard', '789 Pine St', 'Apt 2B', 'Chicago', 'IL', '60601', 'USA', 'Stripe', '4444'),
        (4, '4000-0000-0000-0002', 'Carol Williams', '06', '2026', '654', 'Visa', '321 Elm St', NULL, 'Houston', 'TX', '77001', 'USA', 'Authorize.Net', '0002'),
        (5, '5555-5555-5555-5555', 'David Brown', '09', '2025', '987', 'Mastercard', '654 Maple Dr', 'Unit 5', 'Phoenix', 'AZ', '85001', 'USA', 'PayPal', '5555'),
        (6, '4242-4242-4242-4242', 'Eve Davis', '04', '2027', '147', 'Visa', '987 Cedar Ln', NULL, 'Philadelphia', 'PA', '19101', 'USA', 'Stripe', '4242'),
        (7, '5555-5555-5555-6666', 'Frank Miller', '12', '2025', '258', 'Mastercard', '147 Birch St', 'Apt 3A', 'San Antonio', 'TX', '78201', 'USA', 'Square', '6666'),
        (8, '4000-0000-0000-0003', 'Grace Wilson', '07', '2026', '369', 'Visa', '258 Spruce Ave', NULL, 'San Diego', 'CA', '92101', 'USA', 'Authorize.Net', '0003'),
        (9, '5555-5555-5555-7777', 'Henry Moore', '02', '2025', '741', 'Mastercard', '369 Walnut Rd', 'Suite 200', 'Dallas', 'TX', '75201', 'USA', 'PayPal', '7777'),
        (10, '4111-1111-1111-1112', 'Iris Taylor', '10', '2027', '852', 'Visa', '741 Poplar St', NULL, 'San Jose', 'CA', '95101', 'USA', 'Stripe', '1112'),
        (11, '5555-5555-5555-8888', 'Jack Anderson', '05', '2025', '963', 'Mastercard', '852 Hickory Dr', 'Apt 4B', 'Austin', 'TX', '73301', 'USA', 'Square', '8888'),
        (12, '4000-0000-0000-0004', 'Kate Thomas', '08', '2026', '159', 'Visa', '963 Ash St', NULL, 'Jacksonville', 'FL', '32201', 'USA', 'Authorize.Net', '0004')");
    
    $message = "Database reset to original state with enhanced demo data";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vulnerable SQLi Demo - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="alert alert-danger text-center" role="alert">
                    <strong>Demo environment — Do not use in production</strong>
                </div>
                <?php if (isset($message)): ?>
                <div class="alert alert-info text-center" role="alert">
                    <?= htmlspecialchars($message) ?>
                </div>
                <?php endif; ?>
                <div class="card shadow">
                    <div class="card-body">
                        <h3 class="card-title mb-4 text-center">Login</h3>
                        <form method="POST" action="login.php">
                            <div class="mb-3">
                                <label for="email" class="form-label">Username or Email</label>
                                <input type="text" class="form-control" id="email" name="email" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3 text-danger small">
                                This login is intentionally vulnerable to SQL injection. <br>
                                Try payloads like <code>' OR 1=1 -- </code> in the email or password field.
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                    </div>
                </div>
                
                <!-- Database Management (No Auth Required) -->
                <div class="card shadow mt-3">
                    <div class="card-body">
                        <h5 class="card-title text-center">Database Management</h5>
                        <div class="text-center">
                            <form method="POST" action="" class="d-inline me-2">
                                <input type="hidden" name="action" value="view_all_data">
                                <button type="submit" class="btn btn-info">View All Data</button>
                            </form>
                            <form method="POST" action="" class="d-inline">
                                <input type="hidden" name="action" value="reset_db">
                                <button type="submit" class="btn btn-warning" onclick="return confirm('Reset database to original state? This will restore all users and credit cards.')">Reset DB</button>
                            </form>
                        </div>
                        <div class="mt-2 text-center small text-muted">
                            View all data or reset database to original state (no authentication required)
                        </div>
                    </div>
                </div>

                
            </div>
        </div>
    </div>
</body>
</html>

