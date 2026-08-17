<?php
// admin/edit_product.php - Edit Product Page
session_start();
require_once('../config/db_connection.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: manage_products.php');
    exit();
}

$product_id = $_GET['id'];
$error = '';
$success = '';

// Fetch categories for dropdown
$categories_query = "SELECT * FROM categories ORDER BY category_name";
$categories_result = mysqli_query($conn, $categories_query);

// Fetch product data
$product_query = "SELECT p.*, c.category_name FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.category_id 
                  WHERE p.product_id = $product_id";
$product_result = mysqli_query($conn, $product_query);

if (mysqli_num_rows($product_result) == 0) {
    header('Location: manage_products.php');
    exit();
}

$product = mysqli_fetch_assoc($product_result);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $stock_quantity = mysqli_real_escape_string($conn, $_POST['stock_quantity']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $image_url = mysqli_real_escape_string($conn, $_POST['image_url']);
    
    // Validation
    if (empty($product_name) || empty($price) || empty($category_id)) {
        $error = 'Please fill in all required fields!';
    } elseif (!is_numeric($price) || $price <= 0) {
        $error = 'Price must be a positive number!';
    } elseif (!is_numeric($stock_quantity) || $stock_quantity < 0) {
        $error = 'Stock quantity must be 0 or more!';
    } else {
        // Update product
        $update_query = "UPDATE products SET 
                         product_name = '$product_name',
                         description = '$description',
                         price = '$price',
                         stock_quantity = '$stock_quantity',
                         category_id = '$category_id',
                         image_url = '$image_url'
                         WHERE product_id = $product_id";
        
        if (mysqli_query($conn, $update_query)) {
            $success = 'Product updated successfully!';
            // Refresh product data
            $product_result = mysqli_query($conn, "SELECT * FROM products WHERE product_id = $product_id");
            $product = mysqli_fetch_assoc($product_result);
        } else {
            $error = 'Error: ' . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Product - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/admin_style.css">
</head>
<body>
    <div class="admin-container">
        <!-- Header -->
        <div class="admin-header">
            <h1>📝 Edit Product</h1>
            <div class="user-info">
                <span class="avatar">A</span>
                <span class="username">Admin</span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>

        <div class="form-container">
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Product Name <span class="required">*</span></label>
                    <input type="text" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description"><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Price (RM) <span class="required">*</span></label>
                    <input type="number" name="price" step="0.01" value="<?php echo $product['price']; ?>" required>
                </div>

                <div class="form-group">
                    <label>Stock Quantity</label>
                    <input type="number" name="stock_quantity" value="<?php echo $product['stock_quantity']; ?>">
                </div>

                <div class="form-group">
                    <label>Category <span class="required">*</span></label>
                    <select name="category_id" required>
                        <option value="">Select Category</option>
                        <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                            <option value="<?php echo $category['category_id']; ?>" 
                                    <?php echo ($category['category_id'] == $product['category_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['category_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Image URL</label>
                    <input type="text" name="image_url" placeholder="https://example.com/image.jpg" 
                           value="<?php echo htmlspecialchars($product['image_url']); ?>">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">Update Product</button>
                    <a href="manage_products.php" class="btn-cancel">Cancel</a>
                </div>
            </form>

            <div style="margin-top: 20px; padding: 15px; background: #f8fafc; border-radius: 8px;">
                <p><strong>Current Image:</strong></p>
                <?php if ($product['image_url']): ?>
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                         alt="Product Image" style="max-width: 150px; max-height: 150px; border-radius: 8px;">
                <?php else: ?>
                    <p style="color: #888;">No image uploaded</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>