<?php
// modules/member2_products/products.php
session_start();

// Include BOTH config files
require_once '../includes/config.php';        // For BASE_URL
require_once '../config/db_connection.php';   // For database connection

$search = trim($_GET['search'] ?? '');
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;

$categories = [];
$category_result = mysqli_query($conn, "SELECT category_id, category_name FROM categories ORDER BY category_name");
if ($category_result) {
    while ($row = mysqli_fetch_assoc($category_result)) {
        $categories[] = $row;
    }
}

$sql = "SELECT p.product_id, p.product_name, p.description, p.price, p.image_url as image,
               c.category_id, c.category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        WHERE 1=1";

if ($search !== '') {
    $safe_search = mysqli_real_escape_string($conn, $search);
    $sql .= " AND (p.product_name LIKE '%$safe_search%'
              OR p.description LIKE '%$safe_search%')";
}

if ($category_id > 0) {
    $sql .= " AND p.category_id = $category_id";
}

$sql .= " ORDER BY p.product_id DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Stationery Store</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/member2.css">
    <style>
        .products-page {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        .products-heading {
            text-align: center;
            margin-bottom: 30px;
        }
        .products-heading h1 {
            font-size: 36px;
            color: #1a1a2e;
            margin-bottom: 10px;
        }
        .products-heading p {
            color: #666;
            font-size: 16px;
        }
        .product-controls {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 25px;
            justify-content: space-between;
            align-items: center;
        }
        .product-search {
            display: flex;
            gap: 10px;
            flex: 1;
            max-width: 450px;
        }
        .product-search input {
            flex: 1;
            padding: 12px 18px;
            border: 2px solid #e8ecf1;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        .product-search input:focus {
            border-color: #4A90D9;
            outline: none;
            box-shadow: 0 0 0 4px rgba(74, 144, 217, 0.15);
        }
        .product-search button {
            padding: 12px 25px;
            background: #4A90D9;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .product-search button:hover {
            background: #357ABD;
        }
        .product-categories {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .product-categories a {
            padding: 8px 18px;
            background: #f0f2f5;
            color: #555;
            text-decoration: none;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .product-categories a:hover {
            background: #e0e4e8;
        }
        .product-categories a.active {
            background: #4A90D9;
            color: white;
        }
        .product-result-count {
            color: #888;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }
        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            transition: all 0.3s ease;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .product-image {
            display: block;
            height: 220px;
            overflow: hidden;
            background: #f8fafc;
        }
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.3s ease;
        }
        .product-image img:hover {
            transform: scale(1.05);
        }
        .no-image {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #bbb;
            font-size: 14px;
            background: #f8fafc;
        }
        .product-card-content {
            padding: 20px;
        }
        .product-category {
            display: inline-block;
            padding: 3px 12px;
            background: #f0f2f5;
            border-radius: 20px;
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
        }
        .product-card-content h2 {
            font-size: 18px;
            margin-bottom: 10px;
        }
        .product-card-content h2 a {
            color: #1a1a2e;
            text-decoration: none;
        }
        .product-card-content h2 a:hover {
            color: #4A90D9;
        }
        .product-card-content p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .product-card-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .product-card-bottom strong {
            font-size: 20px;
            color: #1a1a2e;
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
        .no-products {
            text-align: center;
            padding: 60px 20px;
            grid-column: 1 / -1;
        }
        .no-products h2 {
            color: #333;
            margin-bottom: 10px;
        }
        .no-products p {
            color: #888;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            .product-controls {
                flex-direction: column;
                align-items: stretch;
            }
            .product-search {
                max-width: 100%;
            }
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
                gap: 20px;
            }
        }
        @media (max-width: 480px) {
            .product-grid {
                grid-template-columns: 1fr;
            }
            .products-heading h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>

<?php include '../includes/header.php'; ?>

<section class="products-page">
    <div class="products-heading">
        <h1>Our Products</h1>
        <p>Browse our stationery collection for school, university, office and creative work.</p>
    </div>

    <div class="product-controls">
        <form action="" method="GET" class="product-search">
            <input
                type="text"
                name="search"
                placeholder="Search stationery..."
                value="<?php echo htmlspecialchars($search); ?>"
            >
            <?php if ($category_id > 0): ?>
                <input type="hidden" name="category" value="<?php echo $category_id; ?>">
            <?php endif; ?>
            <button type="submit">Search</button>
        </form>

        <div class="product-categories">
            <a class="<?php echo $category_id === 0 ? 'active' : ''; ?>"
               href="products.php">All</a>

            <?php foreach ($categories as $category): ?>
                <a class="<?php echo $category_id === (int)$category['category_id'] ? 'active' : ''; ?>"
                   href="products.php?category=<?php echo (int)$category['category_id']; ?>">
                    <?php echo htmlspecialchars($category['category_name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if ($search !== '' || $category_id > 0): ?>
        <p class="product-result-count">
            <?php echo $result ? mysqli_num_rows($result) : 0; ?> product(s) found.
        </p>
    <?php endif; ?>

    <div class="product-grid">
        <?php if ($result && mysqli_num_rows($result) > 0): ?>

            <?php while ($product = mysqli_fetch_assoc($result)): ?>
                <div class="product-card">

                    <a href="product_details.php?id=<?php echo (int)$product['product_id']; ?>"
                       class="product-image">
                        <?php if (!empty($product['image'])): ?>
                            <img
                                src="<?php echo htmlspecialchars($product['image']); ?>"
                                alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                            >
                        <?php else: ?>
                            <div class="no-image">No Image</div>
                        <?php endif; ?>
                    </a>

                    <div class="product-card-content">
                        <span class="product-category">
                            <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                        </span>

                        <h2>
                            <a href="product_details.php?id=<?php echo (int)$product['product_id']; ?>">
                                <?php echo htmlspecialchars($product['product_name']); ?>
                            </a>
                        </h2>

                        <p>
                            <?php
                            $description = $product['description'] ?? '';
                            echo htmlspecialchars(
                                strlen($description) > 90
                                    ? substr($description, 0, 90) . '...'
                                    : $description
                            );
                            ?>
                        </p>

                        <div class="product-card-bottom">
                            <strong>RM <?php echo number_format((float)$product['price'], 2); ?></strong>
                            <a href="product_details.php?id=<?php echo (int)$product['product_id']; ?>"
                               class="product-btn">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>

        <?php else: ?>

            <div class="no-products">
                <h2>No products found</h2>
                <p>Try another search keyword or category.</p>
                <a href="products.php" class="product-btn">View All Products</a>
            </div>

        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

</body>
</html>
