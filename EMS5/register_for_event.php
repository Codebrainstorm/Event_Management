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
    header('Location: events.php');
    exit();
}

// Check if event exists
$event_stmt = $connection->prepare("SELECT id FROM events WHERE id = ? AND active = 1 LIMIT 1");
$event_stmt->bind_param('i', $event_id);
$event_stmt->execute();
$event_result = $event_stmt->get_result();

if ($event_result->num_rows === 0) {
    header('Location: events.php');
    exit();
}
$event_stmt->close();

// Check if user is already registered
$check_stmt = $connection->prepare("SELECT id FROM registrations WHERE event_id = ? AND user_id = ? LIMIT 1");
$check_stmt->bind_param('ii', $event_id, $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    $check_stmt->close();
    header('Location: event.php?id=' . $event_id);
    exit();
}
$check_stmt->close();

// Register user for event
$register_stmt = $connection->prepare("INSERT INTO registrations (event_id, user_id, registered_at) VALUES (?, ?, NOW())");
$register_stmt->bind_param('ii', $event_id, $user_id);

if ($register_stmt->execute()) {
    $register_stmt->close();
    // Redirect to event page with success message
    header('Location: event.php?id=' . $event_id . '&registered=true');
} else {
    $register_stmt->close();
    header('Location: event.php?id=' . $event_id . '&error=true');
}
exit();
?>
