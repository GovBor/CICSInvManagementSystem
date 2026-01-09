<?php
session_start();
require_once 'db_connection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit();
}

$e_ID = isset($data['e_ID']) ? trim($data['e_ID']) : null;

if (empty($e_ID)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Equipment ID is required']);
    exit();
}

try {
    $checkStmt = $conn->prepare("SELECT s_ID FROM equipment_tbl WHERE e_ID = ?");
    $checkStmt->bind_param("s", $e_ID);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Equipment not found']);
        exit();
    }
    
    $equipment = $result->fetch_assoc();
    $archiveStatus = 3; // Archived status ID
    
    // If already archived
    if ($equipment['s_ID'] == $archiveStatus) {
        echo json_encode(['success' => true, 'message' => 'Equipment is already archived']);
        exit();
    }
    
    // Update status to archived
    $updateStmt = $conn->prepare("UPDATE equipment_tbl SET s_ID = ? WHERE e_ID = ?");
    $updateStmt->bind_param("is", $archiveStatus, $e_ID);
    $updateStmt->execute();
    
    if ($updateStmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Equipment archived successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to archive equipment']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} finally {
    if (isset($conn)) $conn->close();
}
?>
