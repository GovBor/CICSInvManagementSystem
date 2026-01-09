<?php
require 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    // Validate input
    if (empty($username) || empty($email) || empty($role)) {
        echo json_encode(["error" => "All fields are required."]);
        exit();
    }

    // Update user
    $stmt = $conn->prepare("UPDATE user_tbl SET u_name = ?, u_email = ?, role = ? WHERE u_id = ?");
    $stmt->bind_param("sssi", $username, $email, $role, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => "User updated successfully."]);
    } else {
        echo json_encode(["error" => "Failed to update user."]);
    }

    $stmt->close();
} else {
    echo json_encode(["error" => "Invalid request"]);
}

$conn->close();
?>
