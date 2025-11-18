<?php
ini_set('session.gc_maxlifetime', 21600);
ini_set('session.cookie_lifetime', 21600);

session_start();
// Include centralized session check
require_once '../includes/session_check.php';

// Get user data safely
$user = getCurrentUser();
$full_name = $user['full_name'];
$username = $user['username'];
$user_id = $user['user_id'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Document Requests | Barangay 498 Management System</title>
    <link rel="stylesheet" href="../user-request/user-request.css" />
    <link rel="stylesheet" href="../user-chatbot/chatbot.css" />
    <link rel="stylesheet" href="../preloader/preloader.css" />
    <link rel="stylesheet" href="../logout-modal.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-top">
                <div class="brand">
                    <div class="logo-container">
                        <img src="../images/barangay-logo.png" alt="Barangay Logo" class="barangay-logo" />
                    </div>
                    <h1>Barangay Management System</h1>
                </div>
            </div>

            <nav class="menu">
                <ul class="menu-items">
                    <li><a href="../user-dashboard/user-dashboard.php">Dashboard</a></li>
                    <li><a href="../user-profile/user-profile.php">Profile</a></li>
                    <li><a href="../user-request/user_request.php" class="active">Document Requests</a></li>
                    <li><a href="../user-announcement/user-announcement.php">Announcement</a></li>
                    <li><a href="../user-calendar/user-calendar.php">Calendar</a></li>
                    <li><a href="../user-officials/user-officials.php">Officials</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Preloader -->
        <div class="preloader" id="preloader">
            <div class="spinner"></div>
            <p>Loading...</p>
        </div>

        <!-- Main Content -->
        <div class="right-content">
            <button class="burger-menu" id="burgerMenu">☰</button>

            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <h1>DOCUMENT REQUEST SERVICE</h1>
                    <p>Request and manage your barangay certificates</p>
                </div>
                <div class="header-right">
                    <div id="clock">12:00:00 AM</div>
                    <a href="#" id="logoutBtn">
                        <img src="../images/logout.png" alt="logout" class="logout-logo" />
                    </a>
                </div>
            </header>

            <!-- Main Container -->
            <div class="main-container">
                <!-- Success/Error Messages -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="success-message">
                        <strong>Success!</strong> <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="error-message">
                        <strong>Error!</strong> <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                    </div>
                <?php endif; ?>

                <!-- Content Container -->
                <div class="content-container">
                    <div class="content-header">
                        <h2>Document Request Information</h2>
                    </div>

                    <!-- Content Wrapper -->
                    <div class="content-wrapper">
                        <!-- Left Column - Information Sections -->
                        <div class="left-column">
                            <!-- Certificates Available -->
                            <div class="info-section">
                                <div class="section-header" onclick="toggleSection('certificates')">
                                    <span class="section-title">Certificates Available</span>
                                    <span class="toggle-icon" id="certificates-icon">▼</span>
                                </div>
                                <div class="section-content" id="certificates-content">
                                    <ul>
                                        <li>Barangay Certificate</li>
                                        <li>Certificate of Indigency</li>
                                        <li>Proof of Residency</li>
                                        <li>Barangay Business Permit</li>
                                        <li>Barangay ID</li>
                                        <li>First Time Job Seeker Certificate</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Procedure -->
                            <div class="info-section">
                                <div class="section-header" onclick="toggleSection('procedure')">
                                    <span class="section-title">Request Procedure</span>
                                    <span class="toggle-icon" id="procedure-icon">▼</span>
                                </div>
                                <div class="section-content" id="procedure-content">
                                    <ol>
                                        <li>Fill out the request form with accurate information</li>
                                        <li>Submit the form and wait for confirmation</li>
                                        <li>Pay the required fees at the barangay office</li>
                                        <li>Wait for processing (usually takes 3-5 business days)</li>
                                        <li>Pick up your document at the barangay office</li>
                                    </ol>
                                </div>
                            </div>

                            <!-- Guidelines -->
                            <div class="info-section">
                                <div class="section-header" onclick="toggleSection('guidelines')">
                                    <span class="section-title">Claiming Guidelines</span>
                                    <span class="toggle-icon" id="guidelines-icon">▼</span>
                                </div>
                                <div class="section-content" id="guidelines-content">
                                    <ul>
                                        <li>Bring a valid ID when claiming documents</li>
                                        <li>Documents can be claimed within 30 days from approval</li>
                                        <li>Claiming hours: 8:00 AM - 5:00 PM, Monday to Friday</li>
                                        <li>Authorized representatives must present authorization letter</li>
                                        <li>Processing time: 3-5 business days</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - Quick Links -->
                        <div class="right-column">
                            <div class="quick-links-container">
                                <h2 class="quick-links-title">Quick Actions</h2>

                                <!-- Online Request -->
                                <div class="quick-link-item">
                                    <div class="quick-link-header">
                                        <i class="fa-solid fa-circle quick-link-icon"></i>
                                        <h3>Request Barangay Certificates</h3>
                                    </div>
                                    <p class="quick-link-description">
                                        Submit an online request for barangay certificates and other official documents. Track your request status in real-time.
                                    </p>
                                    <button class="quick-link-btn" onclick="window.location.href='../user-request/user_form.php'">
                                        Request Now
                                    </button>
                                </div>

                                <!-- FAQs -->
                                <div class="quick-link-item">
                                    <div class="quick-link-header">
                                        <i class="fa-solid fa-circle quick-link-icon"></i>
                                        <h3>Frequently Asked Questions</h3>
                                    </div>
                                    <p class="quick-link-description">
                                        Find answers to common questions about document requests, processing times, and requirements.
                                    </p>
                                    <button class="quick-link-btn" id="openModalbtn">
                                        View FAQs
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ Modal -->
    <div class="modal" id="faqModal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title">Frequently Asked Questions</h3>
                <button type="button" class="btn-close" id="closeModalBtn" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="faq-item">
                    <h4>1. How long does it take to process my document request?</h4>
                    <p>Most document requests are processed within 3-5 business days from the date of submission.</p>
                </div>
                <div class="faq-item">
                    <h4>2. What are the requirements for requesting a barangay certificate?</h4>
                    <p>You need a valid ID, proof of residency, and a completed request form with the purpose of the certificate.</p>
                </div>
                <div class="faq-item">
                    <h4>3. Can someone else claim my document on my behalf?</h4>
                    <p>Yes, authorized representatives can claim documents with a valid authorization letter and their own ID.</p>
                </div>
                <div class="faq-item">
                    <h4>4. How will I know if my document is ready for pickup?</h4>
                    <p>You can track the status of your document request on the dashboard page.</p>
                </div>
                <div class="faq-item">
                    <h4>5. What should I do if I made a mistake in my request?</h4>
                    <p>Contact the barangay office immediately with your name and request ID to make corrections before processing.</p>
                </div>
                <div class="faq-item">
                    <h4>6. Are there any fees for document requests?</h4>
                    <p>All files and documents are free of charge.</p>
                </div>
            </div>
            <div class="modal-footer">
                <footer>
                    <button type="button" class="close-faq-btn" id="closeFaqBtn">Close</button>
                </footer>
            </div>
        </div>
    </div>

    <!-- Logout Modal -->
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

    <script src="../preloader/preloader.js"></script>
    <script src="../user-request/user-request.js"></script>
    <script src="../user-chatbot/chatbot.js"></script>
    <script src="../logout-modal.js"></script>
</body>
</html>