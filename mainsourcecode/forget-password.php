<?php
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

// Database connection code
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cicsinvsystem";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email = $_POST['email'];
$sql = "SELECT * FROM user_tbl WHERE u_email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {    
    // Email exists, proceed with password reset logic
    header("Location: check-email-page.php?email=" . urlencode($email));
} else {
    header("Location: forgot-password-page.html?error=email_not_found");
    exit();
}

$stmt->close();
$conn->close();
?>
