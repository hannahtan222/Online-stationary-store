<?php

session_start();

require_once('../config/db_connection.php');

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

// Display success message.
flash(
    'success',
    'You have been logged out.'
);

// Redirect user to login page.
redirect('login.php');

?>
