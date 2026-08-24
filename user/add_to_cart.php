<?php
// modules/member3_user/add_to_cart.php
session_start();

// Include BOTH config files
require_once '../includes/config.php';        // For BASE_URL
require_once '../config/db_connection.php';   // For database connection
require_once '../config/auth.php';            // For authentication functions

// Require user to be logged in
require_login();

// ==================================================
// GET PRODUCT INFORMATION
// ==================================================

// Get product ID submitted by the user.
$productId = filter_input(INPUT_GET, 'add', FILTER_VALIDATE_INT);
if (!$productId) {
    $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
}

// If still no product ID, redirect to products
if (!$productId) {
    flash('error', 'No product selected.');
    redirect(BASE_URL . 'modules/member2_products/products.php');
    exit();
}

// Get quantity submitted by the user.
// If no quantity is provided, use 1.
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
if (!$quantity) {
    $quantity = 1;
}

// ==================================================
// READ - CHECK PRODUCT STOCK
// ==================================================

$productQuery = mysqli_prepare(
    $conn,
    "SELECT stock_quantity
     FROM products
     WHERE product_id = ?"
);

mysqli_stmt_bind_param(
    $productQuery,
    "i",
    $productId
);

mysqli_stmt_execute($productQuery);
$result = mysqli_stmt_get_result($productQuery);
$product = mysqli_fetch_assoc($result);

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

    redirect(BASE_URL . 'product_module/products.php');
}

// ==================================================
// READ - CHECK EXISTING CART ITEM
// ==================================================

$cartQuery = mysqli_prepare(
    $conn,
    "SELECT cart_id, quantity
     FROM cart
     WHERE user_id = ?
     AND product_id = ?"
);

mysqli_stmt_bind_param(
    $cartQuery,
    "ii",
    $_SESSION['user_id'],
    $productId
);

mysqli_stmt_execute($cartQuery);
$result = mysqli_stmt_get_result($cartQuery);
$cartItem = mysqli_fetch_assoc($result);

// ==================================================
// UPDATE OR CREATE CART ITEM
// ==================================================

if ($cartItem) {

    // ==============================================
    // UPDATE - INCREASE EXISTING CART QUANTITY
    // ==============================================

    $newQuantity = $cartItem['quantity'] + $quantity;

    if ($newQuantity > $product['stock_quantity']) {

        flash(
            'error',
            'Quantity cannot exceed available stock.'
        );

    } else {

        $updateCart = mysqli_prepare(
            $conn,
            "UPDATE cart
             SET quantity = quantity + ?
             WHERE cart_id = ?"
        );

        mysqli_stmt_bind_param(
            $updateCart,
            "ii",
            $quantity,
            $cartItem['cart_id']
        );

        mysqli_stmt_execute($updateCart);

        flash(
            'success',
            'Product added to cart successfully.'
        );
    }

} else {

    // ==============================================
    // CREATE - ADD NEW ITEM TO CART
    // ==============================================

    $addCart = mysqli_prepare(
        $conn,
        "INSERT INTO cart
        (user_id, product_id, quantity)
        VALUES (?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $addCart,
        "iii",
        $_SESSION['user_id'],
        $productId,
        $quantity
    );

    mysqli_stmt_execute($addCart);

    flash(
        'success',
        'Product added to cart successfully.'
    );
}

// ==================================================
// REDIRECT TO CART
// ==================================================

redirect('cart.php');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add to Cart - Stationery Store</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/user_style.css">
</head>
<body>
    <!-- This page redirects immediately -->
    <p>Redirecting to cart...</p>
</body>
</html>