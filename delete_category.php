<?php
session_start();
require_once 'db_connection.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid category ID']);
    exit;
}

$categoryId = intval($_GET['id']);

$stmt = $conn->prepare("DELETE FROM category_tbl WHERE category_id = ?");
$stmt->bind_param("i", $categoryId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete category']);
}

$stmt->close();
$conn->close();
?>