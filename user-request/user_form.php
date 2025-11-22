<?php
ini_set('session.gc_maxlifetime', 21600);
ini_set('session.cookie_lifetime', 21600);

session_start();
require_once '../user-request/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

// Get user data from session
$username = $_SESSION['username'];
$full_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : $username;
$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Session error. Please try logging in again.";
    header("Location: ../user-login/user-login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_request'])) {
    // Get form data
    $first_name = $conn->real_escape_string(trim($_POST['first_name']));
    $middle_name = $conn->real_escape_string(trim($_POST['middle_name']));
    $last_name = $conn->real_escape_string(trim($_POST['last_name']));
    $suffix = $conn->real_escape_string(trim($_POST['suffix']));
    $contact_number = $conn->real_escape_string(trim($_POST['contact_number']));
    $date_of_birth = $conn->real_escape_string($_POST['date_of_birth']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $place_of_birth = $conn->real_escape_string(trim($_POST['place_of_birth']));
    
    // Address
    $apartment = $conn->real_escape_string(trim($_POST['apartment']));
    $street = $conn->real_escape_string(trim($_POST['street']));
    $barangay = $conn->real_escape_string(trim($_POST['barangay']));
    $city = $conn->real_escape_string(trim($_POST['city']));
    $province = $conn->real_escape_string(trim($_POST['province']));
    $region = $conn->real_escape_string(trim($_POST['region']));
    $country = $conn->real_escape_string(trim($_POST['country']));
    $zip_code = $conn->real_escape_string(trim($_POST['zip_code']));
    
    // Document details
    $document_type = $conn->real_escape_string($_POST['document_type']);
    $purpose = trim($_POST['purpose']);
    $quantity = intval($_POST['quantity']);

    // Server-side validation
    $errors = [];

    // Names: letters, spaces, hyphen, apostrophe (allow accented chars)
    if (!preg_match("/^[A-Za-zÀ-ž'\-\s]+$/u", $first_name)) {
        $errors[] = 'First name contains invalid characters.';
    }
    if ($middle_name !== '' && !preg_match("/^[A-Za-zÀ-ž'\-\s]*$/u", $middle_name)) {
        $errors[] = 'Middle name contains invalid characters.';
    }
    if (!preg_match("/^[A-Za-zÀ-ž'\-\s]+$/u", $last_name)) {
        $errors[] = 'Last name contains invalid characters.';
    }

    // Contact number: strip non-digits and validate length
    $contact_number = preg_replace('/\D/', '', $contact_number);
    if (!preg_match('/^\d{10,11}$/', $contact_number)) {
        $errors[] = 'Contact number must be 10 to 11 digits.';
    }

    // Zip code (if provided): 4 digits
    $zip_code = preg_replace('/\D/', '', $zip_code);
    if ($zip_code !== '' && !preg_match('/^\d{4}$/', $zip_code)) {
        $errors[] = 'Zip code must be 4 digits.';
    }

    // Quantity: integer >= 1
    if ($quantity < 1) {
        $errors[] = 'Quantity must be at least 1.';
    }

    if (!empty($errors)) {
        $_SESSION['error_message'] = implode(' | ', $errors);
    } else {
        // Build resident name from form data (not from session) - Format: Last Name, First Name Middle Name
        $resident_name = trim("$last_name, $first_name $middle_name" . ($suffix ? ' ' . $suffix : ''));
        $full_address = trim("$apartment $street, $barangay, $city, $province");

        // Handle file uploads
        $upload_dir = '../uploads/document_requests/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $selfie_path = '';
        $id_path = '';

        if (isset($_FILES['selfie_with_id']) && $_FILES['selfie_with_id']['error'] == 0) {
            $selfie_name = time() . '_selfie_' . basename($_FILES['selfie_with_id']['name']);
            $selfie_path = $upload_dir . $selfie_name;
            move_uploaded_file($_FILES['selfie_with_id']['tmp_name'], $selfie_path);
        }

        if (isset($_FILES['id_picture']) && $_FILES['id_picture']['error'] == 0) {
            $id_name = time() . '_id_' . basename($_FILES['id_picture']['name']);
            $id_path = $upload_dir . $id_name;
            move_uploaded_file($_FILES['id_picture']['tmp_name'], $id_path);
        }

        // Calculate expected date: 3 days from now
        $expected_date_dt = new DateTime();
        $expected_date_dt->modify('+3 days');
        $expected_date = $expected_date_dt->format('Y-m-d');

        // Start transaction
        $conn->begin_transaction();

        try {
            // Insert document request with form-provided name
            $sql = "INSERT INTO document_requests (
                user_id,
                resident_name, 
                document_type, 
                purpose, 
                contact_number, 
                status, 
                expected_date,
                additional_notes,
                selfie_image,
                id_image,
                date_requested
            ) VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, NOW())";

            $additional_notes = "Address: $full_address\nDOB: $date_of_birth\nGender: $gender\nPlace of Birth: $place_of_birth\nQuantity: $quantity";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issssssss", 
                $user_id, 
                $resident_name,  // Using form-provided name
                $document_type, 
                $purpose, 
                $contact_number, 
                $expected_date, 
                $additional_notes,
                $selfie_path,
                $id_path
            );
            $stmt->execute();
            $request_id = $stmt->insert_id;
            $stmt->close();

            // Commit the transaction
            $conn->commit();

            // Store success message with request ID
            $_SESSION['success_message'] = "Document request submitted successfully! Your request ID is: REQ-" . str_pad($request_id, 3, '0', STR_PAD_LEFT);
            
            // Redirect to dashboard with success parameter
            header("Location: user_request.php?success=1");
            exit();

        } catch (Exception $e) {
            // Roll back the changes on error
            $conn->rollback();
            $_SESSION['error_message'] = "Error submitting request: " . $e->getMessage() . " Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>User Form - Barangay Document Request</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="../user-request/user-form.css" />
    <link rel="stylesheet" href="../preloader/preloader.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>
    <!-- Preloader -->
    <div class="preloader" id="preloader">
        <div class="spinner"></div>
        <p>Loading...</p>
    </div>
    
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <img src="../images/barangay-logo.png" alt="" class="barangay-logo" />
            <h1>Barangay Management System</h1>
            <nav class="sidebar-nav">
                <ul class="nav-items">
                    <li>
    <a href="../user-request/user_request.php" class="request-page" style="text-decoration: none; color: #fff; display: flex; align-items: center; gap: 15px;">
      <i class="fas fa-angle-left fa-1x"></i> Request Page
    </a>
  </li>
                    <li class="nav-item active" data-step="0">
                        <a href="#step1">
                            <span class="nav-item-icon"><i class="fa-solid fa-person fa-2x"></i></span>
                            <span class="nav-item-text">Step 1: Information</span>
                        </a>
                    </li>
                    <li class="nav-item" data-step="1">
                        <a href="#step2">
                            <span class="nav-item-icon"><i class="fa-solid fa-file fa-2x"></i></span>
                            <span class="nav-item-text">Step 2: Document Request and Requirements</span>
                        </a>
                    </li>
                    <li class="nav-item" data-step="2">
                        <a href="#step3">
                            <span class="nav-item-icon"><i class="fa-solid fa-shield-halved fa-2x"></i></span>
                            <span class="nav-item-text">Step 3: Data Privacy Policy</span>
                        </a>
                    </li>
                    <li class="nav-item" data-step="3">
                        <a href="#step4">
                            <span class="nav-item-icon"><i class="fa-solid fa-folder-open fa-2x"></i></span>
                            <span class="nav-item-text">Step 4: Preview and Submit Request</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-container">
            <button class="burger-menu" id="burgerMenu">☰</button>
            <div class="form-wrapper">
                <h1>REQUEST DOCUMENT FORM</h1>
                <div id="validationAlert" class="validation-alert"></div>

                <!-- Display Error Messages -->
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div style="background: #f8d7da; color: #842029; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #842029;">
                        <strong>Error!</strong> <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                    </div>
                <?php endif; ?>

                <form id="documentRequestForm" method="POST" enctype="multipart/form-data">
                    <!-- Step 1: Personal Information -->
                    <div class="section active" data-step="0">
                        <h2 class="section-title">PERSONAL INFORMATION</h2>
                        <div class="form-row side-by-side">
                            <div class="form-group">
                                <label>Last Name <span class="required">*</span></label>
                                <input type="text" name="last_name" id="last_name" pattern="[A-Za-z\s\-'.]+" onkeypress="return /[A-Za-z\s\-'.]/.test(event.key)" required />
                            </div>
                            <div class="form-group">
                                <label>First Name <span class="required">*</span></label>
                                <input type="text" name="first_name" id="first_name" pattern="[A-Za-z\s\-'.]+" onkeypress="return /[A-Za-z\s\-'.]/.test(event.key)" required />
                            </div>
                            <div class="form-group">
                                <label>Middle Name <span class="required">*</span></label>
                                <input type="text" name="middle_name" id="middle_name" pattern="[A-Za-z\s\-'.]+" onkeypress="return /[A-Za-z\s\-'.]/.test(event.key)" required />
                            </div>
                            <div class="form-group">
                                <label>Suffix</label>
                                <select id="suffix" name="suffix">
                                    <option value="">N/A</option>
                                    <option value="Jr.">Jr.</option>
                                    <option value="Sr.">Sr.</option>
                                    <option value="I">I</option>
                                    <option value="II">II</option>
                                    <option value="III">III</option>
                                    <option value="IV">IV</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row side-by-side">
                            <div class="form-group">
                                <label>Contact Number <span class="required">*</span></label>
                                <input type="text" id="contact_number" name="contact_number" maxlength="11" onkeypress="return /[0-9]/.test(event.key)" pattern="[0-9]+" inputmode="numeric" required />
                            </div>
                            <div class="form-group">
                                <label>Date of Birth <span class="required">*</span></label>
                                <input type="date" name="date_of_birth" id="date_of_birth" required />
                            </div>
                            <div class="form-group">
                                <label for="gender">Gender at Birth <span class="required">*</span></label>
                                <select id="gender" name="gender" required>
                                    <option value="" disabled selected>Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Place of Birth <span class="required">*</span></label>
                                <input type="text" id="place_of_birth" name="place_of_birth" pattern="[A-Za-z\s\-'.,]+" onkeypress="return /[A-Za-z\s\-'.,]/.test(event.key)" required />
                            </div>
                        </div>

                        <h2 class="section-title">PERMANENT ADDRESS</h2>
                        <div class="form-row side-by-side">
                            <div class="form-group">
                                <label>Apartment/Unit/Building/Floor/etc.<span class="required">*</span></label>
                                <textarea name="apartment" id="apartment" required></textarea>
                            </div>
                            <div class="form-group">
                                <label>Street <span class="required">*</span></label>
                                <input type="text" name="street" id="street" required />
                            </div>
                            <div class="form-group">
                                <label>Barangay</label>
                                <input type="text" name="barangay" id="barangay" value="BARANGAY 498" required />
                            </div>
                        </div>

                        <div class="form-row side-by-side">
                            <div class="form-group">
                                <label>City</label>
                                <input type="text" name="city" id="city" value="SAMPALOC" required />
                            </div>
                            <div class="form-group">
                                <label>Province</label>
                                <input type="text" name="province" id="province" value="MANILA" required />
                            </div>
                            <div class="form-group">
                                <label>Region</label>
                                <input type="text" name="region" id="region" value="NCR - NATIONAL CAPITAL REGION" required />
                            </div>
                        </div>

                        <div class="form-row side-by-side">
                            <div class="form-group">
                                <label>Country</label>
                                <input type="text" name="country" id="country" value="PHILIPPINES" required />
                            </div>
                            <div class="form-group">
                                <label>Zip Code</label>
                                <input type="text" id="zip_code" name="zip_code" maxlength="4" value="1008" required />
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Document Request -->
                    <div class="section" data-step="1">
                        <h2 class="section-title">DOCUMENT TO BE REQUESTED</h2>
                        <div class="form-row side-by-side">
                            <div class="form-group">
                                <label for ="document_type">Document Type <span class="required">*</span></label>
                                <select name="document_type" id="document_type" required>
                                    <option value="" disabled selected>Select Document Type</option>
                                    <option value="Barangay Certificate">Barangay Certificate</option>
                                    <option value="Certificate of Indigency">Certificate of Indigency</option>
                                    <option value="Proof of Residency">Proof of Residency</option>
                                    <option value="Barangay Business Permit">Barangay Business Permit</option>
                                    <option value="Barangay ID">Barangay ID</option>
                                    <option value="First Time Job Seeker Certificate">First Time Job Seeker Certificate</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Purpose <span class="required">*</span></label>
                                <textarea name="purpose" id="purpose" placeholder="Input your purpose here." required ></textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Quantity <span class="required">*</span></label>
                            <input type="number" name="quantity" id="quantity" min="1" value="1" required />
                        </div>

                        <h2 class="section-title">DOCUMENT REQUIREMENTS</h2>
                        <div class="form-row side-by-side">
                            <div class="form-group">
                                <label>Photo/Selfie with any Valid ID <span class="required">*</span></label>
                                <input type="file" name="selfie_with_id" id="selfie_with_id" accept="image/*" required />
                                <span class="file-upload-hint">Max file size: 5MB (JPG, PNG)</span>
                            </div>
                            <div class="form-group">
                                <label>Picture of ID <span class="required">*</span></label>
                                <input type="file" name="id_picture" id="id_picture" accept="image/*" required />
                                <span class="file-upload-hint">Max file size: 5MB (JPG, PNG)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Privacy Policy -->
                    <div class="section" data-step="2">
                        <h2 class="section-title">DATA PRIVACY POLICY</h2>
                        <div class="checkbox-group">
                            <input type="checkbox" name="privacy_agreement" id="privacy_agreement" required />
                            <label for="privacy_agreement" class="checkboxLabel">
                                I have read and agree to the <a href="#" id="openPrivacyModal" class="policy-link">Data Privacy Policy</a> agreement. I understand that the data I will be sending will be forwarded to Brgy. 498 Zone 49 Officials and will be used only for the purpose of what I mentioned above. <span class="required">*</span>
                            </label>
                        </div>

                        <div class="checkbox-group">
                            <input type="checkbox" name="claiming" id="claiming" required />
                            <label for="claiming" class="checkboxLabel">
                                I have read the procedures and guidelines in claiming my documents. <span class="required">*</span>
                            </label>
                        </div>
                    </div>

                    <!-- Step 4: Preview -->
                    <div class="section" data-step="3">
                        <h2 class="section-title">REVIEW YOUR INFORMATION</h2>
                        <p style="font-size: 16px; color: #666; line-height: 1.8; margin: 20px 0;">
                            You have completed all required steps. Click the <strong>"Done"</strong> button below to review all your information before final submission.
                        </p>
                        <div style="background: #f0f7ff; padding: 20px; border-radius: 8px; border-left: 4px solid #21205d;">
                            <p style="margin: 0; color: #333">
                                <i class="fa-solid fa-info-circle" style="color: #21205d; margin-right: 10px"></i>
                                Please ensure all information is accurate before submitting.
                            </p>
                        </div>
                    </div>

                    <div class="buttons">
                        <button type="button" id="prevBtn">Previous</button>
                        <button type="button" id="nextBtn">Next</button>
                        <input type="hidden" name="submit_request" value="1" />
                    </div>
                </form>
            </div>
        </main>
    </div>

    <!-- Privacy Modal -->
    <div class="modal" id="privacyModal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title">DATA PRIVACY POLICY</h3>
                <button type="button" class="btn-close" id="closePrivacyModalBtn" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="data-act-content">
                    <h4>Barangay 498, Zone 49, District IV, Manila</h4>
                    <p>In compliance with <strong>Republic Act No. 10173</strong>, also known as the <strong>Data Privacy Act of 2012</strong>, Barangay 498, Zone 49, District IV, Manila is committed to safeguarding the privacy and security of all personal information collected through the Barangay Management System and other official transactions. This policy outlines how personal data is collected, processed, stored, and protected in accordance with the law.</p>
                    <p>The barangay collects only the information necessary for legitimate purposes, including but not limited to names, contact details, addresses, and valid identification documents. These details are used solely to process official barangay transactions such as certifications, clearances, and other related services. All data collected shall be handled with transparency, legitimate purpose, and proportionality as required under the Data Privacy Act.</p>
                    <p>Personal data shall be stored securely and accessed only by authorized barangay personnel. The barangay employs organizational, physical, and technical safeguards to prevent unauthorized access, disclosure, alteration, or destruction of information. Collected data shall not be shared or disclosed to third parties without the consent of the individual, unless required by law or legal process.</p>
                    <p>Information will be retained only for as long as necessary to fulfill the purpose for which it was collected or as required by existing regulations. Once the purpose has been achieved, the barangay shall ensure that data is securely deleted or disposed of to prevent unauthorized use.</p>
                    <p>For inquiries or requests regarding your personal data, you may contact the Barangay Office during official working hours.</p>
                    <p>By submitting your information through this platform, you acknowledge that you have read, understood, and agreed to this Data Privacy Policy, and you consent to the collection and processing of your personal data in accordance with the <strong>Data Privacy Act of 2012</strong> and its <strong>Implementing Rules and Regulations (IRR).</strong></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div id="previewModal">
        <div id="modalContent"></div>
    </div>

    <script src="../user-request/user_form.js"></script>
    <script src="../preloader/preloader.js"></script>
</body>
</html>