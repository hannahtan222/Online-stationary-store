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
    <style>
        .checkout-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .checkout-container h1 {
            text-align: center;
            font-size: 32px;
            color: #1a1a2e;
            margin-bottom: 30px;
        }
        .checkout-card {
            background: white;
            border-radius: 12px;
            padding: 30px 35px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }
        .checkout-card h2 {
            font-size: 20px;
            color: #1a1a2e;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f2f5;
        }
        .checkout-card .customer-name {
            font-size: 18px;
            color: #1a1a2e;
            margin-bottom: 5px;
        }
        .checkout-card .customer-email {
            color: #888;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .checkout-card .order-summary {
            margin: 20px 0;
            background: #f8fafc;
            border-radius: 8px;
            padding: 15px;
        }
        .checkout-card .order-summary table {
            width: 100%;
            border-collapse: collapse;
        }
        .checkout-card .order-summary th {
            text-align: left;
            padding: 8px 10px;
            font-size: 13px;
            color: #888;
            font-weight: 600;
            border-bottom: 1px solid #e8ecf1;
        }
        .checkout-card .order-summary td {
            padding: 10px 10px;
            border-bottom: 1px solid #f0f2f5;
            font-size: 14px;
        }
        .checkout-card .order-summary .total-row {
            font-weight: 700;
            font-size: 16px;
            color: #1a1a2e;
        }
        .checkout-card .order-summary .total-row td {
            border-bottom: none;
            padding-top: 15px;
        }
        .checkout-card label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #444;
        }
        .checkout-card textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e8ecf1;
            border-radius: 8px;
            font-size: 15px;
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        .checkout-card textarea:focus {
            border-color: #4A90D9;
            outline: none;
            box-shadow: 0 0 0 4px rgba(74, 144, 217, 0.15);
        }
        .checkout-card .btn-submit {
            width: 100%;
            padding: 14px;
            background: #4A90D9;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .checkout-card .btn-submit:hover {
            background: #357ABD;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(74, 144, 217, 0.35);
        }
        .checkout-card .btn-submit:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        .checkout-card .empty-cart {
            text-align: center;
            padding: 40px 20px;
            color: #888;
        }
        .checkout-card .empty-cart h2 {
            border-bottom: none;
            color: #333;
        }
        .checkout-card .back-link {
            display: inline-block;
            margin-top: 15px;
            color: #4A90D9;
            text-decoration: none;
        }
        .checkout-card .back-link:hover {
            text-decoration: underline;
        }
        @media (max-width: 600px) {
            .checkout-card {
                padding: 20px;
            }
            .checkout-card .order-summary {
                overflow-x: auto;
            }
        }
    </style>
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