<?php

require 'db_connect.php';
require_login();


// ==================================================
// READ - RETRIEVE CURRENT USER PROFILE
// ==================================================

$statement = $pdo->prepare(
    'SELECT
        username,
        email,
        full_name,
        address,
        phone
     FROM users
     WHERE user_id = ?'
);

$statement->execute([
    $_SESSION['user_id']
]);

$user = $statement->fetch();


page_header('My Profile');

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

            <?= nl2br(
                e($user['address'])
            ) ?>
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


<?php page_footer(); ?>