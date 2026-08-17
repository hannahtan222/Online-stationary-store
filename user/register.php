<?php

require 'db_connect.php';


// ==================================================
// VARIABLES
// ==================================================

$errors = [];

$fields = [

    'username' => '',
    'email' => '',
    'full_name' => '',
    'address' => '',
    'phone' => ''
];


// ==================================================
// PROCESS REGISTRATION
// ==================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    // ==============================================
    // GET FORM DATA
    // ==============================================

    foreach ($fields as $key => $value) {

        $fields[$key] =
            trim($_POST[$key] ?? '');
    }

    $password =
        $_POST['password'] ?? '';

    $confirmPassword =
        $_POST['confirm_password'] ?? '';


    // ==============================================
    // VALIDATE USER INPUT
    // ==============================================

    if (
        !$fields['username'] ||
        !$fields['email'] ||
        !$password
    ) {

        $errors[] =
            'Please complete all required fields.';
    }


    if (
        $fields['email'] &&
        !filter_var(
            $fields['email'],
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $errors[] =
            'Please enter a valid email address.';
    }


    if (strlen($password) < 6) {

        $errors[] =
            'Password must be at least 6 characters.';
    }


    if ($password !== $confirmPassword) {

        $errors[] =
            'Passwords do not match.';
    }


    // ==============================================
    // CHECK DUPLICATE USER
    // ==============================================

    if (!$errors) {

        $checkUser = $pdo->prepare(
            'SELECT user_id
             FROM users
             WHERE username = ?
             OR email = ?'
        );

        $checkUser->execute([
            $fields['username'],
            $fields['email']
        ]);


        if ($checkUser->fetch()) {

            $errors[] =
                'That username or email is already registered.';

        } else {


            // ======================================
            // CREATE - REGISTER NEW USER
            // ======================================

            // Hash the password before saving it.
            $hashedPassword =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );


            $createUser = $pdo->prepare(
                'INSERT INTO users
                (
                    username,
                    email,
                    password,
                    full_name,
                    address,
                    phone
                )
                VALUES (?, ?, ?, ?, ?, ?)'
            );


            $createUser->execute([

                $fields['username'],

                $fields['email'],

                $hashedPassword,

                $fields['full_name'],

                $fields['address'],

                $fields['phone']
            ]);


            flash(
                'success',
                'Account created successfully. Please log in.'
            );

            redirect('login.php');
        }
    }
}


page_header('Register');

?>

<section class="form-card">

    <h1>Create Account</h1>


    <!-- ==========================================
         DISPLAY ERRORS
    =========================================== -->

    <?php foreach ($errors as $error): ?>

        <div class="message error">

            <?= e($error) ?>

        </div>

    <?php endforeach; ?>


    <form method="post">


        <label>
            Username *

            <input
                type="text"
                name="username"
                required
                value="<?= e($fields['username']) ?>"
            >

        </label>


        <label>
            Email *

            <input
                type="email"
                name="email"
                required
                value="<?= e($fields['email']) ?>"
            >

        </label>


        <label>
            Full Name

            <input
                type="text"
                name="full_name"
                value="<?= e($fields['full_name']) ?>"
            >

        </label>


        <label>
            Address

            <textarea
                name="address"
            ><?= e($fields['address']) ?></textarea>

        </label>


        <label>
            Phone

            <input
                type="text"
                name="phone"
                value="<?= e($fields['phone']) ?>"
            >

        </label>


        <label>
            Password *

            <input
                type="password"
                name="password"
                required
            >

        </label>


        <label>
            Confirm Password *

            <input
                type="password"
                name="confirm_password"
                required
            >

        </label>


        <button type="submit">
            Create Account
        </button>

    </form>

</section>


<?php page_footer(); ?>