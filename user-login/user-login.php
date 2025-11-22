<?php

// Start session
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

// Database Connection Function
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

// Check if user is already logged in - redirect to dashboard
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    // Verify session is still valid
    if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
        header("Location: ../user-dashboard/user-dashboard.php");
        exit();
    } else {
        // Invalid session, clear it
        session_unset();
        session_destroy();
        session_start();
    }
}

// Handle logout message
if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $success_message = "You have been successfully logged out.";
}

// LOGIN PROCESSING
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = trim(strtoupper($_POST['username']));
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error_message = "Please enter both username and password.";
    } else {
        $conn = getDBConnection();
        
        // Query from usercreds with LEFT JOIN to userprofile498
        // Check if lockout columns exist first
        $check_columns = $conn->query("SHOW COLUMNS FROM usercreds LIKE 'login_attempts'");
        $has_lockout_columns = ($check_columns && $check_columns->num_rows > 0);
        $stmt = null;
        
        if ($has_lockout_columns) {
            // Include login_attempts and is_locked columns
            $stmt = $conn->prepare("
                SELECT uc.user_id, uc.username, uc.password, uc.full_name, uc.status, 
                       uc.account_type, uc.address, uc.date_of_birth, uc.place_of_birth, 
                       uc.sex, uc.civil_status, uc.occupation, uc.citizenship, 
                       uc.relation_to_household, uc.contact_number, uc.email,
                       uc.login_attempts, uc.is_locked,
                       up.profile_id, up.first_name
                FROM usercreds uc
                LEFT JOIN userprofile498 up ON uc.user_id = up.user_id
                WHERE uc.username = ? AND uc.status = 'active'
            ");
        } else {
            // Columns don't exist - show error message
            $error_message = "System configuration incomplete. Please run setup_login_lockout.php to enable login lockout feature.";
            error_log("Login lockout columns not found. Please run setup_login_lockout.php");
            $stmt = null;
        }
        
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            // Setup incomplete - can't proceed
            $result = null;
        }
        
        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Check if account is locked (handle case where column might not exist)
            $is_locked = isset($user['is_locked']) ? intval($user['is_locked']) : 0;
            if ($is_locked) {
                $error_message = "Your account is locked. Please visit the barangay hall to unlock it.";
                error_log("Locked account login attempt: $username");
            } else {
                // Verify password
                if (password_verify($password, $user['password'])) {
                    // SECURITY CHECK: Block admin accounts from logging in here
                    if ($user['account_type'] !== 'admin' && $user['account_type'] !== 'administrator') {
                        
                        // Reset login attempts on successful login
                        if ($has_lockout_columns) {
                            $reset_stmt = $conn->prepare("UPDATE usercreds SET login_attempts = 0 WHERE user_id = ?");
                            $reset_stmt->bind_param("i", $user['user_id']);
                            $reset_stmt->execute();
                            $reset_stmt->close();
                        }
                        
                        // Regenerate session ID to prevent session fixation
                        session_regenerate_id(true);
                        
                        // Set session variables
                        $_SESSION['user_logged_in'] = true;
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['full_name'] = $user['full_name'];
                        $_SESSION['account_type'] = $user['account_type'];
                        $_SESSION['address'] = $user['address'];
                        $_SESSION['date_of_birth'] = $user['date_of_birth'];
                        $_SESSION['place_of_birth'] = $user['place_of_birth'];
                        $_SESSION['sex'] = $user['sex'];
                        $_SESSION['civil_status'] = $user['civil_status'];
                        $_SESSION['occupation'] = $user['occupation'];
                        $_SESSION['citizenship'] = $user['citizenship'];
                        $_SESSION['relation_to_household'] = $user['relation_to_household'];
                        $_SESSION['contact_number'] = $user['contact_number'] ?? '';
                        $_SESSION['email'] = $user['email'] ?? '';
                        $_SESSION['profile_id'] = $user['profile_id'];
                        $_SESSION['first_name'] = $user['first_name'] ?? '';
                        $_SESSION['login_time'] = time();
                        $_SESSION['login_ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                        
                        // Update last login timestamp
                        $update_stmt = $conn->prepare("UPDATE usercreds SET last_login = CURRENT_TIMESTAMP WHERE user_id = ?");
                        $update_stmt->bind_param("i", $user['user_id']);
                        $update_stmt->execute();
                        $update_stmt->close();
                        
                        // Log successful login
                        error_log("Successful login: User ID " . $user['user_id'] . " - " . $user['username']);
                        
                        // Redirect to dashboard
                        header("Location: ../user-dashboard/user-dashboard.php");
                        exit();
                    } else {
                        $error_message = "Access denied. Please use the admin login portal.";
                        error_log("Admin account attempted to login via user portal: $username");
                    }
                } else {
                    // Wrong password: increment login attempts
                    $current_attempts = isset($user['login_attempts']) ? intval($user['login_attempts']) : 0;
                    $attempts = $current_attempts + 1;
                    $is_locked = ($attempts >= 3) ? 1 : 0;
                    
                    // Update login attempts and lock status
                    if ($has_lockout_columns) {
                        $update_stmt = $conn->prepare("UPDATE usercreds SET login_attempts = ?, is_locked = ? WHERE user_id = ?");
                        $update_stmt->bind_param("iii", $attempts, $is_locked, $user['user_id']);
                        $update_stmt->execute();
                        $update_stmt->close();
                    }
                    
                    if ($is_locked) {
                        $error_message = "You have entered the wrong credentials 3 times. Your account is now locked. Please visit the barangay hall to unlock it.";
                        error_log("Account locked after 3 failed attempts: User ID " . $user['user_id'] . " - $username");
                    } else {
                        $remaining = 3 - $attempts;
                        $error_message = "Invalid username or password. You have $remaining attempt" . ($remaining != 1 ? 's' : '') . " remaining out of 3.";
                        error_log("Invalid password attempt for user: $username (Attempt $attempts/3)");
                    }
                }
            }
        } else {
            // Username does not exist - show generic error (don't reveal if username exists)
            $error_message = "Username or password incorrect.";
            error_log("User not found or inactive: $username");
        }
        
        if ($stmt) {
            $stmt->close();
        }
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
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap" rel="stylesheet" />
    <title>User Sign In - Barangay 498</title>
    <style>
        .error-message {
            background-color: #fee;
            color: #c33;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #fcc;
            width: 90%;
            animation: shake 0.3s;
        }
        .success-message {
            background-color: #d1e7dd;
            color: #0f5132;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #a3cfbb;
            width: 90%;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
    </style>
</head>
<body>
    <div class="choosing-page">
        <div class="left-landingpage">
            <img src="../images/barangay-logo.png" class="barangay-logo" alt="barangay-logo" />
            <h1>Barangay Management System</h1>
            <p class="sub-content">
                This is the official platform of Barangay 498, Zone 49, District IV,
                Manila, designed to streamline operations and enhance public service
                delivery. It provides a centralized system for requesting documents,
                managing records, announcements, and community information. Through
                this platform, the barangay upholds transparency, accountability, and
                efficient communication with its constituents.
            </p>
            <p class="footer">Where community service meets innovation.</p>
        </div>

        <div class="right-landingpage">
            <a href="../landingpage/index.php" class="blueback-btn">
                <img src="../images/blueback-logo.png" alt="back-logo" />
            </a>

            <div class="form-container" id="loginForm">
                <h1 class="welcome">Welcome!</h1>
                <p class="subheader">Get instant access to everything you need right here.</p>
                
                <?php if (!empty($success_message)): ?>
                    <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <h1 class="form-header">Username</h1>
                    <input type="text" name="username" placeholder="Enter your username" required autofocus />
                    
                    <h1 class="form-header">Password</h1>
                    <div class="password-wrapper">
                        <input 
                            type="password" 
                            name="password" 
                            id="passwordInput" 
                            placeholder="Enter your password" 
                            required 
                        />
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
                    
                    <button type="submit" name="login" class="sign-in">Sign In</button>
                </form>
            
                <p class="forgot-password" id="forgotPasswordBtn">
                    Forgot Password?
                    <span style="color: red; font-weight: 600; cursor: pointer">Learn More.</span>
                </p>
            </div>
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

    <!-- forgot pass modal -->
    <div id="forgotModal">
      <div class="modal-content">
        <h2>Password Assistance</h2>
        <p>
          To recover your password, please visit the Barangay Hall in person and
          bring a valid ID for verification.
        </p>

        <button id="closeForgotModal">Okay, I Understand</button>
      </div>
    </div>

    <script src="../user-login/user-login.js"></script>
    <script src="../preloader/preloader.js"></script>
    <script>
        // Prevent back button to cached page
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
</body>
</html>