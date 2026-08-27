<?php
// admin/manage_messages.php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

require_once '../includes/config.php';
require_once '../config/db_connection.php';

$error = '';
$success = '';
$view_message = null;

// Handle Delete Message
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $message_id = (int)$_GET['delete'];
    $delete_query = "DELETE FROM contact_messages WHERE message_id = $message_id";
    if (mysqli_query($conn, $delete_query)) {
        $success = "Message deleted successfully!";
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}

// Handle Status Update
if (isset($_GET['mark']) && !empty($_GET['mark']) && isset($_GET['id'])) {
    $message_id = (int)$_GET['id'];
    $status = mysqli_real_escape_string($conn, $_GET['mark']);
    $valid_statuses = ['read', 'unread', 'replied'];
    if (in_array($status, $valid_statuses)) {
        $update_query = "UPDATE contact_messages SET status = '$status' WHERE message_id = $message_id";
        mysqli_query($conn, $update_query);
        $success = "Message marked as $status!";
    }
}

// Handle View Message
if (isset($_GET['view']) && !empty($_GET['view'])) {
    $message_id = (int)$_GET['view'];
    $view_query = "SELECT * FROM contact_messages WHERE message_id = $message_id";
    $view_result = mysqli_query($conn, $view_query);
    if ($view_message = mysqli_fetch_assoc($view_result)) {
        // Mark as read when viewed
        if ($view_message['status'] == 'unread') {
            $update_query = "UPDATE contact_messages SET status = 'read' WHERE message_id = $message_id";
            mysqli_query($conn, $update_query);
            $view_message['status'] = 'read';
        }
    }
}

// Get all messages
$sql = "SELECT * FROM contact_messages ORDER BY submitted_at DESC";
$messages = mysqli_query($conn, $sql);
$total_messages = mysqli_num_rows($messages);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Messages - Admin Panel</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin_style.css">
    <style>
       
    </style>
</head>
<body>
<div class="manage-container">
    <div class="manage-header">
        <h1>📩 Contact Messages</h1>
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

    <div class="table-container">
        <div class="table-header">
            <h2>All Messages</h2>
            <div class="count">Total: <?php echo $total_messages; ?> messages</div>
        </div>

        <?php if ($total_messages > 0): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th style="text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($msg = mysqli_fetch_assoc($messages)): ?>
                            <tr>
                                <td>#<?php echo $msg['message_id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($msg['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($msg['email']); ?></td>
                                <td class="message-preview"><?php echo htmlspecialchars(substr($msg['message'], 0, 60)); ?>...</td>
                                <td>
                                    <span class="message-status <?php echo $msg['status'] ?? 'unread'; ?>">
                                        <?php echo ucfirst($msg['status'] ?? 'unread'); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d M Y', strtotime($msg['submitted_at'])); ?></td>
                                <td style="text-align: center;">
                                    <div class="actions" style="justify-content: center;">
                                        <a href="manage_messages.php?view=<?php echo $msg['message_id']; ?>" class="btn-view">👁️ View</a>
                                        <a href="manage_messages.php?mark=read&id=<?php echo $msg['message_id']; ?>" class="btn-status read">Mark Read</a>
                                        <a href="manage_messages.php?mark=replied&id=<?php echo $msg['message_id']; ?>" class="btn-status replied">Mark Replied</a>
                                        <a href="manage_messages.php?delete=<?php echo $msg['message_id']; ?>" class="btn-delete" onclick="return confirm('Delete this message?')">🗑️ Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <span class="empty-icon">📩</span>
                <h3>No Messages Yet</h3>
                <p>No contact messages have been submitted.</p>
            </div>
        <?php endif; ?>
    </div>

    <div style="margin-top: 20px;">
        <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
    </div>
</div>

<!-- ========================================== -->
<!-- VIEW MESSAGE MODAL -->
<!-- ========================================== -->
<?php if ($view_message): ?>
<div class="modal-overlay" id="messageModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>📄 Message Details</h2>
            <a href="manage_messages.php" class="close-btn">&times;</a>
        </div>

        <!-- Sender Info -->
        <div class="message-detail-row">
            <span class="label">From:</span>
            <span class="value">
                <strong><?php echo htmlspecialchars($view_message['name']); ?></strong>
                <span style="color: #888; font-size: 14px;">&lt;<?php echo htmlspecialchars($view_message['email']); ?>&gt;</span>
            </span>
        </div>

        <!-- Date -->
        <div class="message-detail-row">
            <span class="label">Date:</span>
            <span class="value"><?php echo date('l, d F Y, h:i A', strtotime($view_message['submitted_at'])); ?></span>
        </div>

        <!-- Status -->
        <div class="message-detail-row">
            <span class="label">Status:</span>
            <span class="value">
                <span class="status-badge <?php echo $view_message['status'] ?? 'unread'; ?>">
                    <?php echo ucfirst($view_message['status'] ?? 'unread'); ?>
                </span>
            </span>
        </div>

        <!-- Full Message -->
        <div style="margin-top: 15px;">
            <strong style="color: #555; display: block; margin-bottom: 8px;">Message:</strong>
            <div class="message-full">
                <?php echo nl2br(htmlspecialchars($view_message['message'])); ?>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="modal-actions">
            <?php if ($view_message['status'] != 'read'): ?>
                <a href="manage_messages.php?mark=read&id=<?php echo $view_message['message_id']; ?>" class="btn btn-primary">📖 Mark as Read</a>
            <?php endif; ?>
            <?php if ($view_message['status'] != 'replied'): ?>
                <a href="manage_messages.php?mark=replied&id=<?php echo $view_message['message_id']; ?>" class="btn btn-success">✅ Mark as Replied</a>
            <?php endif; ?>
            <a href="manage_messages.php?delete=<?php echo $view_message['message_id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this message?')">🗑️ Delete</a>
            <a href="manage_messages.php" class="btn btn-secondary">Close</a>
        </div>
    </div>
</div>
<?php endif; ?>

</body>
</html>