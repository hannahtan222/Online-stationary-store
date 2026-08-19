<?php
//config/auth.php

function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// ==================================================
// AUTHENTICATION FUNCTIONS
// ==================================================

// Check whether user is logged in
function is_logged_in(){
    return isset($_SESSION['user_id']);
}

// ==================================================
// REQUIRE USER TO BE LOGGED IN
// ==================================================

function require_login(){
    // If the user is NOT logged in
    if (!is_logged_in()) {
        // Store error message in session
        $_SESSION['flash_error'] = 'Please log in to continue.';
        // Send the user to the login page
        header('Location: login.php');
        exit();
    }
}

// ==================================================
// REQUIRE ADMIN TO BE LOGGED IN
// ==================================================

function require_admin_login(){
    // If the admin is NOT logged in
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        $_SESSION['flash_error'] = 'Please log in as admin to continue.';
        header('Location: ../admin/admin_login.php');
        exit();
    }
}

// ==================================================
// REDIRECT FUNCTION
// ==================================================

function redirect($url){
    header('Location: ' . $url);
    exit();
}

// ==================================================
// FLASH MESSAGE FUNCTIONS
// ==================================================

function flash($type, $message){
    $_SESSION['flash_' . $type] = $message;
}

function get_flash($type){
    if (isset($_SESSION['flash_' . $type])) {
        $message = $_SESSION['flash_' . $type];
        unset($_SESSION['flash_' . $type]);
        return $message;
    }
    return null;
}

function display_flash(){
    $types = ['success', 'error', 'warning', 'info'];
    $output = '';
    foreach ($types as $type) {
        if (isset($_SESSION['flash_' . $type])) {
            $message = $_SESSION['flash_' . $type];
            unset($_SESSION['flash_' . $type]);
            $output .= '<div class="flash-message flash-' . $type . '">' . htmlspecialchars($message) . '</div>';
        }
    }
    return $output;
}
?>