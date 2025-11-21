<?php
session_start();

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', '498portal');

function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        die("Connection error. Please try again later.");
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}

$error_message = '';
$success_message = '';

// Redirect if already logged in
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
        header("Location: ../user-dashboard/user-dashboard.php");
        exit();
    } else {
        session_unset();
        session_destroy();
        session_start();
    }
}

// Logout message
if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $success_message = "You have been successfully logged out.";
}

// LOGIN PROCESSING
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error_message = "Please enter both username and password.";
    } else {
        $conn = getDBConnection();

        $stmt = $conn->prepare("SELECT * FROM usercreds WHERE UPPER(username) = UPPER(?) AND status='active'");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if ($user['is_locked']) {
                $error_message = "Your account is locked. Please visit the barangay hall to unlock it.";
            } elseif (password_verify($password, $user['password'])) {
                // Reset login attempts
                $update_stmt = $conn->prepare("UPDATE usercreds SET login_attempts = 0 WHERE user_id = ?");
                $update_stmt->bind_param("i", $user['user_id']);
                $update_stmt->execute();
                $update_stmt->close();

                // Login session
                session_regenerate_id(true);
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['account_type'] = $user['account_type'];
                $_SESSION['login_time'] = time();
                $_SESSION['login_ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

                // Update last login
                $update_stmt = $conn->prepare("UPDATE usercreds SET last_login = CURRENT_TIMESTAMP WHERE user_id = ?");
                $update_stmt->bind_param("i", $user['user_id']);
                $update_stmt->execute();
                $update_stmt->close();

                header("Location: ../user-dashboard/user-dashboard.php");
                exit();
            } else {
                // Wrong password: increment attempts
                $attempts = $user['login_attempts'] + 1;
                $is_locked = ($attempts >= 3) ? 1 : 0;

                $update_stmt = $conn->prepare("UPDATE usercreds SET login_attempts = ?, is_locked = ? WHERE user_id = ?");
                $update_stmt->bind_param("iii", $attempts, $is_locked, $user['user_id']);
                $update_stmt->execute();
                $update_stmt->close();

                if ($is_locked) {
                    $error_message = "You have entered the wrong credentials 3 times. Your account is now locked. Please visit the barangay hall to unlock it.";
                } else {
                    $remaining = 3 - $attempts;
                    $error_message = "Username or password incorrect. $remaining/3 attempts left.";
                }
            }
        } else {
            // Username does not exist
            $error_message = "Username or password incorrect. 2/3 attempts left.";
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>

<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel="stylesheet" href="../user-login/user-login.css" />
<link rel="stylesheet" href="../preloader/preloader.css" />
<title>User Sign In - Barangay 498</title>
<style>
.error-message { background-color: #fee; color: #c33; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; text-align: center; border: 1px solid #fcc; width: 90%; animation: shake 0.3s; }
.success-message { background-color: #d1e7dd; color: #0f5132; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; text-align: center; border: 1px solid #a3cfbb; width: 90%; }
@keyframes shake {0%,100%{transform:translateX(0);}25%{transform:translateX(-10px);}75%{transform:translateX(10px);} }
</style>
</head>
<body>
<div class="choosing-page">
    <div class="left-landingpage">
        <img src="../images/barangay-logo.png" class="barangay-logo" alt="barangay-logo" />
        <h1>Barangay Management System</h1>
        <p class="sub-content">Official platform for Barangay 498, Manila. Streamlines requests, records, announcements, and info.</p>
        <p class="footer">Where community service meets innovation.</p>
    </div>
    <div class="right-landingpage">
        <a href="../landingpage/index.php" class="blueback-btn"><img src="../images/blueback-logo.png" alt="back-logo" /></a>
        <?php if (!empty($success_message)): ?><div class="success-message"><?= htmlspecialchars($success_message) ?></div><?php endif; ?>
        <?php if (!empty($error_message)): ?><div class="error-message"><?= htmlspecialchars($error_message) ?></div><?php endif; ?>
        <form method="POST" action="">
            <h1 class="form-header">Username</h1>
            <input type="text" name="username" placeholder="Enter your username" required autofocus />
            <h1 class="form-header">Password</h1>
            <div class="password-wrapper">
                <input type="password" name="password" placeholder="Enter your password" required />
                <button type="button" id="togglePassword" class="toggle-password">Show/Hide</button>
            </div>
            <button type="submit" name="login" class="sign-in">Sign In</button>
        </form>
    </div>
</div>
<script src="../user-login/user-login.js"></script>
<script src="../preloader/preloader.js"></script>
</body>
</html>
