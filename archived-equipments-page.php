<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: loginpage.php");
    exit();
}

if (isset($_POST['logout'])) {
    session_unset();
    session_destroy(); 
    header("Location: loginpage.php?logged_out=true");
    exit();
}

require 'db_connection.php';

$categories = [];
$category_query = "SELECT * FROM category_tbl";
if ($category_result = $conn->query($category_query)) {
    $categories = $category_result->fetch_all(MYSQLI_ASSOC);
    $category_result->free();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Equipments - CICS Techbooth Inventory</title>
    <link rel="stylesheet" href="archived-equipments-page.css">
    <link rel="icon" href="favicon-16x16.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<div class="sidebar">
        <div class="logo-container">
            <a href="main-page.php">
                <img src="CICS LOGO.png" alt="CICS Logo">
            </a>
            <h2>CICS <span>Techbooth<br>Inventory</span></h2>
        </div>
        <div class="user-info">
    <div class="user-greeting">
    <p>Hi,
        <span class="greeting-name">
            <?php echo isset($_SESSION['u_name']) ? $_SESSION['u_name'] : 'user'; ?>
        </span>
    </p>
</div>
    <div class="user-details">
        <p class="user-role"><?php echo isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : 'Role'; ?> 
        <span class="user-id">#<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '0000'; ?></span></p>
    </div>
</div>

        <ul>

    <li class="dasboardSideButton">
        <a href="main-page.php"><i class="fas fa-tachometer-alt"></i> Home</a>
    </li>
    <li class="generateReportsSideButton">
        <a href="generate-reports-page.php"><i class="fas fa-file-alt"></i> Reports</a>
    </li>
    <li class="archivedEquipmentsSideButton selected">
        <a href="archived-equipments-page.php"><i class="fas fa-archive"></i> Archives</a>
    </li>
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'supervisor'): ?>
        <li class="userManagementSideButton">
            <a href="user-management.php"><i class="fas fa-users-cog"></i> User</a>
        </li>
    <?php endif; ?>
</ul>
        <form method="POST" class="logout">
            <button class="logout" name="logout">
                Log Out <i class="fas fa-door-open"></i>
            </button>
        </form>
    </div>

    <div class="main-content">
        <header>
            <h1><span class="underline">ARCHIVES</span></h1>
        </header>

        <div class="filters">
            <div class="filters-container">
                <input type="text" id="searchBox" placeholder="Search Equipment Name...">
                <select id="categoryFilter">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['category_id'] ?>"><?= $category['category_name'] ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="locationFilter">
                    <option value="">All Locations</option>
                    <?php foreach ($locations as $location): ?>
                        <option value="<?= $location['location_id'] ?>"><?= $location['location_name'] ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="date-filter">
                    <label for="dateFrom">From:</label>
                    <input type="date" id="dateFrom">
                </div>
                <div class="date-filter">
                    <label for="dateTo">To:</label>
                    <input type="date" id="dateTo">
                </div>
                <button id="filterBtn">Apply</button>
            </div>
        </div>

        <section class="content">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th data-sort="category_name">Category <span class="sort-arrow"></span></th>
                            <th data-sort="e_name">Name <span class="sort-arrow"></span></th>
                            <th data-sort="asset_id">Asset ID <span class="sort-arrow"></span></th>
                            <th data-sort="e_ID">Serial Number <span class="sort-arrow"></span></th>
                            <th data-sort="e_desc">Description <span class="sort-arrow"></span></th>
                            <th data-sort="date_archived">Date Archived <span class="sort-arrow"></span></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="equipment-table-body">

</tbody>
                </table>
            </div>
        </section>

        <div class="pagination">
            <button id="prevPage" disabled>Previous</button>
            <span id="pageInfo">Page 1 of 1</span>
            <button id="nextPage" disabled>Next</button>
        </div>
    </div>

    <div id="restoreModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Restore Equipment</h2>
            <p>Are you sure you want to restore this equipment?</p>
            <div class="modal-actions">
                <button id="confirmRestore" class="confirm-btn">Restore</button>
                <button id="cancelRestore" class="cancel-btn">Cancel</button>
            </div>
        </div>
    </div>

    <div id="successModal" class="modal">
        <div class="modal-content">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2>Success!</h2>
            <p id="successMessage">Equipment restored successfully.</p>
            <div class="modal-actions">
                <button id="confirmSuccess" class="confirm-btn">OK</button>
            </div>
        </div>
    </div>

    <div id="logoutConfirmModal" class="modal logout-modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Confirm Logout</h2>
            <p>Are you sure you want to log out?</p>
            <div class="modal-actions">
                <button id="confirmLogout" class="confirm-btn logout">Logout</button>
                <button id="cancelLogout" class="cancel-btn">Cancel</button>
            </div>
        </div>
    </div>

    <div id="logoutSuccessModal" class="modal">
        <div class="modal-content">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2>Logged Out Successfully</h2>
            <p>You have been logged out. Redirecting to login page...</p>
        </div>
    </div>

    <script src="archived-equipments-page.js"></script>
</body>
</html>