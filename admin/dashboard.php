<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit();
}
require_once '../config/db_connection.php';

// Get stats for dashboard
$product_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM products"))['total'];
$category_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM categories"))['total'];
$order_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders"))['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin_style.css">
</head>
<body>
    <div class="admin-container">
        <h1>Welcome Admin!</h1>
        <div class="dashboard-stats">
            <div class="stat-box">
                <h3>Products</h3>
                <p><?php echo $product_count; ?></p>
                <a href="manage_products.php">Manage</a>
            </div>
            <div class="stat-box">
                <h3>Categories</h3>
                <p><?php echo $category_count; ?></p>
                <a href="manage_categories.php">Manage</a>
            </div>
            <div class="stat-box">
                <h3>Orders</h3>
                <p><?php echo $order_count; ?></p>
                <a href="manage_orders.php">View</a>
            </div>
        </div>
        <a href="logout.php">Logout</a>
    </div>
</body>
</html>