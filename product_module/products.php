<?php
session_start();
include 'header.php';
include 'config.php';

$search = trim($_GET['search'] ?? '');
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;

$categories = [];
$category_result = $conn->query("SELECT category_id, category_name FROM categories ORDER BY category_name");
if ($category_result) {
    while ($row = $category_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

$sql = "SELECT p.product_id, p.product_name, p.description, p.price, p.image,
               c.category_id, c.category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        WHERE 1=1";

if ($search !== '') {
    $safe_search = $conn->real_escape_string($search);
    $sql .= " AND (p.product_name LIKE '%$safe_search%'
              OR p.description LIKE '%$safe_search%')";
}

if ($category_id > 0) {
    $sql .= " AND p.category_id = $category_id";
}

$sql .= " ORDER BY p.product_id DESC";
$result = $conn->query($sql);
?>

<section class="products-page">
    <div class="products-heading">
        <h1>Our Products</h1>
        <p>Browse our stationery collection for school, university, office and creative work.</p>
    </div>

    <div class="product-controls">
        <form action="products.php" method="GET" class="product-search">
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
            <?php echo $result ? $result->num_rows : 0; ?> product(s) found.
        </p>
    <?php endif; ?>

    <div class="product-grid">
        <?php if ($result && $result->num_rows > 0): ?>

            <?php while ($product = $result->fetch_assoc()): ?>
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

<?php
include 'footer.php';
?>
