<?php
//register.php
session_start();

// Include BOTH config files
require_once '../includes/config.php';        // For BASE_URL
require_once '../config/db_connection.php';   // For database connection
require_once '../config/auth.php';            // For authentication functions

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
        $fields[$key] = trim($_POST[$key] ?? '');
    }

    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // ==============================================
    // VALIDATE USER INPUT
    // ==============================================

    if (!$fields['username'] || !$fields['email'] || !$password) {
        $errors[] = 'Please complete all required fields.';
    }

    if ($fields['email'] && !filter_var($fields['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    // ==============================================
    // CHECK DUPLICATE USER
    // ==============================================

    if (!$errors) {
        $checkUser = mysqli_prepare(
            $conn,
            "SELECT user_id
             FROM users
             WHERE username = ?
             OR email = ?"
        );

        mysqli_stmt_bind_param(
            $checkUser,
            "ss",
            $fields['username'],
            $fields['email']
        );

        mysqli_stmt_execute($checkUser);
        $result = mysqli_stmt_get_result($checkUser);
        $existingUser = mysqli_fetch_assoc($result);

        if ($existingUser) {
            $errors[] = 'That username or email is already registered.';
        } else {
            // ======================================
            // CREATE - REGISTER NEW USER
            // ======================================

            // Hash password before saving it.
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $createUser = mysqli_prepare(
                $conn,
                "INSERT INTO users
                (
                    username,
                    email,
                    password,
                    full_name,
                    address,
                    phone
                )
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $createUser,
                "ssssss",
                $fields['username'],
                $fields['email'],
                $passwordHash,
                $fields['full_name'],
                $fields['address'],
                $fields['phone']
            );

            if (mysqli_stmt_execute($createUser)) {
                // Set success flash message
                flash('success', 'Account created successfully. Please log in.');
                
                // Redirect to login page
                redirect('login.php');
                exit();
            } else {
                $errors[] = 'Registration failed: ' . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Stationery Store</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/user_style.css">
    <style>
        .form-card {
            max-width: 600px;
            margin: 40px auto;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }
        .form-card h1 {
            text-align: center;
            font-size: 28px;
            color: #1a1a2e;
            margin-bottom: 25px;
        }
        .form-card label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            color: #444;
        }
        .form-card label .required {
            color: #dc2626;
        }
        .form-card input,
        .form-card textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e8ecf1;
            border-radius: 8px;
            font-size: 15px;
            margin-bottom: 18px;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        .form-card input:focus,
        .form-card textarea:focus {
            border-color: #4A90D9;
            outline: none;
            box-shadow: 0 0 0 4px rgba(74, 144, 217, 0.15);
        }
        .form-card textarea {
            resize: vertical;
            min-height: 80px;
        }
        .form-card button {
            width: 100%;
            background: #4A90D9;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .form-card button:hover {
            background: #357ABD;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(74, 144, 217, 0.35);
        }
        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }
        .form-footer {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        .form-footer a {
            color: #4A90D9;
            text-decoration: none;
            font-weight: 600;
        }
        .form-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<?php include '../includes/header.php'; ?>

<section class="form-card">
    <h1>Create Account</h1>

    <!-- ==========================================
         DISPLAY ERRORS
    =========================================== -->
    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- ==========================================
         REGISTRATION FORM
    =========================================== -->
    <form method="POST" action="">
        <label>
            Username <span class="required">*</span>
            <input type="text" name="username" required value="<?php echo htmlspecialchars($fields['username']); ?>">
        </label>

        <label>
            Email <span class="required">*</span>
            <input type="email" name="email" required value="<?php echo htmlspecialchars($fields['email']); ?>">
        </label>

        <label>
            Full Name
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($fields['full_name']); ?>">
        </label>

        <label>
            Address
            <textarea name="address"><?php echo htmlspecialchars($fields['address']); ?></textarea>
        </label>

        <label>
            Phone
            <input type="text" name="phone" value="<?php echo htmlspecialchars($fields['phone']); ?>">
        </label>

        <label>
            Password <span class="required">*</span>
            <input type="password" name="password" required>
        </label>

        <label>
            Confirm Password <span class="required">*</span>
            <input type="password" name="confirm_password" required>
        </label>

        <button type="submit">Create Account</button>
    </form>

    <p class="form-footer">
        Already have an account? <a href="login.php">Login here</a>
    </p>
</section>

<?php include '../includes/footer.php'; ?>

</body>
</html>