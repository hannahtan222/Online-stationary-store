<?php
// modules/member3_user/order_history.php
session_start();

// Include BOTH config files - FIXED PATHS
require_once '../includes/config.php';        // For BASE_URL
require_once '../config/db_connection.php';   // For database connection
require_once '../config/auth.php';            // For authentication functions

require_login();

// ==================================================
// READ - RETRIEVE CURRENT USER'S ORDERS
// ==================================================

$orderQuery = mysqli_prepare(
    $conn,
    "SELECT
        order_id,
        total_amount,
        order_date,
        status
     FROM orders
     WHERE user_id = ?
     ORDER BY order_date DESC"
);

// Bind the logged-in user's ID.
mysqli_stmt_bind_param(
    $orderQuery,
    "i",
    $_SESSION['user_id']
);

// Execute the query.
mysqli_stmt_execute($orderQuery);

// Get query result.
$result = mysqli_stmt_get_result($orderQuery);

// Convert results into an associative array.
$orders = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - Stationery Store</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/user_style.css">
    <style>
        .order-container {
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .order-container h1 {
            text-align: center;
            font-size: 32px;
            color: #1a1a2e;
            margin-bottom: 30px;
        }
        .order-card {
            background: white;
            border-radius: 12px;
            padding: 30px 35px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }
        .order-card .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        .order-card .empty-state h2 {
            color: #333;
            margin-bottom: 10px;
        }
        .order-card .empty-state p {
            color: #888;
            margin-bottom: 20px;
        }
        .order-card .empty-state .button {
            display: inline-block;
            padding: 12px 35px;
            background: #4A90D9;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .order-card .empty-state .button:hover {
            background: #357ABD;
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
            padding: 14px 15px;
            text-align: left;
            font-weight: 600;
            color: #555;
            font-size: 14px;
        }
        table td {
            padding: 14px 15px;
            border-top: 1px solid #f0f2f5;
            vertical-align: middle;
        }
        table .order-id {
            font-weight: 600;
            color: #1a1a2e;
        }
        table .order-status {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-pending {
            background: #fef3c7;
            color: #d97706;
        }
        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }
        .status-shipped {
            background: #dbeafe;
            color: #1e40af;
        }
        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }
        .order-back-link {
            display: inline-block;
            margin-top: 20px;
            color: #4A90D9;
            text-decoration: none;
        }
        .order-back-link:hover {
            text-decoration: underline;
        }
        @media (max-width: 600px) {
            .order-card {
                padding: 20px;
            }
            table {
                font-size: 14px;
                min-width: 400px;
            }
        }
    </style>
</head>
<body>

<?php include '../includes/header.php'; ?>

<section class="order-container">

    <h1>📦 Order History</h1>

    <div class="order-card">

        <?php if (!$orders): ?>

            <!-- ==========================================
                 EMPTY ORDER HISTORY
            =========================================== -->
            <div class="empty-state">
                <h2>No orders yet</h2>
                <p>Your orders will appear here after you complete checkout.</p>
                <a href="<?php echo BASE_URL; ?>product_module/products.php" class="button">
                    Start Shopping
                </a>
            </div>

        <?php else: ?>

            <!-- ==========================================
                 ORDER TABLE
            =========================================== -->
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="order-id">#<?php echo $order['order_id']; ?></td>
                                <td><?php echo date('d M Y, h:i A', strtotime($order['order_date'])); ?></td>
                                <td><strong>RM <?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                <td>
                                    <span class="order-status status-<?php echo strtolower($order['status']); ?>">
                                        <?php echo $order['status']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <a href="<?php echo BASE_URL; ?>product_module/products.php" class="order-back-link">
                ← Continue Shopping
            </a>

        <?php endif; ?>

    </div>

</section>

<?php include '../includes/footer.php'; ?>

</body>
</html>