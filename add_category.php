<?php
session_start();
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryName = $_POST['category_name'];
    
    // Validate input
    if (empty($categoryName)) {
        echo json_encode(['success' => false, 'message' => 'Category name is required']);
        exit;
    }
    
    // Check if category already exists
    $checkStmt = $conn->prepare("SELECT category_name FROM category_tbl WHERE category_name = ?");
    $checkStmt->bind_param("s", $categoryName);
    $checkStmt->execute();
    $checkStmt->store_result();
    
    if ($checkStmt->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Category already exists']);
        exit;
    }
    
    // Insert new category
    $stmt = $conn->prepare("INSERT INTO category_tbl (category_name) VALUES (?)");
    $stmt->bind_param("s", $categoryName);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Category added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error adding category']);
    }
    
    $stmt->close();
    $checkStmt->close();
    $conn->close();
}
?>