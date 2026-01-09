<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: loginpage.php");
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor') {
    header("Location: main-page.php"); 
    exit();
}

if (isset($_POST['logout'])) {
    session_unset();
    session_destroy(); 
    header("Location: loginpage.php?logged_out=true");

    exit();
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User - CICS Techbooth Inventory</title>
    <link rel="stylesheet" href="user-management.css">
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
            <li class="generateReportsSideButton"><a href="generate-reports-page.php"><i
                        class="fas fa-file-alt"></i>Reports</a></li>
            <li class="archivedEquipmentsSideButton"><a href="archived-equipments-page.php"><i
                        class="fas fa-archive"></i> Archives</a></li>
            <li class="userManagementSideButton selected"><a href="user-management.php"><i class="fas fa-users-cog"></i>
                    User</a></li>
        </ul>
        <form method="POST" class="logout">
            <button class="logout" name="logout">
                Log Out <i class="fas fa-door-open"></i>
            </button>
        </form>
    </div>

    <div class="main-content">
        <header>
            <!-- Empty space on the left -->
            <div></div>
            <!-- Add User button moved to the right -->
            <button id="openAddUserModal" class="add-user">Add User <i class="fas fa-user-plus"></i></button>
        </header>

        <section class="content">
            <div class="top-bar">
                <div class="products-container">
                    <h3 class="products-heading">USER MANAGEMENT</h3>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="users-table-body">

                </tbody>
            </table>
        </section>
    </div>

    <!-- Add User Modal -->
    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <h2 class="add-title">Add New User</h2>
            <form id="addUserForm" action="add_user.php" method="POST">
                <div class="input-group">
                    <label>Username</label>
                    <input type="text" name="username" placeholder="Enter username" required>
                </div>
                <div class="input-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="Enter email address" required>
                </div>
                <div class="input-group">
                    <label>Password</label>
                    <div class="input-container">
                        <input type="password" id="password" name="password" placeholder="Enter password" required>
                        <i class="fas fa-eye toggle-password" onclick="togglePassword('password', this)"></i>
                    </div>
                </div>
                <div class="input-group">
                    <label>Confirm Password</label>
                    <div class="input-container">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password" required>
                        <i class="fas fa-eye toggle-password" onclick="togglePassword('confirm_password', this)"></i>
                    </div>
                </div>

                <script>
                    function togglePassword(fieldId, icon) {
                        var passwordField = document.getElementById(fieldId);

                        if (passwordField.type === "password") {
                            passwordField.type = "text";
                            icon.classList.remove("fa-eye");
                            icon.classList.add("fa-eye-slash");
                        } else {
                            passwordField.type = "password";
                            icon.classList.remove("fa-eye-slash");
                            icon.classList.add("fa-eye");
                        }
                    }
                </script>
                <div class="input-group">
                    <label>Role</label>
                    <select name="role" required>
                        <option value="">Select role</option>
                        <option value="supervisor">Supervisor</option>
                        <option value="technician">Technician</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" id="closeAddUserModal" class="cancel-btn">Cancel</button>
                    <button type="submit" class="add-btn">Add User</button>
                </div>
            </form>
            <div id="addUserResponseMessage"></div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <h2 class="edit-title">Edit User</h2>
            <form id="editUserForm">
                <input type="hidden" id="editUserId" name="user_id">
                <div class="input-group">
                    <label>Username</label>
                    <input type="text" id="editUsername" name="username" required>
                </div>
                <div class="input-group">
                    <label>Email</label>
                    <input type="email" id="editEmail" name="email" required>
                </div>
                <div class="input-group">
                    <label>Role</label>
                    <select id="editRole" name="role" required>
                    <option value="supervisor">Supervisor</option>
                    <option value="technician">Technician</option>
                    </select>
                </div>

                <div class="otp-box">
                <p id="otpBox" style="
                    background-color: #c62828;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    font-size: 16px;
                    cursor: pointer;
                    border-radius: 5px;
                    user-select: none;
                ">
                    Generate OTP: <?php echo isset($_SESSION["code"]) ? $_SESSION["code"] : 'No OTP'; ?>
                </p>

                </div>
                <div class="modal-footer">
                    <button type="button" id="closeEditUserModal" class="cancel-btn">Cancel</button>
                    <button type="submit" class="save-btn">Save Changes</button>
                </div>
            </form>
            <div id="editUserResponseMessage"></div>
        </div>
    </div>

        <!-- Edit Confirmation Modal -->
    <div id="editConfirmModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Confirm Changes</h2>
            <p>Are you sure you want to save these changes to the user?</p>
            <div class="modal-actions">
                <button id="confirmEdit" class="confirm-btn">Save Changes</button>
                <button id="cancelEdit" class="cancel-btn">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Edit Success Modal -->
    <div id="editSuccessModal" class="modal">
        <div class="modal-content">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2>Success!</h2>
            <p id="editSuccessMessage">User changes saved successfully.</p>
            <div class="modal-actions">
                <button id="confirmEditSuccess" class="confirm-btn">OK</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Delete User</h2>
        <p>Are you sure you want to delete this user? This action cannot be undone.</p>
        <div class="modal-actions">
            <button id="confirmDelete" class="confirm-btn delete-btn">Delete</button>
            <button id="cancelDelete" class="cancel-btn">Cancel</button>
        </div>
    </div>
</div>

<!-- Delete Success Modal -->
<div id="deleteSuccessModal" class="modal">
    <div class="modal-content">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h2>Success!</h2>
        <p id="deleteSuccessMessage">User deleted successfully.</p>
        <div class="modal-actions">
            <button id="confirmDeleteSuccess" class="confirm-btn">OK</button>
        </div>
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
    <script src="user-management.js"></script>
</body>

</html>
