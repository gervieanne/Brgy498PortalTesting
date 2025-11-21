<?php
session_start();

$host = "localhost";
$user = "root";
$password = "";
$db = "498portal";

$data = mysqli_connect($host, $user, $password, $db);
if ($data === false) {
    die("Connection error: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($data, $_POST["username"]);
    $password = $_POST["password"];

    $sql = "SELECT * FROM admincreds WHERE username = ?";
    $stmt = mysqli_prepare($data, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_array($result);

    if ($row) {
        if ($row['is_locked']) {
            $error_message = "Your account is locked. Please visit the barangay hall to unlock it.";
        } elseif (password_verify($password, $row["password"])) {
            // Reset login attempts
            $update_sql = "UPDATE admincreds SET login_attempts = 0 WHERE username = ?";
            $update_stmt = mysqli_prepare($data, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "s", $username);
            mysqli_stmt_execute($update_stmt);

            if ($row["usertype"] == "admin") {
                $_SESSION["username"] = $username;
                $_SESSION["usertype"] = "admin";
                $_SESSION["admin_logged_in"] = true;
                header("location: ../admin-dashboard.php");
                exit();
            }
        } else {
            // Increment login attempts
            $attempts = $row['login_attempts'] + 1;
            $is_locked = ($attempts >= 3) ? 1 : 0;
            $update_sql = "UPDATE admincreds SET login_attempts = ?, is_locked = ? WHERE username = ?";
            $update_stmt = mysqli_prepare($data, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "iis", $attempts, $is_locked, $username);
            mysqli_stmt_execute($update_stmt);

            if ($is_locked) {
                $error_message = "You have entered the wrong credentials 3 times. Your account is now locked. Please visit the barangay hall to unlock it.";
            } else {
                $remaining = 3 - $attempts;
                $error_message = "Username or password incorrect. $remaining/3 attempts left.";
            }
        }
    } else {
        // Non-existent username
        $error_message = "Username or password incorrect.";
    }
}
?>

<!DOCTYPE html>

<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel="stylesheet" href="../admin-login/admin-login.css" />
<link rel="stylesheet" href="../preloader/preloader.css" />
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
<title>Admin Sign In</title>
</head>
<body>
<div class="choosing-page">
    <div class="left-landingpage">
        <img src="../images/barangay-logo.png" class="barangay-logo" alt="barangay-logo" />
        <h1>Barangay Management System</h1>
        <p class="sub-content">
            This is the official platform of Barangay 498, Zone 49, District IV, Manila, designed to streamline operations and enhance public service delivery. It provides a centralized system for requesting documents, managing records, announcements, and community information. Through this platform, the barangay upholds transparency, accountability, and efficient communication with its constituents.
        </p>
        <p class="footer">Where community service meets innovation.</p>
    </div>
    <div class="right-landingpage">
        <a href="../landingpage/index.php" class="blueback-btn">
            <img src="../images/blueback-logo.png" alt="back-logo" />
        </a>

    <?php if (isset($error_message)): ?>
        <div class="error-message" style="color: red; text-align: center; font-weight: bold; margin-bottom: 20px;">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" id="login-form" class="fade show">
        <h1 class="welcome">Welcome!</h1>
        <p class="subheader">Ready to manage your system? Sign in below.</p>

        <h1 class="form-header">Username</h1>
        <input type="text" name="username" placeholder="ex. John Doe" required />

        <h1 class="form-header">Password</h1>
        <div class="password-wrapper">
            <input type="password" name="password" id="passwordInput" placeholder="Enter your password" required />
            <button type="button" id="togglePassword" class="toggle-password">
                <svg class="eye-icon" viewBox="0 0 24 24">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
                <svg class="eye-off-icon" viewBox="0 0 24 24" style="display: none;">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                </svg>
            </button>
        </div>

        <input type="submit" name="login" value="Sign In" class="sign-in" />
    </form>
</div>

</div>

<div class="down-arrow" id="scrollArrow">
    <svg viewBox="0 0 24 24">
        <path d="M12 5v14M19 12l-7 7-7-7" />
    </svg>
</div>

<div class="preloader" id="preloader">
    <div class="spinner"></div>
    <p>Loading...</p>
</div>

<script src="../admin-login/admin-login.js"></script>

<script src="../preloader/preloader.js"></script>

</body>
</html>
