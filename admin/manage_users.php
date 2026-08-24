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
    <style>
        .manage-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .manage-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .manage-header h1 {
            font-size: 28px;
            color: #1a1a2e;
        }
        .manage-header .admin-info {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .manage-header .admin-info span {
            color: #666;
        }
        .manage-header .admin-info strong {
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
        .btn-back {
            display: inline-block;
            padding: 8px 20px;
            background: #e8ecf1;
            color: #333;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-back:hover {
            background: #d1d5db;
        }
        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            overflow: hidden;
            padding: 20px;
        }
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .table-header h2 {
            font-size: 18px;
            color: #1a1a2e;
        }
        .table-header .count {
            color: #888;
            font-size: 14px;
        }
        .table-wrapper {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table thead {
            background: #f8fafc;
        }
        table th {
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: #555;
            font-size: 13px;
            white-space: nowrap;
        }
        table td {
            padding: 12px 15px;
            border-top: 1px solid #f0f2f5;
            vertical-align: middle;
            font-size: 14px;
        }
        table tbody tr:hover {
            background: #fafbfc;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #4A90D9;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
        }
        .user-avatar.green { background: #10B981; }
        .user-avatar.orange { background: #F59E0B; }
        .user-avatar.purple { background: #8B5CF6; }
        .user-avatar.red { background: #EF4444; }
        .user-avatar.pink { background: #EC4899; }
        .role-badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .role-admin {
            background: #fee2e2;
            color: #991b1b;
        }
        .role-user {
            background: #dbeafe;
            color: #1e40af;
        }
        table .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        table .btn-view {
            display: inline-block;
            padding: 6px 16px;
            background: #e8ecf1;
            color: #333;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        table .btn-view:hover {
            background: #d1d5db;
        }
        table .btn-delete {
            display: inline-block;
            padding: 6px 16px;
            background: #fee2e2;
            color: #dc2626;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        table .btn-status {
            padding: 4px 12px;
            background: #4A90D9;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        table .btn-status:hover {
            background: #357ABD;
        }
        table .btn-status.promote {
            background: #10B981;
        }
        table .btn-status.promote:hover {
            background: #059669;
        }
        table .btn-status.demote {
            background: #F59E0B;
        }
        table .btn-status.demote:hover {
            background: #D97706;
        }
        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }
        .success-message {
            background: #d1fae5;
            color: #065f46;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #888;
        }
        .empty-state .empty-icon {
            font-size: 64px;
            display: block;
            margin-bottom: 15px;
        }
        .empty-state h3 {
            color: #333;
            margin-bottom: 8px;
        }

        /* Modal/View Details */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        .modal-content {
            background: white;
            border-radius: 12px;
            max-width: 600px;
            width: 95%;
            max-height: 90vh;
            overflow-y: auto;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .modal-content .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f2f5;
        }
        .modal-content .modal-header h2 {
            font-size: 22px;
            color: #1a1a2e;
        }
        .modal-content .modal-header .close-btn {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #888;
        }
        .modal-content .modal-header .close-btn:hover {
            color: #333;
        }
        .user-detail-row {
            display: flex;
            padding: 8px 0;
            border-bottom: 1px solid #f8fafc;
        }
        .user-detail-row .label {
            font-weight: 600;
            width: 130px;
            color: #555;
            flex-shrink: 0;
        }
        .user-detail-row .value {
            flex: 1;
            color: #1a1a2e;
        }
        .user-detail-row .value .role-badge {
            display: inline-block;
        }
        .user-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 15px;
        }
        .user-stats .stat-item {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .user-stats .stat-item .stat-number {
            font-size: 28px;
            font-weight: 700;
            color: #4A90D9;
        }
        .user-stats .stat-item .stat-label {
            font-size: 13px;
            color: #888;
        }
        @media (max-width: 768px) {
            .manage-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .manage-header .admin-info {
                width: 100%;
                justify-content: space-between;
            }
            .table-header {
                flex-direction: column;
                align-items: flex-start;
            }
            table {
                font-size: 13px;
                min-width: 600px;
            }
            .modal-content {
                padding: 20px;
            }
            .user-detail-row {
                flex-direction: column;
            }
            .user-detail-row .label {
                width: 100%;
                margin-bottom: 3px;
            }
            .user-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
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