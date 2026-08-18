<?php
// includes/config.php
// Define base URL for the project

// Automatically detect the base URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$base_url = $protocol . $host . '/stationery_store/';

// For WampServer local development
define('BASE_URL', $base_url);
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/stationery_store/');

?>