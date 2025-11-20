<?php
ini_set('session.gc_maxlifetime', 21600);
ini_set('session.cookie_lifetime', 21600);

session_start();

/* -------------------------
   USER LOGIN VALIDATION
-------------------------- */

if (
    !isset($_SESSION['username']) ||
    empty($_SESSION['username']) ||
    !isset($_SESSION['user_id']) ||
    empty($_SESSION['user_id'])
) {
    header("Location: ../user-login/login.php");
    exit();
}

$username = $_SESSION['username'];
$full_name = $_SESSION['full_name'] ?? $username;
$user_id = intval($_SESSION['user_id']);

/* -------------------------
   DATABASE CONNECTION
-------------------------- */
require_once '../user-request/db_connection.php';

/* -------------------------
   CLEAN QUERY FUNCTION
-------------------------- */
function fetchRequests($conn, $user_id, $statusList) {
    if (empty($statusList)) {
        return [];
    }

    // Create placeholders for prepared statement
    $statusPlaceholder = implode(",", array_fill(0, count($statusList), "?"));
    $types = str_repeat("s", count($statusList));

    // Explicitly select columns
    $sql = "SELECT request_id, document_type, purpose, date_requested, expected_date, status, rejection_reason
        FROM document_requests
        WHERE user_id = ? 
        AND status IN ($statusPlaceholder)
        ORDER BY date_requested DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind parameters
    $bindTypes = "i" . $types; // i for user_id + s for each status
    $bindValues = array_merge([$user_id], $statusList);
    $stmt->bind_param($bindTypes, ...$bindValues);

    // Execute and fetch results
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        // Ensure 'request_id' always exists
        if (!isset($row['request_id'])) {
            $row['request_id'] = 0;
        }
        $data[] = $row;
    }

    $stmt->close();
    return $data;
}

/* -------------------------
   FETCH USER REQUESTS
-------------------------- */
$pending_requests   = fetchRequests($conn, $user_id, ['pending', 'processing']);
$ready_requests     = fetchRequests($conn, $user_id, ['ready']);
$completed_requests = fetchRequests($conn, $user_id, ['completed']);
$rejected_requests  = fetchRequests($conn, $user_id, ['rejected']);

/* -------------------------
   FETCH ANNOUNCEMENTS
-------------------------- */
$announcements = [];

