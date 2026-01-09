<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cicsinvsystem";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['email'])) {
    $email = $_POST['email'];  // Retrieve the email from the POST request
} else {
    echo "No email provided!";
    exit; // Exit if no email is provided
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the new password and confirm password from the form
    $newPassword = $_POST['password'];
    $confirmPassword = $_POST['confirm-password'];

        // Define password rules
        $passwordMinLength = 8;
        $passwordMaxLength = 20;
        $passwordPattern = "/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";
    
        if (strlen($newPassword) < $passwordMinLength) {
            header("Location: change-password-paged.php?email=" . urlencode($email) . "&error=Password must be at least $passwordMinLength characters long.");
            exit; // Stop execution and redirect
        }
        
        if (strlen($newPassword) > $passwordMaxLength) {
            header("Location: change-password-paged.php?email=" . urlencode($email) . "&error=Password must be at most $passwordMaxLength characters long.");
            exit; 
        }
    
        if (!preg_match($passwordPattern, $newPassword)) {
            header("Location: change-password-paged.php?email=" . urlencode($email) . "&error=Password must contain at least one letter, one number, and one special character.");
            exit;
        }

    // Check if passwords match
    if ($newPassword === $confirmPassword) {
        // Hash the new password
        $hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);

        // Prepare the SQL query to find the user ID based on the email
        $query = "SELECT u_ID FROM user_tbl WHERE u_email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email); // Bind the email parameter
        $stmt->execute();
        $stmt->bind_result($u_ID);
        $stmt->fetch();

        // Consume the result (make sure to close the statement)
        $stmt->free_result();
        $stmt->close();

        if ($u_ID) {
            // If a user is found, update the password
            $updateQuery = "UPDATE user_tbl SET u_password = ? WHERE u_ID = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("si", $hashed_password, $u_ID); // "si" means string and integer
            $updateStmt->execute();

            if ($updateStmt->affected_rows > 0) {
                // Redirect to login page after updating
                header("Location: loginpage.php?code_executed=true");
                exit;
            } else {
                echo "Failed to update password.";
            }

            $updateStmt->close();
        } else {
            echo "No user found with that email.";
        }
    } else {
        header("Location: change-password-paged.php?email=" . urlencode($email) . "&error=Passwords do not match!");
        exit;
    }
}
?>
