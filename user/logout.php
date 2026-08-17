<?php

require 'db_connect.php';


// ==================================================
// LOGOUT
// ==================================================

// Clear all session data
$_SESSION = [];


// Destroy current session
session_destroy();


// Start a new session so that we can show
// a logout confirmation message.
session_start();

flash(
    'success',
    'You have been logged out.'
);

redirect('login.php');