<?php
require 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}

// Check if booking_id is provided
$booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;

if ($booking_id <= 0) {
    header('Location: admin-dashboard.php');
    exit;
}

// Verify booking exists before deletion
$check_query = "SELECT id FROM bookings WHERE id = ?";
$stmt = $connection->prepare($check_query);
$stmt->bind_param('i', $booking_id);
$stmt->execute();
$check_result = $stmt->get_result();

if ($check_result->num_rows == 0) {
    header('Location: admin-dashboard.php');
    exit;
}

// Delete the booking
$delete_query = "DELETE FROM bookings WHERE id = ?";
$delete_stmt = $connection->prepare($delete_query);
$delete_stmt->bind_param('i', $booking_id);

if ($delete_stmt->execute()) {
    header('Location: admin-dashboard.php?deleted=1');
} else {
    header('Location: admin-dashboard.php?error=1');
}

$delete_stmt->close();
$stmt->close();
$connection->close();
exit;
?>