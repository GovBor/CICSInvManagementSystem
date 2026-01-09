<?php 
session_start();
$email = $_SESSION['email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - CICS Inventory Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="change-password-page.css">
    <link rel="icon" href="favicon/favicon-16x16.png" type="image/x-icon">
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <h1>SET A NEW<br>
                <h1>PASSWORD</h1><br>
                <form action="change-password-page.php" method="post">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                <div class="mb-3">
                  <label for="password" class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control" placeholder="New password" required>
                </div>
                <div class="mb-3">
                  <label for="confirm-password" class="form-label">Confirm Password</label>
                    <input type="password" name="confirm-password"class="form-control" placeholder="Confirm new password" required>
                </div>
                <?php if (isset($_GET['error'])) { ?>
                    <div class="mb-3 text-danger">
                        <?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                    <?php } ?>
                <button type="submit" class="btn btn-danger">Update Password</button>
            </form>
        </div>
        <div class="logo-container">
            <img src="CICS LOGO.png" alt="CICS LOGO">
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>