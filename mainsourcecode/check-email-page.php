<?php
session_start();
require 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = $_POST["code1"] . $_POST["code2"] . $_POST["code3"] . $_POST["code4"] . $_POST["code5"];
    $email = $_GET['email'];
    $_SESSION['email'] = $email;

    // Fetch from DB
    $stmt = $conn->prepare("SELECT generated_at FROM otp_codes WHERE code = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row) {
        header("Location: check-email-page.php?error=invalid");
        exit();
    }

    $generated = strtotime($row['generated_at']);
    if (time() - $generated > 180) {
        header("Location: check-email-page.php?error=expired");
        exit();
    }

    // Success!
    header("Location: change-password-paged.php");
    exit();
}

$email = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : 'your_email@example.com';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email OTP - CICS Inventory Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="check-email-page.css">
    <link rel="icon" href="favicon/favicon-16x16.png" type="image/x-icon">
</head>
<body>
    <div class="check-email-container d-flex justify-content-center align-items-center">
        <div class="content-container d-flex">
            <div class="form-container text-center">
                <button type="button" class="btn btn-outline-secondary btn-sm mb-4 back-button" onclick="history.back()">&#x2190;</button>
                <h1 class="mb-3">ONE-TIME<br>PASSCODE</h1>
                <p class="text-muted">Ask your supervisor for your OTP <b><?= $email ?></b><br>Enter the 5-digit code</p>
                
                <!-- Wrap inputs and button in a form -->
                <form method="POST">
                    <div class="otp-container d-flex justify-content-center mb-4">
                        <input type="text" name="code1" class="otp-input" maxlength="1" required>
                        <input type="text" name="code2" class="otp-input" maxlength="1" required>
                        <input type="text" name="code3" class="otp-input" maxlength="1" required>
                        <input type="text" name="code4" class="otp-input" maxlength="1" required>
                        <input type="text" name="code5" class="otp-input" maxlength="1" required>
                    </div>
                    <?php if (isset($_GET['error']) && ($_GET['error'] == 'invalid' || $_GET['error'] == 'expired')) { ?>
                    <div class="mb-3 text-danger">The OTP is invalid or has expired. Please try again.</div>
                    <?php } ?>

                    <button type="submit" class="btn btn-danger">Verify OTP</button>
                </form>
            </div>
            <div class="logo-container text-center">
                <img src="CICS LOGO.png" alt="CICS LOGO" class="logo">
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script to move to next input on typing on the OTP-->
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const inputs = document.querySelectorAll(".otp-input");

        inputs.forEach((input, index) => {
            input.addEventListener("input", (e) => {
                if (e.inputType !== "deleteContentBackward" && input.value.length === 1) {
                    if (index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                }
            });

            input.addEventListener("keydown", (e) => {
                if (e.key === "Backspace" && index > 0 && !input.value) {
                    inputs[index - 1].focus();
                }
            });
        });
    });
</script>

</body>
</html>
