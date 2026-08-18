<?php
// includes/navigation.php
// Include config to use BASE_URL
require_once 'config.php';
?>
<nav>
    <ul class="nav-menu">
        <li><a href="<?php echo BASE_URL; ?>index.php">Home</a></li>
        <li><a href="<?php echo BASE_URL; ?>products.php">Products</a></li>
        <li><a href="<?php echo BASE_URL; ?>cart.php">Cart</a></li>
        <li><a href="<?php echo BASE_URL; ?>front-end/contact.php">Contact</a></li>
        
        <?php if(isset($_SESSION['user_id'])): ?>
            <li><a href="<?php echo BASE_URL; ?>user/profile.php">Profile</a></li>
            <li><a href="<?php echo BASE_URL; ?>user/logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="<?php echo BASE_URL; ?>user/login.php">Login</a></li>
            <li><a href="<?php echo BASE_URL; ?>user/register.php">Register</a></li>
        <?php endif; ?>
        
        <li><a href="<?php echo BASE_URL; ?>admin/admin_login.php" class="admin-link">Admin</a></li>
    </ul>
</nav>