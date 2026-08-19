<?php
//order_history.php
session_start();

require_once('../config/db_connection.php');
require_once('../config/auth.php');

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/user_style.css">

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
$orders = mysqli_fetch_all(
    $result,
    MYSQLI_ASSOC
);

// ==================================================
// PAGE HEADER
// ==================================================

include '../includes/header.php';

?>

<section class="card">

    <h1>Order History</h1>

    <?php if (!$orders): ?>

        <!-- ==========================================
             EMPTY ORDER HISTORY
        =========================================== -->

        <div class="empty-state">

            <h2>No orders yet</h2>

            <p>
                Your orders will appear here
                after you complete checkout.
            </p>

        </div>

    <?php else: ?>

        <!-- ==========================================
             ORDER TABLE
        =========================================== -->

        <div class="table">

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
                            <td>
                                #<?= e($order['order_id']) ?>
                            </td>

                            <td>
                                <?= e($order['order_date']) ?>
                            </td>

                            <td>
                                RM
                                <?= number_format(
                                    $order['total_amount'],
                                    2
                                ) ?>
                            </td>

                            <td>
                                <?= e($order['status']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php

include '../includes/footer.php';

?>
