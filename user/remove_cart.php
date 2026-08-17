<?php

require 'db_connect.php';
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

    // The user_id condition makes sure
    // users can only delete their own cart items.

    $deleteCart = $pdo->prepare(
        'DELETE FROM cart
         WHERE cart_id = ?
         AND user_id = ?'
    );

    $deleteCart->execute([
        $cartId,
        $_SESSION['user_id']
    ]);

    flash(
        'success',
        'Product removed from cart.'
    );
}


redirect('cart.php');