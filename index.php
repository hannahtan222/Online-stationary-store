<?php
// index.php - Home Page
session_start();
require_once('config/db_connection.php');

// Fetch featured products or categories count
$category_query = "SELECT COUNT(*) as total FROM categories";
$category_result = mysqli_query($conn, $category_query);
$category_count = mysqli_fetch_assoc($category_result)['total'];

$product_query = "SELECT COUNT(*) as total FROM products";
$product_result = mysqli_query($conn, $product_query);
$product_count = mysqli_fetch_assoc($product_result)['total'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stationery Store - Home</title>
    
    <!-- Main Styles -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<!-- ======================================== -->
<!-- HERO SECTION -->
<!-- ======================================== -->
<section class="hero">
    <div class="hero-text">
        <h1>Most Valuable Stationery Store</h1>
        <p>
            Discover quality stationery for school, university,
            office and creative projects.
        </p>
        <a href="product_module/products.php" class="btn btn-primary">Shop Now</a>
    </div>
</section>

<!-- ======================================== -->
<!-- CATEGORIES SECTION -->
<!-- ======================================== -->
<section class="categories">
    <h2>Shop by Category</h2>
    
    <div class="category-container">
        <div class="category-card">
            <h3>✏️ Writing</h3>
            <p>Pens, pencils and markers.</p>
            <a href="product_module/products.php?category=1">View Products</a>
        </div>

        <div class="category-card">
            <h3>📓 Paper</h3>
            <p>Notebooks, journals and paper.</p>
            <a href="product_module/products.php?category=2">View Products</a>
        </div>

        <div class="category-card">
            <h3>🎨 Art Supplies</h3>
            <p>Creative tools for your ideas.</p>
            <a href="product_module/products.php?category=3">View Products</a>
        </div>
        
        <div class="category-card">
            <h3>🖥️ Office Supplies</h3>
            <p>Support your daily working routines.</p>
            <a href="product_module/products.php?category=4">View Products</a>
        </div>

        <div class="category-card">
            <h3>🖥️ Desk Accessories</h3>
            <p>Organise your workspace.</p>
            <a href="product_module/products.php?category=5">View Products</a>
        </div>
    </div>
</section>


<?php include 'includes/footer.php'; ?>

</body>
</html>


