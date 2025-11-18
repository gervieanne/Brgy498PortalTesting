<?php
/**
 * Debug Tool: Verify User-Request Connection
 * Place this file in your root directory and access it to verify the setup
 */

session_start();
require_once '../user-request/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    die("Please login first to test the connection.");
}

$username = $_SESSION['username'];
$user_id = crc32($username);

echo "<h2>User Connection Verification</h2>";
echo "<hr>";

echo "<h3>Session Information:</h3>";
echo "Username: " . htmlspecialchars($username) . "<br>";
echo "Full Name: " . htmlspecialchars($_SESSION['full_name'] ?? 'Not set') . "<br>";
echo "Generated User ID: " . $user_id . "<br>";
echo "<hr>";

// Check document_requests table structure
echo "<h3>Database Structure Check:</h3>";
$result = $conn->query("DESCRIBE document_requests");
if ($result) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error checking table structure: " . $conn->error;
}
echo "<hr>";

// Check if user_id column exists and is correct type
echo "<h3>User ID Column Check:</h3>";
$check = $conn->query("SHOW COLUMNS FROM document_requests LIKE 'user_id'");
if ($check && $check->num_rows > 0) {
    $col = $check->fetch_assoc();
    echo "✓ user_id column exists<br>";
    echo "Type: " . $col['Type'] . "<br>";
    echo "Null: " . $col['Null'] . "<br>";
    
    if (strpos(strtolower($col['Type']), 'int') !== false) {
        echo "✓ Column type is correct (integer)<br>";
    } else {
        echo "⚠ Warning: Column type should be INT, but is " . $col['Type'] . "<br>";
    }
} else {
    echo "✗ user_id column does NOT exist!<br>";
    echo "<strong>ACTION REQUIRED:</strong> Run this SQL to add the column:<br>";
    echo "<code>ALTER TABLE document_requests ADD COLUMN user_id INT NOT NULL DEFAULT 0 AFTER request_id;</code><br>";
}
echo "<hr>";

// Check existing requests for this user
echo "<h3>Your Document Requests:</h3>";
$stmt = $conn->prepare("SELECT request_id, user_id, resident_name, document_type, status, date_requested FROM document_requests WHERE user_id = ? ORDER BY date_requested DESC LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Request ID</th><th>User ID</th><th>Name</th><th>Document</th><th>Status</th><th>Date</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>REQ-" . str_pad($row['request_id'], 3, '0', STR_PAD_LEFT) . "</td>";
        echo "<td>" . $row['user_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['resident_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['document_type']) . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['date_requested'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No requests found for User ID: $user_id<br>";
    echo "This could mean:<br>";
    echo "1. You haven't submitted any requests yet<br>";
    echo "2. The user_id wasn't saved correctly when you submitted<br>";
}
$stmt->close();
echo "<hr>";

// Check all requests with NULL or 0 user_id
echo "<h3>Orphaned Requests (user_id = 0 or NULL):</h3>";
$orphaned = $conn->query("SELECT COUNT(*) as count FROM document_requests WHERE user_id IS NULL OR user_id = 0");
if ($orphaned) {
    $count = $orphaned->fetch_assoc()['count'];
    echo "Found $count requests without proper user_id<br>";
    if ($count > 0) {
        echo "<strong>These requests won't show up in any user's dashboard!</strong><br>";
    }
}
echo "<hr>";

// Test insert capability
echo "<h3>Database Write Test:</h3>";
echo "Testing if we can write to the database...<br>";
$test_sql = "INSERT INTO document_requests (user_id, resident_name, document_type, purpose, contact_number, status, date_requested) 
             VALUES (?, 'TEST ENTRY', 'Test Document', 'Testing', '09123456789', 'pending', NOW())";
$test_stmt = $conn->prepare($test_sql);
$test_stmt->bind_param("i", $user_id);
if ($test_stmt->execute()) {
    $test_id = $test_stmt->insert_id;
    echo "✓ Test insert successful! Request ID: $test_id<br>";
    
    // Clean up test entry
    $conn->query("DELETE FROM document_requests WHERE request_id = $test_id");
    echo "✓ Test entry cleaned up<br>";
} else {
    echo "✗ Test insert failed: " . $conn->error . "<br>";
}
$test_stmt->close();
echo "<hr>";

echo "<h3>Recommendations:</h3>";
echo "<ol>";
echo "<li>Make sure the user_id column exists in document_requests table (INT type)</li>";
echo "<li>Verify that both user_form.php and user-dashboard.php use: <code>\$user_id = crc32(\$username);</code></li>";
echo "<li>After submitting a request, it should appear in 'Your Document Requests' section above</li>";
echo "<li>Check that the form is using method='POST' and has name='submit_request'</li>";
echo "</ol>";

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; }
table { border-collapse: collapse; margin: 10px 0; }
th { background: #21205d; color: white; padding: 8px; }
td { padding: 8px; }
code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; }
hr { margin: 20px 0; }
</style>