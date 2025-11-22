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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
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

        <!-- Dashboard Info Cards -->
        <div class="quick-actions-bar">
          <div class="quick-action-card info-card">
            <div class="quick-action-icon">
              <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="quick-action-content">
              <h4 id="currentDate">Loading...</h4>
              <p id="currentDay">Today</p>
            </div>
            <div class="quick-action-badge">
              <i class="fas fa-clock"></i>
            </div>
          </div>
          
          <div class="quick-action-card info-card">
            <div class="quick-action-icon">
              <i class="fas fa-shield-alt"></i>
            </div>
            <div class="quick-action-content">
              <h4>System Status</h4>
              <p id="systemStatus">All systems operational</p>
            </div>
            <div class="quick-action-badge status-badge active">
              <i class="fas fa-check-circle"></i>
            </div>
          </div>
          
          <div class="quick-action-card info-card">
            <div class="quick-action-icon">
              <i class="fas fa-bell"></i>
            </div>
            <div class="quick-action-content">
              <h4>Notifications</h4>
              <p id="notificationCount">0 new alerts</p>
            </div>
            <div class="quick-action-badge notification-badge" id="notificationBadge">
              <span id="notificationNumber">0</span>
            </div>
          </div>
          
          <div class="quick-action-card info-card">
            <div class="quick-action-icon">
              <i class="fas fa-chart-bar"></i>
            </div>
            <div class="quick-action-content">
              <h4>Today's Activity</h4>
              <p id="todayActivity">View statistics</p>
            </div>
            <div class="quick-action-badge">
              <i class="fas fa-arrow-trend-up"></i>
            </div>
          </div>
        </div>

        <!-- Main Dashboard Layout: Charts Carousel Left, Stats Right -->
        <div class="main-dashboard-layout">
          <!-- Left Side: Big Charts Carousel (Demographics, Document Requests, etc.) -->
          <div class="big-charts-panel">
            <div class="big-charts-carousel">
              <div class="chart-widget big-chart active" data-index="0" id="residentsDemographics">
                <div class="charts-header">
                  <div class="chart-header-content">
                    <div class="chart-icon-wrapper">
                      <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <div class="chart-title-wrapper">
                      <h3 class="chart-title">Demographics by Street</h3>
                      <p class="chart-subtitle">Resident distribution across streets</p>
                    </div>
                  </div>
                </div>
                <div class="chart-body">
                  <canvas id="demographicsChart"></canvas>
                </div>
              </div>

              <div class="chart-widget big-chart" data-index="1" id="documentRequest">
                <div class="charts-header">
                  <div class="chart-header-content">
                    <div class="chart-icon-wrapper">
                      <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="chart-title-wrapper">
                      <h3 class="chart-title">Document Requests</h3>
                      <p class="chart-subtitle">Requests by document type</p>
                    </div>
                  </div>
                </div>
                <div class="chart-body">
                  <canvas id="documentChart"></canvas>
                </div>
              </div>

              <div class="chart-widget big-chart" data-index="2" id="pendingApplication">
                <div class="charts-header">
                  <div class="chart-header-content">
                    <div class="chart-icon-wrapper">
                      <i class="fas fa-clock"></i>
                    </div>
                    <div class="chart-title-wrapper">
                      <h3 class="chart-title">Pending Applications</h3>
                      <p class="chart-subtitle">Awaiting review & processing</p>
                    </div>
                  </div>
                </div>
                <div class="chart-body">
                  <canvas id="pendingChart"></canvas>
                </div>
              </div>

              <div class="chart-widget big-chart" data-index="3" id="cancelledRequest">
                <div class="charts-header">
                  <div class="chart-header-content">
                    <div class="chart-icon-wrapper">
                      <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="chart-title-wrapper">
                      <h3 class="chart-title">Cancelled Request</h3>
                      <p class="chart-subtitle">Cancelled applications</p>
                    </div>
                  </div>
                </div>
                <div class="chart-body">
                  <canvas id="cancelledChart"></canvas>
                </div>
              </div>

              <div class="chart-widget big-chart" data-index="4" id="completedRequest">
                <div class="charts-header">
                  <div class="chart-header-content">
                    <div class="chart-icon-wrapper">
                      <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="chart-title-wrapper">
                      <h3 class="chart-title">Completed Request</h3>
                      <p class="chart-subtitle">Successfully processed</p>
                    </div>
                  </div>
                </div>
                <div class="chart-body">
                  <canvas id="completedChart"></canvas>
                </div>
              </div>
            </div>
            
            <!-- Big Charts Carousel Navigation -->
            <div class="big-chart-navigation">
              <button class="big-chart-nav-btn prev-btn" id="prevChartBtn" aria-label="Previous Chart">
                <i class="fas fa-chevron-left"></i>
              </button>
              
              <div class="big-chart-indicators">
                <span class="big-chart-indicator active" data-slide="0"></span>
                <span class="big-chart-indicator" data-slide="1"></span>
                <span class="big-chart-indicator" data-slide="2"></span>
                <span class="big-chart-indicator" data-slide="3"></span>
                <span class="big-chart-indicator" data-slide="4"></span>
              </div>
              
              <button class="big-chart-nav-btn next-btn" id="nextChartBtn" aria-label="Next Chart">
                <i class="fas fa-chevron-right"></i>
              </button>
            </div>
          </div>

          <!-- Right Side: Stat Cards (All Visible) -->
          <div class="stats-panel">
            <div class="stats-container">
              <div class="stat-card-vertical" data-index="0">
                <div class="stat-icon">
                  <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                  <div class="stat-number"><?php echo $totalResidents; ?></div>
                  <div class="stat-label">Total Residents</div>
                  <div class="stat-description">Registered residents in Barangay 498</div>
                </div>
              </div>
              <div class="stat-card-vertical" data-index="1">
                <div class="stat-icon">
                  <i class="fas fa-mars"></i>
                </div>
                <div class="stat-info">
                  <div class="stat-number"><?php echo $maleCount; ?></div>
                  <div class="stat-label">Male</div>
                  <div class="stat-description">Male population count</div>
                </div>
              </div>
              <div class="stat-card-vertical" data-index="2">
                <div class="stat-icon">
                  <i class="fas fa-venus"></i>
                </div>
                <div class="stat-info">
                  <div class="stat-number"><?php echo $femaleCount; ?></div>
                  <div class="stat-label">Female</div>
                  <div class="stat-description">Female population count</div>
                </div>
              </div>
              <div class="stat-card-vertical" data-index="3">
                <div class="stat-icon">
                  <i class="fas fa-user-clock"></i>
                </div>
                <div class="stat-info">
                  <div class="stat-number"><?php echo $seniorsCount; ?></div>
                  <div class="stat-label">Seniors</div>
                  <div class="stat-description">Residents aged 60 and above</div>
                </div>
              </div>
              <div class="stat-card-vertical" data-index="4">
                <div class="stat-icon">
                  <i class="fas fa-vote-yea"></i>
                </div>
                <div class="stat-info">
                  <div class="stat-number"><?php echo $votersCount; ?></div>
                  <div class="stat-label">Total Voters</div>
                  <div class="stat-description">Eligible voters (18 years and above)</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Scroll Indicator -->
        <div class="scroll-indicator" id="scrollIndicator">
          <div class="scroll-indicator-content">
            <p>Scroll to see more</p>
            <div class="scroll-arrow">
              <i class="fas fa-chevron-down"></i>
            </div>
          </div>
        </div>

        <!-- Bottom Section: Recent Activity & Quick Stats -->
        <div class="dashboard-bottom-section">
          <!-- Recent Activity Feed -->
          <div class="activity-feed-widget">
            <div class="widget-header">
              <div class="widget-header-content">
                <i class="fas fa-history"></i>
                <h3>Recent Activity</h3>
              </div>
            </div>
            <div class="activity-list" id="activityList">
              <div class="activity-item">
                <div class="activity-icon">
                  <i class="fas fa-file-check"></i>
                </div>
                <div class="activity-content">
                  <p class="activity-text">New document request received</p>
                  <span class="activity-time">Just now</span>
                </div>
              </div>
              <div class="activity-item">
                <div class="activity-icon">
                  <i class="fas fa-user-plus"></i>
                </div>
                <div class="activity-content">
                  <p class="activity-text">New resident registered</p>
                  <span class="activity-time">5 minutes ago</span>
                </div>
              </div>
              <div class="activity-item">
                <div class="activity-icon">
                  <i class="fas fa-bullhorn"></i>
                </div>
                <div class="activity-content">
                  <p class="activity-text">Announcement published</p>
                  <span class="activity-time">1 hour ago</span>
                </div>
              </div>
              <div class="activity-item">
                <div class="activity-icon">
                  <i class="fas fa-check-circle"></i>
                </div>
                <div class="activity-content">
                  <p class="activity-text">Document request completed</p>
                  <span class="activity-time">2 hours ago</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Stats Summary -->
          <div class="quick-stats-widget">
            <div class="widget-header">
              <div class="widget-header-content">
                <i class="fas fa-chart-line"></i>
                <h3>Quick Stats</h3>
              </div>
            </div>
            <div class="quick-stats-grid">
              <div class="quick-stat-item">
                <div class="quick-stat-icon pending">
                  <i class="fas fa-clock"></i>
                </div>
                <div class="quick-stat-info">
                  <div class="quick-stat-number" id="pendingCount">0</div>
                  <div class="quick-stat-label">Pending Requests</div>
                </div>
              </div>
              <div class="quick-stat-item">
                <div class="quick-stat-icon processing">
                  <i class="fas fa-spinner"></i>
                </div>
                <div class="quick-stat-info">
                  <div class="quick-stat-number" id="processingCount">0</div>
                  <div class="quick-stat-label">In Process</div>
                </div>
              </div>
              <div class="quick-stat-item">
                <div class="quick-stat-icon completed">
                  <i class="fas fa-check-circle"></i>
                </div>
                <div class="quick-stat-info">
                  <div class="quick-stat-number" id="completedCount">0</div>
                  <div class="quick-stat-label">Completed</div>
                </div>
              </div>
              <div class="quick-stat-item">
                <div class="quick-stat-icon ready">
                  <i class="fas fa-check-double"></i>
                </div>
                <div class="quick-stat-info">
                  <div class="quick-stat-number" id="readyCount">0</div>
                  <div class="quick-stat-label">Ready for Pickup</div>
                </div>
              </div>
            </div>
          </div>
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
