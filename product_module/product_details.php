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
    <style>
        .products-page {
            max-width: 1100px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        .product-not-found {
            text-align: center;
            padding: 60px 20px;
        }
        .product-not-found h1 {
            font-size: 32px;
            color: #1a1a2e;
            margin-bottom: 10px;
        }
        .product-not-found p {
            color: #666;
            margin-bottom: 20px;
        }
        .product-back {
            display: inline-block;
            margin-bottom: 25px;
            color: #4A90D9;
            text-decoration: none;
            font-weight: 500;
        }
        .product-back:hover {
            text-decoration: underline;
        }
        .product-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }
        .product-detail-image {
            height: 400px;
            overflow: hidden;
            border-radius: 8px;
            background: #f8fafc;
        }
        .product-detail-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .product-detail-image .no-image {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #bbb;
            font-size: 16px;
            background: #f8fafc;
        }
        .product-detail-info {
            display: flex;
            flex-direction: column;
        }
        .product-detail-info .product-category {
            display: inline-block;
            padding: 4px 14px;
            background: #f0f2f5;
            border-radius: 20px;
            font-size: 13px;
            color: #666;
            margin-bottom: 12px;
            align-self: flex-start;
        }
        .product-detail-info h1 {
            font-size: 28px;
            color: #1a1a2e;
            margin-bottom: 15px;
        }
        .product-detail-price {
            font-size: 28px;
            font-weight: 700;
            color: #4A90D9;
            margin-bottom: 20px;
        }
        .product-detail-info h3 {
            font-size: 16px;
            color: #1a1a2e;
            margin-bottom: 8px;
        }
        .product-description {
            color: #555;
            line-height: 1.8;
            margin-bottom: 25px;
            flex: 1;
        }
        .product-add-cart {
            display: inline-block;
            padding: 14px 35px;
            background: #4A90D9;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s ease;
            align-self: flex-start;
        }
        .product-add-cart:hover {
            background: #357ABD;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(74, 144, 217, 0.35);
        }
        .product-btn {
            padding: 8px 20px;
            background: #4A90D9;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .product-btn:hover {
            background: #357ABD;
        }
        @media (max-width: 768px) {
            .product-detail {
                grid-template-columns: 1fr;
                padding: 25px;
            }
            .product-detail-image {
                height: 250px;
            }
            .product-detail-info h1 {
                font-size: 22px;
            }
            .product-detail-price {
                font-size: 22px;
            }
        }
        @media (max-width: 480px) {
            .product-detail {
                padding: 18px;
            }
            .product-detail-image {
                height: 200px;
            }
            .product-add-cart {
                width: 100%;
                text-align: center;
            }
        }
    </style>
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