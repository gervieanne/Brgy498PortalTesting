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

// Database connection
$servername = "localhost";
$username_db = "root";
$password = "";
$dbname = "498portal";

$conn = new mysqli($servername, $username_db, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to handle special characters properly
$conn->set_charset("utf8mb4");

// Fetch announcements - show announcements that are scheduled for now/past OR were created recently
// Send to 'all' or 'residents', ordered by scheduled_date (most recent first)
// Added 1 minute buffer and 1 hour window for recently created announcements to show immediately
$sql = "SELECT * FROM announcements 
        WHERE send_to IN ('all', 'residents') 
        AND (scheduled_date <= DATE_ADD(NOW(), INTERVAL 1 MINUTE) 
             OR created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR))
        ORDER BY scheduled_date DESC, created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Resident Announcements</title>
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="../user-announcement/user-announcement.css" />
    <link rel="stylesheet" href="../preloader/preloader.css" />
    <link rel="stylesheet" href="../user-chatbot/chatbot.css" />
    <link rel="stylesheet" href="../logout-modal.css" />
  </head>

  <body>
    <div class="container">
      <!-- Sidebar -->
      <aside class="sidebar">
        <img
          src="../images/barangay-logo.png"
          alt="Barangay Logo"
          class="barangay-logo"
        />
        <h2>Barangay Management System</h2>
        <ul>
          <li><a href="../user-dashboard/user-dashboard.php">Dashboard</a></li>
          <li><a href="../user-profile/user-profile.php">Profile</a></li>
          <li><a href="../user-request/user_request.php">Document Requests</a></li>
          <li>
            <a href="../user-announcement/user-announcement.php" class="active">Announcement</a>
          </li>
          <li><a href="../user-calendar/user-calendar.php">Calendar</a></li>
          <li><a href="../user-officials/user-officials.php">Officials</a></li>
        </ul>
      </aside>

      <!-- Main Content -->
      <main class="main-content">
        <button class="burger-menu" id="burgerMenu">☰</button>
        <div class="topbar">
          <div class="time-user">
            <span id="time"></span>
            <a href="#" id="logoutBtn">
              <img
                src="../images/logoutbtn.png"
                alt="logout"
                class="logout-logo"
              />
            </a>
          </div>
        </div>

        <div class="header-bar">
          <h3>Announcement</h3>
        </div>

        <section class="announcement-list">
          <?php
          if ($result && $result->num_rows > 0) {
              while($row = $result->fetch_assoc()) {
                  echo '<div class="announcement-card">';
                  echo '<h4>' . htmlspecialchars($row['title']) . '</h4>';
                  
                  // Convert markdown-style links to HTML and preserve line breaks
                  $message = htmlspecialchars($row['message']);
                  $message = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" target="_blank" style="color: #21205d; text-decoration: underline; font-weight: 500;">$1</a>', $message);
                  echo '<p>' . nl2br($message) . '</p>';
                  
                  // Display image if exists
                  if (!empty($row['image_path']) && file_exists('../admin-announcement/' . $row['image_path'])) {
                      echo '<div style="margin-top: 15px;">';
                      echo '<img src="../admin-announcement/' . htmlspecialchars($row['image_path']) . '" ';
                      echo 'style="max-width: 100%; height: auto; border-radius: 5px; display: block;" ';
                      echo 'alt="Announcement Image" />';
                      echo '</div>';
                  }
                  
                  // Display video if exists
                  if (!empty($row['video_path']) && file_exists('../admin-announcement/' . $row['video_path'])) {
                      echo '<div style="margin-top: 15px;">';
                      echo '<video controls style="max-width: 100%; height: auto; border-radius: 5px; display: block;">';
                      echo '<source src="../admin-announcement/' . htmlspecialchars($row['video_path']) . '" type="video/mp4">';
                      echo 'Your browser does not support the video tag.';
                      echo '</video>';
                      echo '</div>';
                  }
                  
                  // Display scheduled date and time
                  $scheduled_date = new DateTime($row['scheduled_date']);
                  echo '<div class="details">';
                  echo '<span>Date: ' . $scheduled_date->format('M d, Y') . '</span>';
                  echo '<span>Time: ' . $scheduled_date->format('g:i A') . '</span>';
                  echo '</div>';
                  echo '</div>';
              }
          } else {
              echo '<div class="announcement-card">';
              echo '<p style="text-align: center; color: #666;">No announcements available at the moment.</p>';
              echo '</div>';
          }
          ?>
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

    <!-- chatbot -->
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
        <input
          id="chat-input"
          type="text"
          placeholder="Type your question..."
        />
        <button id="send-btn">➤</button>
      </div>
    </div>

    <script src="../preloader/preloader.js"></script>
    <script src="../user-chatbot/chatbot.js"></script>
    <script src="../logout-modal.js"></script>
    <script>
      // Live clock update
      function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString("en-US", { hour12: true });
        document.getElementById("time").textContent = timeString;
      }
      setInterval(updateTime, 1000);
      updateTime();

      // Burger menu functionality
      const burgerMenu = document.getElementById("burgerMenu");
      const sidebar = document.querySelector(".sidebar");

      burgerMenu.addEventListener("click", function () {
        sidebar.classList.toggle("active");
      });

      // Close sidebar when clicking on a link
      const sidebarLinks = document.querySelectorAll(".sidebar li a");
      sidebarLinks.forEach((link) => {
        link.addEventListener("click", function () {
          sidebar.classList.remove("active");
        });
      });

      // Close sidebar when clicking outside
      document.addEventListener("click", function (event) {
        if (
          !event.target.closest(".sidebar") &&
          !event.target.closest(".burger-menu")
        ) {
          sidebar.classList.remove("active");
        }
      });
    </script>
  </body>
</html>

<?php
$conn->close();
?>
