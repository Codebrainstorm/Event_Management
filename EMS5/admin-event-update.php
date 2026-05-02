<?php
require 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}

// Get event ID and action
$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$action = isset($_GET['action']) ? trim($_GET['action']) : '';

// Validate inputs
$valid_actions = ['approve', 'reject', 'delete'];

if ($event_id <= 0 || !in_array($action, $valid_actions)) {
    header('Location: admin-events.php');
    exit;
}

// Check if event exists
$check_query = "SELECT id FROM events WHERE id = ?";
$check_stmt = $connection->prepare($check_query);
$check_stmt->bind_param('i', $event_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    header('Location: admin-events.php');
    exit;
}

$check_stmt->close();

// Process action
if ($action === 'approve') {
    $update_query = "UPDATE events SET status = 'approved' WHERE id = ?";
    $update_stmt = $connection->prepare($update_query);
    $update_stmt->bind_param('i', $event_id);
    $update_stmt->execute();
    $update_stmt->close();
} elseif ($action === 'reject') {
    $update_query = "UPDATE events SET status = 'rejected' WHERE id = ?";
    $update_stmt = $connection->prepare($update_query);
    $update_stmt->bind_param('i', $event_id);
    $update_stmt->execute();
    $update_stmt->close();
} elseif ($action === 'delete') {
    $delete_query = "DELETE FROM events WHERE id = ?";
    $delete_stmt = $connection->prepare($delete_query);
    $delete_stmt->bind_param('i', $event_id);
    $delete_stmt->execute();
    $delete_stmt->close();
}

// Redirect back to admin events page
header('Location: admin-events.php');
exit;
?>
