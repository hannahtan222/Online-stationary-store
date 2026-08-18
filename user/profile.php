<?php

session_start();

require_once('../config/db_connection.php');
require_once('../config/auth.php');

require_login();

// ==================================================
// READ - RETRIEVE CURRENT USER PROFILE
// ==================================================

$statement = mysqli_prepare(
    $conn,
    "SELECT
        username,
        email,
        full_name,
        address,
        phone
     FROM users
     WHERE user_id = ?"
);

mysqli_stmt_bind_param(
    $statement,
    "i",
    $_SESSION['user_id']
);

mysqli_stmt_execute($statement);

$result = mysqli_stmt_get_result($statement);

$user = mysqli_fetch_assoc($result);

// ==================================================
// DISPLAY PROFILE PAGE
// ==================================================

include '../includes/header.php';

?>

<section class="card">

    <h1>My Profile</h1>

    <div class="profile-details">

        <p>
            <strong>Username:</strong>

            <?= e($user['username']) ?>
        </p>

        <p>
            <strong>Full Name:</strong>

            <?= e($user['full_name']) ?>
        </p>

        <p>
            <strong>Email:</strong>

            <?= e($user['email']) ?>
        </p>

        <p>
            <strong>Address:</strong><br>

            <?= nl2br(e($user['address'])) ?>
        </p>

        <p>
            <strong>Phone:</strong>

            <?= e($user['phone']) ?>
        </p>

    </div>

    <a
        class="button"
        href="update_profile.php"
    >
        Edit Profile
    </a>

</section>


<?php include '../includes/footer.php'; ?>
