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
$new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
// Intentionally vulnerable SQL
$sql = "UPDATE users SET password = '$new_password' WHERE email = '$email_target'";
$mysqli->query($sql);
// Logging
$log_entry = sprintf("[%s] POST /update email_target=%s new_password=%s query=%s\n",
    date('Y-m-d H:i:s'), $email_target, $new_password, $sql);
file_put_contents('/var/www/logs/requests.log', $log_entry, FILE_APPEND);
header('Location: dashboard.php');
exit;

