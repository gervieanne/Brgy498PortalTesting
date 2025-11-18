<?php

$servername = "localhost";
$username = "root"; 
$password = "";
$dbname = "498portal";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

if (isset($_GET['id'])) {
    $request_id = intval($_GET['id']);

    $stmt = $conn->prepare("SELECT * FROM document_requests WHERE request_id = ?");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $request = $result->fetch_assoc();
        $request['date_requested'] = date('Y-m-d', strtotime($request['date_requested']));
        
        $request['has_selfie'] = !empty($request['selfie_image']);
        $request['has_id'] = !empty($request['id_image']);
        echo json_encode(['success' => true, 'request' => $request]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Request not found']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'No request ID provided']);
}

$conn->close();
?>