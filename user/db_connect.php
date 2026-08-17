<?php

// ==================================================
// DATABASE CONNECTION
// ==================================================

$databaseHost = 'localhost';
$databaseName = 'paper_nest';
$databaseUser = 'root';
$databasePassword = '';

try {

    // Create PDO connection to MySQL database
    $pdo = new PDO(
        "mysql:host=$databaseHost;dbname=$databaseName;charset=utf8mb4",
        $databaseUser,
        $databasePassword,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

} catch (PDOException $exception) {

    // Show error if database connection fails
    exit('Unable to connect to the database. Please check your database settings.');
}


// ==================================================
// START SESSION
// ==================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// ==================================================
// HELPER FUNCTIONS
// ==================================================

// Escape output before displaying it in HTML
// This helps prevent HTML injection.
function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}


// Check whether a user is currently logged in
function is_logged_in()
{
    return isset($_SESSION['user_id']);
}


// Redirect user to another page
function redirect($page)
{
    header('Location: ' . $page);
    exit;
}


// Require the user to be logged in
function require_login()
{
    if (!is_logged_in()) {

        flash(
            'error',
            'Please log in to continue.'
        );

        redirect('login.php');
    }
}


// Store a temporary message in the session
function flash($type, $message)
{
    $_SESSION['flash_' . $type] = $message;
}


// Display temporary messages
function show_flash()
{
    foreach (['error', 'success'] as $type) {

        if (isset($_SESSION['flash_' . $type])) {

            echo '<div class="message ' . $type . '">'
                . e($_SESSION['flash_' . $type])
                . '</div>';

            unset($_SESSION['flash_' . $type]);
        }
    }
}


// ==================================================
// PAGE HEADER
// ==================================================

function page_header($title)
{
    $currentPage = basename($_SERVER['PHP_SELF']);

    echo '<!DOCTYPE html>';
    echo '<html lang="en">';
    echo '<head>';

    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';

    echo '<title>'
        . e($title)
        . ' | PaperNest</title>';

    echo '<link rel="stylesheet" href="css/style.css">';
    echo '<script defer src="js/script.js"></script>';

    echo '</head>';
    echo '<body>';

    // ==============================
    // HEADER / NAVIGATION
    // ==============================

    echo '<header>';
    echo '<div class="nav">';

    echo '<a class="brand" href="index.php">
            PaperNest
          </a>';

    echo '<button class="menu-button" type="button">
            Menu
          </button>';

    echo '<nav>';

    $navigation = [
        'index.php' => 'Home',
        'products.php' => 'Shop',
        'categories.php' => 'Categories'
    ];

    foreach ($navigation as $url => $label) {

        $activeClass = ($currentPage === $url)
            ? 'active'
            : '';

        echo '<a class="' . $activeClass . '"
                 href="' . $url . '">'
            . $label .
            '</a>';
    }


    // ==============================
    // USER NAVIGATION
    // ==============================

    if (is_logged_in()) {

        echo '<a href="cart.php">Cart</a>';
        echo '<a href="profile.php">Profile</a>';
        echo '<a href="order_history.php">Orders</a>';
        echo '<a href="logout.php">Logout</a>';

    } else {

        echo '<a href="login.php">Login</a>';
        echo '<a href="register.php">Register</a>';
    }

    echo '</nav>';
    echo '</div>';
    echo '</header>';

    echo '<main class="container">';

    // Display success/error messages
    show_flash();
}


// ==================================================
// PAGE FOOTER
// ==================================================

function page_footer()
{
    echo '</main>';

    echo '<footer>';

    echo '<strong>PaperNest</strong>';

    echo '<p>Your online stationery store.</p>';

    echo '<p>
            hello@papernest.test ·
            +60 12-345 6789
          </p>';

    echo '<p>
            © ' . date('Y') . ' PaperNest
          </p>';

    echo '</footer>';

    echo '</body>';
    echo '</html>';
}