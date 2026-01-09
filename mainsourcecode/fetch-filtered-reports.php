<?php
$servername = "localhost";
$username = "root"; 
$password = ""; 
$database = "cicsinvsystem"; 
$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Get Filters & Pagination Parameters
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$status_id = isset($_GET['status']) ? (int)$_GET['status'] : 0;
$location_id = isset($_GET['location']) ? (int)$_GET['location'] : 0; // NEW: Add location filter
$dateFrom = isset($_GET['dateFrom']) ? $conn->real_escape_string($_GET['dateFrom']) : '';
$dateTo = isset($_GET['dateTo']) ? $conn->real_escape_string($_GET['dateTo']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 15;
$offset = ($page - 1) * $limit;

// Base SQL Query
$sql = "SELECT e.e_ID, e.e_name, e.e_desc, e.asset_id, e.date_added, 
               c.category_name, l.location_name, s.s_status, s.s_ID
        FROM equipment_tbl e
        JOIN status_tbl s ON e.s_ID = s.s_ID
        LEFT JOIN category_tbl c ON e.category_id = c.category_id
        LEFT JOIN location_tbl l ON e.location_id = l.location_id
        WHERE 1";

// Apply Filters
if (!empty($search)) {
    $sql .= " AND (e.e_name LIKE '%$search%' OR e.e_desc LIKE '%$search%' OR e.e_ID LIKE '%$search%')";
}
if ($category_id > 0) {
    $sql .= " AND e.category_id = $category_id";
}
if ($status_id > 0) {
    $sql .= " AND e.s_ID = $status_id";
}
if ($location_id > 0) { // NEW: Add location filter condition
    $sql .= " AND e.location_id = $location_id";
}
if (!empty($dateFrom) && !empty($dateTo)) {
    $sql .= " AND e.date_added BETWEEN '$dateFrom' AND '$dateTo'";
}

// Count Total Rows
$countQuery = "SELECT COUNT(*) as total FROM ($sql) as count_table";
$totalResult = $conn->query($countQuery);
$totalRows = $totalResult->fetch_assoc()['total'];

// Apply Pagination and sorting
$sql .= " ORDER BY e.date_added DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['date_added'] = date('Y-m-d', strtotime($row['date_added']));
        $data[] = $row;
    }
}

// Return JSON Data
header('Content-Type: application/json');
echo json_encode([
    'data' => $data,
    'totalRows' => $totalRows,
    'currentPage' => $page,
    'totalPages' => ceil($totalRows / $limit)
]);

$conn->close();
?>