<?php
// admin/dashboard.php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

// Include BOTH config files
require_once '../includes/config.php';        // For BASE_URL
require_once '../config/db_connection.php';   // For database connection

// Get stats for dashboard
$product_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM products"))['total'];
$category_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM categories"))['total'];
$order_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders"))['total'];

// Get user count
$user_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users"))['total'];

// Get contact messages count
$contact_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM contact_messages"))['total'];
$unread_contact_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM contact_messages WHERE status = 'unread'"))['total'];

// Get recent orders (for display)
$recent_orders_query = "SELECT o.*, u.username 
                        FROM orders o 
                        JOIN users u ON o.user_id = u.user_id 
                        ORDER BY o.order_date DESC LIMIT 5";
$recent_orders = mysqli_query($conn, $recent_orders_query);

// Get low stock products (for alert)
$low_stock_query = "SELECT * FROM products WHERE stock_quantity < 10 ORDER BY stock_quantity ASC LIMIT 5";
$low_stock = mysqli_query($conn, $low_stock_query);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Panel</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin_style.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<div class="admin-container">
    <!-- Page Header -->
    <div class="dashboard-admin-header">
        <h1>📊 Dashboard</h1>
        <div class="admin-info">
            <span>Welcome, <strong><?php echo $_SESSION['admin_username'] ?? 'Admin'; ?></strong></span>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="dashboard-stats">
        <div class="stat-box">
            <span class="stat-icon">📦</span>
            <h3>Total Products</h3>
            <p class="stat-number"><?php echo $product_count; ?></p>
            <a href="manage_product.php" class="stat-link">Manage Products →</a>
        </div>
        
        <div class="stat-box">
            <span class="stat-icon">📂</span>
            <h3>Categories</h3>
            <p class="stat-number"><?php echo $category_count; ?></p>
            <a href="manage_categories.php" class="stat-link">Manage Categories →</a>
        </div>
        
        <div class="stat-box">
            <span class="stat-icon">🛒</span>
            <h3>Total Orders</h3>
            <p class="stat-number"><?php echo $order_count; ?></p>
            <a href="manage_orders.php" class="stat-link">View Orders →</a>
        </div>
		
		<div class="stat-box contact-stat">
            <span class="stat-icon">📩</span>
            <h3>Contact Messages</h3>
            <p class="stat-number">
                <?php echo $contact_count; ?>
                <?php if ($unread_contact_count > 0): ?>
                    <span class="unread-badge"><?php echo $unread_contact_count; ?> new</span>
                <?php endif; ?>
            </p>
            <a href="manage_messages.php" class="stat-link">View Messages →</a>
        </div>
		
        <div class="stat-box">
            <span class="stat-icon">👤</span>
            <h3>Users</h3>
            <p class="stat-number"><?php echo $user_count; ?></p>
            <a href="manage_users.php" class="stat-link">Manage Users →</a>
        </div>
    </div>
    
    <!-- Recent Orders & Low Stock -->
<div class="dashboard-grid">
    <!-- Recent Orders -->
    <div class="dashboard-card">
        <h3>🕐 Recent Orders</h3>
        <?php 
        // Fetch only orders that are NOT completed
       $recent_orders_query = "SELECT o.order_id, o.user_id, o.status, o.total_amount, u.username 
                                FROM orders o 
                                INNER JOIN users u ON o.user_id = u.user_id 
                                WHERE o.status != 'Completed' 
                                ORDER BY o.order_id DESC 
                                LIMIT 5";
        $recent_orders = mysqli_query($conn, $recent_orders_query);
        
        if (mysqli_num_rows($recent_orders) > 0): 
        ?>
            <ul>
                <?php while ($order = mysqli_fetch_assoc($recent_orders)): ?>
                    <li>
                        <span>
                            <strong>#<?php echo $order['order_id']; ?></strong>
                            - <?php echo htmlspecialchars($order['username']); ?>
                        </span>
                        <span>
                            <span class="status-badge <?php echo strtolower($order['status']); ?>">
                                <?php echo $order['status']; ?>
                            </span>
                            <span style="color: #888; font-size: 13px; margin-left: 10px;">
                                RM <?php echo number_format($order['total_amount'], 2); ?>
                            </span>
                        </span>
                    </li>
                <?php endwhile; ?>
            </ul>
            <div style="margin-top: 15px; text-align: right;">
                <a href="manage_orders.php" style="color: #506294; text-decoration: none; font-weight: 500;">View All Orders →</a>
            </div>
        <?php else: ?>
            <p style="color: #888; text-align: center; padding: 20px;">No pending orders.</p>
        <?php endif; ?>
    </div>
        
        <!-- Low Stock Alert -->
        <div class="dashboard-card">
            <h3>⚠️ Low Stock Alert</h3>
            <?php if (mysqli_num_rows($low_stock) > 0): ?>
                <ul>
                    <?php while ($product = mysqli_fetch_assoc($low_stock)): ?>
                        <li>
                            <span><?php echo htmlspecialchars($product['product_name']); ?></span>
                            <span class="low-stock-warning">
                                <?php echo $product['stock_quantity']; ?> left
                            </span>
                        </li>
                    <?php endwhile; ?>
                </ul>
                <div style="margin-top: 15px; text-align: right;">
                    <a href="manage_product.php" style="color: #4A90D9; text-decoration: none; font-weight: 500;">Manage Products →</a>
                </div>
            <?php else: ?>
                <p style="color: #10B981; text-align: center; padding: 20px;">✅ All products are well stocked!</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

</body>
</html>