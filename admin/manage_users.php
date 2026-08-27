<?php
// admin/manage_users.php
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
$view_user = null;

// Handle Delete User
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    
    // Check if user has orders
    $check_query = "SELECT COUNT(*) as count FROM orders WHERE user_id = $user_id";
    $check_result = mysqli_query($conn, $check_query);
    $count = mysqli_fetch_assoc($check_result)['count'];
    
    if ($count > 0) {
        $error = "Cannot delete this user because they have $count order(s)!";
    } else {
        // Delete user
        $delete_query = "DELETE FROM users WHERE user_id = $user_id";
        if (mysqli_query($conn, $delete_query)) {
            $success = "User deleted successfully!";
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}

// Handle View User Details
if (isset($_GET['view']) && !empty($_GET['view'])) {
    $user_id = (int)$_GET['view'];
    $view_query = "SELECT * FROM users WHERE user_id = $user_id";
    $view_result = mysqli_query($conn, $view_query);
    if ($view_user = mysqli_fetch_assoc($view_result)) {
        // Get order count for this user
        $order_count_query = "SELECT COUNT(*) as total FROM orders WHERE user_id = $user_id";
        $order_result = mysqli_query($conn, $order_count_query);
        $view_user['order_count'] = mysqli_fetch_assoc($order_result)['total'];
        
        // Get cart items count for this user
        $cart_count_query = "SELECT COUNT(*) as total FROM cart WHERE user_id = $user_id";
        $cart_result = mysqli_query($conn, $cart_count_query);
        $view_user['cart_count'] = mysqli_fetch_assoc($cart_result)['total'];
    }
}

// Handle User Role Update (Promote/Demote)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $user_id = (int)$_POST['user_id'];
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    
    $update_query = "UPDATE users SET role = '$role' WHERE user_id = $user_id";
    if (mysqli_query($conn, $update_query)) {
        $success = "User role updated successfully!";
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}

// Get all users
$sql = "SELECT u.*, 
        (SELECT COUNT(*) FROM orders WHERE user_id = u.user_id) as order_count,
        (SELECT COUNT(*) FROM cart WHERE user_id = u.user_id) as cart_count
        FROM users u 
        ORDER BY u.created_at DESC";
$users = mysqli_query($conn, $sql);
$total_users = mysqli_num_rows($users);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Panel</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin_style.css">
</head>
<body>

<div class="manage-container">
    <!-- Header -->
    <div class="manage-header">
        <h1>👥 Manage Users</h1>
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

    <!-- Users Table -->
    <div class="table-container">
        <div class="table-header">
            <h2>All Users</h2>
            <div class="count">Total: <?php echo $total_users; ?> users</div>
        </div>

        <?php if ($total_users > 0): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 50px;">Avatar</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Orders</th>
                            <th>Role</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $colors = ['green', 'orange', 'purple', 'red', 'pink'];
                        $color_index = 0;
                        while ($user = mysqli_fetch_assoc($users)): 
                            $first_letter = strtoupper(substr($user['username'], 0, 1));
                            $avatar_color = $colors[$color_index % count($colors)];
                            $color_index++;
                        ?>
                            <tr>
                                <td>
                                    <div class="user-avatar <?php echo $avatar_color; ?>">
                                        <?php echo $first_letter; ?>
                                    </div>
                                </td>
                                <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($user['full_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td style="text-align: center;"><?php echo $user['order_count'] ?? 0; ?></td>
                                <td>
                                    <form method="POST" action="" style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                        <span class="role-badge role-<?php echo strtolower($user['role'] ?? 'user'); ?>">
                                            <?php echo ucfirst($user['role'] ?? 'User'); ?>
                                        </span>
                                    </form>
                                </td>
                                <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                                <td style="text-align: center;"></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <span class="empty-icon">👥</span>
                <h3>No Users Yet</h3>
                <p>There are no registered users yet.</p>
            </div>
        <?php endif; ?>
    </div>

    <div style="margin-top: 20px;">
        <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
    </div>
</div>

<!-- View User Modal -->
<?php if ($view_user): ?>
<div class="modal-overlay" id="userModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>👤 User Details</h2>
            <a href="manage_users.php" class="close-btn">&times;</a>
        </div>

        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
            <div class="user-avatar <?php echo $colors[0]; ?>" style="width: 60px; height: 60px; font-size: 24px;">
                <?php echo strtoupper(substr($view_user['username'], 0, 1)); ?>
            </div>
            <div>
                <h3 style="margin: 0; color: #1a1a2e;"><?php echo htmlspecialchars($view_user['full_name'] ?? $view_user['username']); ?></h3>
                <p style="margin: 0; color: #888;">@<?php echo htmlspecialchars($view_user['username']); ?></p>
            </div>
        </div>

        <div style="margin-bottom: 20px;">
            <div class="user-detail-row">
                <span class="label">User ID:</span>
                <span class="value">#<?php echo $view_user['user_id']; ?></span>
            </div>
            <div class="user-detail-row">
                <span class="label">Username:</span>
                <span class="value"><?php echo htmlspecialchars($view_user['username']); ?></span>
            </div>
            <div class="user-detail-row">
                <span class="label">Full Name:</span>
                <span class="value"><?php echo htmlspecialchars($view_user['full_name'] ?? 'Not provided'); ?></span>
            </div>
            <div class="user-detail-row">
                <span class="label">Email:</span>
                <span class="value"><?php echo htmlspecialchars($view_user['email']); ?></span>
            </div>
            <div class="user-detail-row">
                <span class="label">Phone:</span>
                <span class="value"><?php echo htmlspecialchars($view_user['phone'] ?? 'Not provided'); ?></span>
            </div>
            <div class="user-detail-row">
                <span class="label">Address:</span>
                <span class="value"><?php echo nl2br(htmlspecialchars($view_user['address'] ?? 'Not provided')); ?></span>
            </div>
            <div class="user-detail-row">
                <span class="label">Role:</span>
                <span class="value">
                    <span class="role-badge role-<?php echo strtolower($view_user['role'] ?? 'user'); ?>">
                        <?php echo ucfirst($view_user['role'] ?? 'User'); ?>
                    </span>
                </span>
            </div>
            <div class="user-detail-row">
                <span class="label">Joined:</span>
                <span class="value"><?php echo date('d M Y, h:i A', strtotime($view_user['created_at'])); ?></span>
            </div>
        </div>

        <h3 style="margin-bottom: 15px;">Activity Summary</h3>
        <div class="user-stats">
            <div class="stat-item">
                <div class="stat-number"><?php echo $view_user['order_count'] ?? 0; ?></div>
                <div class="stat-label">Orders Placed</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $view_user['cart_count'] ?? 0; ?></div>
                <div class="stat-label">Items in Cart</div>
            </div>
        </div>

        <div style="margin-top: 20px; text-align: right;">
            <a href="manage_users.php" class="btn-back">Close</a>
        </div>
    </div>
</div>
<?php endif; ?>

</body>
</html>