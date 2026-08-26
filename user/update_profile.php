<?php
// modules/member3_user/update_profile.php
session_start();

// Include BOTH config files
require_once '../includes/config.php';        // For BASE_URL
require_once '../config/db_connection.php';   // For database connection
require_once '../config/auth.php';            // For authentication functions

require_login();

// ==================================================
// READ - GET CURRENT USER INFORMATION
// ==================================================

$userQuery = mysqli_prepare(
    $conn,
    "SELECT
        full_name,
        address,
        phone
     FROM users
     WHERE user_id = ?"
);

// Bind logged-in user's ID.
mysqli_stmt_bind_param(
    $userQuery,
    "i",
    $_SESSION['user_id']
);

// Execute query.
mysqli_stmt_execute($userQuery);
$result = mysqli_stmt_get_result($userQuery);
$user = mysqli_fetch_assoc($result);

// ==================================================
// UPDATE PROFILE
// ==================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ==============================================
    // GET UPDATED INFORMATION
    // ==============================================

    $fullName = trim($_POST['full_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    // ==============================================
    // UPDATE - UPDATE CURRENT USER'S PROFILE
    // ==============================================

    $updateProfile = mysqli_prepare(
        $conn,
        "UPDATE users
         SET
            full_name = ?,
            address = ?,
            phone = ?
         WHERE user_id = ?"
    );

    // Bind updated information.
    mysqli_stmt_bind_param(
        $updateProfile,
        "sssi",
        $fullName,
        $address,
        $phone,
        $_SESSION['user_id']
    );

    // Execute update.
    mysqli_stmt_execute($updateProfile);

    // ==============================================
    // SUCCESS MESSAGE
    // ==============================================

    flash('success', 'Profile updated successfully.');

    // Return to profile page.
    redirect(BASE_URL . 'user/profile.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Stationery Store</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/user_style.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<section class="form-card">
    <h1>✏️ Edit Profile</h1>

    <!-- ==========================================
         UPDATE PROFILE FORM
    =========================================== -->
    <form method="POST" action="">

        <!-- Full Name -->
        <label for="full_name">Full Name</label>
        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" placeholder="Enter your full name">

        <!-- Address -->
        <label for="address">Address</label>
        <textarea id="address" name="address" placeholder="Enter your shipping address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>

        <!-- Phone -->
        <label for="phone">Phone</label>
        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="Enter your phone number">

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="submit" class="btn-submit">💾 Save Changes</button>
            <a href="profile.php" class="btn-cancel">Cancel</a>
        </div>

        <div class="form-footer">
            <a href="profile.php">← Back to Profile</a>
        </div>
    </form>
</section>

<?php include '../includes/footer.php'; ?>

</body>
</html>