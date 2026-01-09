<?php
session_start();
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $locationName = $_POST['location_name'];
    
    // Validate input
    if (empty($locationName)) {
        echo json_encode(['success' => false, 'message' => 'Location name is required']);
        exit;
    }
    
    // Check if location already exists
    $checkStmt = $conn->prepare("SELECT location_name FROM location_tbl WHERE location_name = ?");
    $checkStmt->bind_param("s", $locationName);
    $checkStmt->execute();
    $checkStmt->store_result();
    
    if ($checkStmt->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Location already exists']);
        exit;
    }
    
    // Insert new location
    $stmt = $conn->prepare("INSERT INTO location_tbl (location_name) VALUES (?)");
    $stmt->bind_param("s", $locationName);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Location added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error adding location']);
    }
    
    $stmt->close();
    $checkStmt->close();
    $conn->close();
}
?>