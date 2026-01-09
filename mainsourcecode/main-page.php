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
require_once 'db_connection.php';

$categories = [];
$locations = [];
$category_query = "SELECT * FROM category_tbl";
$location_query = "SELECT * FROM location_tbl";

if (!isset($conn) || !$conn instanceof mysqli || $conn->connect_error) {
    die("Database connection error: " . $conn->connect_error);
}

if ($category_result = $conn->query($category_query)) {
    $categories = $category_result->fetch_all(MYSQLI_ASSOC);
    $category_result->free();
} else {
    die("Error fetching categories: " . $conn->error);
}

if ($location_result = $conn->query($location_query)) {
    $locations = $location_result->fetch_all(MYSQLI_ASSOC);
    $location_result->free();
} else {
    die("Error fetching locations: " . $conn->error);
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Page - CICS Inventory</title>
    <link rel="stylesheet" href="main-page.css">
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
            <li class="dasboardSideButton selected">
                <a href="main-page.php"><i class="fas fa-tachometer-alt"></i> Home</a>
            </li>
            <li class="generateReportsSideButton"><a href="generate-reports-page.php"><i class="fas fa-file-alt"></i>Reports</a></li>
            <li class="archivedEquipmentsSideButton"><a href="archived-equipments-page.php"><i class="fas fa-archive"></i> Archives</a></li>
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
        <h1 class="page-title"><span class="underline">EQUIPMENTS</span></h1>
        
        <div class="action-buttons">
            <button id="openModal" class="action-btn add-equipment">Add Equipment <i class="fas fa-plus"></i></button>
            <button id="addCategoryBtn" class="action-btn add-category">Add Category <i class="fas fa-plus"></i></button>
            <button id="addLocationBtn" class="action-btn add-location">Add Location <i class="fas fa-plus"></i></button>
            <button id="deleteCategoryBtn" class="action-btn delete-category">Delete Category <i class="fas fa-trash"></i></button>
            <button id="deleteLocationBtn" class="action-btn delete-location">Delete Location <i class="fas fa-trash"></i></button>

        </div>
            <div class="filters">
            <div class="filters-container">
                <input type="text" id="searchBox" placeholder="Search Equipment Name...">
                <div class="category-select-wrapper">
                    <select id="categoryFilter">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['category_id'] ?>"><?= $category['category_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <select id="statusFilter">
                    <option value="">All Status</option>
                    <option value="1">Working</option>
                    <option value="2">Defective</option>
                </select>
                <div class="category-select-wrapper">
                    <select id="locationFilter"> <!-- Ensure this ID matches -->
                        <option value="">All Locations</option>
                        <?php foreach ($locations as $location): ?>
                    <option value="<?= $location['location_id'] ?>"><?= $location['location_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
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
            <table>
                <thead>
                    <tr>
                        <th data-sort="category_name">Category <span class="sort-arrow"></span></th>
                        <th data-sort="e_name">Name <span class="sort-arrow"></span></th>
                        <th data-sort="asset_id">Asset ID <span class="sort-arrow"></span></th>
                        <th data-sort="e_ID">Serial Number <span class="sort-arrow"></span></th>
                        <th data-sort="e_desc">Description <span class="sort-arrow"></span></th>
                        <th data-sort="location_name">Location <span class="sort-arrow"></span></th>
                        <th data-sort="s_status">Status <span class="sort-arrow"></span></th>
                        <th data-sort="date_added">Date Added <span class="sort-arrow"></span></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="equipment-table-body">
                </tbody>
            </table>
        </section>
        <div class="pagination">
            <button id="prevPageBtn">Previous</button>
            <span id="pageInfo">Page 1 of 1</span>
            <button id="nextPageBtn">Next</button>
        </div>
    </div>
<div id="equipmentNotFoundModal" class="modal hidden">
    <div class="modal-content">
        <div class="warning-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h2>Equipment Not Found</h2>
        <p>The equipment with barcode <span id="scannedBarcode"></span> was not found in the database.</p>
        <p>Would you like to add this as new equipment?</p>
        <div class="modal-actions">
            <button id="cancelAddNewEquipment" class="cancel-btn">No, Cancel</button>
            <button id="confirmAddNewEquipment" class="add-btn">Yes, Add Equipment</button>
        </div>
    </div>
</div>
    <div id="addEquipmentModal" class="modal hidden">
    <div class="modal-content">
        <h2 class="add-title">Add Equipment</h2>
        <form id="addEquipmentForm" action="add_equipment.php" method="POST">
            <div class="input-group">
                <label>Name</label>
                <input type="text" name="e_name" id="e_name" placeholder="Enter equipment name" required>
            </div>
            <div class="input-group">
                <label>Asset ID</label>
                <input type="text" name="asset_id" id="asset_id" placeholder="Enter asset ID">
            </div>
            <div class="input-group">
                <label>Serial No.</label>
                <div class="input-container">
                    <input type="text" name="e_ID" id="e_ID" placeholder="Enter equipment ID" maxlength= "18" required>
                    <i class="fas fa-barcode"></i>
                </div>
            </div>
            <div class="input-group">
                <label>Category</label>
                <select name="category_id" id="category_id" required>
                <option value="">-- Select Category --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['category_id'] ?>"><?= $category['category_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-group">
                <label>Location</label>
                <select name="location_id" id="location_id">
                    <option value="">-- Select Location --</option>
                    <?php foreach ($locations as $location): ?>
                        <option value="<?= $location['location_id'] ?>"><?= $location['location_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-group">
                <label>Description</label>
                <input type="text" name="e_desc" id="e_desc" placeholder="Enter equipment description" maxlength="60" required>
            </div>
            <div class="modal-footer">
                <button type="button" id="closeModal" class="cancel-btn">Cancel</button>
                <button type="submit" class="add-btn">Add Equipment</button>
            </div>
        </form>
        <div id="responseMessage"></div>
    </div>
</div>
<div id="editEquipmentModal" class="modal hidden">
    <div class="modal-content">
        <h2 class="edit-title">Edit Equipment</h2>
        <form id="editEquipmentForm" action="edit_equipment.php" method="POST">
            <input type="hidden" id="oldEditId" name="old_e_ID">
            <div class="input-group">
                <label>Name</label>
                <input type="text" id="editName" name="e_name" placeholder="Enter equipment name" required>
            </div>
            <div class="input-group">
                <label>Asset ID</label>
                <input type="text" id="editAssetId" name="asset_id" placeholder="Enter asset ID">
            </div>
            <div class="input-group">
                <label>Serial No.</label>
                <div class="input-container">
                    <input type="text" id="editId" name="e_ID" placeholder="Enter equipment ID" maxlength="18" required>
                    <i class="fas fa-barcode"></i>
                </div>
            </div>
            <div class="input-group">
                <label>Category</label>
                <select id="editCategory" name="category_id" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['category_id'] ?>"><?= $category['category_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-group">
                <label>Location</label>
                <select id="editLocation" name="location_id">
                    <option value="">-- Select Location --</option>
                    <?php foreach ($locations as $location): ?>
                        <option value="<?= $location['location_id'] ?>"><?= $location['location_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="input-group">
                <label>Description</label>
                <input type="text" id="editDescription" name="e_desc" placeholder="Enter equipment description" maxlength="60" required>
            </div>
            <div class="modal-footer">
                <button type="button" id="closeEditModal" class="cancel-btn">Cancel</button>
                <button type="submit" class="edit-btn">Save Changes</button>
            </div>
        </form>
        <div id="editResponseMessage"></div>
    </div>
</div>
<div id="logoutConfirmModal" class="modal logout-modal">
    <div class="modal-content">
        <h2>Confirm Logout</h2>
        <p>Are you sure you want to log out?</p>
        <div class="modal-actions">
            <button id="confirmLogout" class="confirm-btn logout">Logout</button>
            <button id="cancelLogout" class="cancel-btn logout">Cancel</button>
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
<div id="archiveConfirmModal" class="modal hidden">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Confirm Archive</h2>
        <p>Are you sure you want to archive this equipment?</p>
        <div class="modal-actions">
            <button id="confirmArchive" class="confirm-btn archive">Archive</button>
            <button id="cancelArchive" class="cancel-btn archive">Cancel</button>
        </div>
    </div>
</div>
<div id="archiveSuccessModal" class="modal hidden">
    <div class="modal-content">
        <div class="success-icon">
            <i class="fas fa-archive"></i>
        </div>
        <h2>Equipment Archived</h2>
        <p>The equipment has been successfully archived.</p>
        <div class="modal-actions">
            <button id="confirmArchiveSuccess" class="add-btn">OK</button>
        </div>
    </div>
</div>
<div id="addCategoryModal" class="modal hidden">
    <div class="modal-content">
        <h2 class="add-title">Add Category</h2>
        <form id="addCategoryForm">
            <div class="input-group">
                <label>Name</label>
                <input type="text" id="categoryName" placeholder="Enter category name" required>
            </div>
            <div class="modal-footer">
                <button type="button" id="closeCategoryModal" class="cancel-btn">Cancel</button>
                <button type="submit" class="add-btn">Save Category</button>
            </div>
        </form>
        <div id="categoryResponseMessage"></div>
    </div>
</div>

<!-- Add Location Modal -->
<div id="addLocationModal" class="modal hidden">
    <div class="modal-content">
        <h2 class="add-title">Add Location</h2>
        <form id="addLocationForm">
            <div class="input-group">
                <label>Name</label>
                <input type="text" id="locationName" placeholder="Enter location name" required>
            </div>
            <div class="modal-footer">
                <button type="button" id="closeLocationModal" class="cancel-btn">Cancel</button>
                <button type="submit" class="add-btn">Save Location</button>
            </div>
        </form>
        <div id="locationResponseMessage"></div>
    </div>
</div>

<div id="addEquipmentSuccessModal" class="modal hidden">
    <div class="modal-content">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h2>Equipment Added</h2>
        <p>The equipment has been successfully added to the inventory.</p>
        <div class="modal-actions">
            <button id="confirmAddSuccess" class="add-btn">OK</button>
        </div>
    </div>
</div>

<!-- Delete Category Modal -->
<div id="deleteCategoryModal" class="modal hidden">
    <div class="modal-content">
        <h2 class="delete-title">Delete Category</h2>
        <p>Select a category to delete:</p>
        <div class="input-group">
            <select id="deleteCategorySelect">
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['category_id'] ?>"><?= $category['category_name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="modal-footer">
            <button type="button" id="closeDeleteCategoryModal" class="cancel-btn">Cancel</button>
            <button type="button" id="confirmDeleteCategory" class="delete-btn">Delete</button>
        </div>
        <div id="deleteCategoryResponseMessage"></div>
    </div>
</div>

<!-- Delete Location Modal -->
<div id="deleteLocationModal" class="modal hidden">
    <div class="modal-content">
        <h2 class="delete-title">Delete Location</h2>
        <p>Select a location to delete:</p>
        <div class="input-group">
            <select id="deleteLocationSelect">
                <?php foreach ($locations as $location): ?>
                    <option value="<?= $location['location_id'] ?>"><?= $location['location_name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="modal-footer">
            <button type="button" id="closeDeleteLocationModal" class="cancel-btn">Cancel</button>
            <button type="button" id="confirmDeleteLocation" class="delete-btn">Delete</button>
        </div>
        <div id="deleteLocationResponseMessage"></div>
    </div>
</div>

<!-- Delete Category Confirmation Modal -->
<div id="deleteCategoryConfirmModal" class="modal hidden">
    <div class="modal-content">
        <h2>Confirm Delete Category</h2>
        <p>Are you sure you want to delete the category "<span id="categoryToDeleteName"></span>"?</p>
        <p class="warning-text"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone.</p>
        <div class="modal-actions">
            <button type="button" id="cancelDeleteCategory" class="cancel-btn">Cancel</button>
            <button type="button" id="finalConfirmDeleteCategory" class="delete-btn">Delete</button>
        </div>
    </div>
</div>

<!-- Delete Location Confirmation Modal -->
<div id="deleteLocationConfirmModal" class="modal hidden">
    <div class="modal-content">
        <h2>Confirm Delete Location</h2>
        <p>Are you sure you want to delete the location "<span id="locationToDeleteName"></span>"?</p>
        <p class="warning-text"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone.</p>
        <div class="modal-actions">
            <button type="button" id="cancelDeleteLocation" class="cancel-btn">Cancel</button>
            <button type="button" id="finalConfirmDeleteLocation" class="delete-btn">Delete</button>
        </div>
    </div>
</div>
<!-- Delete Category Success Modal -->
<div id="deleteCategorySuccessModal" class="modal hidden">
    <div class="modal-content">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h2>Category Deleted</h2>
        <p>The category "<span id="deletedCategoryName"></span>" has been successfully deleted.</p>
    </div>
</div>

<!-- Delete Location Success Modal -->
<div id="deleteLocationSuccessModal" class="modal hidden">
    <div class="modal-content">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h2>Location Deleted</h2>
        <p>The location "<span id="deletedLocationName"></span>" has been successfully deleted.</p>
    </div>
</div>

<script src="main-page.js"></script>
</body>
</html>
