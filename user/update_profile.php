<?php

require 'config/db_connection.php';
require_login();


// ==================================================
// READ - GET CURRENT USER INFORMATION
// ==================================================

$statement = $pdo->prepare(
    'SELECT
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


// ==================================================
// UPDATE PROFILE
// ==================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    // Get updated information
    $user['full_name'] =
        trim($_POST['full_name'] ?? '');

    $user['address'] =
        trim($_POST['address'] ?? '');

    $user['phone'] =
        trim($_POST['phone'] ?? '');


    // ==============================================
    // UPDATE - UPDATE CURRENT USER'S PROFILE
    // ==============================================

    $update = $pdo->prepare(
        'UPDATE users
         SET
            full_name = ?,
            address = ?,
            phone = ?
         WHERE user_id = ?'
    );


    $update->execute([

        $user['full_name'],

        $user['address'],

        $user['phone'],

        $_SESSION['user_id']
    ]);


    flash(
        'success',
        'Profile updated successfully.'
    );


    redirect('profile.php');
}


page_header('Edit Profile');

?>

<section class="form-card">

    <h1>Edit Profile</h1>


    <form method="post">


        <label>
            Full Name

            <input
                type="text"
                name="full_name"
                value="<?= e($user['full_name']) ?>"
            >

        </label>


        <label>
            Address

            <textarea
                name="address"
            ><?= e($user['address']) ?></textarea>

        </label>


        <label>
            Phone

            <input
                type="text"
                name="phone"
                value="<?= e($user['phone']) ?>"
            >

        </label>


        <button type="submit">
            Save Changes
        </button>


        <a
            class="button secondary"
            href="profile.php"
        >
            Cancel
        </a>

    </form>

</section>


<?php page_footer(); ?>