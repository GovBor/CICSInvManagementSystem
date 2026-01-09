<?php
require 'db_connection.php';

header('Content-Type: application/json');

try {
    // Input validation for pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $page = max(1, $page); // Ensure page is at least 1

    $itemsPerPage = 10;
    $offset = ($page - 1) * $itemsPerPage;

    // Handle sorting safely
    $allowedSortColumns = ['e_name', 'e_ID', 's_status', 'category_name', 'location_name', 'asset_id', 'e_desc', 'date_added'];
    $sortColumn = in_array($_GET['sort'] ?? '', $allowedSortColumns) ? $_GET['sort'] : 'date_added';
    $sortOrder = ($_GET['order'] ?? 'desc') === 'desc' ? 'DESC' : 'ASC';

    // Handle filters
    $whereClauses = [];
    $params = [];
    $types = '';

    // ARCHIVE-SPECIFIC FILTERS
    $showArchived = isset($_GET['archived']) && $_GET['archived'] == '1';
    if ($showArchived) {
        $whereClauses[] = "s.s_ID = 3"; // Only show archived items
    } else {
        $excludeArchived = !isset($_GET['include_archived']) || $_GET['include_archived'] != '1';
        if ($excludeArchived) {
            $whereClauses[] = "s.s_ID != 3";
        }
    }

    // Status filter
    if (!empty($_GET['status'])) {
        $statuses = explode(',', $_GET['status']);
        $placeholders = implode(',', array_fill(0, count($statuses), '?'));
        $whereClauses[] = "s.s_ID IN ($placeholders)";
        $types .= str_repeat('i', count($statuses));
        $params = array_merge($params, $statuses);
    }

    // Category filter
    if (!empty($_GET['category'])) {
        $categories = explode(',', $_GET['category']);
        $placeholders = implode(',', array_fill(0, count($categories), '?'));
        $whereClauses[] = "e.category_id IN ($placeholders)";
        $types .= str_repeat('i', count($categories));
        $params = array_merge($params, $categories);
    }

    // Location filter
    if (!empty($_GET['location'])) {
        $locations = is_array($_GET['location']) ? $_GET['location'] : explode(',', $_GET['location']);
        $placeholders = implode(',', array_fill(0, count($locations), '?'));
        $whereClauses[] = "e.location_id IN ($placeholders)";
        $types .= str_repeat('i', count($locations));
        $params = array_merge($params, $locations);
    }

    // Search term filter
    if (!empty($_GET['search'])) {
        $searchTerm = '%' . $_GET['search'] . '%';
        $whereClauses[] = "(e.e_name LIKE ? OR e.e_desc LIKE ? OR e.e_ID LIKE ? OR e.asset_id LIKE ?)";
        $types .= 'ssss';
        array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    }

    // Date range filter
    if (!empty($_GET['date_from']) || !empty($_GET['date_to'])) {
        $dateField = $showArchived ? 'COALESCE(e.date_archived, e.date_added)' : 'e.date_added';
        $dateConditions = [];
        
        if (!empty($_GET['date_from'])) {
            $dateConditions[] = "$dateField >= ?";
            $types .= 's';
            array_push($params, $_GET['date_from']);
        }
        
        if (!empty($_GET['date_to'])) {
            $dateConditions[] = "$dateField <= ?";
            $types .= 's';
            array_push($params, $_GET['date_to']);
        }
        
        if (!empty($dateConditions)) {
            $whereClauses[] = "(" . implode(' AND ', $dateConditions) . ")";
        }
    }

    // Build WHERE clause
    $whereClause = empty($whereClauses) ? '' : 'WHERE ' . implode(' AND ', $whereClauses);

    // Fetch total number of records with filters
    $totalQuery = "SELECT COUNT(*) as total 
                   FROM equipment_tbl e 
                   JOIN status_tbl s ON e.s_ID = s.s_ID 
                   LEFT JOIN category_tbl c ON e.category_id = c.category_id
                   LEFT JOIN location_tbl l ON e.location_id = l.location_id
                   $whereClause";
    
    $totalStmt = $conn->prepare($totalQuery);
    
    if (!empty($params)) {
        $totalStmt->bind_param($types, ...$params);
    }
    
    $totalStmt->execute();
    $totalResult = $totalStmt->get_result();
    $totalRows = $totalResult->fetch_assoc()['total'];
    $totalPages = ceil($totalRows / $itemsPerPage);
    $totalStmt->close();

    // Main query with all necessary fields for duplication
    // Main query with all necessary fields for duplication - FIXED VERSION
$sql = "SELECT e.e_ID, e.e_name, e.e_desc, e.asset_id, e.s_ID, 
e.date_added, e.date_archived, 
e.category_id, e.location_id,
c.category_name, l.location_name, s.s_status,
COALESCE(e.date_archived, e.date_added) as sort_date
FROM equipment_tbl e
JOIN status_tbl s ON e.s_ID = s.s_ID
LEFT JOIN category_tbl c ON e.category_id = c.category_id
LEFT JOIN location_tbl l ON e.location_id = l.location_id
$whereClause
ORDER BY $sortColumn $sortOrder
LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
throw new Exception("SQL Prepare Error: " . $conn->error);
}

// Bind parameters - FIXED VERSION
if (!empty($params)) {
// First bind the filter parameters
$filterTypes = $types;
$filterParams = $params;

// Then add the pagination parameters
$filterTypes .= 'ii';
array_push($filterParams, $itemsPerPage, $offset);

$stmt->bind_param($filterTypes, ...$filterParams);
} else {
// No filters, just pagination
$stmt->bind_param("ii", $itemsPerPage, $offset);
}
    $stmt->execute();
    $result = $stmt->get_result();

    $equipments = [];
    while ($row = $result->fetch_assoc()) {
        // Format dates for display
        $row['date_added_formatted'] = $row['date_added'] ? date('M d, Y', strtotime($row['date_added'])) : '';
        $row['date_archived_formatted'] = $row['date_archived'] ? date('M d, Y', strtotime($row['date_archived'])) : '';
        $equipments[] = $row;
    }

    // Return JSON response
    echo json_encode([
        'success' => true,
        'data' => $equipments,
        'pagination' => [
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalRows,
            'itemsPerPage' => $itemsPerPage
        ],
        'filters' => [
            'search' => $_GET['search'] ?? null,
            'category' => $_GET['category'] ?? null,
            'location' => $_GET['location'] ?? null,
            'status' => $_GET['status'] ?? null,
            'archived' => $showArchived,
            'dateFrom' => $_GET['date_from'] ?? null,
            'dateTo' => $_GET['date_to'] ?? null
        ],
        'sort' => [
            'column' => $sortColumn,
            'order' => strtolower($sortOrder)
        ]
    ]);

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>