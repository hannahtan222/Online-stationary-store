<?php
// modules/member3_user/checkout.php
session_start();

// Include BOTH config files - FIXED PATHS
require_once '../includes/config.php';        // For BASE_URL
require_once '../config/db_connection.php';   // For database connection
require_once '../config/auth.php';            // For authentication functions

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
$items = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Redirect to cart if empty
if (empty($items)) {
    flash('error', 'Your cart is empty.');
    redirect(BASE_URL . 'modules/member3_user/cart.php');
    exit();
}

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

            if ($item['quantity'] > $item['stock_quantity']) {

                throw new RuntimeException(
                    'Insufficient stock for ' . $item['product_name']
                );
            }

            $total += $item['price'] * $item['quantity'];
        }

        // ==========================================
        // GET SHIPPING ADDRESS
        // ==========================================

        $shippingAddress = trim($_POST['shipping_address'] ?? '');

        if (empty($shippingAddress)) {
            throw new RuntimeException('Please enter a shipping address.');
        }

        // ==========================================
        // CREATE - CREATE NEW ORDER
        // ==========================================

        $createOrder = mysqli_prepare(
            $conn,
            "INSERT INTO orders
            (
                user_id,
                total_amount,
                shipping_address,
                status
            )
            VALUES (?, ?, ?, 'Pending')"
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
             SET stock_quantity = stock_quantity - ?
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
            'Your order has been placed successfully! Order #' . $orderId
        );

        redirect(BASE_URL . 'user/order_history.php');

    } catch (Throwable $error) {

        // Undo database changes if something fails.
        mysqli_rollback($conn);

        flash(
            'error',
            $error->getMessage() ?: 'Order could not be placed. Please try again.'
        );

        redirect(BASE_URL . 'user/cart.php');
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Stationery Store</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/user_style.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<section class="checkout-container">

    <h1>🛒 Checkout</h1>

    <div class="checkout-card">

        <?php if (empty($items)): ?>
            <div class="empty-cart">
                <h2>Your cart is empty</h2>
                <p>Add some products before checking out.</p>
                <a href="<?php echo BASE_URL; ?>product_module/products.php" class="back-link">← Continue Shopping</a>
            </div>
        <?php else: ?>

            <h2>Customer Information</h2>
            <p class="customer-name"><strong><?php echo htmlspecialchars($user['full_name'] ?? 'Customer'); ?></strong></p>
            <p class="customer-email"><?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></p>

            <h2 style="margin-top: 25px;">Order Summary</h2>
            <div class="order-summary">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th style="text-align: center;">Qty</th>
                            <th style="text-align: right;">Price</th>
                            <th style="text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $subtotal = 0;
                        foreach ($items as $item): 
                            $item_total = $item['price'] * $item['quantity'];
                            $subtotal += $item_total;
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td style="text-align: center;"><?php echo $item['quantity']; ?></td>
                                <td style="text-align: right;">RM <?php echo number_format($item['price'], 2); ?></td>
                                <td style="text-align: right;">RM <?php echo number_format($item_total, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="total-row">
                            <td colspan="3" style="text-align: right;">Total:</td>
                            <td style="text-align: right;">RM <?php echo number_format($subtotal, 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <form method="POST" action="">
                <label for="shipping_address">Shipping Address <span style="color: #dc2626;">*</span></label>
                <textarea 
                    name="shipping_address" 
                    id="shipping_address" 
                    required
                    placeholder="Enter your full shipping address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>

                <button type="submit" class="btn-submit">✅ Confirm Order</button>
            </form>

        <?php endif; ?>

    </div>

</section>

<?php include '../includes/footer.php'; ?>

</body>
</html>