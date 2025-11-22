<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once 'db_connection.php';

$user_id = intval($_SESSION['user_id']);
$activities = [];

// Fetch recent document requests with status changes
$sql = "SELECT request_id, document_type, status, date_requested, date_updated, 
        CASE 
            WHEN date_updated IS NOT NULL AND date_updated != '0000-00-00 00:00:00' THEN date_updated
            ELSE date_requested
        END as activity_date
        FROM document_requests 
        WHERE user_id = ? 
        ORDER BY activity_date DESC 
        LIMIT 10";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $activity_date = new DateTime($row['activity_date']);
        $now = new DateTime();
        $diff = $now->diff($activity_date);
        
        // Calculate time ago
        $time_ago = '';
        if ($diff->days > 0) {
            $time_ago = $diff->days . ($diff->days == 1 ? ' day' : ' days') . ' ago';
        } elseif ($diff->h > 0) {
            $time_ago = $diff->h . ($diff->h == 1 ? ' hour' : ' hours') . ' ago';
        } elseif ($diff->i > 0) {
            $time_ago = $diff->i . ($diff->i == 1 ? ' minute' : ' minutes') . ' ago';
        } else {
            $time_ago = 'Just now';
        }
        
        // Create activity message based on status
        $status = strtolower($row['status']);
        $doc_type = htmlspecialchars($row['document_type']);
        
        $message = '';
        switch ($status) {
            case 'pending':
            case 'processing':
                $message = "Document request submitted: {$doc_type}";
                break;
            case 'ready':
                $message = "Document ready for pickup: {$doc_type}";
                break;
            case 'completed':
                $message = "Document request completed: {$doc_type}";
                break;
            case 'rejected':
                $message = "Document request rejected: {$doc_type}";
                break;
            default:
                $message = "Document request updated: {$doc_type}";
        }
        
        $activities[] = [
            'request_id' => $row['request_id'],
            'status' => $status,
            'message' => $message,
            'time_ago' => $time_ago,
            'document_type' => $doc_type
        ];
    }
    
    $stmt->close();
}

$conn->close();

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'activities' => $activities
]);
?>

