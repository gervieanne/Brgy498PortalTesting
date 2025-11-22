<?php
/**
 * Logout Handler
 * Properly destroys user session and redirects to login
 */

// Start session
session_start();

// Log the logout action for debugging
if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
    error_log("User logout: ID " . $_SESSION['user_id'] . " - " . $_SESSION['username']);
}

// Unset all session variables
$_SESSION = array();

// Delete the session cookie if it exists
if (isset($_COOKIE[session_name()])) {
    setcookie(
        session_name(), 
        '', 
        time() - 21600, 
        '/',
        '',
        isset($_SERVER['HTTPS']),
        true
    );
}

// Destroy the session
session_destroy();

// Regenerate session ID for security
session_regenerate_id(true);

// Clear any cached credentials
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// ✅ FIXED: Redirect to landing page
// Construct the redirect URL properly
$script_name = $_SERVER['SCRIPT_NAME']; // e.g., /498/Brgy498PortalTesting/logout.php
$base_path = dirname($script_name);      // e.g., /498/Brgy498PortalTesting

// Clean up the base path
$base_path = trim($base_path, '/');
if (!empty($base_path)) {
    $base_path = '/' . $base_path;
}

// Build redirect URL
$redirect_url = $base_path . '/landingpage/index.php';

// Ensure proper formatting (no double slashes)
$redirect_url = str_replace('//', '/', $redirect_url);

// Redirect
header("Location: " . $redirect_url);
exit();
?>