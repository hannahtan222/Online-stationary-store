<?php

session_start();

require_once('../config/db_connection.php');

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

// ==================================================
// DISPLAY PAGE
// ==================================================

include '../includes/header.php';

?>

<section class="page-intro">

    <h1>Shop by Category</h1>

    <p>
        Browse our stationery by category.
    </p>

</section>

<div class="grid">

    <?php foreach ($categories as $category): ?>

        <section class="card">

            <h2>
                <?= e($category['category_name']) ?>
            </h2>

            <p>
                <?= $category['product_count'] ?>
                products
            </p>

            <a
                class="button"
                href="products.php?category=<?= $category['category_id'] ?>"
            >
                Shop Category
            </a>

        </section>

    <?php endforeach; ?>

</div>

<?php include '../includes/footer.php'; ?>
