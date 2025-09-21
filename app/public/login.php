<?php
// DEMO ONLY: This code is intentionally vulnerable to SQL injection for demonstration purposes.
// DO NOT USE IN PRODUCTION. See README for details.
// Log file path updated for container compatibility.
session_start();
require_once __DIR__ . '/../src/db.php';

$email = isset($_POST['email']) ? $_POST['email'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Intentionally vulnerable SQL (do not use in real apps!)
$sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password' LIMIT 1";
$result = $mysqli->query($sql);

// Logging
$log_entry = sprintf("[%s] POST /login email=%s password=%s query=%s\n",
    date('Y-m-d H:i:s'), $email, $password, $sql);
file_put_contents('/var/www/logs/requests.log', $log_entry, FILE_APPEND);

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $_SESSION['auth'] = true;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    header('Location: dashboard.php');
    exit;
} else {
    // Show error and link back
    echo '<!DOCTYPE html><html><head><title>Login Failed</title></head><body>';
    echo '<div style="margin:2em auto;max-width:400px;text-align:center;">';
    echo '<h2>Login failed</h2>';
    echo '<p>Invalid credentials or demo payload not successful.</p>';
    echo '<a href="index.php">Try again</a>';
    echo '</div></body></html>';
    exit;
}

