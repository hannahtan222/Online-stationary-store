<?php
//login.php
session_start();
require_once('../config/db_connection.php');

$error = '';

// ==================================================
// PROCESS LOGIN
// ==================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields!';
    } else {
        // ==============================================
        // READ - FIND USER BY EMAIL (Using MySQLi)
        // ==============================================
        $findUser = mysqli_prepare($conn, "SELECT user_id, username, full_name, password FROM users WHERE email = ?");
        mysqli_stmt_bind_param($findUser, "s", $email);
        mysqli_stmt_execute($findUser);
        $result = mysqli_stmt_get_result($findUser);
        $user = mysqli_fetch_assoc($result);
        
        // ==============================================
        // VERIFY PASSWORD
        // ==============================================
        if ($user && password_verify($password, $user['password'])) {
            // Regenerate session ID for security
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $email;
            
            // Redirect to profile page
            header('Location: profile.php');
            exit();
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Stationery Store</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<!-- ========================================== -->
<!-- LOGIN FORM -->
<!-- ========================================== -->
<section class="form-card">
    <div class="form-container">
        <h1>🔐 Login</h1>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            
            <button type="submit" class="btn-submit">Login</button>
        </form>
        
        <p class="form-footer">
            Don't have an account? 
            <a href="register.php">Register here</a>
        </p>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

</body>
</html>