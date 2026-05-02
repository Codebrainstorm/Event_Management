<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'event_management_system');

// Create connection
$connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($connection->connect_error) {
    die('Connection failed: ' . $connection->connect_error);
}

// Set charset to utf8
$connection->set_charset('utf8');

// Define app settings
define('APP_NAME', 'Event Management System');
define('APP_URL', 'http://localhost/event-management/');
define('UPLOAD_DIR', __DIR__ . '/uploads/');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS
ini_set('session.use_strict_mode', 1);
?>
