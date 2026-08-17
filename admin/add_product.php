<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit();
}
require_once '../config/db_connection.php';

// Fetch categories for dropdown
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['product_name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category = $_POST['category_id'];
    
    $sql = "INSERT INTO products (product_name, description, price, stock_quantity, category_id) 
            VALUES ('$name', '$desc', '$price', '$stock', '$category')";
    
    if (mysqli_query($conn, $sql)) {
        $success = "Product added successfully!";
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}
?>