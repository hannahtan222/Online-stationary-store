<?php
// modules/member2_products/product_details.php
session_start();

// Include BOTH config files
require_once '../includes/config.php';        // For BASE_URL
require_once '../config/db_connection.php';   // For database connection

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$product = null;

if ($product_id > 0) {
    $sql = "SELECT p.product_id, p.product_name, p.description, p.price, p.stock_quantity, p.image_url as image,
                   c.category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.category_id
            WHERE p.product_id = $product_id
            LIMIT 1";

    $result = mysqli_query($conn, $sql);

    if ($result) {
        $product = mysqli_fetch_assoc($result);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['product_name'] ?? 'Product Details'); ?> - Stationery Store</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/member2.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

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
				
				  <?php 
                $stock = (int)$product['stock_quantity'];
                if ($stock > 10): 
                ?>
                    <span class="product-stock in-stock">✅ In Stock (<?php echo $stock; ?> units)</span>
                <?php elseif ($stock > 0 && $stock <= 10): ?>
                    <span class="product-stock low-stock">⚠️ Low Stock (<?php echo $stock; ?> units left)</span>
                <?php else: ?>
                    <span class="product-stock out-of-stock">❌ Out of Stock</span>
                <?php endif; ?>

                <h3>Description</h3>
                <p class="product-description">
                    <?php echo nl2br(htmlspecialchars($product['description'] ?? 'No description available.')); ?>
                </p>

                 <?php if ($stock > 0): ?>
                    <a href="<?php echo BASE_URL; ?>user/add_to_cart.php?add=<?php echo (int)$product['product_id']; ?>"
                       class="product-btn product-add-cart">
                        🛒 Add to Cart
                    </a>
                <?php else: ?>
                    <button class="product-btn product-add-cart disabled" disabled>
                        ❌ Out of Stock
                    </button>
                <?php endif; ?>
            </div>

        </div>

    <?php endif; ?>

</section>

<?php include '../includes/footer.php'; ?>

</body>
</html>