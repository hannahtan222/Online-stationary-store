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