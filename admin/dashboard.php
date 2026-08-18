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
    <style>
        /* Extra dashboard styles */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
            margin-top: 30px;
        }
        
        .dashboard-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        
        .dashboard-card h3 {
            color: #1a1a2e;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f2f5;
        }
        
        .dashboard-card ul {
            list-style: none;
            padding: 0;
        }
        
        .dashboard-card ul li {
            padding: 10px 0;
            border-bottom: 1px solid #f8fafc;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .dashboard-card ul li:last-child {
            border-bottom: none;
        }
        
        .status-badge {
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-badge.pending { background: #fef3c7; color: #d97706; }
        .status-badge.completed { background: #d1fae5; color: #065f46; }
        .status-badge.shipped { background: #dbeafe; color: #1e40af; }
        .status-badge.cancelled { background: #fee2e2; color: #991b1b; }
        
        .low-stock-warning {
            color: #dc2626;
            font-weight: 600;
        }
        
        .dashboard-admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .dashboard-admin-header h1 {
            font-size: 28px;
            color: #1a1a2e;
        }
        
        .dashboard-admin-header .admin-info {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .dashboard-admin-header .admin-info span {
            color: #666;
        }
        
        .dashboard-admin-header .admin-info strong {
            color: #1a1a2e;
        }
        
        .btn-logout {
            background: #fee2e2;
            color: #dc2626;
            padding: 8px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-logout:hover {
            background: #fecaca;
        }
        
        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .dashboard-admin-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
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
            <a href="manage_products.php" class="stat-link">Manage Products →</a>
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
        
        <div class="stat-box">
            <span class="stat-icon">👤</span>
            <h3>Users</h3>
            <p class="stat-number"><?php echo $user_count; ?></p>
            <a href="#" class="stat-link">Manage Users →</a>
        </div>
    </div>
    
    <!-- Recent Orders & Low Stock -->
    <div class="dashboard-grid">
        <!-- Recent Orders -->
        <div class="dashboard-card">
            <h3>🕐 Recent Orders</h3>
            <?php if (mysqli_num_rows($recent_orders) > 0): ?>
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
                    <a href="manage_orders.php" style="color: #4A90D9; text-decoration: none; font-weight: 500;">View All Orders →</a>
                </div>
            <?php else: ?>
                <p style="color: #888; text-align: center; padding: 20px;">No orders yet.</p>
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
                    <a href="manage_products.php" style="color: #4A90D9; text-decoration: none; font-weight: 500;">Manage Products →</a>
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