<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
if (!isset($_SESSION['logged_in'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once 'db_connection.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $barcode = trim($input['barcode'] ?? '');

    if (empty($barcode)) {
        echo json_encode(['error' => 'Barcode is required']);
        exit();
    }

    // Check both e_ID and asset_id fields
    $stmt = $conn->prepare("
        SELECT 
            e.e_ID,
            e.e_name,
            e.e_desc,
            e.asset_id,
            c.category_name,
            l.location_name,
            s.s_status,
            CASE 
                WHEN s.s_status = 'Available' THEN 'available'
                WHEN s.s_status = 'Checked Out' THEN 'checked-out' 
                WHEN s.s_status = 'Under Maintenance' THEN 'maintenance'
                ELSE ''
            END as status_class
        FROM equipment_tbl e
        LEFT JOIN category_tbl c ON e.category_id = c.category_id
        LEFT JOIN location_tbl l ON e.location_id = l.location_id
        LEFT JOIN status_tbl s ON e.s_id = s.s_id
        WHERE e.e_ID = ? OR e.asset_id = ?
    ");
    
    $stmt->bind_param("ss", $barcode, $barcode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $equipment = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'exists' => true,
            'equipment' => $equipment
        ]);
    } else {
        // Debugging - log all barcodes
        $allCodes = $conn->query("SELECT e_ID, asset_id FROM equipment_tbl");
        error_log("Existing barcodes: " . print_r($allCodes->fetch_all(), true));
        
        echo json_encode([
            'success' => true,
            'exists' => false,
            'message' => 'Barcode not found'
        ]);
    }
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage()
    ]);
} finally {
    if (isset($conn)) $conn->close();
}
?>