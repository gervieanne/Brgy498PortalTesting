<?php
ini_set('session.gc_maxlifetime', 21600);
ini_set('session.cookie_lifetime', 21600);

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../user-login/user-login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "498portal";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Get current user's ID from session
$user_id = $_SESSION['user_id'] ?? 0;

// Fetch user's personalized data from both tables with proper priority
$stmt = $conn->prepare("
    SELECT 
        uc.user_id, uc.username, uc.full_name, uc.address, 
        uc.date_of_birth, uc.place_of_birth, uc.sex, uc.civil_status,
        uc.occupation, uc.citizenship, uc.relation_to_household,
        uc.contact_number as creds_contact, 
        uc.email as creds_email, 
        uc.status, uc.created_at,
        up.first_name, up.profile_id, up.profile_photo,
        up.contact_number as profile_contact, 
        up.email as profile_email
    FROM usercreds uc
    LEFT JOIN userprofile498 up ON uc.user_id = up.user_id
    WHERE uc.user_id = ? AND uc.status = 'active'
    LIMIT 1
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    // Security: If user data not found, logout
    session_destroy();
    header("Location: ../user-login/user-login.php");
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Extract user data with proper priority (profile data first, then creds data)
$username = $user['username'];
$full_name = $user['full_name'];
$address = $user['address'] ?? 'Not provided';
$date_of_birth = $user['date_of_birth'] ?? 'N/A';
$place_of_birth = $user['place_of_birth'] ?? 'N/A';
$sex = $user['sex'] ?? 'N/A';
$civil_status = $user['civil_status'] ?? 'N/A';
$occupation = $user['occupation'] ?? 'N/A';
$citizenship = $user['citizenship'] ?? 'N/A';
$relation = $user['relation_to_household'] ?? 'N/A';

// Priority: userprofile498 values first, fallback to usercreds
$contact = !empty($user['profile_contact']) ? $user['profile_contact'] : (!empty($user['creds_contact']) ? $user['creds_contact'] : 'Not provided');
$email = !empty($user['profile_email']) ? $user['profile_email'] : (!empty($user['creds_email']) ? $user['creds_email'] : 'Not provided');

$created_at = $user['created_at'] ?? date('Y-m-d');
$profile_photo = $user['profile_photo'] ?? '';

// Calculate age
$age = 'N/A';
if ($date_of_birth !== 'N/A' && $date_of_birth) {
    try {
        $dob = new DateTime($date_of_birth);
        $now = new DateTime();
        $age = $now->diff($dob)->y;
    } catch (Exception $e) {
        $age = 'N/A';
    }
}

// Format date of birth
$formatted_dob = 'N/A';
if ($date_of_birth !== 'N/A' && $date_of_birth) {
    $formatted_dob = date('F d, Y', strtotime($date_of_birth));
}

// Format account creation date
$formatted_created = date('F d, Y', strtotime($created_at));

// Format contact number properly
$contact_raw = !empty($user['profile_contact']) ? $user['profile_contact'] : (!empty($user['creds_contact']) ? $user['creds_contact'] : null);
$contact = 'Not provided';
if (!empty($contact_raw) && is_string($contact_raw)) {
    $contact = $contact_raw;
    // Ensure it has leading 0 if it's 10 digits
    if (strlen($contact) === 10 && substr($contact, 0, 1) !== '0') {
        $contact = '0' . $contact;
    }
}

// Generate resident ID
$resident_id = 'BRG-498-' . str_pad($user_id, 5, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>My Profile | Barangay 498 Management System</title>
    <link rel="stylesheet" href="../user-profile/user-profile.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="stylesheet" href="../user-chatbot/chatbot.css" />
    <link rel="stylesheet" href="../logout-modal.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
</head>
<body>
    <div class="app">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-top">
                <div class="brand">
                    <div class="logo-container">
                        <img src="../images/barangay-logo.png" alt="Barangay Logo" class="barangay-logo" />
                    </div>
                    <h2>Barangay Management System</h2>
                </div>
            </div>

            <nav class="nav">
                <ul>
                    <li><a href="../user-dashboard/user-dashboard.php">Dashboard</a></li>
                    <li class="active"><a href="../user-profile/user-profile.php">Profile</a></li>
                    <li><a href="../user-request/user_request.php">Document Requests</a></li>
                    <li><a href="../user-calendar/user-calendar.php">Calendar</a></li>
                    <li><a href="../user-officials/user-officials.php">Officials</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="main">
            
            <!-- Topbar -->
            <header class="topbar">
                            <button class="burger-menu" id="burgerMenu">☰</button>

                <div class="topbar-right">
                    <div id="clock" class="clock">00:00:00 AM</div>
                    <a href="#" id="logoutBtn">
                        <img src="../images/logoutbtn.png" alt="logout" class="logout-logo" id="logoutBtn"/>
                    </a>
                </div>
            </header>

            <!-- Page Content -->
            <section class="content">
                <div class="page-title">My Profile</div>

                <!-- Alert Messages -->
                <div id="alertContainer"></div>

                <!-- Profile Header -->
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php if (!empty($profile_photo) && file_exists("../uploads/profiles/" . $profile_photo)): ?>
                            <img src="../uploads/profiles/<?php echo htmlspecialchars($profile_photo); ?>" alt="Profile Photo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        <?php else: ?>
                            <?php echo strtoupper(substr($full_name, 0, 1)); ?>
                        <?php endif; ?>
                    </div>
                    <div class="profile-info">
                        <h1 class="profile-name"><?php echo htmlspecialchars($full_name); ?></h1>
                        <div class="profile-meta">
                            <div class="meta-item">
                                <span class="meta-label">Resident ID:</span>
                                <span class="meta-value"><?php echo htmlspecialchars($resident_id); ?></span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Status:</span>
                                <span class="meta-value status-active">Active Resident</span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Username:</span>
                                <span class="meta-value"><?php echo htmlspecialchars($username); ?></span>
                            </div>
                            <div class="meta-item">
                                <span class="meta-label">Account Created:</span>
                                <span class="meta-value"><?php echo htmlspecialchars($formatted_created); ?></span>
                            </div>
                        </div>
                    </div>
                    <button class="edit-profile-btn" id="editProfilePicture">Edit Profile Picture</button>
                </div>

                <!-- Content Cards -->
                <div class="content-wrapper">
                    <!-- Personal & Household Information -->
                    <h2 class="section-title">Personal & Household Information</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Full Name</div>
                            <div class="info-value"><?php echo htmlspecialchars($full_name); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Age</div>
                            <div class="info-value"><?php echo htmlspecialchars($age); ?> years old</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Gender</div>
                            <div class="info-value"><?php echo $sex === 'M' ? 'Male' : ($sex === 'F' ? 'Female' : 'N/A'); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Date of Birth</div>
                            <div class="info-value"><?php echo htmlspecialchars($formatted_dob); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Place of Birth</div>
                            <div class="info-value"><?php echo htmlspecialchars($place_of_birth); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Civil Status</div>
                            <div class="info-value"><?php echo htmlspecialchars(ucfirst(strtolower($civil_status))); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Occupation</div>
                            <div class="info-value"><?php echo htmlspecialchars($occupation); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Citizenship</div>
                            <div class="info-value"><?php echo htmlspecialchars($citizenship); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Relation to Household</div>
                            <div class="info-value"><?php echo htmlspecialchars($relation); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Residential Address</div>
                            <div class="info-value"><?php echo htmlspecialchars($address); ?></div>
                        </div>
                    </div>

                    <!-- Contact & Security Row -->
                    <div class="row-section">
                        <!-- Contact Information -->
                        <div class="card-section">
                            <h3 class="card-title">Contact Information</h3>
                            <div class="contact-item editable-field">
                                <div class="info-label">Contact Number <span class="edit-icon" onclick="editField('contact')" title="Edit">✏</span></div>
                                <div class="info-value" id="contact-display"><?php echo htmlspecialchars($contact); ?></div>
                                <div id="contact-edit" style="display: none;">
                                    <input 
                                        type="text" 
                                        class="edit-input" 
                                        id="contact-input" 
                                        value="<?php echo htmlspecialchars($contact !== 'Not provided' ? $contact : ''); ?>" 
                                        placeholder="09XXXXXXXXX (11 digits)"
                                        maxlength="11"
                                        pattern="09[0-9]{9}"
                                    >
                                    <small style="color: #666; font-size: 12px; display: block; margin-top: 5px;">
                                        Format: 09XXXXXXXXX (11 digits)
                                    </small>
                                    <div class="save-cancel-btns">
                                        <button class="save-btn" onclick="saveContactInfo()">Save</button>
                                        <button class="cancel-btn" onclick="cancelEdit('contact')">Cancel</button>
                                    </div>
                                </div>
                            </div>
                            <div class="contact-item editable-field">
                                <div class="info-label">Email Address <span class="edit-icon" onclick="editField('email')" title="Edit">✏</span></div>
                                <div class="info-value" id="email-display"><?php echo htmlspecialchars($email); ?></div>
                                <div id="email-edit" style="display: none;">
                                    <input 
                                        type="email" 
                                        class="edit-input" 
                                        id="email-input" 
                                        value="<?php echo htmlspecialchars($email !== 'Not provided' ? $email : ''); ?>" 
                                        placeholder="your.email@example.com"
                                    >
                                    <div class="save-cancel-btns">
                                        <button class="save-btn" onclick="saveContactInfo()">Save</button>
                                        <button class="cancel-btn" onclick="cancelEdit('email')">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Account Security -->
                        <div class="card-section">
                            <h3 class="card-title">Account Security</h3>
                            <p class="security-note">Protect your account by using a strong password. Change your password regularly to maintain security.</p>
                            <button class="change-password-btn" id="changePasswordBtn">Change Password</button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Password Change Modal -->
    <div id="passwordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Change Password</h2>
                <span class="close" id="closePasswordModal">&times;</span>
            </div>

            <form id="passwordChangeForm">
                <div class="form-group">
                    <label for="currentPassword">Current Password</label>
                    <input type="password" id="currentPassword" name="currentPassword" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('currentPassword')">
                        <svg class="eye-icon" viewBox="0 0 24 24">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg class="eye-off-icon" viewBox="0 0 24 24" style="display: none;">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                            <line x1="1" y1="1" x2="23" y2="23"></line>
                        </svg>
                    </button>
                </div>
                <div class="form-group">
                    <label for="newPassword">New Password</label>
                    <input type="password" id="newPassword" name="newPassword" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('newPassword')">
                        <svg class="eye-icon" viewBox="0 0 24 24">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg class="eye-off-icon" viewBox="0 0 24 24" style="display: none;">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                            <line x1="1" y1="1" x2="23" y2="23"></line>
                        </svg>
                    </button>
                    <small class="password-hint">Password must be at least 8 characters long and contain uppercase, lowercase, and numbers.</small>
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirm New Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword')">
                        <svg class="eye-icon" viewBox="0 0 24 24">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg class="eye-off-icon" viewBox="0 0 24 24" style="display: none;">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                            <line x1="1" y1="1" x2="23" y2="23"></line>
                        </svg>
                    </button>
                </div>
                <div class="form-buttons">
                    <button type="submit" class="submit-btn">Update Password</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Photo Upload Modal -->
    <div id="photoModal" class="photo-modal">
        <div class="photo-modal-content">
            <div class="photo-modal-header">
                <h2>Update Profile Picture</h2>
                <span class="close-photo-modal" onclick="closePhotoModal()">&times;</span>
            </div>
            
            <video id="cameraPreview" autoplay playsinline style="display:none;"></video>
            <canvas id="canvas" style="display: none;"></canvas>
            <img id="capturedImage" alt="Captured photo preview" style="display: none;" />

            <div class="camera-controls" id="cameraControls">
                <button class="photo-btn" onclick="capturePhoto()">Capture</button>
            </div>

            <div class="camera-controls" id="uploadControls">
                <button class="photo-btn" id="retakeBtn" onclick="retakePhoto()">Retake</button>
                <button class="photo-btn" onclick="uploadPhoto()">Upload</button>
            </div>

            <div class="spinner" id="uploadSpinner"></div>

            <div id="photoOptions" class="photo-options">
                <button class="photo-btn" onclick="openCamera()">Take Photo with Camera</button>
                <button class="photo-btn" onclick="document.getElementById('fileInput').click()">Upload from Device</button>
                <input type="file" id="fileInput" accept="image/*" style="display: none;" onchange="handleFileSelect(event)">
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="logout-modal">
        <div class="logout-modal-content">
            <div class="logout-modal-icon">⚠️</div>
            <h2>Confirm Logout</h2>
            <p>Are you sure you want to logout?</p>
            <div class="logout-modal-buttons">
                <button class="logout-btn-cancel" id="cancelLogout">No</button>
                <button class="logout-btn-confirm" id="confirmLogout">Yes</button>
            </div>
        </div>
    </div>

    <!-- Chatbot -->
    <div id="chat-toggle">?</div>
    <div id="chatbot">
        <div id="chat-header">Help & FAQs</div>
        <div id="chat-body"></div>
        <div id="quick-questions">
            <div class="quick-scroll">
                <button class="quick-btn">How to request clearance?</button>
                <button class="quick-btn">How to view my profile?</button>
                <button class="quick-btn">How to see pending requests?</button>
                <button class="quick-btn">How to logout?</button>
            </div>
        </div>
        <div id="chat-input-area">
            <input id="chat-input" type="text" placeholder="Type your question..." />
            <button id="send-btn">➤</button>
        </div>
    </div>

    <script src="../user-profile/user-profile.js"></script>
    <script src="../user-chatbot/chatbot.js"></script>
    <script src="../logout-modal.js"></script>
</body>
</html>