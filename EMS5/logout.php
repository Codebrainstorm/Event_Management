<?php
session_start();

// Destroy all session data
$_SESSION = array();

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect based on user type
// If admin was logged in, redirect to admin login
// Otherwise redirect to user login
header('Location: login.php');
exit;
?>
