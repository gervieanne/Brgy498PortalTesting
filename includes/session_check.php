<?php
/**
 * Centralized Session Check for User Pages
 * Include this file at the top of every user page
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Prevent caching of pages
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

/**
 * Check if user is logged in
 */
function isUserLoggedIn() {
    return isset($_SESSION['user_logged_in']) 
           && $_SESSION['user_logged_in'] === true 
           && isset($_SESSION['user_id'])
           && isset($_SESSION['username']);
}

/**
 * Check if user is admin (should not access user pages)
 */
function isAdmin() {
    return isset($_SESSION['account_type']) 
           && ($_SESSION['account_type'] === 'admin' 
               || $_SESSION['account_type'] === 'administrator');
}

/**
 * Redirect to login if not authenticated
 */
function requireUserAuth() {
    if (!isUserLoggedIn()) {
        // Clear any residual session data
        session_unset();
        session_destroy();
        
        // Redirect to login
        header("Location: ../BRGY498PORTAL/user-login/user-login.php");
        exit();
    }
    
    // Security: Block admin accounts from user pages
    if (isAdmin()) {
        session_unset();
        session_destroy();
        header("Location: ../BRGY498PORTAL/user-login/user-login.php");
        exit();
    }
}

/**
 * Get current user data safely
 */
function getCurrentUser() {
    if (!isUserLoggedIn()) {
        return null;
    }
    
    return [
        'user_id' => $_SESSION['user_id'] ?? 0,
        'username' => $_SESSION['username'] ?? '',
        'full_name' => $_SESSION['full_name'] ?? 'Resident',
        'email' => $_SESSION['email'] ?? '',
        'contact_number' => $_SESSION['contact_number'] ?? '',
        'address' => $_SESSION['address'] ?? '',
        'account_type' => $_SESSION['account_type'] ?? 'resident'
    ];
}

// Automatically check authentication
requireUserAuth();
?>