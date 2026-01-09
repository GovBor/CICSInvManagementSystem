<?php
header("Content-Type: application/json"); // Ensure JSON output

// Check if request is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit();
}

// Connect to database
$conn = new mysqli("localhost", "root", "", "cicsinvsystem");
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit();
}

// Retrieve and validate input
$e_ID = isset($_POST["e_ID"]) ? trim($_POST["e_ID"]) : null;
$s_ID = isset($_POST["s_ID"]) ? intval($_POST["s_ID"]) : null;

if (!$e_ID || !$s_ID) {
    echo json_encode(["status" => "error", "message" => "Invalid data"]);
    exit();
}

// Update status in the database
$sql = "UPDATE equipment_tbl SET s_ID = ? WHERE e_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $s_ID, $e_ID);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}

$stmt->close();
$conn->close();
?>
