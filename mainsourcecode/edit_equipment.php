<?php
require 'db_connection.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize input
    $old_e_ID = trim($_POST['old_e_ID'] ?? '');
    $new_e_ID = trim($_POST['e_ID'] ?? '');
    $e_name = trim($_POST['e_name'] ?? '');
    $asset_id = trim($_POST['asset_id'] ?? '');
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $location_id = isset($_POST['location_id']) ? (int)$_POST['location_id'] : null;
    $e_desc = trim($_POST['e_desc'] ?? '');

    // Validate required fields
    if (empty($new_e_ID) || empty($e_name) || empty($category_id) || empty($e_desc)) {
        echo json_encode(["success" => false, "message" => "All required fields must be filled"]);
        exit;
    }

    // Check if this is a new record (duplicate) or an edit
    $isNewRecord = empty($old_e_ID);

    if ($isNewRecord) {
        // ===== CREATE NEW RECORD (DUPLICATE) =====
        
        // Check if e_ID already exists
        $check = $conn->prepare("SELECT e_ID FROM equipment_tbl WHERE e_ID = ?");
        $check->bind_param("s", $new_e_ID);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            echo json_encode(["success" => false, "message" => "Equipment ID already exists"]);
            exit;
        }
        $check->close();

        // Insert new record
        $stmt = $conn->prepare("INSERT INTO equipment_tbl 
            (e_ID, e_name, asset_id, category_id, location_id, e_desc, date_added, status_id) 
            VALUES (?, ?, ?, ?, ?, ?, NOW(), 1)");
        
        $stmt->bind_param("sssiis", $new_e_ID, $e_name, $asset_id, $category_id, $location_id, $e_desc);
        
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Equipment duplicated successfully!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to create duplicate: " . $stmt->error]);
        }
        
    } else {
        // ===== UPDATE EXISTING RECORD =====
        
        // Check if new e_ID is different and already exists
        if ($old_e_ID !== $new_e_ID) {
            $check = $conn->prepare("SELECT e_ID FROM equipment_tbl WHERE e_ID = ?");
            $check->bind_param("s", $new_e_ID);
            $check->execute();
            $check->store_result();
            
            if ($check->num_rows > 0) {
                echo json_encode(["success" => false, "message" => "New Equipment ID already exists"]);
                exit;
            }
            $check->close();
        }

        // Update existing record
        $stmt = $conn->prepare("UPDATE equipment_tbl SET 
            e_ID = ?, 
            e_name = ?, 
            asset_id = ?, 
            category_id = ?, 
            location_id = ?, 
            e_desc = ? 
            WHERE e_ID = ?");
        
        $stmt->bind_param("sssiiss", $new_e_ID, $e_name, $asset_id, $category_id, $location_id, $e_desc, $old_e_ID);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(["success" => true, "message" => "Equipment updated successfully!"]);
            } else {
                echo json_encode(["success" => false, "message" => "No changes made"]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Update failed: " . $stmt->error]);
        }
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
?>