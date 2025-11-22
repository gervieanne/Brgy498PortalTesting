<?php
// get_dashboard_stats.php - API endpoint for dashboard statistics
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "498portal";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => 'Database connection failed']));
}

$conn->set_charset("utf8mb4");

// Get residents count by street
// Exclude addresses that contain all three street names together (Sisa Basillio Instruccion)
$streetQuery = "SELECT 
    CASE
        WHEN address LIKE '%IBARRA%' THEN 'Ibarra'
        WHEN address LIKE '%SISA%' AND address LIKE '%BASILIO%' AND address LIKE '%INSTRUCCION%' THEN NULL
        WHEN address LIKE '%SISA%' THEN 'Sisa'
        WHEN address LIKE '%BASILIO%' THEN 'Basilio'
        WHEN address LIKE '%INSTRUCCION%' THEN 'Instruccion'
        WHEN address LIKE '%SIMOUN%' THEN 'Simoun'
        WHEN address LIKE '%MA. CLARA%' OR address LIKE '%MA.CLARA%' THEN 'Ma. Clara'
        WHEN address LIKE '%MACEDA%' THEN 'Maceda'
        ELSE 'Other'
    END as street,
    COUNT(*) as count
FROM userprofile498
WHERE NOT (address LIKE '%SISA%' AND address LIKE '%BASILIO%' AND address LIKE '%INSTRUCCION%')
GROUP BY street
HAVING street IS NOT NULL
ORDER BY count DESC";

$streetResult = $conn->query($streetQuery);
$streetData = [];
while ($row = $streetResult->fetch_assoc()) {
    if ($row['street'] !== 'Other' && $row['street'] !== null) { // Exclude 'Other' category and NULL
        $streetData[$row['street']] = $row['count'];
    }
}

// Get document request statistics
$pendingQuery = "SELECT COUNT(*) as pending FROM document_requests WHERE status = 'pending'";
$pendingResult = $conn->query($pendingQuery);
$pendingCount = $pendingResult->fetch_assoc()['pending'];

$processingQuery = "SELECT COUNT(*) as processing FROM document_requests WHERE status = 'processing'";
$processingResult = $conn->query($processingQuery);
$processingCount = $processingResult->fetch_assoc()['processing'];

$completedQuery = "SELECT COUNT(*) as completed FROM document_requests WHERE status = 'completed'";
$completedResult = $conn->query($completedQuery);
$completedCount = $completedResult->fetch_assoc()['completed'];

$rejectedQuery = "SELECT COUNT(*) as rejected FROM document_requests WHERE status = 'rejected'";
$rejectedResult = $conn->query($rejectedQuery);
$rejectedCount = $rejectedResult->fetch_assoc()['rejected'];

$readyQuery = "SELECT COUNT(*) as ready FROM document_requests WHERE status = 'ready'";
$readyResult = $conn->query($readyQuery);
$readyCount = $readyResult->fetch_assoc()['ready'];

// Get document types breakdown
$docTypesQuery = "SELECT document_type, COUNT(*) as count 
                  FROM document_requests 
                  GROUP BY document_type 
                  ORDER BY count DESC";
$docTypesResult = $conn->query($docTypesQuery);
$docTypes = [];
while ($row = $docTypesResult->fetch_assoc()) {
    $docTypes[$row['document_type']] = $row['count'];
}

$conn->close();

// Return JSON response
echo json_encode([
    'success' => true,
    'demographics' => [
        'streets' => $streetData
    ],
    'documents' => [
        'pending' => $pendingCount,
        'processing' => $processingCount,
        'completed' => $completedCount,
        'rejected' => $rejectedCount,
        'ready' => $readyCount,
        'types' => $docTypes
    ]
]);
?>
