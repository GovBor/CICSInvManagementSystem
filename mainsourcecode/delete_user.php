<?php
include 'db_connection.php'; // Make sure this file correctly connects to your database

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['user_id']) && is_numeric($_POST['user_id'])) {
        $user_id = intval($_POST['user_id']);

        // Prepare SQL to delete user
        $stmt = $conn->prepare("DELETE FROM user_tbl WHERE u_ID = ?");
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            echo json_encode(["success" => "User deleted successfully."]);
        } else {
            echo json_encode(["error" => "Error deleting user."]);
        }

        $stmt->close();
        $conn->close();
    } else {
        echo json_encode(["error" => "Invalid user ID."]);
    }
} else {
    echo json_encode(["error" => "Invalid request method."]);
}
?>
