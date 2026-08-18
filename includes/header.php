<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Best Stationery</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/script.js"></script>
</head>
<body>
<header class="site-header">
    <div class="logo"><a href="index.php">Best Stationery</a></div>
    <button class="menu-toggle" onclick="toggleMenu()">☰</button>
    <nav>
        <ul class="nav-menu">
            <li><a href="front-end/index.php">Home</a></li>
            <li><a href="products.php">Products</a></li>
            <li><a href="cart.php">Cart</a></li>
            <li><a href="contact.php">Contact</a></li>
            <?php if(isset($_SESSION['user_id'])): ?>
                <li><a href="user/profile.php">Profile</a></li>
                <li><a href="user/logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="user/login.php">Login</a></li>
                <li><a href="user/register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<main class="main-content">
