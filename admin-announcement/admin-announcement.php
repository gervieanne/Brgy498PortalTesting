<?php

// 6 hours each for Session Configuration
ini_set('session.gc_maxlifetime', 21600); 
ini_set('session.cookie_lifetime', 21600);

// Once open, session will start
session_start();

// Database connection Setup 
// this is all standard for localhost/localdev
$servername = "localhost"; 
$username = "root";
$password = "";
$dbname = "498portal";

// to create a new server:
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to handle special characters properly
$conn->set_charset("utf8mb4");

// 
// * =============================================
// * HANDLER: Add New Announcement
// * ===========================================
// * POST triggers request with 'confirm_post' parameter
// * [This is to insert new announcement info after preview confirmation]
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_post'])) {

  // to sanitize data, remove the extra whitespace and escape specialc characters
    $title = $conn->real_escape_string(trim($_POST['title']));
    $message = $conn->real_escape_string(trim($_POST['message']));
    $scheduled_date = $_POST['scheduled_date'];

  // send to all by default
    $send_to = 'all';
    
    // =============================
    // FOR IMAGE AND VIDEO HANDLERS
    // =============================
    // We use NULL if there is no file upload
    $image_path = isset($_POST['temp_image_path']) && !empty($_POST       ['temp_image_path']) 
                ? $_POST['temp_image_path'] : NULL;
    
    $video_path = isset($_POST['temp_video_path']) && !empty($_POST['temp_video_path']) 
                ? $_POST['temp_video_path'] : NULL;
    
    // Standard SQL statement to prevent SQL injection
    // placeholders
    $sql = "INSERT INTO announcements (title, message, send_to, scheduled_date, image_path, video_path, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";

    // Statement
    $stmt = $conn->prepare($sql);
    
    // s = string path,
    // title, message, send_to, scheduled_date, image_path, video_path
    $stmt->bind_param("ssssss", $title, $message, $send_to, $scheduled_date, $image_path, $video_path);
    
    // Execute prepare statement, check the result
    if ($stmt->execute()) {
        $_SESSION['announcement_success'] = true;
        $_SESSION['announcement_message'] = "Announcement posted successfully!";
    } else {
        $_SESSION['announcement_error'] = true;
        $_SESSION['announcement_message'] = "Failed to post announcement: " . $conn->error;
    }
    $stmt->close();
    
    // prevent submission refresh
    header("Location: admin-announcement.php");
    exit();
}

// 
// * =============================================
// * HANDLER: Temporary File Upload
// * ===========================================
// * AJAX POST triggers request with 'upload_temp_file' parameter
// * [This is to handle file uploads before confirmation of announcement]

