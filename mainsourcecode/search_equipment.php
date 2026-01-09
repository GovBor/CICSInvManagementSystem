<?php
session_start();
require_once 'db_connection.php';

// Disable error reporting
error_reporting(0);
ini_set('display_errors', 0);

// Check if connection exists
if (!isset($conn) || !$conn instanceof mysqli || $conn->connect_error) {
    die(json_encode(['error' => 'Database connection error']));
}

// Get search term from POST request
$searchTerm = isset($_POST['searchTerm']) ? trim($_POST['searchTerm']) : '';

// If search term is empty, return all equipment or an appropriate message
if (empty($searchTerm)) {
    die(json_encode(['error' => 'No search term provided']));
}

try {
    // Prepare SQL query with JOINs (now includes status_tbl)
    $query = "SELECT e.*, c.category_name, l.location_name, s.s_status 
              FROM equipment_tbl e
              LEFT JOIN category_tbl c ON e.category_id = c.category_id
              LEFT JOIN location_tbl l ON e.location_id = l.location_id
              LEFT JOIN status_tbl s ON e.s_id = s.s_id
              WHERE (e.e_name LIKE ? OR 
                     e.e_ID LIKE ? OR 
                     e.asset_id LIKE ? OR 
                     e.e_desc LIKE ? OR
                     c.category_name LIKE ? OR
                     l.location_name LIKE ?)
              AND e.s_id IN (1, 2)  -- Limit to status ID 1 and 2
              ORDER BY e.e_name ASC";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $searchPattern = "%$searchTerm%";
    $stmt->bind_param("ssssss", 
        $searchPattern, 
        $searchPattern, 
        $searchPattern, 
        $searchPattern,
        $searchPattern,
        $searchPattern);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $equipments = $result->fetch_all(MYSQLI_ASSOC);
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode(['data' => $equipments, 'error' => null]);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['data' => [], 'error' => $e->getMessage()]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?>