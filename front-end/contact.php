<?php
// modules/member1_frontend/contact.php
session_start();

// Include BOTH config files
require_once '../includes/config.php';        // For BASE_URL
require_once '../config/db_connection.php';   // For database connection

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    // SECURE VERSION - prevents SQL injection
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    
    if (!empty($name) && !empty($email) && !empty($message)) {
        $sql = "INSERT INTO contact_messages (name, email, message) VALUES ('$name', '$email', '$message')";
        if (mysqli_query($conn, $sql)) {
            $success = "Message sent successfully!";
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    } else {
        $error = "Please fill in all fields!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Stationery Store</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        .contact-page {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .contact-page h1 {
            text-align: center;
            font-size: 36px;
            color: #1a1a2e;
            margin-bottom: 10px;
        }
        .contact-page > p {
            text-align: center;
            color: #666;
            margin-bottom: 40px;
        }
        .contact-container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 40px;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }
        .contact-info h2 {
            font-size: 22px;
            color: #1a1a2e;
            margin-bottom: 20px;
        }
        .contact-info p {
            margin: 12px 0;
            color: #555;
            line-height: 1.6;
        }
        .contact-info strong {
            color: #1a1a2e;
        }
        .contact-form h2 {
            font-size: 22px;
            color: #1a1a2e;
            margin-bottom: 20px;
        }
        .contact-form label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            color: #444;
        }
        .contact-form input,
        .contact-form textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e8ecf1;
            border-radius: 8px;
            font-size: 15px;
            margin-bottom: 18px;
            transition: all 0.3s ease;
            font-family: inherit;
        }
        .contact-form input:focus,
        .contact-form textarea:focus {
            border-color: #4A90D9;
            outline: none;
            box-shadow: 0 0 0 4px rgba(74, 144, 217, 0.15);
        }
        .contact-form textarea {
            resize: vertical;
            min-height: 150px;
        }
        .contact-form button {
            background: #4A90D9;
            color: white;
            padding: 14px 35px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .contact-form button:hover {
            background: #357ABD;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(74, 144, 217, 0.35);
        }
        .success-message {
            background: #d1fae5;
            color: #065f46;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
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
        @media (max-width: 768px) {
            .contact-container {
                grid-template-columns: 1fr;
                padding: 25px;
            }
            .contact-page h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>

<?php include '../includes/header.php'; ?>

<section class="contact-page">
    <h1>Contact Us</h1>
    <p>Feel free to contact us if there is any question.</p>
    
    <?php if (isset($success)): ?>
        <div class="success-message"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="contact-container">
        <div class="contact-info">
            <h2>Get In Touch</h2>
            <p><strong>Email:</strong> beststationery@gmail.com</p>
            <p><strong>Phone:</strong> 012-3456789</p>
            <p><strong>Address:</strong> UTAR Sungai Long Campus</p>
        </div>
        <div class="contact-form">
            <h2>Send Us a Message</h2>
            <form action="" method="POST">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" required>
                
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
                
                <label for="message">Message</label>
                <textarea id="message" name="message" rows="6" required></textarea>
                
                <button type="submit" name="submit">Send Message</button>
            </form>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

</body>
</html>