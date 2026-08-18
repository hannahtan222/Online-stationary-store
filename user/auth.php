<?php

// ==================================================
// AUTHENTICATION FUNCTIONS
// ==================================================

// Check whether user is logged in
function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

// ==================================================
// REQUIRE USER TO BE LOGGED IN
// ==================================================

function require_login()
{
    // If the user is NOT logged in
    if (!is_logged_in()) {

        // Show an error message
        flash(
            'error',
            'Please log in to continue.'
        );

        // Send the user to the login page
        redirect('login.php');
    }
}