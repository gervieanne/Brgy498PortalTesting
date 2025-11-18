<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "498portal";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$conn->set_charset("utf8mb4");

// Get POST data
$currentPassword = $_POST['currentPassword'] ?? '';
$newPassword = $_POST['newPassword'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';
$user_id = $_SESSION['user_id'];

// Validate input
if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

if ($newPassword !== $confirmPassword) {
    echo json_encode(['success' => false, 'message' => 'New passwords do not match']);
    exit();
}

// Password complexity validation
if (strlen($newPassword) < 8) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long']);
    exit();
}

if (!preg_match('/[A-Z]/', $newPassword) || 
    !preg_match('/[a-z]/', $newPassword) || 
    !preg_match('/[0-9]/', $newPassword)) {
    echo json_encode(['success' => false, 'message' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number']);
    exit();
}

// Verify current password
$stmt = $conn->prepare("SELECT password FROM usercreds WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit();
}

$user = $result->fetch_assoc();
if (!password_verify($currentPassword, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
    exit();
}

// Hash new password
$newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

// Update password
$stmt = $conn->prepare("UPDATE usercreds SET password = ? WHERE user_id = ?");
$stmt->bind_param("si", $newPasswordHash, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update password']);
}

$stmt->close();
$conn->close();
?>