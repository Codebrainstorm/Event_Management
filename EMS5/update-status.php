<?php
require 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}

// Get booking ID and new status
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

// Validate inputs
$valid_statuses = ['Pending', 'Approved', 'Completed', 'Cancelled'];

if ($booking_id <= 0 || !in_array($status, $valid_statuses)) {
    header('Location: admin-dashboard.php');
    exit;
}

// Check if booking exists
$check_query = "SELECT id FROM bookings WHERE id = ?";
$check_stmt = $connection->prepare($check_query);
$check_stmt->bind_param('i', $booking_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    header('Location: admin-dashboard.php');
    exit;
}

$check_stmt->close();

// Update booking status
$update_query = "UPDATE bookings SET status = ? WHERE id = ?";
$update_stmt = $connection->prepare($update_query);
$update_stmt->bind_param('si', $status, $booking_id);
$update_stmt->execute();
$update_stmt->close();

// Redirect back to admin dashboard
header('Location: admin-dashboard.php');
exit;
?>
