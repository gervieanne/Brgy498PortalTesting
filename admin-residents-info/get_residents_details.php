<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "498portal";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed']);
    exit();
}

if (!isset($_GET['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User ID not provided']);
    exit();
}

$user_id = intval($_GET['user_id']);

// Get comprehensive data from both tables
$sql = "SELECT 
    up.user_id,
    up.first_name,
    up.full_name,
    up.address,
    up.date_of_birth,
    up.place_of_birth,
    up.sex,
    up.civil_status,
    up.occupation,
    up.citizenship,
    up.relation_to_household,
    COALESCE(NULLIF(up.contact_number, 0), uc.contact_number) as contact_number,
    COALESCE(NULLIF(up.email, ''), uc.email) as email,
    uc.username
FROM userprofile498 up
INNER JOIN usercreds uc ON up.user_id = uc.user_id
WHERE up.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $resident = $result->fetch_assoc();
    
    // Format contact number
        if (!empty($resident['contact_number'])) {
            $contact_str = $resident['contact_number'];
            // If 10 digits without leading 0, add it
            if (strlen($contact_str) === 10 && $contact_str[0] !== '0') {
                $resident['contact_number'] = '0' . $contact_str;
            }
        } else {
            $resident['contact_number'] = '';
    }
    
    echo json_encode(['success' => true, 'resident' => $resident]);
} else {
    echo json_encode(['success' => false, 'message' => 'Resident not found']);
}

$stmt->close();
$conn->close();
?>
