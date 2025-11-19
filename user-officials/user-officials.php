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
$username = "root";
$password = "";
$dbname = "498portal";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all officials from database
$sql = "SELECT * FROM barangay_officials_db ORDER BY id ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../user-officials/user-officials.css" />
    <link rel="stylesheet" href="../preloader/preloader.css" />
    <link rel="stylesheet" href="../logout-modal.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="stylesheet" href="../user-chatbot/chatbot.css" />
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
      rel="stylesheet"
    />
    <title>Officials</title>
  </head>
  <body>
    <!-- Preloader -->
    <div class="preloader" id="preloader">
      <div class="spinner"></div>
      <p>Loading...</p>
    </div>

    <!-- Main Container -->
    <div class="officials-container">
      <button class="burger-menu" id="burgerMenu">☰</button>
      <div class="sidebar" id="sidebar">
        <img
          src="../images/barangay-logo.png"
          alt="barangay-logo"
          class="barangay-logo"
        />
        <h1>Barangay Management System</h1>
        <nav class="menu">
          <ul class="menu-items">
            <li><a href="../user-dashboard/user-dashboard.php">Dashboard</a></li>
            <li><a href="../user-profile/user-profile.php">Profile</a></li>
            <li><a href="../user-request/user_request.php">Document Requests</a></li>
            <li><a href="../user-announcement/user-announcement.php">Announcement</a></li>
            <li><a href="../user-calendar/user-calendar.php">Calendar</a></li>
            <li><a href="../user-officials/user-officials.php" class="officials-link">Officials</a></li>
          </ul>
        </nav>
      </div>

      <!-- Header -->
      <div class="right-container">
        <header class="officials-header">
          <div class="time-and-logo">
            <p id="clock">12:00:00 AM</p>
            <a href="#" id="logoutBtn">
              <img
                src="../images/logoutbtn.png"
                alt="logout"
                class="logout-logo"
                id="logoutBtn"
              />
            </a>
          </div>
          <div class="header-left">
            <img src="../images/plm-logo.png" alt="plm-logo" />
          </div>
          <div class="header-center">
            <h1>BARANGAY OFFICIALS</h1>
            <p class="sub-header">Meet Our Dedicated Community Leaders</p>
            <p class="center-sub">
              We are proud to present the officials who tirelessly work to
              ensure the peace, safety, and well-being of every resident. Their
              service is the foundation of our community.
            </p>
          </div>
          <div class="header-right">
            <img src="../images/newph-logo.png" alt="newph-logo" />
          </div>
        </header>

        <!-- Officials Grid -->
        <div class="officials-grid">
          <?php
          if ($result->num_rows > 0) {
              while($row = $result->fetch_assoc()) {
                  echo '<div class="official-card">';
                  
                  // Image section
                  if ($row['image_path'] && file_exists('../admin-officials/' . $row['image_path'])) {
                      echo '<div class="official-image" style="background-image: url(\'../admin-officials/' . $row['image_path'] . '\'); background-size: cover; background-position: center;"></div>';
                  } else {
                      echo '<div class="official-image"></div>';
                  }
                  
                  // Info section
                  echo '<div class="official-info">';
                  echo '<h3>' . htmlspecialchars($row['name']) . '</h3>';
                  echo '<p>' . htmlspecialchars($row['position']) . '</p>';
                  echo '</div>';
                  
                  echo '</div>';
              }
          } else {
              echo '<p style="grid-column: 1/-1; text-align: center; color: #666;">No officials information available at the moment.</p>';
          }
          ?>
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
  </body>

  <script src="../preloader/preloader.js"></script>
  <script src="../admin-dashboard.js"></script>
  <script src="../user-chatbot/chatbot.js"></script>
  <script src="../logout-modal.js"></script>
</html>

<?php
$conn->close();
?>