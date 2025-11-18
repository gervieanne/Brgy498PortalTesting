<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../landingpage/landingpage.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
      rel="stylesheet"
    />
    <title>Barangay 498</title>
  </head>

  <body>
    <div class="choosing-page">
      <div class="left-landingpage">
        <img
          src="../images/barangay-logo.png"
          class="barangay-logo"
          alt="barangay-logo"
        />
        <h1>Barangay Management System</h1>
        <p class="sub-content">
          Mabuhay! This is the official platform of Barangay 498, Zone 49, District IV, Manila, designed to streamline operations and enhance public service delivery. It provides a centralized system for requesting documents, managing records, announcements, and community information. Through this platform, the barangay upholds transparency, accountability, and efficient communication with its constituents.
        </p>
        <p class="footer">
          Where community service meets innovation.
        </p>
      </div>

      <div class="right-landingpage">
        <h2 class="right-heading">Welcome!</h2>
        <p class="right-sub">Choose how you want to Sign In</p>

        <a href="../admin-login/admin-login.php" class="admin-login">
          <div class="admin-card">
            <img
              src="../images/admin-logo.png"
              alt="admin-logo"
              class="admin-logo"
            />
            <h2>Sign in as Admin</h2>
            <p>Access Dashboard, Manage Documents, Scheduling, Announcing</p>
          </div>
        </a>

        <a href="../user-login/user-login.php" class="user-login">
          <div class="user-card">
            <img
              src="../images/user-logo.png"
              alt="user-logo"
              class="user-logo"
            />
            <h2>Sign in as User</h2>
            <p>View Dashboard, Request, Documents, View Announcement</p>
          </div>
        </a>
      </div>
    </div>

    <!-- Scroll Arrow -->
    <div class="down-arrow" id="scrollArrow">
      <svg viewBox="0 0 24 24">
        <path d="M12 5v14M19 12l-7 7-7-7" />
      </svg>
    </div>
  </body>
  <script src="../user-login/user-login.js"></script>
</html>
