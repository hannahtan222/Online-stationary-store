<?php

session_start();
require_once('../config/db_connection.php');
require_once('../config/auth.php');

require_login();

// ==================================================
// READ - RETRIEVE CURRENT USER'S CART
// ==================================================

$query = $pdo->prepare(
    'SELECT
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
     ORDER BY c.cart_id DESC'
);

$query->execute([
    $_SESSION['user_id']
]);

$items = $query->fetchAll();

// ==================================================
// CALCULATE CART TOTAL
// ==================================================

$total = 0;

foreach ($items as $item) {

    $total +=
        $item['price'] * $item['quantity'];
}

page_header('Shopping Cart');
?>

<section class="card">

    <h1>Shopping Cart</h1>

    <?php if (!$items): ?>

        <div class="empty-state">

            <h2>Your cart is empty</h2>

            <p>
                Add some stationery products
                to your cart.
            </p>

            <a
                class="button"
                href="products.php"
            >
                Continue Shopping
            </a>

        </div>

    <?php else: ?>

        <!-- ==========================================
             CART TABLE
        =========================================== -->

        <div class="table">

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
                            <td>
                                <?= e($item['product_name']) ?>
                            </td>
                            <td>
                                RM
                                <?= number_format(
                                    $item['price'],
                                    2
                                ) ?>
                            </td>
                            <td>
                                <!-- UPDATE CART -->
                                <form
                                    action="update_cart.php"
                                    method="post"
                                >
                                    <input
                                        type="hidden"
                                        name="cart_id"
                                        value="<?= $item['cart_id'] ?>"
                                    >

                                    <input
                                        type="number"
                                        name="quantity"
                                        value="<?= $item['quantity'] ?>"
                                        min="1"
                                        max="<?= $item['stock_quantity'] ?>"
                                    >

                                    <button type="submit">
                                        Update
                                    </button>
                                </form>
                            </td>
                            
                            <td>
                                RM
                                <?= number_format(
                                    $item['price'] *
                                    $item['quantity'],
                                    2
                                ) ?>
                            </td>

                            <td>
                                <!-- DELETE CART ITEM -->
                                <form
                                    action="remove_cart.php"
                                    method="post"
                                >
                                    <input
                                        type="hidden"
                                        name="cart_id"
                                        value="<?= $item['cart_id'] ?>"
                                    >

                                    <button type="submit">
                                        Remove
                                    </button>
                                </form>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                </tbody>
            </table>
        </div>

        <!-- ==========================================
             CART TOTAL
        =========================================== -->

        <h2>
            Total:
            RM <?= number_format($total, 2) ?>
        </h2>

        <div class="cart-actions">

            <a
                class="button"
                href="products.php"
            >
                Continue Shopping
            </a>

            <a
                class="button"
                href="checkout.php"
            >
                Proceed to Checkout
            </a>

        </div>

    <?php endif; ?>
</section>


<?php

page_footer();

?>
