<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit();
}
require_once '../config/db_connection.php';

// Get all orders with user info
$sql = "SELECT o.*, u.username, u.email 
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id 
        ORDER BY o.order_date DESC";
$orders = mysqli_query($conn, $sql);
?>
<!-- Display orders in a table -->