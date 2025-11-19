<?php
ini_set('session.gc_maxlifetime', 21600);
ini_set('session.cookie_lifetime', 21600);

session_start();

// Check if user is logged in
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    header("Location: ../user-login/login.php");
    exit();
}

// Get user data from session
$username = $_SESSION['username'];
$full_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : $username;
$user_id = $_SESSION['user_id']; // Get the actual user_id from session

if (!isset($_SESSION['user_id'])) {
    header("Location: ../user-login/user-login.php");
    exit();
}

// Database connection
require_once '../user-request/db_connection.php';

// Fetch user's pending and processing requests
$pending_requests = [];
$sql_pending = "SELECT * FROM document_requests 
                WHERE user_id = ? 
                AND status IN ('pending', 'processing') 
                ORDER BY date_requested DESC";
$stmt = $conn->prepare($sql_pending);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_pending = $stmt->get_result();
while($row = $result_pending->fetch_assoc()) {
    $pending_requests[] = $row;
}
$stmt->close();

// Fetch user's ready for pickup requests
$ready_requests = [];
$sql_ready = "SELECT * FROM document_requests 
              WHERE user_id = ? 
              AND status = 'ready' 
              ORDER BY date_requested DESC";
$stmt = $conn->prepare($sql_ready);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_ready = $stmt->get_result();
while($row = $result_ready->fetch_assoc()) {
    $ready_requests[] = $row;
}
$stmt->close();

// Fetch user's completed requests
$completed_requests = [];
$sql_completed = "SELECT * FROM document_requests 
                  WHERE user_id = ? 
                  AND status = 'completed' 
                  ORDER BY date_requested DESC";
$stmt = $conn->prepare($sql_completed);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_completed = $stmt->get_result();
while($row = $result_completed->fetch_assoc()) {
    $completed_requests[] = $row;
}
$stmt->close();

// Fetch user's rejected requests
$rejected_requests = [];
$sql_rejected = "SELECT * FROM document_requests 
                 WHERE user_id = ? 
                 AND status = 'rejected' 
                 ORDER BY date_requested DESC";
$stmt = $conn->prepare($sql_rejected);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_rejected = $stmt->get_result();
while($row = $result_rejected->fetch_assoc()) {
    $rejected_requests[] = $row;
}
$stmt->close();

