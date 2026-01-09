<?php
require 'db_connection.php';

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

// Pagination variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Number of users per page
$offset = ($page - 1) * $limit;
$sortColumn = isset($_GET['sort']) ? $_GET['sort'] : 'u_ID';
$sortOrder = isset($_GET['order']) && $_GET['order'] === 'desc' ? 'DESC' : 'ASC';
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Fetch users
$sql = "SELECT u_ID, u_name, u_email, role, last_logged_in 
        FROM user_tbl 
        WHERE u_name LIKE '%$search%' OR u_email LIKE '%$search%' 
        ORDER BY $sortColumn $sortOrder 
        LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = [
        "id" => $row["u_ID"],
        "username" => $row["u_name"],
        "email" => $row["u_email"],
        "role" => $row["role"],
        "last_logged_in" => $row["last_logged_in"]
    ];
}

// Get total user count for pagination
$countQuery = "SELECT COUNT(*) as total FROM user_tbl WHERE u_name LIKE '%$search%' OR u_email LIKE '%$search%'";
$countResult = $conn->query($countQuery);
$totalUsers = $countResult->fetch_assoc()["total"];

echo json_encode([
    "users" => $users,
    "pagination" => [
        "total" => $totalUsers,
        "page" => $page,
        "limit" => $limit
    ]
]);

$conn->close();
?>
