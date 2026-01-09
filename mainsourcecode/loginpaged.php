<?php
session_start();
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: main-page.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "cicsinvsystem";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $plain_password = $_POST['password'];

    // Fetch user details including u_name
    $stmt = $conn->prepare("SELECT u_ID, u_password, role, u_name FROM user_tbl WHERE u_email = ?");
    if (!$stmt) {
        header("Location: loginpage.php?error=database_error");
        exit();
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows === 0) {
        header("Location: loginpage.php?error=invalid");
        exit();
    }

    $stmt->bind_result($user_id, $stored_password, $role, $u_name);
    $stmt->fetch();

    if (password_verify(trim($plain_password), trim($stored_password))) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $role;
        $_SESSION['u_name'] = $u_name;
        $_SESSION['logged_in'] = true;

        date_default_timezone_set("Asia/Manila");
        $current_time = date("Y-m-d H:i:s");

        $update_stmt = $conn->prepare("UPDATE user_tbl SET last_logged_in = ? WHERE u_ID = ?");
        if ($update_stmt) {
            $update_stmt->bind_param("si", $current_time, $user_id);
            $update_stmt->execute();
            $update_stmt->close();
        }

        header("Location: main-page.php");
        exit();
    } else {
        header("Location: loginpage.php?error=invalid");
        exit();
    }

    $stmt->close();
}

$conn->close();
?>