<?php
// admin/manage_products.php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

// Include BOTH config files
require_once '../includes/config.php';        // For BASE_URL
require_once '../config/db_connection.php';   // For database connection

// Handle Delete
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $product_id = (int)$_GET['delete'];
    
    // Check if product exists in any order
    $check_query = "SELECT COUNT(*) as count FROM order_items WHERE product_id = $product_id";
    $check_result = mysqli_query($conn, $check_query);
    $count = mysqli_fetch_assoc($check_result)['count'];
    
    if ($count > 0) {
        $error = "Cannot delete this product because it has been ordered by customers!";
    } else {
        $delete_query = "DELETE FROM products WHERE product_id = $product_id";
        if (mysqli_query($conn, $delete_query)) {
            $success = "Product deleted successfully!";
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}

// Get all products with category names
$sql = "SELECT p.*, c.category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        ORDER BY p.created_at DESC";
$products = mysqli_query($conn, $sql);
$product_count = mysqli_num_rows($products);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin Panel</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin_style.css">
</head>
<body>

<div class="manage-container">
    <!-- Header -->
    <div class="manage-header">
        <h1>📦 Manage Products</h1>
        <div class="admin-info">
            <span>Welcome, <strong><?php echo $_SESSION['admin_username'] ?? 'Admin'; ?></strong></span>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if (isset($success)): ?>
        <div class="success-message"><?php echo $success; ?></div>
    <?php endif; ?>

    <!-- Table -->
    <div class="table-container">
        <div class="table-header">
            <h2>All Products</h2>
            <div class="top-actions">
                <a href="add_product.php" class="btn-add">➕ Add New Product</a>
            </div>
        </div>

        <?php if ($product_count > 0): ?>
            <div style="margin-bottom: 15px; color: #888; font-size: 14px;">
                Showing <?php echo $product_count; ?> product(s)
            </div>
            
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 60px;">Image</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th style="text-align: right;">Price</th>
                            <th style="text-align: center;">Stock</th>
                            <th style="text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($product = mysqli_fetch_assoc($products)): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($product['image_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                                             class="product-image">
                                    <?php else: ?>
                                        <div class="product-image-placeholder">No img</div>
                                    <?php endif; ?>
                                </td>
                                <td class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                                <td class="product-price" style="text-align: right;">
                                    RM <?php echo number_format($product['price'], 2); ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php 
                                    $stock = (int)$product['stock_quantity'];
                                    $stock_class = $stock <= 0 ? 'stock-low' : ($stock <= 10 ? 'stock-medium' : 'stock-high');
                                    ?>
                                    <span class="product-stock <?php echo $stock_class; ?>">
                                        <?php echo $stock; ?>
                                        <?php if ($stock <= 0): ?>
                                            (Out of Stock)
                                        <?php elseif ($stock <= 10): ?>
                                            (Low Stock)
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <div class="actions" style="justify-content: center;">
                                        <a href="edit_product.php?id=<?php echo $product['product_id']; ?>" class="btn-edit">✏️ Edit</a>
                                        <a href="manage_product.php?delete=<?php echo $product['product_id']; ?>" 
                                           class="btn-delete" 
                                           onclick="return confirm('Are you sure you want to delete \'<?php echo htmlspecialchars($product['product_name']); ?>\'? This cannot be undone.')">
                                            🗑️ Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <span class="empty-icon">📦</span>
                <h3>No Products Yet</h3>
                <p>Start by adding your first product.</p>
                <a href="add_product.php" class="btn-add" style="display: inline-block; margin-top: 15px;">➕ Add New Product</a>
            </div>
        <?php endif; ?>
    </div>
	
	  <div style="margin-top: 20px;">
        <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
    </div>
</div>

</body>
</html>