// * Allowed file types:
// * Images: jpg, jpeg, png, gif, webp
// * Videos: mp4, avi, mov, wmv, webm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_temp_file'])) {

    $response = ['success' => false];
    
    // Check if file was uploaded without errors
    if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
        $target_dir = "uploads/announcements/"; // Directory path
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        } // if it doesnt exist, there will be creation of directory
        
        // extracts the file extension to be able to convert to lowercase
        $file_extension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION)); 
        $new_filename = uniqid() . '.' . $file_extension; // this then generates a unique file name
        // usually timestamp and random number
        $target_file = $target_dir . $new_filename; // full file path
        
        // Below allowed file types
        $allowed_image_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $allowed_video_types = ['mp4', 'avi', 'mov', 'wmv', 'webm'];
        
        // Below checcks if the file extension is in allowed list
        if (in_array($file_extension, $allowed_image_types) || in_array($file_extension, $allowed_video_types)) {

            // This moves temporary upload file to target OR created directory
            if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
                $response = ['success' => true, 'path' => $target_file];
            }
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// 
// * =============================================
// * HANDLER: Edit Posted Announcement
// * ===========================================
// * POST triggers request with 'edit_announcement' parameter
// * [This is to update announcement details, including media managemetn]
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_announcement'])) {

    // using past data (past announcement), it will be able to
    // retrieve and validate announcement Id
    $announcement_id = intval($_POST['announcement_id']);
    
    // 'real_escape_string' - sanitize text inputs once again
    $title = $conn->real_escape_string(trim($_POST['edit_title']));
    $message = $conn->real_escape_string(trim($_POST['edit_message']));
    $scheduled_date = $_POST['edit_scheduled_date'];

    // Check if media removal is requested
    // '1' means user clicked remove button in editModal
    $remove_image = isset($_POST['remove_image']) && $_POST['remove_image'] == '1';
    $remove_video = isset($_POST['remove_video']) && $_POST['remove_video'] == '1';

    // Fetch the existing paths from the database
    $existing_sql = "SELECT image_path, video_path FROM announcements WHERE announcement_id = ?";
    $existing_stmt = $conn->prepare($existing_sql);
    $existing_stmt->bind_param("i", $announcement_id);
    $existing_stmt->execute();
    $existing_result = $existing_stmt->get_result();
    $existing_data = $existing_result->fetch_assoc();
    $existing_stmt->close();

    // To verify if the data exists
    if (!$existing_data) {
        $_SESSION['announcement_error'] = true;
        $_SESSION['announcement_message'] = "Announcement not found!";
        header("Location: admin-announcement.php");
        exit();
    }

    // existing values
    $image_path = $existing_data['image_path'];
    $video_path = $existing_data['video_path'];

    

    // BY 'remove_image' & and 'image_path' it will set NULL to database
    // unlink - delete file from the server
    
    // Handle image removal
    if ($remove_image && $image_path && file_exists($image_path)) {
        unlink($image_path);
        $image_path = NULL; 
    }

    // Handle video removal
    if ($remove_video && $video_path && file_exists($video_path)) {
        unlink($video_path);
        $video_path = NULL;
    }

    // NEW IMAGE upload
    // When you click browse image :
    if (isset($_FILES['edit_image']) && $_FILES['edit_image']['error'] === 0) {
        $target_dir = "uploads/announcements/";

        // New directory if it doesnt exist
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);

        // Deletion old image if exists
        if ($image_path && file_exists($image_path)) unlink($image_path);

        // Generation of unique file name for the new uploaded image
        $file_extension = strtolower(pathinfo($_FILES['edit_image']['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
      // Update the path of uploaded file and move it to the target file
        if (move_uploaded_file($_FILES['edit_image']['tmp_name'], $target_file)) {
            $image_path = $target_file;
        }
    }

    // NEW VIDEO upload
    // When you click new video upload:
    if (isset($_FILES['edit_video']) && $_FILES['edit_video']['error'] === 0) {

       
        $target_dir = "uploads/announcements/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);

        // Deletion old video if exists
        if ($video_path && file_exists($video_path)) unlink($video_path);

        $file_extension = strtolower(pathinfo($_FILES['edit_video']['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['edit_video']['tmp_name'], $target_file)) {
            $video_path = $target_file;
        }
    }

    // Update announcement in database
    $sql = "UPDATE announcements SET title=?, message=?, scheduled_date=?, image_path=?, video_path=?, updated_at=NOW() WHERE announcement_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $title, $message, $scheduled_date, $image_path, $video_path, $announcement_id);
    
    if ($stmt->execute()) {
        $_SESSION['announcement_updated'] = true;
        $_SESSION['announcement_message'] = "Announcement updated successfully!";
    } else {
        $_SESSION['announcement_error'] = true;
        $_SESSION['announcement_message'] = "Failed to update announcement: " . $conn->error;
    }
    $stmt->close();

    header("Location: admin-announcement.php");
    exit();
}

// Handle Delete Announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_announcement'])) {
    $announcement_id = intval($_POST['announcement_id']);
    
    // Get file paths before deletion
    $sql = "SELECT image_path, video_path FROM announcements WHERE announcement_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $announcement_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    
    if ($data) {
        // Delete files from server
        if ($data['image_path'] && file_exists($data['image_path'])) {
            unlink($data['image_path']);
        }
        if ($data['video_path'] && file_exists($data['video_path'])) {
            unlink($data['video_path']);
        }
        
        // Delete from database
        $sql = "DELETE FROM announcements WHERE announcement_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $announcement_id);
        
        if ($stmt->execute()) {
            $_SESSION['announcement_deleted'] = true;
            $_SESSION['announcement_message'] = "Announcement deleted successfully!";
        } else {
            $_SESSION['announcement_error'] = true;
            $_SESSION['announcement_message'] = "Failed to delete announcement: " . $conn->error;
        }
        $stmt->close();
    }
    
    header("Location: admin-announcement.php");
    exit();
}

// Fetch all announcements ordered by scheduled_date (most recent first)
$announcements_sql = "SELECT * FROM announcements ORDER BY scheduled_date DESC, created_at DESC";
$announcements_result = $conn->query($announcements_sql);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="../admin-announcement/admin-announcement.css" />
    <link rel="stylesheet" href="../preloader/preloader.css" />
    <link rel="stylesheet" href="../logout-modal.css" />

    <title>Announcement</title>
  </head>
  <body>
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
          <li>
            <a href="../admin-dashboard.php">Dashboard</a>
          </li>
          <li><a href="../admin-officials/admin-officials.php">Officials</a></li>
          <li>
            <a href="../admin-residents-info/admin-residents.php"
              >Residents Information</a
            >
          </li>
          <li><a href="../admin-calendar/admin-calendar.php">Calendar</a></li>
          <li>
            <a href="../admin-document-req/admin-document-requests.php"
              >Document Requests</a
            >
          </li>
          <li>
            <a href="../admin-announcement/admin-announcement.php" class="active">Announcement</a>
          </li>
        </ul>
      </nav>
    </div>

    <div class="main-container">
      <div class="header">
        <div class="clock" id="clock">12:00:00 AM</div>
        <a href="../landingpage/index.php" class="logout-logo" id="logoutBtn">
          <img src="../images/logout.png" alt="logout" class="logout-logo" />
        </a>
      </div>

      <div class="content-box">
        <div class="content-header">Create Announcement</div>

        <form class="announcement-form" id="announcementForm" enctype="multipart/form-data">
          <div class="form-group">
            <label>Title</label>
            <input
              type="text"
              id="titleInput"
              placeholder="Enter announcement title"
              required
            />
          </div>

          <div class="form-group">
            <label>Schedule Date and Time of Announcement</label>
            <div class="datetime-container">
            <input type="date" id="dateInput" required />
            <input type="time" id="timeInput" required />
            </div>
          </div>

          <div class="form-group full-width">
            <label>Message</label>
            <div class="textarea-wrapper">
              <textarea
                id="messageInput"
                placeholder="Write your announcement here..."
                required
                class="message-input"
              ></textarea>
              <div class="input-icons">
                <button
                  type="button"
                  class="icon-btn"
                  id="linkBtn"
                  data-tooltip="Add Link"
                  title="Add Link"
                >
                  <img src="../images/link-logo.png" alt="link-logo" />
                </button>
                <button
                  type="button"
                  class="icon-btn"
                  id="imageBtn"
                  data-tooltip="Add Image"
                  title="Add Image"
                >
                  <img
                    src="../images/attachphoto-logo.png"
                    alt="attachphoto-logo"
                  />
                </button>
                <button
                  type="button"
                  class="icon-btn"
                  id="videoBtn"
                  data-tooltip="Add Video"
                  title="Add Video"
                >
                  <img
                    src="../images/attachvideo-logo.png"
                    alt="attachvideo-logo"
                  />
                </button>
              </div>
            </div>
          </div>

          <div class="form-group full-width">
            <button type="button" onclick="showPreview()" class="send-btn">PREVIEW ANNOUNCEMENT</button>
          </div>

          <!-- Hidden file inputs -->
          <input
            type="file"
            id="imageInput"
            class="hidden-input"
            accept="image/*"
          />
          <input
            type="file"
            id="videoInput"
            class="hidden-input"
            accept="video/*"
          />
        </form>
      </div>

      <!-- Announcement History Section -->
      <div class="content-box history-box">
        <div class="content-header">Announcement History</div>
        
        <div class="announcements-table">
          <table>
            <thead>
              <tr>
                <th>Title</th>
                <th>Scheduled Date</th>
                <th>Created At</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($announcements_result && $announcements_result->num_rows > 0): ?>
                <?php while($announcement = $announcements_result->fetch_assoc()): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($announcement['title']); ?></td>
                    <td><?php echo date('M d, Y h:i A', strtotime($announcement['scheduled_date'])); ?></td>
                    <td><?php echo date('M d, Y h:i A', strtotime($announcement['created_at'])); ?></td>
                    <td class="action-buttons">
                      <button class="btn-view" onclick="viewAnnouncement(<?php echo $announcement['announcement_id']; ?>)">
                        <img src="../images/view-icon.png" alt="View" title="View" />
                      </button>
                      <button class="btn-edit" onclick="editAnnouncement(<?php echo $announcement['announcement_id']; ?>)">
                        <img src="../images/edit-icon.png" alt="Edit" title="Edit" />
                      </button>
                      <button class="btn-delete" onclick="deleteAnnouncement(<?php echo $announcement['announcement_id']; ?>)">
                        <img src="../images/delete-icon.png" alt="Delete" title="Delete" />
                      </button>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="4" style="text-align: center; padding: 20px;">No announcements found</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Preview Modal -->
    <div id="previewModal" class="modal">
      <div class="modal-content preview-modal">
        <span class="close" onclick="closePreviewModal()">&times;</span>
        <h2>Preview Announcement</h2>
        
        <div class="preview-section">
          <h3>Title</h3>
          <div class="preview-content" id="previewTitle"></div>
        </div>

        <div class="preview-section">
          <h3>Scheduled Date & Time</h3>
          <div class="preview-content" id="previewDateTime"></div>
        </div>

        <div class="preview-section">
          <h3>Message</h3>
          <div class="preview-content" id="previewMessage"></div>
        </div>

        <div id="previewImageContainer"></div>
        <div id="previewVideoContainer"></div>

        <form method="POST" id="confirmPostForm">
          <input type="hidden" name="title" id="confirmTitle">
          <input type="hidden" name="message" id="confirmMessage">
          <input type="hidden" name="scheduled_date" id="confirmDateTime">
          <input type="hidden" name="temp_image_path" id="confirmImagePath">
          <input type="hidden" name="temp_video_path" id="confirmVideoPath">
          
          <div class="modal-buttons">
            <button type="button" class="btn-cancel" onclick="closePreviewModal()">Edit</button>
            <button type="submit" name="confirm_post" class="btn-confirm-post">Confirm & Post</button>
          </div>
        </form>
      </div>
    </div>

    <!-- View Modal -->
    <div id="viewModal" class="modal">
      <div class="modal-content view-modal">
        <span class="close" onclick="closeViewModal()">&times;</span>
        <h2 id="viewTitle"></h2>
        <div class="modal-details">
          <p><strong>Scheduled:</strong> <span id="viewScheduled"></span></p>
          <div id="viewMessage"></div>
          <div id="viewImage"></div>
          <div id="viewVideo"></div>
        </div>
      </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
      <div class="modal-content">
        <span class="close" onclick="closeEditModal()">&times;</span>
        <h2>Edit Announcement</h2>
        <form id="editForm" method="POST" enctype="multipart/form-data">
          <input type="hidden" id="editAnnouncementId" name="announcement_id" />
          
          <div class="form-group">
            <label>Title</label>
            <input type="text" id="editTitle" name="edit_title" required />
          </div>

          <div class="form-group">
            <label>Message</label>
            <textarea id="editMessage" name="edit_message" required></textarea>
          </div>

          <div class="form-group">
            <label>Schedule Date and Time</label>
            <input type="datetime-local" id="editScheduledDate" name="edit_scheduled_date" required />
          </div>

          <div class="form-group">
            <label>Change Image (Optional)</label>
            <input type="file" name="edit_image" id="editImageInput" accept="image/*" />

            <div id="currentImageWrapper" class="media-preview" style="display: none;">
              <img id="currentImagePreview" src="" alt="Current Image" style="max-width: 100%; border-radius: 6px;">
              <span id="removeImage" class="remove-media-btn">🗑️ Remove Image</span>
            </div>
          </div>

          <div class="form-group">
            <label>Change Video (Optional)</label>
            <input type="file" name="edit_video" id="editVideoInput" accept="video/*" />

            <div id="currentVideoWrapper" class="media-preview" style="display: none;">
              <video id="currentVideoPreview" controls style="max-width: 100%; border-radius: 6px;">
                <source src="" type="video/mp4">
              </video>
              <span id="removeVideo" class="remove-media-btn">🗑️ Remove Video</span>
            </div>
          </div>

          <input type="hidden" name="remove_image" id="removeImageInput" value="0">
          <input type="hidden" name="remove_video" id="removeVideoInput" value="0">

          <div class="modal-buttons">
            <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
            <button type="submit" name="edit_announcement" class="btn-save">Save Changes</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="delete-modal">
      <div class="delete-modal-content">
        <div class="delete-icon">⚠️</div>
        <h2>Delete Announcement</h2>
        <p>Are you sure you want to delete this announcement? This action cannot be undone.</p>
        <form id="deleteForm" method="POST">
          <input type="hidden" id="deleteAnnouncementId" name="announcement_id" />
          <div class="delete-modal-buttons">
            <button type="button" class="btn-cancel delete-btn-cancel" onclick="closeDeleteModal()">Cancel</button>
            <button type="submit" name="delete_announcement" class="btn-delete-confirm delete-btn-confirm">Delete</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Link input modal -->
     <div id="linkModal" class="link-modal">
      <div class="link-modal-content">
        <div class="link-modal-header">
          <h2>Add Link</h2>
          <span class="close" onclick="closeLinkModal()">&times;</span>
        </div>
        <form id="linkForm" onsubmit="insertLink(event)">
          <div class="form-group">
            <label for="linkText">Text:</label>
            <input type="text" id="linkText" placeholder="e.g. Click here" required/>
          </div>
          <div class="form-group">
            <label for="linkURL">Link/URL:</label>
            <input type="url" id="linkURL" placeholder="e.g. https://example.com" required/>
            </div>
          <div class="link-modal-buttons">
            <button type="button" class="link-btn-cancel" onclick="closeLinkModal()">Cancel</button>
            <button type="submit" class="link-btn-add">Add Link</button>
          </div>
          </form>
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

    <script src="../preloader/preloader.js"></script>
    <script src="../logout-modal.js"></script>
    <script>
      // Check session and show popup messages
      <?php if (isset($_SESSION['announcement_success'])): ?>
        window.addEventListener('DOMContentLoaded', () => {
          showPopupMessage('<?php echo $_SESSION['announcement_message'] ?? 'Announcement posted successfully!'; ?>', 'success');
        });
        <?php unset($_SESSION['announcement_success']); unset($_SESSION['announcement_message']); ?>
      <?php endif; ?>

      <?php if (isset($_SESSION['announcement_updated'])): ?>
        window.addEventListener('DOMContentLoaded', () => {
          showPopupMessage('<?php echo $_SESSION['announcement_message'] ?? 'Announcement updated successfully!'; ?>', 'success');
        });
        <?php unset($_SESSION['announcement_updated']); unset($_SESSION['announcement_message']); ?>
      <?php endif; ?>

      <?php if (isset($_SESSION['announcement_deleted'])): ?>
        window.addEventListener('DOMContentLoaded', () => {
          showPopupMessage('<?php echo $_SESSION['announcement_message'] ?? 'Announcement deleted successfully!'; ?>', 'success');
        });
        <?php unset($_SESSION['announcement_deleted']); unset($_SESSION['announcement_message']); ?>
      <?php endif; ?>

      <?php if (isset($_SESSION['announcement_error'])): ?>
        window.addEventListener('DOMContentLoaded', () => {
          showPopupMessage('<?php echo $_SESSION['announcement_message'] ?? 'An error occurred!'; ?>', 'error');
        });
        <?php unset($_SESSION['announcement_error']); unset($_SESSION['announcement_message']); ?>
      <?php endif; ?>

      // Popup message function
      function showPopupMessage(message, type = 'success') {
        let popup = document.getElementById('popupMessage');
        if (!popup) {
          popup = document.createElement('div');
          popup.id = 'popupMessage';
          popup.style.position = 'fixed';
          popup.style.top = '20px';
          popup.style.right = '20px';
          popup.style.padding = '12px 18px';
          popup.style.borderRadius = '8px';
          popup.style.fontSize = '15px';
          popup.style.fontWeight = '500';
          popup.style.color = '#fff';
          popup.style.zIndex = '9999';
          popup.style.transition = 'opacity 0.5s ease';
          popup.style.boxShadow = '0 4px 10px rgba(0,0,0,0.2)';
          document.body.appendChild(popup);
        }

        popup.style.background = type === 'error' ? '#e74c3c' : '#4CAF50';
        popup.textContent = message;
        popup.style.opacity = '1';
        popup.style.display = 'block';

        setTimeout(() => {
          popup.style.opacity = '0';
          setTimeout(() => {
            popup.style.display = 'none';
          }, 500);
        }, 3000);
      }

      // Store announcements data
      const announcementsData = <?php 
        if ($announcements_result) {
          $announcements_result->data_seek(0);
          $announcements = [];
          while($row = $announcements_result->fetch_assoc()) {
            $announcements[] = $row;
          }
          echo json_encode($announcements);
        } else {
          echo '[]';
        }
      ?>;

      let tempImagePath = null;
      let tempVideoPath = null;
      let imagePreviewUrl = null;
      let videoPreviewUrl = null;

      // Clock functionality
      function updateClock() {
        const now = new Date();
        let hours = now.getHours();
        const minutes = now.getMinutes().toString().padStart(2, "0");
        const seconds = now.getSeconds().toString().padStart(2, "0");
        const ampm = hours >= 12 ? "PM" : "AM";

        hours = hours % 12;
        hours = hours ? hours : 12;
        hours = hours.toString().padStart(2, "0");

        document.getElementById("clock").textContent = `${hours}:${minutes}:${seconds} ${ampm}`;
      }

      setInterval(updateClock, 1000);
      updateClock();

      // Burger menu functionality
      const burgerMenu = document.getElementById("burgerMenu");
      const sidebar = document.getElementById("sidebar");

      burgerMenu.addEventListener("click", () => {
        sidebar.classList.toggle("active");
      });

      document.addEventListener("click", (e) => {
        if (window.innerWidth <= 1024) {
          if (!sidebar.contains(e.target) && !burgerMenu.contains(e.target)) {
            sidebar.classList.remove("active");
          }
        }
      });

      // Link button functionality
      document.getElementById("linkBtn").addEventListener("click", () => {
        document.getElementById('linkModal').style.display = 'block';
      });

      function closeLinkModal() {
        document.getElementById('linkModal').style.display = 'none';
        document.getElementById('linkForm').reset();
      }

      function insertLink(e) {
        e.preventDefault();
        const text = document.getElementById('linkText').value;
        const url = document.getElementById('linkURL').value;
        const textarea = document.getElementById('messageInput');
        
        // Insert markdown-style link
        const link = `[${text}](${url})`;
        textarea.value += (textarea.value ? '\n' : '') + link;
        
        closeLinkModal();
        showPopupMessage('Link inserted successfully!', 'success');
      }

      // Convert markdown links to HTML
      function convertLinksToHtml(text) {
        return text.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" style="color: #2196f3; text-decoration: underline;">$1</a>')
                  .replace(/\n/g, '<br>');
      }

      document.getElementById("imageBtn").addEventListener("click", () => {
        document.getElementById("imageInput").click();
      });

      document.getElementById("videoBtn").addEventListener("click", () => {
        document.getElementById("videoInput").click();
      });

      // Handle image upload
      document.getElementById("imageInput").addEventListener("change", async (e) => {
        const file = e.target.files[0];
        if (file) {
          const formData = new FormData();
          formData.append('file', file);
          formData.append('upload_temp_file', '1');

          try {
            const response = await fetch('admin-announcement.php', {
              method: 'POST',
              body: formData
            });
            const result = await response.json();
            
            if (result.success) {
              tempImagePath = result.path;
              imagePreviewUrl = URL.createObjectURL(file);
              showPopupMessage("Image uploaded successfully!\n" + file.name, 'success');
            } else {
              showPopupMessage("Failed to upload image", 'error');
            }
          } catch (error) {
            showPopupMessage("Error uploading image", 'error');
          }
        }
      });

      // Handle video upload
      document.getElementById("videoInput").addEventListener("change", async (e) => {
        const file = e.target.files[0];
        if (file) {
          const formData = new FormData();
          formData.append('file', file);
          formData.append('upload_temp_file', '1');

          try {
            const response = await fetch('admin-announcement.php', {
              method: 'POST',
              body: formData
            });
            const result = await response.json();
            
            if (result.success) {
              tempVideoPath = result.path;
              videoPreviewUrl = URL.createObjectURL(file);
              showPopupMessage("Video uploaded successfully!\n" + file.name, 'success');
            } else {
              showPopupMessage("Failed to upload video", 'error');
            }
          } catch (error) {
            showPopupMessage("Error uploading video", 'error');
          }
        }
      });

      // Show preview modal
      function showPreview() {
        const title = document.getElementById('titleInput').value.trim();
        const message = document.getElementById('messageInput').value.trim();
        const date = document.getElementById('dateInput').value;
        const time = document.getElementById('timeInput').value;

        if (!title || !message || !date || !time) {
          showPopupMessage('Please fill in all required fields', 'error');
          return;
        }

        // Combine date and time
        const dateTime = date + ' ' + time;

        document.getElementById('previewTitle').textContent = title;
        document.getElementById('previewMessage').innerHTML = convertLinksToHtml(message);
        document.getElementById('previewDateTime').textContent = new Date(dateTime).toLocaleString('en-US', {
          year: 'numeric',
          month: 'long',
          day: 'numeric',
          hour: 'numeric',
          minute: '2-digit',
          hour12: true
        });

        // Set hidden form values
        document.getElementById('confirmTitle').value = title;
        document.getElementById('confirmMessage').value = message;
        document.getElementById('confirmDateTime').value = dateTime;
        document.getElementById('confirmImagePath').value = tempImagePath || '';
        document.getElementById('confirmVideoPath').value = tempVideoPath || '';

        // Show image preview
        const imageContainer = document.getElementById('previewImageContainer');
        if (imagePreviewUrl) {
          imageContainer.innerHTML = '<div class="preview-media"><h3>Image Preview</h3><img src="' + imagePreviewUrl + '" alt="Preview" /></div>';
        } else {
          imageContainer.innerHTML = '';
        }

        // Show video preview
        const videoContainer = document.getElementById('previewVideoContainer');
        if (videoPreviewUrl) {
          videoContainer.innerHTML = '<div class="preview-media"><h3>Video Preview</h3><video controls><source src="' + videoPreviewUrl + '" /></video></div>';
        } else {
          videoContainer.innerHTML = '';
        }

        document.getElementById('previewModal').style.display = 'block';
      }

      function closePreviewModal() {
        document.getElementById('previewModal').style.display = 'none';
      }

      // View Announcement
      function viewAnnouncement(id) {
        const announcement = announcementsData.find(a => a.announcement_id == id);
        if (!announcement) return;

        document.getElementById('viewTitle').textContent = announcement.title;
        document.getElementById('viewScheduled').textContent = new Date(announcement.scheduled_date).toLocaleString('en-US', {
          year: 'numeric',
          month: 'long',
          day: 'numeric',
          hour: 'numeric',
          minute: '2-digit',
          hour12: true
        });
        document.getElementById('viewMessage').innerHTML = '<p>' + convertLinksToHtml(announcement.message) + '</p>';
        
        const imageDiv = document.getElementById('viewImage');
        if (announcement.image_path) {
          imageDiv.innerHTML = '<img src="' + announcement.image_path + '" alt="Announcement Image" style="max-width: 100%; margin-top: 15px; border-radius: 8px;" />';
        } else {
          imageDiv.innerHTML = '';
        }

        const videoDiv = document.getElementById('viewVideo');
        if (announcement.video_path) {
          videoDiv.innerHTML = '<video controls style="max-width: 100%; margin-top: 15px; border-radius: 8px;"><source src="' + announcement.video_path + '" /></video>';
        } else {
          videoDiv.innerHTML = '';
        }

        document.getElementById('viewModal').style.display = 'block';
      }

      function closeViewModal() {
        document.getElementById('viewModal').style.display = 'none';
      }

      // Edit Announcement
      function setupRemoveMediaButtons(hasImage, hasVideo) {
        const removeImageBtn = document.getElementById("removeImage");
        const removeVideoBtn = document.getElementById("removeVideo");
        const removeImageInput = document.getElementById("removeImageInput");
        const removeVideoInput = document.getElementById("removeVideoInput");
        const imageWrapper = document.getElementById("currentImageWrapper");
        const videoWrapper = document.getElementById("currentVideoWrapper");

        if (hasImage) {
          removeImageBtn.onclick = () => {
            imageWrapper.style.display = "none";
            removeImageInput.value = "1";
            showPopupMessage("Image will be removed on save", "success");
          };
        }

        if (hasVideo) {
          removeVideoBtn.onclick = () => {
            videoWrapper.style.display = "none";
            removeVideoInput.value = "1";
            showPopupMessage("Video will be removed on save", "success");
          };
        }
      }

      function editAnnouncement(id) {
        const announcement = announcementsData.find(a => a.announcement_id == id);
        if (!announcement) return;

        document.getElementById('editAnnouncementId').value = announcement.announcement_id;
        document.getElementById('editTitle').value = announcement.title;
        document.getElementById('editMessage').value = announcement.message;
        
        // Format datetime for datetime-local input
        const scheduledDate = new Date(announcement.scheduled_date);
        const year = scheduledDate.getFullYear();
        const month = String(scheduledDate.getMonth() + 1).padStart(2, '0');
        const day = String(scheduledDate.getDate()).padStart(2, '0');
        const hours = String(scheduledDate.getHours()).padStart(2, '0');
        const minutes = String(scheduledDate.getMinutes()).padStart(2, '0');
        document.getElementById('editScheduledDate').value = `${year}-${month}-${day}T${hours}:${minutes}`;

        // Image preview setup
        const imageWrapper = document.getElementById('currentImageWrapper');
        const imagePreview = document.getElementById('currentImagePreview');
        const hasImage = !!announcement.image_path;

        if (hasImage) {
          imagePreview.src = announcement.image_path;
          imageWrapper.style.display = "block";
        } else {
          imageWrapper.style.display = "none";
        }

        // Video preview setup
        const videoWrapper = document.getElementById('currentVideoWrapper');
        const videoPreview = document.getElementById('currentVideoPreview');
        const hasVideo = !!announcement.video_path;

        if (hasVideo) {
          videoPreview.querySelector('source').src = announcement.video_path;
          videoPreview.load();
          videoWrapper.style.display = "block";
        } else {
          videoWrapper.style.display = "none";
        }

        // Reset remove flags
        document.getElementById('removeImageInput').value = "0";
        document.getElementById('removeVideoInput').value = "0";

        // Attach event handlers
        setupRemoveMediaButtons(hasImage, hasVideo);

        // Show modal
        document.getElementById('editModal').style.display = 'block';
      }

      function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
      }

      // Delete Announcement
      function deleteAnnouncement(id) {
        document.getElementById('deleteAnnouncementId').value = id;
        document.getElementById('deleteModal').style.display = 'flex';
      }

      function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
      }

      // Close modals when clicking outside
      window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
          event.target.style.display = 'none';
        }
      }
    </script>
  </body>
</html>

<?php
$conn->close();
?>