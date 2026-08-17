<?php

require 'db_connect.php';
require_login();


// ==================================================
// READ - RETRIEVE CART ITEMS
// ==================================================

$cartQuery = $pdo->prepare(
    'SELECT
        c.product_id,
        c.quantity,
        p.product_name,
        p.price,
        p.stock_quantity
     FROM cart c
     JOIN products p
        ON p.product_id = c.product_id
     WHERE c.user_id = ?'
);

$cartQuery->execute([
    $_SESSION['user_id']
]);

$items = $cartQuery->fetchAll();


// ==================================================
// READ - RETRIEVE CUSTOMER INFORMATION
// ==================================================

$userQuery = $pdo->prepare(
    'SELECT
        full_name,
        address
     FROM users
     WHERE user_id = ?'
);

$userQuery->execute([
    $_SESSION['user_id']
]);

$user = $userQuery->fetch();


// ==================================================
// PROCESS CHECKOUT
// ==================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        // Start database transaction
        $pdo->beginTransaction();

        $total = 0;


        // ==========================================
        // CHECK STOCK AND CALCULATE TOTAL
        // ==========================================

        foreach ($items as $item) {

            if (
                $item['quantity'] >
                $item['stock_quantity']
            ) {

                throw new RuntimeException(
                    'Insufficient stock for ' .
                    $item['product_name']
                );
            }

            $total +=
                $item['price'] *
                $item['quantity'];
        }


        // ==========================================
        // CREATE - CREATE NEW ORDER
        // ==========================================

        $shippingAddress =
            trim($_POST['shipping_address'] ?? '');

        $createOrder = $pdo->prepare(
            'INSERT INTO orders
            (
                user_id,
                total_amount,
                shipping_address
            )
            VALUES (?, ?, ?)'
        );

        $createOrder->execute([
            $_SESSION['user_id'],
            $total,
            $shippingAddress
        ]);

        $orderId = $pdo->lastInsertId();


        // ==========================================
        // CREATE - CREATE ORDER ITEMS
        // ==========================================

        $createOrderItem = $pdo->prepare(
            'INSERT INTO order_items
            (
                order_id,
                product_id,
                quantity,
                price
            )
            VALUES (?, ?, ?, ?)'
        );


        // ==========================================
        // UPDATE - REDUCE PRODUCT STOCK
        // ==========================================

        $reduceStock = $pdo->prepare(
            'UPDATE products
             SET stock_quantity =
                 stock_quantity - ?
             WHERE product_id = ?'
        );


        foreach ($items as $item) {

            // Create order item
            $createOrderItem->execute([
                $orderId,
                $item['product_id'],
                $item['quantity'],
                $item['price']
            ]);


            // Reduce product stock
            $reduceStock->execute([
                $item['quantity'],
                $item['product_id']
            ]);
        }


        // ==========================================
        // DELETE - CLEAR CART
        // ==========================================

        $deleteCart = $pdo->prepare(
            'DELETE FROM cart
             WHERE user_id = ?'
        );

        $deleteCart->execute([
            $_SESSION['user_id']
        ]);


        // Complete transaction
        $pdo->commit();


        flash(
            'success',
            'Your order has been placed successfully.'
        );

        redirect('order_history.php');


    } catch (Throwable $error) {

        // Undo all database changes if something fails
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        flash(
            'error',
            'Order could not be placed.'
        );

        redirect('cart.php');
    }
}


page_header('Checkout');

?>

<section class="card">

    <h1>Checkout</h1>

    <h2>
        Customer Information
    </h2>

    <p>
        <strong>
            <?= e($user['full_name']) ?>
        </strong>
    </p>


    <form method="post">

        <label>
            Shipping Address

            <textarea
                name="shipping_address"
                required
            ><?= e($user['address']) ?></textarea>

        </label>


        <button type="submit">
            Confirm Order
        </button>

    </form>

</section>


<?php page_footer(); ?>