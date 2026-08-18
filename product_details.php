<?php
session_start();
include 'header.php';
include 'config.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$product = null;

if ($product_id > 0) {
    $sql = "SELECT p.product_id, p.product_name, p.description, p.price, p.image,
                   c.category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.category_id
            WHERE p.product_id = $product_id
            LIMIT 1";

    $result = $conn->query($sql);

    if ($result) {
        $product = $result->fetch_assoc();
    }
}
?>

<section class="products-page">

    <?php if (!$product): ?>

        <div class="no-products product-not-found">
            <h1>Product Not Found</h1>
            <p>The product you are looking for does not exist.</p>
            <a href="products.php" class="product-btn">Back to Products</a>
        </div>

    <?php else: ?>

        <a href="products.php" class="product-back">&larr; Back to Products</a>

        <div class="product-detail">

            <div class="product-detail-image">
                <?php if (!empty($product['image'])): ?>
                    <img
                        src="<?php echo htmlspecialchars($product['image']); ?>"
                        alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                    >
                <?php else: ?>
                    <div class="no-image">No Image</div>
                <?php endif; ?>
            </div>

            <div class="product-detail-info">
                <span class="product-category">
                    <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                </span>

                <h1><?php echo htmlspecialchars($product['product_name']); ?></h1>

                <div class="product-detail-price">
                    RM <?php echo number_format((float)$product['price'], 2); ?>
                </div>

                <h3>Description</h3>
                <p class="product-description">
                    <?php echo nl2br(htmlspecialchars($product['description'] ?? 'No description available.')); ?>
                </p>

                <!-- Member 3 can connect this link to the final cart/session function. -->
                <a href="cart.php?add=<?php echo (int)$product['product_id']; ?>"
                   class="product-btn product-add-cart">
                    Add to Cart
                </a>
            </div>

        </div>

    <?php endif; ?>

</section>

<?php
include 'footer.php';
?>
