<?php

session_start();

require_once('../config/db_connection.php');
require_once('../config/auth.php');

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

// Get result.
$result = mysqli_stmt_get_result($userQuery);

// Get current user information.
$user = mysqli_fetch_assoc($result);

// ==================================================
// UPDATE PROFILE
// ==================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ==============================================
    // GET UPDATED INFORMATION
    // ==============================================

    $fullName = trim(
        $_POST['full_name'] ?? ''
    );

    $address = trim(
        $_POST['address'] ?? ''
    );

    $phone = trim(
        $_POST['phone'] ?? ''
    );

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

    flash(
        'success',
        'Profile updated successfully.'
    );

    // Return to profile page.
    redirect('profile.php');
}

// ==================================================
// PAGE HEADER
// ==================================================

include '../includes/header.php';

?>

<section class="form-card">

    <h1>Edit Profile</h1>

    <!-- ==========================================
         UPDATE PROFILE FORM
    =========================================== -->

    <form method="post">

        <!-- Full Name -->

        <label>
            Full Name

            <input
                type="text"
                name="full_name"
                value="<?= e($user['full_name']) ?>"
            >

        </label>

        <!-- Address -->

        <label>
            Address

            <textarea
                name="address"
            ><?= e($user['address']) ?></textarea>
        </label>

        <!-- Phone -->

        <label>
            Phone

            <input
                type="text"
                name="phone"
                value="<?= e($user['phone']) ?>"
            >
        </label>

        <!-- Submit Button -->

        <button type="submit">
            Save Changes
        </button>


        <!-- Cancel Button -->

        <a
            class="button secondary"
            href="profile.php"
        >
            Cancel
        </a>
    </form>
</section>

<?php

include '../includes/footer.php';

?>
