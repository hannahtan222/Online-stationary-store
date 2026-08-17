<?php

require 'db_connect.php';
require_login();


// ==================================================
// GET PRODUCT INFORMATION
// ==================================================

// Get product ID submitted by the user
$productId = filter_input(
    INPUT_POST,
    'product_id',
    FILTER_VALIDATE_INT
);

// Get quantity submitted by the user
// If no quantity is provided, use 1.
$quantity = filter_input(
    INPUT_POST,
    'quantity',
    FILTER_VALIDATE_INT
);

if (!$quantity) {
    $quantity = 1;
}


// ==================================================
// READ - CHECK PRODUCT STOCK
// ==================================================

$productQuery = $pdo->prepare(
    'SELECT stock_quantity
     FROM products
     WHERE product_id = ?'
);

$productQuery->execute([$productId]);

$product = $productQuery->fetch();


// Check whether the product exists
// and whether enough stock is available.
if (
    !$product ||
    $quantity < 1 ||
    $quantity > $product['stock_quantity']
) {

    flash(
        'error',
        'This product is unavailable in that quantity.'
    );

    redirect('products.php');
}


// ==================================================
// READ - CHECK EXISTING CART ITEM
// ==================================================

$cartQuery = $pdo->prepare(
    'SELECT cart_id, quantity
     FROM cart
     WHERE user_id = ?
     AND product_id = ?'
);

$cartQuery->execute([
    $_SESSION['user_id'],
    $productId
]);

$cartItem = $cartQuery->fetch();


// ==================================================
// UPDATE OR CREATE CART ITEM
// ==================================================

if ($cartItem) {

    // ==============================================
    // UPDATE - Increase Existing Cart Quantity
    // ==============================================

    $newQuantity =
        $cartItem['quantity'] + $quantity;

    if ($newQuantity > $product['stock_quantity']) {

        flash(
            'error',
            'Quantity cannot exceed available stock.'
        );

    } else {

        $updateCart = $pdo->prepare(
            'UPDATE cart
             SET quantity = quantity + ?
             WHERE cart_id = ?'
        );

        $updateCart->execute([
            $quantity,
            $cartItem['cart_id']
        ]);

        flash(
            'success',
            'Product added to cart successfully.'
        );
    }

} else {

    // ==============================================
    // CREATE - Add New Item To Cart
    // ==============================================

    $addCart = $pdo->prepare(
        'INSERT INTO cart
        (user_id, product_id, quantity)
        VALUES (?, ?, ?)'
    );

    $addCart->execute([
        $_SESSION['user_id'],
        $productId,
        $quantity
    ]);

    flash(
        'success',
        'Product added to cart successfully.'
    );
}


// ==================================================
// REDIRECT TO CART
// ==================================================

redirect('cart.php');