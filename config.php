<?php
$conn = new mysqli("localhost", "root", "", "stationery_db");
if($conn->connect_error) die("Connection failed: " . $conn->connect_error);
?>