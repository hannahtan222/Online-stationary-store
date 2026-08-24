<?php
// modules/member3_user/remove_cart.php
session_start();

// Include BOTH config files - FIXED PATHS
require_once '../includes/config.php';        // For BASE_URL
require_once '../config/db_connection.php';   // For database connection
require_once '../config/auth.php';            // For authentication functions

require_login();

// ==================================================
// GET CART ID
// ==================================================

$cartId = filter_input(
    INPUT_POST,
    'cart_id',
    FILTER_VALIDATE_INT
);

// ==================================================
// DELETE - REMOVE CART ITEM
// ==================================================

if ($cartId) {

    // The user_id condition ensures
    // users can only delete their own cart items.

    $deleteCart = mysqli_prepare(
        $conn,
        "DELETE FROM cart
         WHERE cart_id = ?
         AND user_id = ?"
    );

    mysqli_stmt_bind_param(
        $deleteCart,
        "ii",
        $cartId,
        $_SESSION['user_id']
    );

    mysqli_stmt_execute($deleteCart);

    flash(
        'success',
        'Product removed from cart successfully.'
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
    <title>Remove from Cart - Stationery Store</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/user_style.css">
</head>
<body>
    <p>Removing item from cart...</p>
</body>
</html>