<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json'); // Ensure JSON response

require 'db_connection.php'; // Adjust based on your actual database connection file

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(["error" => true, "message" => "User ID is required."]);
    exit;
}

$user_id = intval($_GET['id']); // Sanitize input

$query = $conn->prepare("SELECT u_ID AS id, u_name AS username, u_email AS email, role FROM user_tbl WHERE u_ID = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(["error" => true, "message" => "User not found."]);
}
?>
