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
    <style>
        .manage-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .manage-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .manage-header h1 {
            font-size: 28px;
            color: #1a1a2e;
        }
        .manage-header .admin-info {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .manage-header .admin-info span {
            color: #666;
        }
        .manage-header .admin-info strong {
            color: #1a1a2e;
        }
        .btn-logout {
            background: #fee2e2;
            color: #dc2626;
            padding: 8px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-logout:hover {
            background: #fecaca;
        }
        .btn-add {
            display: inline-block;
            padding: 12px 25px;
            background: #4A90D9;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-add:hover {
            background: #357ABD;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(74, 144, 217, 0.35);
        }
        .btn-back {
            display: inline-block;
            padding: 8px 20px;
            background: #e8ecf1;
            color: #333;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-back:hover {
            background: #d1d5db;
        }
        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            overflow: hidden;
            padding: 20px;
        }
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .table-header h2 {
            font-size: 18px;
            color: #1a1a2e;
        }
        .table-header .count {
            color: #888;
            font-size: 14px;
        }
        .table-wrapper {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table thead {
            background: #f8fafc;
        }
        table th {
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: #555;
            font-size: 13px;
            white-space: nowrap;
        }
        table td {
            padding: 12px 15px;
            border-top: 1px solid #f0f2f5;
            vertical-align: middle;
            font-size: 14px;
        }
        table tbody tr:hover {
            background: #fafbfc;
        }
        table .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #f0f2f5;
        }
        table .product-image-placeholder {
            width: 50px;
            height: 50px;
            background: #f0f2f5;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #bbb;
            font-size: 12px;
        }
        table .product-name {
            font-weight: 500;
            color: #1a1a2e;
        }
        table .product-price {
            font-weight: 600;
            color: #4A90D9;
        }
        table .product-stock {
            font-weight: 500;
        }
        .stock-low {
            color: #dc2626;
        }
        .stock-medium {
            color: #d97706;
        }
        .stock-high {
            color: #065f46;
        }
        table .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        table .btn-edit {
            display: inline-block;
            padding: 6px 16px;
            background: #e8ecf1;
            color: #333;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        table .btn-edit:hover {
            background: #d1d5db;
        }
        table .btn-delete {
            display: inline-block;
            padding: 6px 16px;
            background: #fee2e2;
            color: #dc2626;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        table .btn-delete:hover {
            background: #fecaca;
        }
        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }
        .success-message {
            background: #d1fae5;
            color: #065f46;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #888;
        }
        .empty-state .empty-icon {
            font-size: 64px;
            display: block;
            margin-bottom: 15px;
        }
        .empty-state h3 {
            color: #333;
            margin-bottom: 8px;
        }
        .top-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        @media (max-width: 768px) {
            .manage-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .manage-header .admin-info {
                width: 100%;
                justify-content: space-between;
            }
            .table-header {
                flex-direction: column;
                align-items: flex-start;
            }
            table {
                font-size: 13px;
                min-width: 600px;
            }
            table th, table td {
                padding: 10px 12px;
            }
            .top-actions {
                width: 100%;
            }
            .top-actions .btn-add,
            .top-actions .btn-back {
                flex: 1;
                text-align: center;
            }
        }
        @media (max-width: 480px) {
            .manage-container {
                padding: 15px;
            }
            .manage-header h1 {
                font-size: 22px;
            }
        }
    </style>
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
                <a href="dashboard.php" class="btn-back">← Back to Dashboard</a>
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
                                        <a href="manage_products.php?delete=<?php echo $product['product_id']; ?>" 
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
</div>

</body>
</html>