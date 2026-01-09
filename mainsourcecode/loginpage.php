<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CICS Inventory Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="loginpage.css">
    <link rel="icon" href="favicon-16x16.png" type="image/x-icon">
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <h1>WELCOME<br>
              <span class="welcome-subtitle">CICS Inventory Management System</span></h1>
            <form action="loginpaged.php" method="post">
                <div class="mb-3">
                  <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" required>
                </div>
                <div class="mb-3">
                  <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                </div>
                <?php if (isset($_GET['error']) && $_GET['error'] == 'invalid') { ?>
                    <div class="mb-3 text-danger">Invalid Login Credentials. Please Try again.</div>
                <?php } ?>
                <div class="mb-3 form-text">
                    <a href="forgot-password-page.html" class="forgot-password">Forgot password?</a>
                </div>
                <button type="submit" class="btn btn-danger">Sign in</button>
            </form>
        </div>
        <div class="logo-container">
            <img src="CICS LOGO.png" alt="CICS LOGO">
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
