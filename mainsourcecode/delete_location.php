<?php
session_start();
require_once 'db_connection.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid location ID']);
    exit;
}

$locationId = intval($_GET['id']);

$stmt = $conn->prepare("DELETE FROM location_tbl WHERE location_id = ?");
$stmt->bind_param("i", $locationId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete location']);
}

$stmt->close();
$conn->close();
?>
