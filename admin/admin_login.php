<?php
// admin/admin_login.php
session_start();

// Include BOTH config files
require_once '../includes/config.php';        // For BASE_URL
require_once '../config/db_connection.php';   // For database connection

// Simple admin credentials (can be moved to database later)
$admin_username = 'admin';
$admin_password = 'admin123';
$error = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === $admin_username && $password === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: dashboard.php');
        exit();
    } else {
        $error = "Invalid credentials!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Stationery Store</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin_style.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<div class="login-container">
    <h2>🔐 Admin Login</h2>
    <p class="subtitle">Stationery Store Management Panel</p>
    
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Enter admin username" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter admin password" required>
        </div>
        
        <button type="submit" class="login-btn">Login</button>
    </form>
    
   
</div>

<?php include '../includes/footer.php'; ?>

</body>
</html>