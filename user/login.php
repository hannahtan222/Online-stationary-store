<?php

require 'db_connect.php';

$error = '';


// ==================================================
// PROCESS LOGIN
// ==================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim(
        $_POST['email'] ?? ''
    );

    $password = $_POST['password'] ?? '';


    // ==============================================
    // READ - FIND USER BY EMAIL
    // ==============================================

    $findUser = $pdo->prepare(
        'SELECT
            user_id,
            username,
            full_name,
            password
         FROM users
         WHERE email = ?'
    );

    $findUser->execute([
        $email
    ]);

    $user = $findUser->fetch();


    // ==============================================
    // VERIFY PASSWORD
    // ==============================================

    if (
        $user &&
        password_verify(
            $password,
            $user['password']
        )
    ) {

        // Create a new session ID after login
        session_regenerate_id(true);

        $_SESSION['user_id'] =
            $user['user_id'];

        $_SESSION['username'] =
            $user['username'];

        $_SESSION['full_name'] =
            $user['full_name'];


        redirect('profile.php');

    } else {

        $error =
            'Invalid email or password.';
    }
}


page_header('Login');

?>

<section class="form-card">

    <h1>Login</h1>


    <?php if ($error): ?>

        <div class="message error">
            <?= e($error) ?>
        </div>

    <?php endif; ?>


    <form method="post">

        <label>
            Email

            <input
                type="email"
                name="email"
                required
            >

        </label>


        <label>
            Password

            <input
                type="password"
                name="password"
                required
            >

        </label>


        <button type="submit">
            Login
        </button>

    </form>


    <p>
        Don't have an account?

        <a href="register.php">
            Register here
        </a>
    </p>

</section>


<?php page_footer(); ?>