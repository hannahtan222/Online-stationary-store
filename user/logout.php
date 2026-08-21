<?php
// modules/member3_user/logout.php
session_start();

// Include config files
require_once '../includes/config.php';
require_once '../config/db_connection.php';
require_once '../config/auth.php';

// ==================================================
// LOGOUT
// ==================================================

// Clear all session variables.
$_SESSION = [];

// Destroy the current session.
session_destroy();

// Start a new session so that we can
// display the logout confirmation message.
session_start();

// Store flash message
flash('success', 'You have been logged out.');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Stationery Store</title>
</head>
<body>
    <script>
        // Show alert popup
        alert('✅ You have been successfully logged out!');
        
        // Redirect to login page
        window.location.href = 'login.php';
    </script>
</body>
</html>