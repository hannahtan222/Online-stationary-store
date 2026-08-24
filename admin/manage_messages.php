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
        .manage-container { max-width: 1100px; margin: 0 auto; padding: 20px; }
        .manage-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .manage-header h1 { font-size: 28px; color: #1a1a2e; }
        .manage-header .admin-info { display: flex; gap: 15px; align-items: center; }
        .manage-header .admin-info span { color: #666; }
        .manage-header .admin-info strong { color: #1a1a2e; }
        .btn-logout { background: #fee2e2; color: #dc2626; padding: 8px 20px; border-radius: 8px; text-decoration: none; font-weight: 500; }
        .btn-logout:hover { background: #fecaca; }
        .btn-back { display: inline-block; padding: 8px 20px; background: #e8ecf1; color: #333; border-radius: 8px; text-decoration: none; font-weight: 500; }
        .btn-back:hover { background: #d1d5db; }
        .table-container { background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); padding: 20px; }
        .table-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; }
        .table-header h2 { font-size: 18px; color: #1a1a2e; }
        .table-header .count { color: #888; font-size: 14px; }
        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        table thead { background: #f8fafc; }
        table th { padding: 12px 15px; text-align: left; font-weight: 600; color: #555; font-size: 13px; white-space: nowrap; }
        table td { padding: 12px 15px; border-top: 1px solid #f0f2f5; vertical-align: middle; font-size: 14px; }
        table tbody tr:hover { background: #fafbfc; }
        
        .message-status { padding: 3px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .message-status.unread { background: #fee2e2; color: #991b1b; }
        .message-status.read { background: #dbeafe; color: #1e40af; }
        .message-status.replied { background: #d1fae5; color: #065f46; }
        
        .message-preview { max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .btn-view { padding: 6px 16px; background: #e8ecf1; color: #333; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 500; }
        .btn-view:hover { background: #d1d5db; }
        .btn-delete { padding: 6px 16px; background: #fee2e2; color: #dc2626; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 500; }
        .btn-delete:hover { background: #fecaca; }
        .btn-status { padding: 4px 12px; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 500; }
        .btn-status.read { background: #dbeafe; color: #1e40af; }
        .btn-status.unread { background: #fee2e2; color: #991b1b; }
        .btn-status.replied { background: #d1fae5; color: #065f46; }
        .btn-status:hover { opacity: 0.8; }
        
        .error-message { background: #fee2e2; color: #991b1b; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: 500; }
        .success-message { background: #d1fae5; color: #065f46; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: 500; }
        .empty-state { text-align: center; padding: 60px 20px; color: #888; }
        .empty-state .empty-icon { font-size: 64px; display: block; margin-bottom: 15px; }
        .empty-state h3 { color: #333; margin-bottom: 8px; }

        /* ==========================================
           VIEW MESSAGE MODAL
        =========================================== */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideDown {
            from { transform: translateY(-30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .modal-content {
            background: white;
            border-radius: 12px;
            max-width: 650px;
            width: 95%;
            max-height: 90vh;
            overflow-y: auto;
            padding: 35px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideDown 0.3s ease;
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
            margin: 0;
        }
        .modal-content .modal-header .close-btn {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #888;
            transition: color 0.3s ease;
            text-decoration: none;
        }
        .modal-content .modal-header .close-btn:hover {
            color: #333;
        }
        .message-detail-row {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid #f8fafc;
        }
        .message-detail-row .label {
            font-weight: 600;
            width: 100px;
            color: #555;
            flex-shrink: 0;
        }
        .message-detail-row .value {
            flex: 1;
            color: #1a1a2e;
            word-break: break-word;
        }
        .message-detail-row .value .status-badge {
            display: inline-block;
            padding: 3px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .message-detail-row .value .status-badge.unread { background: #fee2e2; color: #991b1b; }
        .message-detail-row .value .status-badge.read { background: #dbeafe; color: #1e40af; }
        .message-detail-row .value .status-badge.replied { background: #d1fae5; color: #065f46; }
        .message-full {
            background: #f8fafc;
            border-radius: 8px;
            padding: 20px;
            margin: 15px 0 20px;
            line-height: 1.8;
            color: #333;
            white-space: pre-wrap;
            word-break: break-word;
            max-height: 250px;
            overflow-y: auto;
        }
        .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .modal-actions .btn {
            padding: 10px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .modal-actions .btn-primary {
            background: #4A90D9;
            color: white;
        }
        .modal-actions .btn-primary:hover {
            background: #357ABD;
        }
        .modal-actions .btn-success {
            background: #10B981;
            color: white;
        }
        .modal-actions .btn-success:hover {
            background: #059669;
        }
        .modal-actions .btn-danger {
            background: #fee2e2;
            color: #dc2626;
        }
        .modal-actions .btn-danger:hover {
            background: #fecaca;
        }
        .modal-actions .btn-secondary {
            background: #e8ecf1;
            color: #333;
        }
        .modal-actions .btn-secondary:hover {
            background: #d1d5db;
        }

        @media (max-width: 768px) {
            .manage-header { flex-direction: column; align-items: flex-start; }
            .manage-header .admin-info { width: 100%; justify-content: space-between; }
            table { font-size: 13px; min-width: 600px; }
            .modal-content { padding: 20px; }
            .message-detail-row { flex-direction: column; }
            .message-detail-row .label { width: 100%; margin-bottom: 3px; }
            .modal-actions { flex-direction: column; }
            .modal-actions .btn { text-align: center; }
        }
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