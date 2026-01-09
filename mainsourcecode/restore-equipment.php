<?php
session_start();
require_once 'db_connection.php';

header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get equipment ID
$e_ID = isset($_POST['e_ID']) ? trim($_POST['e_ID']) : null;

if (empty($e_ID)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Equipment ID is required']);
    exit();
}

try {
    // Check if equipment exists and is archived
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
    
    // Only allow restoring if currently archived (status 3)
    if ($equipment['s_ID'] != 3) {
        echo json_encode(['success' => false, 'message' => 'Equipment is not archived']);
        exit();
    }
    
    // Restore to Working status (status 1) and clear archive date
    // In your update statement:
$updateStmt = $conn->prepare("
UPDATE equipment_tbl 
SET s_ID = 1, 
    date_archived = NULL,
    marked_for_deletion = 0  -- Clear deletion flag when restoring
WHERE e_ID = ?
");
    $updateStmt->bind_param("s", $e_ID);
    $updateStmt->execute();
    
    if ($updateStmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Equipment restored successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to restore equipment']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} finally {
    if (isset($conn)) $conn->close();
}
?>