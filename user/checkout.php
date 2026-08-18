<?php

session_start();

require_once('../config/db_connection.php');
require_once('../config/auth.php');

require_login();

// ==================================================
// READ - RETRIEVE CART ITEMS
// ==================================================

$cartQuery = mysqli_prepare(
    $conn,
    "SELECT
        c.product_id,
        c.quantity,
        p.product_name,
        p.price,
        p.stock_quantity
     FROM cart c
     JOIN products p
        ON p.product_id = c.product_id
     WHERE c.user_id = ?"
);

mysqli_stmt_bind_param(
    $cartQuery,
    "i",
    $_SESSION['user_id']
);

mysqli_stmt_execute($cartQuery);

$result = mysqli_stmt_get_result($cartQuery);

$items = mysqli_fetch_all(
    $result,
    MYSQLI_ASSOC
);

// ==================================================
// READ - RETRIEVE CUSTOMER INFORMATION
// ==================================================

$userQuery = mysqli_prepare(
    $conn,
    "SELECT
        full_name,
        address
     FROM users
     WHERE user_id = ?"
);

mysqli_stmt_bind_param(
    $userQuery,
    "i",
    $_SESSION['user_id']
);

mysqli_stmt_execute($userQuery);

$result = mysqli_stmt_get_result($userQuery);

$user = mysqli_fetch_assoc($result);

// ==================================================
// PROCESS CHECKOUT
// ==================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        // Start database transaction.
        mysqli_begin_transaction($conn);

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
        // GET SHIPPING ADDRESS
        // ==========================================

        $shippingAddress =
            trim($_POST['shipping_address'] ?? '');

        // ==========================================
        // CREATE - CREATE NEW ORDER
        // ==========================================

        $createOrder = mysqli_prepare(
            $conn,
            "INSERT INTO orders
            (
                user_id,
                total_amount,
                shipping_address
            )
            VALUES (?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $createOrder,
            "ids",
            $_SESSION['user_id'],
            $total,
            $shippingAddress
        );

        mysqli_stmt_execute($createOrder);

        $orderId = mysqli_insert_id($conn);

        // ==========================================
        // CREATE - CREATE ORDER ITEMS
        // ==========================================

        $createOrderItem = mysqli_prepare(
            $conn,
            "INSERT INTO order_items
            (
                order_id,
                product_id,
                quantity,
                price
            )
            VALUES (?, ?, ?, ?)"
        );

        // ==========================================
        // UPDATE - REDUCE PRODUCT STOCK
        // ==========================================

        $reduceStock = mysqli_prepare(
            $conn,
            "UPDATE products
             SET stock_quantity =
                 stock_quantity - ?
             WHERE product_id = ?"
        );

        foreach ($items as $item) {

            // Create order item.
            mysqli_stmt_bind_param(
                $createOrderItem,
                "iiid",
                $orderId,
                $item['product_id'],
                $item['quantity'],
                $item['price']
            );

            mysqli_stmt_execute($createOrderItem);


            // Reduce product stock.
            mysqli_stmt_bind_param(
                $reduceStock,
                "ii",
                $item['quantity'],
                $item['product_id']
            );

            mysqli_stmt_execute($reduceStock);
        }

        // ==========================================
        // DELETE - CLEAR CART
        // ==========================================

        $deleteCart = mysqli_prepare(
            $conn,
            "DELETE FROM cart
             WHERE user_id = ?"
        );

        mysqli_stmt_bind_param(
            $deleteCart,
            "i",
            $_SESSION['user_id']
        );

        mysqli_stmt_execute($deleteCart);

        // Complete transaction.
        mysqli_commit($conn);

        flash(
            'success',
            'Your order has been placed successfully.'
        );

        redirect('order_history.php');

    } catch (Throwable $error) {

        // Undo database changes if something fails.
        mysqli_rollback($conn);

        flash(
            'error',
            'Order could not be placed.'
        );

        redirect('cart.php');
    }
}

// ==================================================
// DISPLAY CHECKOUT PAGE
// ==================================================

include '../includes/header.php';

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

<?php include '../includes/footer.php'; ?>
