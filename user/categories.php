<?php

require 'db_connect.php';


// ==================================================
// READ - RETRIEVE CATEGORIES
// ==================================================

$categories = $pdo->query(
    'SELECT
        c.category_id,
        c.category_name,
        COUNT(p.product_id) AS product_count
     FROM categories c
     LEFT JOIN products p
        ON p.category_id = c.category_id
     GROUP BY
        c.category_id,
        c.category_name
     ORDER BY c.category_name'
)->fetchAll();


page_header('Categories');

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


<?php page_footer(); ?>