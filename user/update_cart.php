<?php

require 'db_connect.php';
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

$itemQuery = $pdo->prepare(
    'SELECT
        p.stock_quantity
     FROM cart c
     JOIN products p
        ON p.product_id = c.product_id
     WHERE c.cart_id = ?
     AND c.user_id = ?'
);

$itemQuery->execute([
    $cartId,
    $_SESSION['user_id']
]);

$item = $itemQuery->fetch();


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

    $updateCart = $pdo->prepare(
        'UPDATE cart
         SET quantity = ?
         WHERE cart_id = ?
         AND user_id = ?'
    );

    $updateCart->execute([
        $quantity,
        $cartId,
        $_SESSION['user_id']
    ]);


    flash(
        'success',
        'Cart updated.'
    );
}


redirect('cart.php');