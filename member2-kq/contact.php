<?php
session_start();
include 'header.php';
include 'config.php';

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    // SECURE VERSION - prevents SQL injection
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $message = $conn->real_escape_string($_POST['message']);
    
    if(!empty($name) && !empty($email) && !empty($message)) {
        $sql = "INSERT INTO contact_messages (name, email, message) VALUES ('$name', '$email', '$message')";
        if($conn->query($sql)) {
            echo "<p style='color:green; text-align:center;'>Message sent!</p>";
        } else {
            echo "<p style='color:red; text-align:center;'>Error: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color:red; text-align:center;'>Fill all fields</p>";
    }
}
?>
<section class="contact-page">
    <h1>Contact Us</h1>
    <p>Feel free to contact us if there is any question.</p>
    <div class="contact-container">
        <div class="contact-info">
            <h2>Get In Touch</h2>
            <p><strong>Email:</strong> beststationery@gmail.com</p>
            <p><strong>Phone:</strong> 012-3456789</p>
            <p><strong>Address:</strong> UTAR Sungai Long Campus</p>
        </div>
        <div class="contact-form">
            <h2>Send Us a Message</h2>
            <form action="contact.php" method="POST">
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
<?php include 'footer.php'; ?>
