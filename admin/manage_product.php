<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit();
}
require_once '../config/db_connection.php';

// Get all products with category names
$sql = "SELECT p.*, c.category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        ORDER BY p.created_at DESC";
$products = mysqli_query($conn, $sql);
?>
<!-- Display products in a table with Edit/Delete buttons -->