<?php
// admin/add_product.php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

// Include BOTH config files
require_once '../includes/config.php';        // For BASE_URL
require_once '../config/db_connection.php';   // For database connection

$error = '';
$success = '';

// Fetch categories for dropdown
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form data
    $name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $category = (int)$_POST['category_id'];
    $image_url = mysqli_real_escape_string($conn, $_POST['image_url'] ?? '');
    
    // Validation
    if (empty($name)) {
        $error = "Product name is required!";
    } elseif ($price <= 0) {
        $error = "Price must be greater than 0!";
    } elseif ($stock < 0) {
        $error = "Stock cannot be negative!";
    } elseif ($category <= 0) {
        $error = "Please select a category!";
    } else {
        // Insert product
        $sql = "INSERT INTO products (product_name, description, price, stock_quantity, category_id, image_url) 
                VALUES ('$name', '$desc', '$price', '$stock', '$category', '$image_url')";
        
        if (mysqli_query($conn, $sql)) {
            $success = "Product added successfully!";
            // Clear form data
            $_POST = array();
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Admin Panel</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin_style.css">
    <style>
        .form-container {
            max-width: 700px;
            margin: 0 auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }
        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .form-header h1 {
            font-size: 28px;
            color: #1a1a2e;
        }
        .form-header .admin-info {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .form-header .admin-info span {
            color: #666;
        }
        .form-header .admin-info strong {
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
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            color: #444;
            font-size: 14px;
        }
        .form-group label .required {
            color: #dc2626;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e8ecf1;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
            font-family: inherit;
            background: #fafbfc;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #4A90D9;
            outline: none;
            box-shadow: 0 0 0 4px rgba(74, 144, 217, 0.15);
            background: white;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        .form-group select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23666' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            padding-right: 40px;
            cursor: pointer;
        }
        .form-group .hint {
            display: block;
            font-size: 12px;
            color: #888;
            margin-top: 4px;
        }
        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 10px;
            flex-wrap: wrap;
        }
        .form-actions .btn-submit {
            padding: 12px 35px;
            background: #4A90D9;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .form-actions .btn-submit:hover {
            background: #357ABD;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(74, 144, 217, 0.35);
        }
        .form-actions .btn-cancel {
            padding: 12px 35px;
            background: #f1f3f5;
            color: #555;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
        }
        .form-actions .btn-cancel:hover {
            background: #e8ecf1;
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
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
                margin: 0 15px;
            }
            .form-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .form-header .admin-info {
                width: 100%;
                justify-content: space-between;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
            .form-actions {
                flex-direction: column;
            }
            .form-actions .btn-submit,
            .form-actions .btn-cancel {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>

<div class="form-container">
    <!-- Header -->
    <div class="form-header">
        <h1>➕ Add New Product</h1>
        <div class="admin-info">
            <span>Welcome, <strong><?php echo $_SESSION['admin_username'] ?? 'Admin'; ?></strong></span>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </div>

    <!-- Messages -->
    <?php if ($error): ?>
        <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success-message"><?php echo $success; ?></div>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST" action="">
        <div class="form-group">
            <label>Product Name <span class="required">*</span></label>
            <input type="text" name="product_name" placeholder="Enter product name" 
                   value="<?php echo htmlspecialchars($_POST['product_name'] ?? ''); ?>" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Price (RM) <span class="required">*</span></label>
                <input type="number" name="price" step="0.01" min="0.01" 
                       placeholder="0.00" 
                       value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label>Stock Quantity <span class="required">*</span></label>
                <input type="number" name="stock" min="0" 
                       placeholder="0" 
                       value="<?php echo htmlspecialchars($_POST['stock'] ?? ''); ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label>Category <span class="required">*</span></label>
            <select name="category_id" required>
                <option value="">-- Select Category --</option>
                <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                    <option value="<?php echo $category['category_id']; ?>"
                            <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['category_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['category_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Image URL</label>
            <input type="text" name="image_url" placeholder="https://example.com/image.jpg" 
                   value="<?php echo htmlspecialchars($_POST['image_url'] ?? ''); ?>">
            <span class="hint">Enter a URL for the product image (optional)</span>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" placeholder="Enter product description..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit">➕ Add Product</button>
            <a href="manage_product.php" class="btn-cancel">Cancel</a>
        </div>
    </form>

    <div style="margin-top: 20px;">
        <a href="manage_product.php" class="btn-back">← Back to Products</a>
    </div>
</div>

</body>
</html>