<?php
// DEMO ONLY: Insecure DB connection for SQLi demo. Do not use in production.
$mysqli = new mysqli(
    getenv('MYSQL_HOST') ?: 'mysql',
    getenv('MYSQL_USER') ?: 'demo',
    getenv('MYSQL_PASSWORD') ?: 'demopass',
    getenv('MYSQL_DATABASE') ?: 'demo',
    3306
);
if ($mysqli->connect_errno) {
    die('DB connection failed: ' . $mysqli->connect_error);
}