// Fetch announcements (if table exists)
$announcements = [];
$check_table = $conn->query("SHOW TABLES LIKE 'announcements'");
if ($check_table && $check_table->num_rows > 0) {
    // Show announcements that are scheduled for now/past OR were created in the last hour (to show immediately when posted)
    $sql_announcements = "SELECT * FROM announcements 
                          WHERE send_to IN ('all', 'residents') 
                          AND (scheduled_date <= DATE_ADD(NOW(), INTERVAL 1 MINUTE) 
                               OR created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR))
                          ORDER BY scheduled_date DESC, created_at DESC 
                          LIMIT 3";
    $result_announcements = $conn->query($sql_announcements);
    if ($result_announcements && $result_announcements->num_rows > 0) {
        while($row = $result_announcements->fetch_assoc()) {
            $announcements[] = $row;
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard | Barangay 498 Management System</title>
    <link rel="stylesheet" href="../user-dashboard/user-dashboard.css" />
    <link rel="stylesheet" href="../preloader/preloader.css">
    <link rel="stylesheet" href="../logout-modal.css">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="stylesheet" href="../user-chatbot/chatbot.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo-section">
                <img src="../images/barangay-logo.png" alt="Barangay 498 Logo" class="barangay-logo" />
                <h2>Barangay Management System</h2>
            </div>

            <nav class="nav-menu">
                <a href="../user-dashboard/user-dashboard.php" class="nav-item active">
                    <span class="nav-label">Dashboard</span>
                </a>
                <a href="../user-profile/user-profile.php" class="nav-item">
                    <span class="nav-label">Profile</span>
                </a>
                <a href="../user-request/user_request.php" class="nav-item">
                    <span class="nav-label">Document Requests</span>
                </a>
                <a href="../user-announcement/user-announcement.php" class="nav-item">
                    <span class="nav-label">Announcement</span>
                </a>
                <a href="../user-calendar/user-calendar.php" class="nav-item">
                    <span class="nav-label">Calendar</span>
                </a>
                <a href="../user-officials/user-officials.php" class="nav-item">
                    <span class="nav-label">Officials</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="preloader" id="preloader"></div>
            
            <button class="burger-menu" id="burgerMenu">☰</button>
            
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <section class="overview-section">
                        <div class="overview-header">
                            <h2>DASHBOARD OVERVIEW</h2>
                            <p class="welcome-text">Welcome back, <?php echo htmlspecialchars($full_name); ?>!</p>
                        </div>
                    </section>
                </div>
                <div class="right-header">
                    <div class="time-and-logo">
                        <p id="time">12:00:00 AM</p>
                        <a href="#" id="logoutBtn">
                            <img src="../images/logoutbtn.png" alt="logout" class="logout-logo"/>
                        </a>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="content">
                <!-- Announcements -->
                <section class="announcements-section">
                    <h3 class="section-title">Announcements</h3>
                    <div class="announcements-list">
                        <?php if (count($announcements) > 0): ?>
                            <?php foreach ($announcements as $index => $announcement): ?>
                                <?php $scheduled_date = new DateTime($announcement['scheduled_date']); ?>
                                <div class="announcement-card <?php echo $index === 0 ? 'active' : ''; ?>">
                                    <h4><?php echo htmlspecialchars($announcement['title']); ?></h4>
                                    <p><?php echo htmlspecialchars(substr($announcement['message'], 0, 150)) . (strlen($announcement['message']) > 150 ? '...' : ''); ?></p>
                                    <div class="details">
                                        <span><?php echo $scheduled_date->format('M d, Y'); ?></span>
                                        <span><?php echo $scheduled_date->format('g:i A'); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div class="announcement-bullets">
                                <?php foreach ($announcements as $index => $announcement): ?>
                                    <div class="bullet <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>"></div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <p>No announcements at this time</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Pending Requests -->
                <section class="pending-request-section">
                    <h3 class="section-title">Pending Requests</h3>
                    <div class="pending-request-list">
                        <?php if (count($pending_requests) > 0): ?>
                            <?php foreach ($pending_requests as $req): ?>
                                <div class="pending-request-card">
                                    <div class="details">
                                        <p><strong>Request ID:</strong> REQ-<?php echo str_pad($req['id'] ?? $req['request_id'], 3, '0', STR_PAD_LEFT); ?></p>
                                        <p><strong>Type:</strong> <?php echo htmlspecialchars($req['document_type']); ?></p>
                                        <p><strong>Purpose:</strong> <?php echo htmlspecialchars($req['purpose']); ?></p>
                                        <p><strong>Date Requested:</strong> <?php echo date('M d, Y', strtotime($req['date_requested'])); ?></p>
                                        <p><strong>Expected Date:</strong> <?php echo date('M d, Y', strtotime($req['expected_date'])); ?></p>
                                        <p class="status-<?php echo strtolower($req['status']); ?>">
                                            <strong>Status:</strong> <?php echo ucfirst(htmlspecialchars($req['status'])); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <p>No pending requests at this time</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Ready to Pickup -->
                <section class="ready-pickup-section">
                    <h3 class="section-title">Ready to Pickup</h3>
                    <div class="ready-pickup-list">
                        <?php if (count($ready_requests) > 0): ?>
                            <?php foreach ($ready_requests as $req): ?>
                                <div class="ready-pickup-card">
                                    <div class="details">
                                        <p><strong>Request ID:</strong> REQ-<?php echo str_pad($req['id'] ?? $req['request_id'], 3, '0', STR_PAD_LEFT); ?></p>
                                        <p><strong>Type:</strong> <?php echo htmlspecialchars($req['document_type']); ?></p>
                                        <p><strong>Purpose:</strong> <?php echo htmlspecialchars($req['purpose']); ?></p>
                                        <p><strong>Expected Date:</strong> <?php echo date('M d, Y', strtotime($req['expected_date'])); ?></p>
                                        <p class="status-ready"><strong>Status:</strong> Ready for Pickup</p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <p>No documents ready for pickup</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Completed Requests -->
                <section class="complete-request-section">
                    <h3 class="section-title">Completed</h3>
                    <div class="completed-request-list">
                        <?php if (count($completed_requests) > 0): ?>
                            <?php foreach ($completed_requests as $req): ?>
                                <div class="completed-request-card">
                                    <div class="details">
                                        <p><strong>Request ID:</strong> REQ-<?php echo str_pad($req['id'] ?? $req['request_id'], 3, '0', STR_PAD_LEFT); ?></p>
                                        <p><strong>Type:</strong> <?php echo htmlspecialchars($req['document_type']); ?></p>
                                        <p><strong>Purpose:</strong> <?php echo htmlspecialchars($req['purpose']); ?></p>
                                        <p><strong>Completed Date:</strong> <?php echo date('M d, Y', strtotime($req['date_completed'] ?? $req['date_requested'])); ?></p>
                                        <p class="status-completed"><strong>Status:</strong> Completed</p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <p>No completed requests at this time</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Rejected Requests -->
                <section class="rejected-request-section">
                    <h3 class="section-title">Rejected</h3>
                    <div class="rejected-request-list">
                        <?php if (count($rejected_requests) > 0): ?>
                            <?php foreach ($rejected_requests as $req): ?>
                                <div class="rejected-request-card">
                                    <div class="details">
                                        <p><strong>Request ID:</strong> REQ-<?php echo str_pad($req['id'] ?? $req['request_id'], 3, '0', STR_PAD_LEFT); ?></p>
                                        <p><strong>Type:</strong> <?php echo htmlspecialchars($req['document_type']); ?></p>
                                        <p><strong>Purpose:</strong> <?php echo htmlspecialchars($req['purpose']); ?></p>
                                        <p><strong>Date Requested:</strong> <?php echo date('M d, Y', strtotime($req['date_requested'])); ?></p>
                                        <p><strong>Rejected Date:</strong> <?php echo date('M d, Y', strtotime($req['date_updated'] ?? $req['date_requested'])); ?></p>
                                        <p class="status-rejected"><strong>Status:</strong> Rejected
                                            <em>(Provided Reason: <?php echo htmlspecialchars($req['rejection_reason'] ?? 'N/A'); ?>)</em></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <p>No rejected requests at this time</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
                </main>
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
        <div id="chat-header">BarangAI</div>
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

    <script>
        const currentUser = {
            id: <?php echo json_encode($user_id); ?>,
            name: <?php echo json_encode($full_name); ?>,
            username: <?php echo json_encode($username); ?>
        };

        // Announcement Slideshow
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.announcement-card');
            const bullets = document.querySelectorAll('.bullet');
            let currentIndex = 0;
            const interval = 5000; // 5 seconds
            let slideTimer;

            function showSlide(index) {
                // Hide all cards and deactivate all bullets
                cards.forEach(card => card.classList.remove('active'));
                bullets.forEach(bullet => bullet.classList.remove('active'));
                
                // Show current card and activate current bullet
                cards[index].classList.add('active');
                bullets[index].classList.add('active');
            }

            function nextSlide() {
                currentIndex = (currentIndex + 1) % cards.length;
                showSlide(currentIndex);
            }

            function startSlideshow() {
                if (slideTimer) clearInterval(slideTimer);
                slideTimer = setInterval(nextSlide, interval);
            }

            // Add click events to bullets
            bullets.forEach((bullet, index) => {
                bullet.addEventListener('click', () => {
                    currentIndex = index;
                    showSlide(currentIndex);
                    startSlideshow(); // Reset timer when manually changing slides
                });
            });

            // Start the slideshow if there are announcements
            if (cards.length > 0) {
                startSlideshow();
            }

            // Pause slideshow when hovering over announcements section
            const announcementsSection = document.querySelector('.announcements-section');
            announcementsSection.addEventListener('mouseenter', () => {
                if (slideTimer) clearInterval(slideTimer);
            });

            announcementsSection.addEventListener('mouseleave', () => {
                startSlideshow();
            });
        });
    </script>
    <script src="../user-chatbot/chatbot.js"></script>
    <script src="../preloader/preloader.js"></script>
    <script src="../logout-modal.js"></script>
    <script src="../user-dashboard/user-dashboard.js"></script>

</body>
</html>