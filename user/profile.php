<?php
//profile.php
session_start();

// Include BOTH config files
require_once '../includes/config.php';        // For BASE_URL
require_once '../config/db_connection.php';   // For database connection
require_once '../config/auth.php';            // For authentication functions

/*<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/user_style.css">*/

// Require user to be logged in
require_login();

// ==================================================
// READ - RETRIEVE CURRENT USER PROFILE
// ==================================================

$statement = mysqli_prepare(
    $conn,
    "SELECT
        username,
        email,
        full_name,
        address,
        phone
     FROM users
     WHERE user_id = ?"
);

mysqli_stmt_bind_param(
    $statement,
    "i",
    $_SESSION['user_id']
);

mysqli_stmt_execute($statement);
$result = mysqli_stmt_get_result($statement);
$user = mysqli_fetch_assoc($result);

// If user not found, redirect to login
if (!$user) {
    flash('error', 'User not found. Please log in again.');
    redirect('login.php');
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Stationery Store</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        .profile-card {
            max-width: 700px;
            margin: 40px auto;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }
        .profile-card h1 {
            text-align: center;
            font-size: 28px;
            color: #1a1a2e;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f2f5;
        }
        .profile-details {
            margin-bottom: 25px;
        }
        .profile-details .detail-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #f8fafc;
        }
        .profile-details .detail-label {
            font-weight: 600;
            width: 120px;
            color: #555;
            flex-shrink: 0;
        }
        .profile-details .detail-value {
            color: #1a1a2e;
            word-break: break-word;
        }
        .profile-details .detail-value.address {
            white-space: pre-line;
        }
        .btn-edit {
            display: inline-block;
            background: #4A90D9;
            color: white;
            padding: 12px 35px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            text-align: center;
            width: 100%;
        }
        .btn-edit:hover {
            background: #357ABD;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(74, 144, 217, 0.35);
        }
        .profile-actions {
            display: flex;
            gap: 15px;
            margin-top: 10px;
            flex-wrap: wrap;
        }
        .profile-actions .btn-secondary {
            flex: 1;
            display: inline-block;
            background: #e8ecf1;
            color: #333;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s ease;
        }
        .profile-actions .btn-secondary:hover {
            background: #d1d5db;
        }
        .profile-actions .btn-danger {
            flex: 1;
            display: inline-block;
            background: #fee2e2;
            color: #dc2626;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s ease;
        }
        .profile-actions .btn-danger:hover {
            background: #fecaca;
        }
        @media (max-width: 600px) {
            .profile-card {
                padding: 25px 20px;
                margin: 20px 15px;
            }
            .profile-details .detail-row {
                flex-direction: column;
                padding: 10px 0;
            }
            .profile-details .detail-label {
                width: 100%;
                margin-bottom: 3px;
            }
            .profile-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<?php include '../includes/header.php'; ?>

<section class="profile-card">
    <h1>👤 My Profile</h1>

    <div class="profile-details">
        <div class="detail-row">
            <span class="detail-label">Username:</span>
            <span class="detail-value"><?php echo htmlspecialchars($user['username']); ?></span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Full Name:</span>
            <span class="detail-value"><?php echo htmlspecialchars($user['full_name'] ?? 'Not set'); ?></span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Email:</span>
            <span class="detail-value"><?php echo htmlspecialchars($user['email']); ?></span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Address:</span>
            <span class="detail-value address"><?php echo nl2br(htmlspecialchars($user['address'] ?? 'Not set')); ?></span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Phone:</span>
            <span class="detail-value"><?php echo htmlspecialchars($user['phone'] ?? 'Not set'); ?></span>
        </div>
    </div>

    <div class="profile-actions">
        <a href="update_profile.php" class="btn-secondary">✏️ Edit Profile</a>
        <a href="cart.php" class="btn-secondary">🛒 View Cart</a>
		<a href="order_history.php" class="btn-secondary">Order History</a>
        <a href="logout.php" class="btn-danger">🚪 Logout</a>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

</body>
</html>