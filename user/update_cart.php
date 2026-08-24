<?php
// modules/member3_user/update_cart.php
session_start();

// Include BOTH config files - FIXED PATHS
require_once '../includes/config.php';        // For BASE_URL
require_once '../config/db_connection.php';   // For database connection
require_once '../config/auth.php';            // For authentication functions

require_login();

// ==================================================
// GET CART INFORMATION
// ==================================================

$cartId = filter_input(
    INPUT_POST,
    'cart_id',
    FILTER_VALIDATE_INT
);

$quantity = filter_input(
    INPUT_POST,
    'quantity',
    FILTER_VALIDATE_INT
);

// ==================================================
// READ - CHECK CART ITEM
// ==================================================

$itemQuery = mysqli_prepare(
    $conn,
    "SELECT
        p.stock_quantity
     FROM cart c
     JOIN products p
        ON p.product_id = c.product_id
     WHERE c.cart_id = ?
     AND c.user_id = ?"
);

mysqli_stmt_bind_param(
    $itemQuery,
    "ii",
    $cartId,
    $_SESSION['user_id']
);

mysqli_stmt_execute($itemQuery);
$result = mysqli_stmt_get_result($itemQuery);
$item = mysqli_fetch_assoc($result);

// ==================================================
// VALIDATE QUANTITY
// ==================================================

if (
    !$item ||
    $quantity < 1 ||
    $quantity > $item['stock_quantity']
) {

    flash(
        'error',
        'Please enter a valid available quantity.'
    );

} else {

    // ==============================================
    // UPDATE - CHANGE CART QUANTITY
    // ==============================================

    $updateCart = mysqli_prepare(
        $conn,
        "UPDATE cart
         SET quantity = ?
         WHERE cart_id = ?
         AND user_id = ?"
    );

    mysqli_stmt_bind_param(
        $updateCart,
        "iii",
        $quantity,
        $cartId,
        $_SESSION['user_id']
    );

    mysqli_stmt_execute($updateCart);

    flash(
        'success',
        'Cart updated successfully.'
    );
}

// ==================================================
// REDIRECT TO CART - FIXED
// ==================================================

redirect(BASE_URL . 'user/cart.php');
exit();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Cart - Stationery Store</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/user_style.css">
</head>
<body>
    <p>Updating cart...</p>
</body>
</html>