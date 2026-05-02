<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

if ($event_id === 0) {
    header('Location: dashboard.php');
    exit();
}

// Check if event exists and user is the creator
$event_query = "SELECT id, created_by FROM events WHERE id = $event_id LIMIT 1";
$event_result = mysqli_query($connection, $event_query);

if (mysqli_num_rows($event_result) === 0) {
    header('Location: dashboard.php');
    exit();
}

$event = mysqli_fetch_assoc($event_result);

// Check if user is the creator
if ($event['created_by'] != $user_id) {
    header('Location: dashboard.php');
    exit();
}

// Delete event registrations first
$delete_registrations_query = "DELETE FROM registrations WHERE event_id = $event_id";
mysqli_query($connection, $delete_registrations_query);

// Delete event
$delete_event_query = "DELETE FROM events WHERE id = $event_id";

if (mysqli_query($connection, $delete_event_query)) {
    header('Location: dashboard.php?deleted=true');
} else {
    header('Location: dashboard.php?error=true');
}
exit();
?>
