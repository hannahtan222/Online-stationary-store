<?php
// admin/manage_categories.php - Manage Categories Page
session_start();
require_once('../config/db_connection.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

$error = '';
$success = '';
$edit_category = null;

// Handle Delete
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $category_id = $_GET['delete'];
    
    // Check if category has products
    $check_query = "SELECT COUNT(*) as count FROM products WHERE category_id = $category_id";
    $check_result = mysqli_query($conn, $check_query);
    $count = mysqli_fetch_assoc($check_result)['count'];
    
    if ($count > 0) {
        $error = "Cannot delete this category because it has $count product(s) linked to it!";
    } else {
        $delete_query = "DELETE FROM categories WHERE category_id = $category_id";
        if (mysqli_query($conn, $delete_query)) {
            $success = 'Category deleted successfully!';
        } else {
            $error = 'Error: ' . mysqli_error($conn);
        }
    }
}

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);
    
    if (empty($category_name)) {
        $error = 'Category name cannot be empty!';
    } else {
        $insert_query = "INSERT INTO categories (category_name) VALUES ('$category_name')";
        if (mysqli_query($conn, $insert_query)) {
            $success = 'Category added successfully!';
        } else {
            $error = 'Error: ' . mysqli_error($conn);
        }
    }
}

// Handle Edit Category (fetch for editing)
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $category_id = $_GET['edit'];
    $edit_query = "SELECT * FROM categories WHERE category_id = $category_id";
    $edit_result = mysqli_query($conn, $edit_query);
    if ($edit_category = mysqli_fetch_assoc($edit_result)) {
        // Store in session for form pre-fill
        $_SESSION['edit_category_id'] = $category_id;
        $_SESSION['edit_category_name'] = $edit_category['category_name'];
    }
}

// Handle Update Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);
    
    if (empty($category_name)) {
        $error = 'Category name cannot be empty!';
    } else {
        $update_query = "UPDATE categories SET category_name = '$category_name' WHERE category_id = $category_id";
        if (mysqli_query($conn, $update_query)) {
            $success = 'Category updated successfully!';
            // Clear edit session
            unset($_SESSION['edit_category_id']);
            unset($_SESSION['edit_category_name']);
        } else {
            $error = 'Error: ' . mysqli_error($conn);
        }
    }
}

// Fetch all categories with product counts
$categories_query = "SELECT c.*, COUNT(p.product_id) as product_count 
                     FROM categories c 
                     LEFT JOIN products p ON c.category_id = p.category_id 
                     GROUP BY c.category_id 
                     ORDER BY c.category_name";
$categories_result = mysqli_query($conn, $categories_query);

// Check if we have edit data in session
if (isset($_SESSION['edit_category_id']) && isset($_SESSION['edit_category_name'])) {
    $edit_category = [
        'category_id' => $_SESSION['edit_category_id'],
        'category_name' => $_SESSION['edit_category_name']
    ];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Categories - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/admin_style.css">
</head>
<body>
    <div class="admin-container">
        <!-- Header -->
        <div class="admin-header">
            <h1>📂 Manage Categories</h1>
            <div class="user-info">
                <span class="avatar">A</span>
                <span class="username">Admin</span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="error" style="margin-bottom: 20px;"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success" style="margin-bottom: 20px;"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Add/Edit Category Form -->
        <div class="form-container" style="margin-bottom: 30px;">
            <h2>
                <?php echo $edit_category ? '✏️ Edit Category' : '➕ Add New Category'; ?>
            </h2>
            
            <form method="POST" action="">
                <?php if ($edit_category): ?>
                    <input type="hidden" name="category_id" value="<?php echo $edit_category['category_id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Category Name <span class="required">*</span></label>
                    <input type="text" name="category_name" 
                           value="<?php echo $edit_category ? htmlspecialchars($edit_category['category_name']) : ''; ?>" 
                           placeholder="Enter category name" required>
                </div>
                
                <div class="form-actions">
                    <?php if ($edit_category): ?>
                        <button type="submit" name="update_category" class="btn-submit">Update Category</button>
                        <a href="manage_categories.php" class="btn-cancel">Cancel</a>
                    <?php else: ?>
                        <button type="submit" name="add_category" class="btn-submit">Add Category</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Categories Table -->
        <div class="table-container">
            <div class="table-header">
                <h2>All Categories</h2>
                <span style="color: #888; font-size: 14px;">
                    Total: <?php echo mysqli_num_rows($categories_result); ?> categories
                </span>
            </div>

            <?php if (mysqli_num_rows($categories_result) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Category Name</th>
                            <th>Products Count</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                            <tr>
                                <td>#<?php echo $category['category_id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($category['category_name']); ?></strong></td>
                                <td><?php echo $category['product_count']; ?> products</td>
                                <td><?php echo date('d M Y', strtotime($category['created_at'])); ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="manage_categories.php?edit=<?php echo $category['category_id']; ?>" 
                                           class="btn-edit">Edit</a>
                                        <a href="manage_categories.php?delete=<?php echo $category['category_id']; ?>" 
                                           class="btn-delete" 
                                           onclick="return confirm('Delete category \'<?php echo htmlspecialchars($category['category_name']); ?>\'? This will only work if no products are linked to it.')">
                                            Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <span class="empty-icon">📂</span>
                    <h3>No Categories Yet</h3>
                    <p>Add your first category using the form above.</p>
                </div>
            <?php endif; ?>
        </div>

        <div style="margin-top: 20px;">
            <a href="dashboard.php" style="color: #4A90D9; text-decoration: none;">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>