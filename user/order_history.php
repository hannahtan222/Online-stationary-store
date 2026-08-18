<?php

require '../config/db_connection.php';
require_login();


// ==================================================
// READ - RETRIEVE CURRENT USER'S ORDERS
// ==================================================

$orderQuery = $pdo->prepare(
    'SELECT
        order_id,
        total_amount,
        order_date,
        status
     FROM orders
     WHERE user_id = ?
     ORDER BY order_date DESC'
);

$orderQuery->execute([
    $_SESSION['user_id']
]);

$orders = $orderQuery->fetchAll();


page_header('Order History');

?>

<section class="card">

    <h1>Order History</h1>


    <?php if (!$orders): ?>

        <div class="empty-state">

            <h2>No orders yet</h2>

            <p>
                Your orders will appear here
                after you complete checkout.
            </p>

        </div>

    <?php else: ?>

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
                                #<?= $order['order_id'] ?>
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


<?php page_footer(); ?>