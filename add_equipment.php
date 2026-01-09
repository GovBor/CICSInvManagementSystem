<?php
include 'db_connection.php'; // Ensure this file initializes a MySQLi connection ($conn)

// Set the content type for JSON response
header('Content-Type: application/json');

// Initialize response array
$response = ['success' => false, 'message' => ''];

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input data
    $e_name = isset($_POST['e_name']) ? trim($_POST['e_name']) : '';
    $asset_id = isset($_POST['asset_id']) ? trim($_POST['asset_id']) : '';
    $e_ID = isset($_POST['e_ID']) ? trim($_POST['e_ID']) : ''; 
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $location_id = isset($_POST['location_id']) ? (int)$_POST['location_id'] : null;
    $e_desc = isset($_POST['e_desc']) ? trim($_POST['e_desc']) : '';
    $s_ID = 1; // Default status ID (1 = Working)

    // Validate required fields
    if (empty($e_name) || empty($category_id) || empty($e_desc)) {
        $response['message'] = 'All required fields must be filled!';
        echo json_encode($response);
        exit;
    }

    // Check if category_id exists
    $check_category = $conn->prepare("SELECT category_id FROM category_tbl WHERE category_id = ?");
    $check_category->bind_param("i", $category_id);
    $check_category->execute();
    $check_category->store_result();
    
    if ($check_category->num_rows === 0) {
        $response['message'] = 'The selected category does not exist.';
        echo json_encode($response);
        exit;
    }
    $check_category->close();

    // Validate location_id exists if provided
    if ($location_id !== null) {
        $check_location = $conn->prepare("SELECT location_id FROM location_tbl WHERE location_id = ?");
        $check_location->bind_param("i", $location_id);
        $check_location->execute();
        $check_location->store_result();
        
        if ($check_location->num_rows === 0) {
            $response['message'] = 'The selected location does not exist.';
            echo json_encode($response);
            exit;
        }
        $check_location->close();
    }

    // Check if equipment ID already exists (only for non-duplicate entries)
    if (!empty($e_ID)) {
        $check_equipment = $conn->prepare("SELECT e_ID FROM equipment_tbl WHERE e_ID = ?");
        $check_equipment->bind_param("s", $e_ID);
        $check_equipment->execute();
        $check_equipment->store_result();
        
        if ($check_equipment->num_rows > 0) {
            $response['message'] = 'Equipment with this ID already exists.';
            echo json_encode($response);
            exit;
        }
        $check_equipment->close();
    }

    // Insert data into the database
    $sql = "
        INSERT INTO equipment_tbl 
        (e_ID, e_name, e_desc, category_id, location_id, asset_id, s_ID, date_added)
        VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_DATE)
    ";

    // Prepare the statement
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $response['message'] = 'Database error: ' . $conn->error;
        echo json_encode($response);
        exit;
    }

    // Bind parameters - set e_ID to NULL for new ID
    $stmt->bind_param('sssiisi', 
        $e_ID,        // string (NULL for new ID)
        $e_name,      // string
        $e_desc,      // string
        $category_id, // int
        $location_id, // int (can be NULL)
        $asset_id,    // string
        $s_ID         // int
    );

    // Execute the statement
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Equipment added successfully!';
    } else {
        $response['message'] = 'Failed to add equipment: ' . $stmt->error;
    }

    // Close the statement
    $stmt->close();
} else {
    $response['message'] = 'Invalid request method.';
}

// Close the database connection
$conn->close();

// Return JSON response
echo json_encode($response);
?>
