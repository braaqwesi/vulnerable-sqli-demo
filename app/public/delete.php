<?php
// DEMO ONLY: This code is intentionally vulnerable to SQL injection for demonstration purposes.
// DO NOT USE IN PRODUCTION. See README for details.
// Log file path updated for container compatibility.
session_start();
if (!isset($_SESSION['auth'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/../src/db.php';
$email_target = isset($_POST['email_target']) ? $_POST['email_target'] : '';
// Intentionally vulnerable SQL
$sql = "DELETE FROM users WHERE email = '$email_target'";
$mysqli->query($sql);
// Logging
$log_entry = sprintf("[%s] POST /delete email_target=%s query=%s\n",
    date('Y-m-d H:i:s'), $email_target, $sql);
file_put_contents('/var/www/logs/requests.log', $log_entry, FILE_APPEND);
header('Location: dashboard.php');
exit;

