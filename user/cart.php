<?php
// modules/member3_user/cart.php
session_start();

// Include BOTH config files - FIXED PATHS
require_once '../includes/config.php';        // For BASE_URL
require_once '../config/db_connection.php';   // For database connection
require_once '../config/auth.php';            // For authentication functions

require_login();

// ==================================================
// READ - RETRIEVE CURRENT USER'S CART
// ==================================================

$query = mysqli_prepare(
    $conn,
    "SELECT
        c.cart_id,
        c.quantity,
        p.product_name,
        p.price,
        p.stock_quantity,
        p.image_url
     FROM cart c
     JOIN products p
        ON p.product_id = c.product_id
     WHERE c.user_id = ?
     ORDER BY c.cart_id DESC"
);

mysqli_stmt_bind_param(
    $query,
    "i",
    $_SESSION['user_id']
);

mysqli_stmt_execute($query);
$result = mysqli_stmt_get_result($query);
$items = mysqli_fetch_all($result, MYSQLI_ASSOC);

// ==================================================
// CALCULATE CART TOTAL
// ==================================================

$total = 0;
foreach ($items as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Stationery Store</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/user_style.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<section class="cart-container">

    <h1>🛒 Shopping Cart</h1>

    <?php if (!$items): ?>

        <div class="empty-state">
            <h2>Your cart is empty</h2>
            <p>Add some stationery products to your cart.</p>
            <a class="button" href="<?php echo BASE_URL; ?>product_module/products.php">
                Continue Shopping
            </a>
        </div>

    <?php else: ?>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Unit Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td class="product-name">
                                <?php echo htmlspecialchars($item['product_name']); ?>
                            </td>
                            <td>RM <?php echo number_format($item['price'], 2); ?></td>
                            <td>
                                <form action="update_cart.php" method="post">
                                    <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock_quantity']; ?>">
                                    <button type="submit" class="btn-update">Update</button>
                                </form>
                            </td>
                            <td>RM <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            <td>
                                <form action="remove_cart.php" method="post">
                                    <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                    <button type="submit" class="btn-remove">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="cart-total">
            <h2>Total: <span>RM <?php echo number_format($total, 2); ?></span></h2>
        </div>

        <div class="cart-actions">
            <a class="button button-secondary" href="<?php echo BASE_URL; ?>product_module/products.php">
                Continue Shopping
            </a>
            <a class="button button-primary" href="checkout.php">
                Proceed to Checkout
            </a>
        </div>

    <?php endif; ?>

</section>

<?php include '../includes/footer.php'; ?>

</body>
</html>