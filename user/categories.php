<?php
// modules/member2_products/categories.php
session_start();

// Include BOTH config files
require_once '../includes/config.php';        // For BASE_URL
require_once '../config/db_connection.php';   // For database connection

// ==================================================
// READ - RETRIEVE CATEGORIES
// ==================================================

$query = mysqli_query(
    $conn,
    "SELECT
        c.category_id,
        c.category_name,
        COUNT(p.product_id) AS product_count
     FROM categories c
     LEFT JOIN products p
        ON p.category_id = c.category_id
     GROUP BY
        c.category_id,
        c.category_name
     ORDER BY c.category_name"
);

$categories = mysqli_fetch_all(
    $query,
    MYSQLI_ASSOC
);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Stationery Store</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/user_style.css">
    <style>
        .page-intro {
            text-align: center;
            margin: 40px 0 30px;
        }
        .page-intro h1 {
            font-size: 36px;
            color: #1a1a2e;
            margin-bottom: 10px;
        }
        .page-intro p {
            color: #666;
            font-size: 16px;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
            max-width: 1100px;
            margin: 0 auto 50px;
            padding: 0 20px;
        }
        .card {
            background: white;
            padding: 30px 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            text-align: center;
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .card h2 {
            font-size: 22px;
            color: #1a1a2e;
            margin-bottom: 8px;
        }
        .card p {
            color: #888;
            font-size: 14px;
            margin-bottom: 18px;
        }
        .card .button {
            display: inline-block;
            padding: 10px 30px;
            background: #4A90D9;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .card .button:hover {
            background: #357ABD;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(74, 144, 217, 0.35);
        }
        .no-categories {
            text-align: center;
            padding: 60px 20px;
            grid-column: 1 / -1;
        }
        .no-categories h2 {
            color: #333;
            margin-bottom: 10px;
        }
        .no-categories p {
            color: #888;
        }
        @media (max-width: 600px) {
            .grid {
                grid-template-columns: 1fr 1fr;
            }
            .page-intro h1 {
                font-size: 28px;
            }
        }
        @media (max-width: 400px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<?php include '../includes/header.php'; ?>

<section class="page-intro">
    <h1>Shop by Category</h1>
    <p>Browse our stationery by category.</p>
</section>

<div class="grid">
    <?php if (!empty($categories)): ?>
        <?php foreach ($categories as $category): ?>
            <section class="card">
                <h2><?php echo htmlspecialchars($category['category_name']); ?></h2>
                <p><?php echo $category['product_count']; ?> products</p>
                <a class="button" href="products.php?category=<?php echo $category['category_id']; ?>">
                    Shop Category
                </a>
            </section>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="no-categories">
            <h2>No Categories Found</h2>
            <p>There are no categories available at the moment.</p>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>

</body>
</html>