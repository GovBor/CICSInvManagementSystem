<?php
// db_connection.php

$host = '127.0.0.1'; // Database host
$user = 'root';      // Database username
$password = '';      // Database password
$database = 'cicsinvsystem'; // Database name

// Create a MySQLi connection
$conn = new mysqli($host, $user, $password, $database);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
