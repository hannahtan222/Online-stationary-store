<?php
// includes/header.php
// Include config first
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Best Stationery</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</head>
<body>
<header class="site-header">
    <div class="logo">
        <a href="<?php echo BASE_URL; ?>index.php">📚 Best Stationery</a>
    </div>
    <button class="menu-toggle" onclick="toggleMenu()">☰</button>
    
    <?php include 'navigation.php'; ?>
    
</header>
<main class="main-content">