$check_table = $conn->query("SHOW TABLES LIKE 'announcements'");
if ($check_table && $check_table->num_rows > 0) {

    $sql_announcements = "SELECT * FROM announcements 
                      WHERE send_to IN ('all', 'residents')
                      ORDER BY scheduled_date DESC, created_at DESC
                      LIMIT 10";


    $result = $conn->query($sql_announcements);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
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
    <link rel="stylesheet" href="../user-chatbot/chatbot.css">

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;900&display=swap" rel="stylesheet" />
</head>

<body>
    <div class="container">

        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo-section">
                <img src="../images/barangay-logo.png" class="barangay-logo" />
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
                            <p class="welcome-text">
                                Welcome back, <?php echo htmlspecialchars($full_name); ?>!
                            </p>
                        </div>
                    </section>
                </div>

                <div class="right-header">
                    <div class="time-and-logo">
                        <p id="time">12:00:00 AM</p>
                        <a href="#" id="logoutBtn">
                            <img src="../images/logoutbtn.png" class="logout-logo" />
                        </a>
                    </div>
                </div>
            </header>

            <div class="content">

                    <!-- Announcements -->
                    <section class="announcements-section">
                        <h3 class="section-title">Announcements</h3>

                        <div class="announcements-list">

                            <?php if (!empty($announcements)): ?>
                                <?php foreach ($announcements as $index => $a): ?>

                                    <?php
                                        $scheduled_date = new DateTime($a['scheduled_date']);
                                        $image_path = !empty($a['image_path']) 
                                        ? '../admin-announcement/' . htmlspecialchars($a['image_path'], ENT_QUOTES, 'UTF-8')
                                        : '';
                                        $video_path = !empty($a['video_path']) 
                                        ? '../admin-announcement/' . htmlspecialchars($a['video_path'], ENT_QUOTES, 'UTF-8')
                                        : '';
                                        $full_message = $a['message'];
                                        $preview = strlen($full_message) > 150 
                                            ? mb_substr($full_message, 0, 150) . '...' 
                                            : $full_message;
                                        $encoded_message = htmlspecialchars($full_message, ENT_QUOTES, 'UTF-8');
                                    ?>

                                    <div class="announcement-card <?php echo $index === 0 ? 'active' : ''; ?>"
                                        data-title="<?php echo htmlspecialchars($a['title']); ?>"
                                        data-message="<?php echo $encoded_message; ?>"
                                        data-image-path="<?php echo $image_path; ?>"
                                        data-video-path="<?php echo $video_path; ?>"
                                        data-date="<?php echo $scheduled_date->format('M d, Y'); ?>"
                                        data-time="<?php echo $scheduled_date->format('g:i A'); ?>">

                                        <h4 class="announcement-title">
                                            <?php echo htmlspecialchars($a['title']); ?>
                                        </h4>

                                        <p class="announcement-preview"><?php echo htmlspecialchars($preview); ?></p>

                                        <div class="details">
                                            <span><?php echo $scheduled_date->format('M d, Y'); ?></span>
                                            <span><?php echo $scheduled_date->format('g:i A'); ?></span>
                                        </div>
                                    </div>

                                <?php endforeach; ?>

                                <div class="announcement-bullets">
                                    <?php foreach ($announcements as $i => $n): ?>
                                        <div class="bullet <?php echo $i === 0 ? 'active' : ''; ?>" data-index="<?php echo $i; ?>"></div>
                                    <?php endforeach; ?>
                                </div>

                            <?php else: ?>
                                <div class="empty-state"><p>No announcements available.</p></div>
                            <?php endif; ?>

                        </div>
                    </section>

                <!-- Pending Requests -->
                <section class="pending-request-section">
                    <h3 class="section-title">Pending Requests</h3>
                    <div class="pending-request-list">
                        <?php if (!empty($pending_requests)): ?>
                            <?php foreach ($pending_requests as $req): ?>
                                <div class="pending-request-card">
                                    <div class="details">
                                        <p><strong>Request ID:</strong> REQ-<?php echo str_pad(isset($req['request_id']) ? $req['request_id'] : 0, 3, '0', STR_PAD_LEFT); ?></p>
                                        <p><strong>Type:</strong> <?php echo htmlspecialchars($req['document_type']); ?></p>
                                        <p><strong>Purpose:</strong> <?php echo htmlspecialchars($req['purpose']); ?></p>
                                        <p><strong>Date Requested:</strong> <?php echo date('M d, Y', strtotime($req['date_requested'])); ?></p>
                                        <p><strong>Expected Date:</strong> <?php echo date('M d, Y', strtotime($req['expected_date'])); ?></p>
                                        <p class="status-<?php echo strtolower($req['status']); ?>">
                                            <strong>Status:</strong> <?php echo ucfirst($req['status']); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state"><p>No pending requests.</p></div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Ready -->
                <section class="ready-pickup-section">
                    <h3 class="section-title">Ready to Pickup</h3>
                    <div class="ready-pickup-list">
                        <?php if (!empty($ready_requests)): ?>
                            <?php foreach ($ready_requests as $req): ?>
                                <div class="ready-pickup-card">
                                    <div class="details">
                                        <p><strong>Request ID:</strong> REQ-<?php echo str_pad(isset($req['request_id']) ? $req['request_id'] : 0, 3, '0', STR_PAD_LEFT); ?></p>
                                        <p><strong>Type:</strong> <?php echo htmlspecialchars($req['document_type']); ?></p>
                                        <p><strong>Purpose:</strong> <?php echo htmlspecialchars($req['purpose']); ?></p>
                                        <p><strong>Expected Date:</strong> <?php echo date('M d, Y', strtotime($req['expected_date'])); ?></p>
                                        <p class="status-ready"><strong>Status:</strong> Ready for Pickup</p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state"><p>No ready documents.</p></div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Completed -->
                <section class="complete-request-section">
                    <h3 class="section-title">Completed</h3>
                    <div class="completed-request-list">
                        <?php if (!empty($completed_requests)): ?>
                            <?php foreach ($completed_requests as $req): ?>
                                <div class="completed-request-card">
                                    <div class="details">
                                        <p><strong>Request ID:</strong> 
                                            REQ-<?php echo str_pad(isset($req['request_id']) ? $req['request_id'] : 0, 3, '0', STR_PAD_LEFT); ?>
                                        </p>
                                        <p><strong>Type:</strong> <?php echo htmlspecialchars($req['document_type'] ?? 'N/A'); ?></p>
                                        <p><strong>Purpose:</strong> <?php echo htmlspecialchars($req['purpose'] ?? 'N/A'); ?></p>
                                        <p><strong>Completed Date:</strong> 
                                            <?php 
                                                echo isset($req['date_completed']) && $req['date_completed'] != '0000-00-00' 
                                                    ? date('M d, Y', strtotime($req['date_completed'])) 
                                                    : 'N/A'; 
                                            ?>
                                        </p>
                                        <p class="status-completed"><strong>Status:</strong> Completed</p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state"><p>No completed requests.</p></div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Rejected -->
                <section class="rejected-request-section">
                    <h3 class="section-title">Rejected</h3>
                    <div class="rejected-request-list">
                        <?php if (!empty($rejected_requests)): ?>
                            <?php foreach ($rejected_requests as $req): ?>
                                <div class="rejected-request-card">
                                    <div class="details">
                                        <p><strong>Request ID:</strong> 
                                            REQ-<?php echo str_pad(isset($req['request_id']) ? $req['request_id'] : 0, 3, '0', STR_PAD_LEFT); ?>
                                        </p>
                                        <p><strong>Type:</strong> <?php echo htmlspecialchars($req['document_type'] ?? 'N/A'); ?></p>
                                        <p><strong>Purpose:</strong> <?php echo htmlspecialchars($req['purpose'] ?? 'N/A'); ?></p>
                                        <p><strong>Date Requested:</strong> 
                                            <?php echo isset($req['date_requested']) ? date('M d, Y', strtotime($req['date_requested'])) : 'N/A'; ?>
                                        </p>
                                        <p><strong>Rejected Date:</strong> 
                                            <?php echo isset($req['date_updated']) ? date('M d, Y', strtotime($req['date_updated'])) : (isset($req['date_requested']) ? date('M d, Y', strtotime($req['date_requested'])) : 'N/A'); ?>
                                        </p>
                                        <p class="status-rejected">
                                            <strong>Status:</strong> Rejected — 
                                            <em><?php echo htmlspecialchars($req['rejection_reason'] ?? 'No reason provided'); ?></em>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state"><p>No rejected requests.</p></div>
                        <?php endif; ?>
                    </div>
                </section>


            </div>
        </main>
    </div>

    <!-- Announcement Modal -->
    <div id="announcementModal" class="announcement-modal">
        <div class="announcement-modal-content">
            <div class="announcement-modal-header">
                <h3 id="modalAnnouncementTitle"></h3>
                <button class="close-modal-btn">&times;</button>
            </div>

            <div class="announcement-modal-body">
                <p id="modalAnnouncementMessage"></p>
                <div id="modalAnnouncementMedia" class="announcement-modal-media"></div>
            </div>

            <div class="announcement-modal-footer">
                <div class="announcement-modal-details">
                    <span><strong>Date:</strong> <span id="modalAnnouncementDate"></span></span>
                    <span><strong>Time:</strong> <span id="modalAnnouncementTime"></span></span>
                </div>
                <button class="modal-close-bottom-btn">Close</button>
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
                <button id="cancelLogout" class="logout-btn-cancel">No</button>
                <button id="confirmLogout" class="logout-btn-confirm">Yes</button>
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
</script>

<script src="../user-chatbot/chatbot.js"></script>
<script src="../preloader/preloader.js"></script>
<script src="../logout-modal.js"></script>
<script src="../user-dashboard/user-dashboard.js"></script>

</body>
</html>
