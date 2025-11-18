<?php
// update_profile.php - Fixed contact update logic
session_start();
header('Content-Type: application/json');

// Security check
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

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
$user_id = $_SESSION['user_id'];

// Handle different update types
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'update_contact':
        $contact_number = trim($_POST['contact_number'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        // Validate - at least one should be provided
        if (empty($contact_number) && empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Please provide at least one contact method']);
            exit();
        }
        
        // Validate email format if provided
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            exit();
        }
        
        // Convert contact number to string
            $contact_number_str = '';
            if (!empty($contact_number)) {
                // Remove any non-numeric characters
                $contact_number_clean = preg_replace('/[^0-9]/', '', $contact_number);
                
                if (!empty($contact_number_clean)) {
                    // Ensure it starts with 0 and is 11 digits
                    if (strlen($contact_number_clean) === 11 && substr($contact_number_clean, 0, 2) === '09') {
                        $contact_number_str = $contact_number_clean;
                    } else if (strlen($contact_number_clean) === 10 && $contact_number_clean[0] === '9') {
                        $contact_number_str = '0' . $contact_number_clean;
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Invalid contact number format']);
                        exit();
                    }
                }
            }
        
        // Start transaction for atomic updates
        $conn->begin_transaction();
        
        try {
            // Update usercreds table
            $stmt1 = $conn->prepare("UPDATE usercreds SET contact_number = ?, email = ? WHERE user_id = ?");
            $stmt1->bind_param("ssi", $contact_number_str, $email, $user_id);
            
            if (!$stmt1->execute()) {
                throw new Exception("Failed to update usercreds: " . $stmt1->error);
            }
            $stmt1->close();
            
            // Update userprofile498 table
            $stmt2 = $conn->prepare("UPDATE userprofile498 SET contact_number = ?, email = ? WHERE user_id = ?");
            $stmt2->bind_param("ssi", $contact_number_str, $email, $user_id);
            
            if (!$stmt2->execute()) {
                throw new Exception("Failed to update userprofile498: " . $stmt2->error);
            }
            $stmt2->close();
            
            // Commit transaction
            $conn->commit();
            echo json_encode([
                'success' => true, 
                'message' => 'Contact information updated successfully',
                'contact_number' => $contact_number,
                'email' => $email
            ]);
            
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Failed to update contact information: ' . $e->getMessage()]);
        }
        break;
        
    case 'upload_photo':
        if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] !== 0) {
            echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error occurred']);
            exit();
        }
        
        $upload_dir = "../uploads/profiles/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Validate file type
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_photo']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF allowed.']);
            exit();
        }
        
        // Validate file size (max 5MB)
        if ($_FILES['profile_photo']['size'] > 5242880) {
            echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 5MB.']);
            exit();
        }
        
        // Generate unique filename
        $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
        $filepath = $upload_dir . $new_filename;
        
        // Delete old profile photo if exists
        $stmt = $conn->prepare("SELECT profile_photo FROM userprofile498 WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $old_photo = $row['profile_photo'];
            if ($old_photo && file_exists($upload_dir . $old_photo)) {
                unlink($upload_dir . $old_photo);
            }
        }
        $stmt->close();
        
        if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $filepath)) {
            // Save filename to database
            $stmt = $conn->prepare("UPDATE userprofile498 SET profile_photo = ? WHERE user_id = ?");
            $stmt->bind_param("si", $new_filename, $user_id);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Profile photo uploaded successfully',
                    'photo_url' => $new_filename
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to save photo reference in database']);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload photo']);
        }
        break;
        
    case 'remove_photo':
        // Get current photo
        $stmt = $conn->prepare("SELECT profile_photo FROM userprofile498 WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $old_photo = $row['profile_photo'];
            $upload_dir = "../uploads/profiles/";
            
            // Delete physical file
            if ($old_photo && file_exists($upload_dir . $old_photo)) {
                unlink($upload_dir . $old_photo);
            }
            
            // Update database
            $stmt2 = $conn->prepare("UPDATE userprofile498 SET profile_photo = NULL WHERE user_id = ?");
            $stmt2->bind_param("i", $user_id);
            
            if ($stmt2->execute()) {
                echo json_encode(['success' => true, 'message' => 'Profile photo removed successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to remove photo']);
            }
            $stmt2->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'No photo found']);
        }
        $stmt->close();
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();
?>