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
    <style>
        .cart-container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .cart-container h1 {
            text-align: center;
            font-size: 32px;
            color: #1a1a2e;
            margin-bottom: 30px;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }
        .empty-state h2 {
            color: #333;
            margin-bottom: 10px;
        }
        .empty-state p {
            color: #888;
            margin-bottom: 20px;
        }
        .empty-state .button {
            display: inline-block;
            padding: 12px 35px;
            background: #4A90D9;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .empty-state .button:hover {
            background: #357ABD;
        }
        .table-wrapper {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            overflow: hidden;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table thead {
            background: #f8fafc;
        }
        table th {
            padding: 14px 15px;
            text-align: left;
            font-weight: 600;
            color: #555;
            font-size: 14px;
        }
        table td {
            padding: 14px 15px;
            border-top: 1px solid #f0f2f5;
            vertical-align: middle;
        }
        table td .product-name {
            font-weight: 500;
            color: #1a1a2e;
        }
        table td form {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }
        table td input[type="number"] {
            width: 60px;
            padding: 6px 8px;
            border: 2px solid #e8ecf1;
            border-radius: 6px;
            text-align: center;
        }
        table td input[type="number"]:focus {
            border-color: #4A90D9;
            outline: none;
        }
        table td .btn-update {
            padding: 6px 15px;
            background: #e8ecf1;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        table td .btn-update:hover {
            background: #d1d5db;
        }
        table td .btn-remove {
            padding: 6px 15px;
            background: #fee2e2;
            color: #dc2626;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        table td .btn-remove:hover {
            background: #fecaca;
        }
        .cart-total {
            text-align: right;
            padding: 20px 0;
            font-size: 24px;
            color: #1a1a2e;
        }
        .cart-total span {
            color: #4A90D9;
        }
        .cart-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .cart-actions .button {
            display: inline-block;
            padding: 12px 35px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .cart-actions .button-secondary {
            background: #e8ecf1;
            color: #333;
        }
        .cart-actions .button-secondary:hover {
            background: #d1d5db;
        }
        .cart-actions .button-primary {
            background: #4A90D9;
            color: white;
        }
        .cart-actions .button-primary:hover {
            background: #357ABD;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(74, 144, 217, 0.35);
        }
        @media (max-width: 768px) {
            .table-wrapper {
                overflow-x: auto;
            }
            table {
                font-size: 14px;
                min-width: 550px;
            }
            .cart-actions {
                flex-direction: column;
            }
            .cart-actions .button {
                text-align: center;
            }
        }
    </style>
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