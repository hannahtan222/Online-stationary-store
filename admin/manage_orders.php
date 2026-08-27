<?php
// admin/manage_orders.php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

// Include BOTH config files
require_once '../includes/config.php';        // For BASE_URL
require_once '../config/db_connection.php';   // For database connection

$error = '';
$success = '';
$view_order = null;
$order_items = [];

// Handle Order Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $valid_statuses = ['Pending', 'Processing', 'Shipped', 'Completed', 'Cancelled'];
    if (!in_array($status, $valid_statuses)) {
        $error = 'Invalid status!';
    } else {
        $update_query = "UPDATE orders SET status = '$status' WHERE order_id = $order_id";
        if (mysqli_query($conn, $update_query)) {
            $success = "Order #$order_id status updated to '$status'!";
        } else {
            $error = 'Error: ' . mysqli_error($conn);
        }
    }
}

// Handle View Order Details
if (isset($_GET['view']) && !empty($_GET['view'])) {
    $order_id = (int)$_GET['view'];
    $view_query = "SELECT o.*, u.username, u.email, u.full_name, u.address, u.phone 
                   FROM orders o 
                   JOIN users u ON o.user_id = u.user_id 
                   WHERE o.order_id = $order_id";
    $view_result = mysqli_query($conn, $view_query);
    if ($view_order = mysqli_fetch_assoc($view_result)) {
        // Fetch order items
        $items_query = "SELECT oi.*, p.product_name, p.image_url 
                        FROM order_items oi 
                        JOIN products p ON oi.product_id = p.product_id 
                        WHERE oi.order_id = $order_id";
        $items_result = mysqli_query($conn, $items_query);
        while ($item = mysqli_fetch_assoc($items_result)) {
            $order_items[] = $item;
        }
    }
}

// Handle Delete Order
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $order_id = (int)$_GET['delete'];
    
    // Delete order items first (foreign key constraint)
    $delete_items = "DELETE FROM order_items WHERE order_id = $order_id";
    mysqli_query($conn, $delete_items);
    
    // Delete order
    $delete_order = "DELETE FROM orders WHERE order_id = $order_id";
    if (mysqli_query($conn, $delete_order)) {
        $success = "Order #$order_id deleted successfully!";
    } else {
        $error = 'Error: ' . mysqli_error($conn);
    }
}

// Get all orders with user info
$sql = "SELECT o.*, u.username, u.email 
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id 
        ORDER BY o.order_date DESC";
$orders = mysqli_query($conn, $sql);
$total_orders = mysqli_num_rows($orders);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Admin Panel</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin_style.css">
    <style>

    </style>
</head>
<body>

<div class="manage-container">
    <!-- Header -->
    <div class="manage-header">
        <h1>📦 Manage Orders</h1>
        <div class="admin-info">
            <span>Welcome, <strong><?php echo $_SESSION['admin_username'] ?? 'Admin'; ?></strong></span>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success-message"><?php echo $success; ?></div>
    <?php endif; ?>

    <!-- Orders Table -->
    <div class="table-container">
        <div class="table-header">
            <h2>All Orders</h2>
            <div class="count">Total: <?php echo $total_orders; ?> orders</div>
        </div>

        <?php if ($total_orders > 0): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th style="text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = mysqli_fetch_assoc($orders)): ?>
                            <tr>
                                <td><strong>#<?php echo $order['order_id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($order['username']); ?></td>
                                <td class="order-total">RM <?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <form method="POST" action="" style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                        <select name="status" class="status-badge status-<?php echo strtolower($order['status']); ?>" 
                                                style="padding: 4px 12px; border: none; border-radius: 20px; font-size: 12px; font-weight: 600; cursor: pointer;">
                                            <option value="Pending" <?php echo $order['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Processing" <?php echo $order['status'] == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="Shipped" <?php echo $order['status'] == 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="Completed" <?php echo $order['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="Cancelled" <?php echo $order['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn-status">Update</button>
                                    </form>
                                </td>
                                <td><?php echo date('d M Y', strtotime($order['order_date'])); ?></td>
                                <td style="text-align: center;">
                                    <div class="actions" style="justify-content: center;">
                                        <a href="manage_orders.php?view=<?php echo $order['order_id']; ?>" class="btn-view">👁️ View</a>
                                        <a href="manage_orders.php?delete=<?php echo $order['order_id']; ?>" 
                                           class="btn-delete" 
                                           onclick="return confirm('Delete order #<?php echo $order['order_id']; ?>? This cannot be undone.')">
                                            🗑️ Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <span class="empty-icon">📦</span>
                <h3>No Orders Yet</h3>
                <p>Customers haven't placed any orders yet.</p>
            </div>
        <?php endif; ?>
    </div>

    <div style="margin-top: 20px;">
        <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
    </div>
</div>

<!-- View Order Modal -->
<?php if ($view_order): ?>
<div class="modal-overlay" id="orderModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Order #<?php echo $view_order['order_id']; ?> Details</h2>
            <a href="manage_orders.php" class="close-btn">&times;</a>
        </div>

        <div style="margin-bottom: 20px;">
            <div class="order-detail-row">
                <span class="label">Customer:</span>
                <span class="value"><?php echo htmlspecialchars($view_order['full_name']); ?></span>
            </div>
            <div class="order-detail-row">
                <span class="label">Username:</span>
                <span class="value"><?php echo htmlspecialchars($view_order['username']); ?></span>
            </div>
            <div class="order-detail-row">
                <span class="label">Email:</span>
                <span class="value"><?php echo htmlspecialchars($view_order['email']); ?></span>
            </div>
            <div class="order-detail-row">
                <span class="label">Phone:</span>
                <span class="value"><?php echo htmlspecialchars($view_order['phone'] ?? 'Not provided'); ?></span>
            </div>
            <div class="order-detail-row">
                <span class="label">Shipping Address:</span>
                <span class="value"><?php echo nl2br(htmlspecialchars($view_order['shipping_address'])); ?></span>
            </div>
            <div class="order-detail-row">
                <span class="label">Order Date:</span>
                <span class="value"><?php echo date('d M Y, h:i A', strtotime($view_order['order_date'])); ?></span>
            </div>
            <div class="order-detail-row">
                <span class="label">Status:</span>
                <span class="value">
                    <span class="status-badge status-<?php echo strtolower($view_order['status']); ?>">
                        <?php echo $view_order['status']; ?>
                    </span>
                </span>
            </div>
        </div>

        <h3 style="margin-bottom: 15px;">Order Items</h3>
        <?php if (!empty($order_items)): ?>
            <table class="order-items-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th style="text-align: center;">Quantity</th>
                        <th style="text-align: right;">Price</th>
                        <th style="text-align: right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td style="text-align: center;"><?php echo $item['quantity']; ?></td>
                            <td style="text-align: right;">RM <?php echo number_format($item['price'], 2); ?></td>
                            <td style="text-align: right;">RM <?php echo number_format($item['quantity'] * $item['price'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="order-total-display">
                Total: <span>RM <?php echo number_format($view_order['total_amount'], 2); ?></span>
            </div>
        <?php else: ?>
            <p style="color: #888;">No items found for this order.</p>
        <?php endif; ?>

        <div style="margin-top: 20px; text-align: right;">
            <a href="manage_orders.php" class="btn-back">Close</a>
        </div>
    </div>
</div>
<?php endif; ?>

</body>
</html>