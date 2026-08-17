<?php
	// config/db_connection.php
	$servername = "localhost";
	$username = "root";
	$password = "";
	$dbname = "stationery_store";

	// Create connection
	$conn = mysqli_connect($servername, $username, $password, $dbname);

	// Check connection
	if (!$conn) {
		die("Connection failed: " . mysqli_connect_error());
	}
?>