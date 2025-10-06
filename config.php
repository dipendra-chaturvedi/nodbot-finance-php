<?php
// config.php
session_start();

// Admin Registration Secret Code (Change this for production)
define('ADMIN_SECRET_CODE', 'NODBOT2025');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'nodbot_finance');

// Create connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// App settings
define('APP_NAME', 'Nodbot Finance');
define('BASE_URL', 'http://localhost/nodbot-finance-php/');
?>
