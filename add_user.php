<?php
header('Content-Type: application/json');
require 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirm_password"];
    $role = $_POST["role"];

    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword) || empty($role)) {
        echo json_encode(["error" => "All fields are required."]);
        exit();
    }

    if ($password !== $confirmPassword) {
        echo json_encode(["error" => "Passwords do not match."]);
        exit();
    }

    // Sanitize inputs
    $username = $conn->real_escape_string($username);
    $email = $conn->real_escape_string($email);
    $role = $conn->real_escape_string($role);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into `user_tbl`
    $sql = "INSERT INTO user_tbl (u_name, u_email, u_password, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $username, $email, $hashedPassword, $role);

    if ($stmt->execute()) {
        echo json_encode(["success" => "User added successfully."]);
    } else {
        echo json_encode(["error" => "Error adding user: " . $stmt->error]);
    }

    $stmt->close();
}

$conn->close();
?>
