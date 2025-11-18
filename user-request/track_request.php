<?php
session_start();
require_once '../user-request/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

// Get current user
$username = $_SESSION['username'];
$user_id = crc32($username);

$search_results = null;
$search_performed = false;
$error_message = '';

// Handle search
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_request'])) {
    $search_id = $conn->real_escape_string($_POST['request_id']);
    $search_name = $conn->real_escape_string($_POST['resident_name']);
    
    if (empty($search_id) && empty($search_name)) {
        $error_message = "Please enter either Request ID or Name to search.";
    } else {
        // Build query - only show current user's requests
        $sql = "SELECT * FROM document_requests WHERE user_id = ?";
        $params = [$user_id];
        $types = "i";
        
        if (!empty($search_id)) {
            $sql .= " AND request_id = ?";
            $params[] = intval($search_id);
            $types .= "i";
        }
        
        if (!empty($search_name)) {
            $sql .= " AND resident_name LIKE ?";
            $params[] = "%$search_name%";
            $types .= "s";
        }
        
        $sql .= " ORDER BY date_requested DESC";
        
        $stmt = $conn->prepare($sql);
        
        // Dynamically bind parameters
        if (count($params) == 1) {
            $stmt->bind_param($types, $params[0]);
        } elseif (count($params) == 2) {
            $stmt->bind_param($types, $params[0], $params[1]);
        } elseif (count($params) == 3) {
            $stmt->bind_param($types, $params[0], $params[1], $params[2]);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $search_results = $result;
        $search_performed = true;
        $stmt->close();
    }
}
?>