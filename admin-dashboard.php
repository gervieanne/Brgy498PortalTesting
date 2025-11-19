<?php
ini_set('session.gc_maxlifetime', 21600);
ini_set('session.cookie_lifetime', 21600);
session_start();

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

// Get statistics for top cards only
$totalQuery = "SELECT COUNT(*) as total FROM userprofile498";
$totalResult = $conn->query($totalQuery);
$totalResidents = $totalResult->fetch_assoc()['total'];

$maleQuery = "SELECT COUNT(*) as male FROM userprofile498 WHERE sex = 'M'";
$maleResult = $conn->query($maleQuery);
$maleCount = $maleResult->fetch_assoc()['male'];

$femaleQuery = "SELECT COUNT(*) as female FROM userprofile498 WHERE sex = 'F'";
$femaleResult = $conn->query($femaleQuery);
$femaleCount = $femaleResult->fetch_assoc()['female'];

$seniorsQuery = "SELECT COUNT(*) as seniors FROM userprofile498 
                 WHERE TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) >= 60";
$seniorsResult = $conn->query($seniorsQuery);
$seniorsCount = $seniorsResult->fetch_assoc()['seniors'];

$votersQuery = "SELECT COUNT(*) as voters FROM userprofile498 
                WHERE TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) >= 18";
$votersResult = $conn->query($votersQuery);
$votersCount = $votersResult->fetch_assoc()['voters'];

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="preloader/preloader.css" />
    <link rel="stylesheet" href="logout-modal.css" />
    <link rel="stylesheet" href="admin-dashboard.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
      rel="stylesheet"
    />
    <title>Dashboard</title>
  </head>
  <body>
    <div class="dashboard-container">
      <!-- Sidebar -->
      <div class="sidebar" id="sidebar">
        <img
          src="images/barangay-logo.png"
          alt="barangay-logo"
          class="barangay-logo"
        />
        <h1>Barangay Management System</h1>
        <nav class="menu">
          <ul class="menu-items">
            <li>
              <a href="admin-dashboard.php" class="dashboard-link">Dashboard</a>
            </li>
            <li><a href="admin-officials/admin-officials.php">Officials</a></li>
            <li>
              <a href="admin-residents-info/admin-residents.php"
                >Residents Information</a
              >
            </li>
            <li><a href="admin-calendar/admin-calendar.php">Calendar</a></li>
            <li>
              <a href="admin-document-req/admin-document-requests.php"
                >Document Requests</a
              >
            </li>
            <li>
              <a href="admin-announcement/admin-announcement.php">Announcement</a>
            </li>
          </ul>
        </nav>
      </div>

      <div class="preloader" id="preloader">
        <div class="spinner"></div>
        <p>Loading.</p>
      </div>

      <!-- Main Content -->
      <div class="right-content">
        <header class="header">
          <button class="burger-menu" id="burgerMenu">☰</button>
          <div class="left-header">
            <h1>DASHBOARD OVERVIEW</h1>
            <p class="leftsub-header">Welcome back, Admin!</p>
          </div>
          <div class="right-header">
            <div class="time-and-logo">
              <p id="clock">12:00:00 AM</p>
              <a href="#" id="logoutBtn">
                <img
                  src="images/logoutbtn.png"
                  alt="logout"
                  class="logout-logo"
                />
              </a>
            </div>
          </div>
        </header>

        <div class="stats-container">
          <div class="stat-card">
            <p>Total Residents</p>
            <h1><?php echo $totalResidents; ?></h1>
          </div>
          <div class="stat-card">
            <p>Male</p>
            <h1><?php echo $maleCount; ?></h1>
          </div>
          <div class="stat-card">
            <p>Female</p>
            <h1><?php echo $femaleCount; ?></h1>
          </div>
          <div class="stat-card">
            <p>Seniors</p>
            <h1><?php echo $seniorsCount; ?></h1>
          </div>
          <div class="stat-card">
            <p>Total Voters</p>
            <h1><?php echo $votersCount; ?></h1>
          </div>
        </div>

        <div class="charts-container">
          <div class="chart-widget" id="residentsDemographics">
            <div class="charts-header">Demographics by Street</div>
            <canvas id="demographicsChart"></canvas>
          </div>

          <div class="chart-widget" id="documentRequest">
            <div class="charts-header">Document Requests</div>
            <canvas id="documentChart"></canvas>
          </div>

          <div class="chart-widget" id="pendingApplication">
            <div class="charts-header">Pending Applications</div>
            <canvas id="pendingChart"></canvas>
          </div>

          <div class="chart-widget" id="cancelledRequest">
            <div class="charts-header">Cancelled Request</div>
            <canvas id="cancelledChart"></canvas>
          </div>

          <div class="chart-widget" id="completedRequest">
            <div class="charts-header">Completed Request</div>
            <canvas id="completedChart"></canvas>
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

    <!-- Load libraries and other scripts first -->
    <script src="preloader/preloader.js"></script>
    <script src="admin-dashboard.js"></script>
    <script src="logout-modal.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    
    <!-- Load the charts script -->
    <script src="admin-charts-dynamic.js"></script>
  </body>
</html>
