<?php
//remove_cart.php
session_start();

require_once('../config/db_connection.php');
require_once('../config/auth.php');

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/user_style.css">

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
        'Product removed from cart.'
    );
}

// ==================================================
// REDIRECT TO CART
// ==================================================

redirect('cart.php');
