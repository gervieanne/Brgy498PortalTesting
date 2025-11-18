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


// Handle Add Official
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_official'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $position = $conn->real_escape_string($_POST['position']);
    
    // Handle image upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $target_dir = "uploads/officials/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = $target_file;
        }
    }
    
    $sql = "INSERT INTO barangay_officials_db (name, position, image_path) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $position, $image_path);
    $stmt->execute();
    $stmt->close();
    
    header("Location: ../admin-officials/admin-officials.php");
    exit();
}

// Handle Edit Official
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_official'])) {
    $id = intval($_POST['id']);
    $name = $conn->real_escape_string($_POST['name']);
    $position = $conn->real_escape_string($_POST['position']);
    
    // Handle image upload or removal
    $image_path = $_POST['existing_image'];
    
    // Check if user wants to remove photo
    if (isset($_POST['remove_photo']) && $_POST['remove_photo'] === '1') {
        // Delete old image if exists
        if ($image_path && file_exists($image_path)) {
            unlink($image_path);
        }
        $image_path = null;
    }
    // Check if new image is uploaded
    elseif (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $target_dir = "uploads/officials/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        // Delete old image if exists
        if ($image_path && file_exists($image_path)) {
            unlink($image_path);
        }
        
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = $target_file;
        }
    }
    
    $sql = "UPDATE barangay_officials_db SET name = ?, position = ?, image_path = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $name, $position, $image_path, $id);
    $stmt->execute();
    $stmt->close();
    
    header("Location: ../admin-officials/admin-officials.php");
    exit();
}

// Handle Delete Official
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Get image path before deleting
    $sql = "SELECT image_path FROM barangay_officials_db WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $official = $result->fetch_assoc();
    
    // Delete image file if exists
    if ($official && $official['image_path'] && file_exists($official['image_path'])) {
        unlink($official['image_path']);
    }
    
    // Delete from database
    $sql = "DELETE FROM barangay_officials_db WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    
    header("Location: ../admin-officials/admin-officials.php");
    exit();
}

// Fetch all officials
$sql = "SELECT * FROM barangay_officials_db ORDER BY id ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../admin-officials/admin-officials.css" />
    <link rel="stylesheet" href="../preloader/preloader.css" />
    <link rel="stylesheet" href="../logout-modal.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
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
            <li>
              <a href="../admin-dashboard.php">Dashboard</a>
            </li>
            <li>
              <a href="../admin-officials/admin-officials.php" class="officials-link"
                >Officials</a
              >
            </li>
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
              <a href="../admin-announcement/admin-announcement.php">Announcement</a>
            </li>
          </ul>
        </nav>
      </div>

      <!-- Header -->
      <div class="right-container">
        <header class="officials-header">
          <div class="time-and-logo">
            <p id="clock">12:00:00 AM</p>
            <a href="#" id="logoutBtn">
              <img src="../images/logout.png" class="logout-logo" />
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
          <button onclick="openModal()" class="add-btn">Add Official</button>
          
          <?php
          if ($result->num_rows > 0) {
              while($row = $result->fetch_assoc()) {
                  echo '<div class="official-card">';
                  
                  // Image section
                  if ($row['image_path'] && file_exists($row['image_path'])) {
                      echo '<div class="official-image" style="background-image: url(\'' . $row['image_path'] . '\'); background-size: cover; background-position: center;"></div>';
                  } else {
                      echo '<div class="official-image"></div>';
                  }
                  
                  // Info section
                  echo '<div class="official-info">';
                  echo '<h3>' . htmlspecialchars($row['name']) . '</h3>';
                  echo '<p>' . htmlspecialchars($row['position']) . '</p>';
                  echo '</div>';
                  
                  // Action buttons
                  echo '<div class="action-buttons">';
                  echo '<button class="edit-btn" onclick="openEditModal(' . $row['id'] . ', \'' . htmlspecialchars($row['name'], ENT_QUOTES) . '\', \'' . htmlspecialchars($row['position'], ENT_QUOTES) . '\', \'' . htmlspecialchars($row['image_path'], ENT_QUOTES) . '\')">Edit</button>';
                  echo '<button class="delete-btn" onclick="deleteOfficial(' . $row['id'] . ')">Delete</button>';
                  echo '</div>';
                  
                  echo '</div>';
              }
          } else {
              echo '<p style="grid-column: 1/-1; text-align: center; color: #666;">No officials found. Click "Add Official" to add one.</p>';
          }
          ?>
        </div>
      </div>
    </div>

    <!-- Add Official Modal -->
    <div id="addModal" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <h2>Add New Official</h2>
          <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form method="POST" enctype="multipart/form-data">
          <div class="form-group">
            <label for="name">Full Name:</label>
            <input type="text" id="name" name="name" required />
          </div>
          
          <div class="form-group">
            <label for="position">Position:</label>
            <select id="position" name="position" required>
              <option value="">Select Position</option>
              <option value="Barangay Chairman">Barangay Chairman</option>
              <option value="Barangay Kagawad">Barangay Kagawad</option>
              <option value="SK Chairman">SK Chairman</option>
              <option value="Barangay Secretary">Barangay Secretary</option>
              <option value="Barangay Treasurer">Barangay Treasurer</option>
            </select>
          </div>
          
          <div class="form-group">
            <label for="image">Photo (Optional):</label>
            <input type="file" id="image" name="image" accept="image/*" />
          </div>
          
          <button type="submit" name="add_official" class="btn-submit">Add Official</button>
        </form>
      </div>
    </div>

    <!-- Edit Official Modal -->
    <div id="editModal" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <h2>Edit Official</h2>
          <span class="close" onclick="closeEditModal()">&times;</span>
        </div>
        <form method="POST" enctype="multipart/form-data" id="editForm">
          <input type="hidden" id="edit_id" name="id" />
          <input type="hidden" id="existing_image" name="existing_image" />
          <input type="hidden" id="remove_photo" name="remove_photo" value="0" />
          
          <div class="form-group">
            <label for="edit_name">Full Name:</label>
            <input type="text" id="edit_name" name="name" required />
          </div>
          
          <div class="form-group">
            <label for="edit_position">Position:</label>
            <select id="edit_position" name="position" required>
              <option value="">Select Position</option>
              <option value="Barangay Chairman">Barangay Chairman</option>
              <option value="Barangay Kagawad">Barangay Kagawad</option>
              <option value="SK Chairman">SK Chairman</option>
              <option value="Barangay Secretary">Barangay Secretary</option>
              <option value="Barangay Treasurer">Barangay Treasurer</option>
            </select>
          </div>
          
          <div class="form-group">
            <label for="edit_image">Change Photo (Optional):</label>
            <input type="file" id="edit_image" name="image" accept="image/*" />
            <small style="color: #666; display: block; margin-top: 5px;">Leave empty to keep current photo</small>
            <div id="currentPhotoWrapper" style="margin-top: 15px; display: none;">
    
              <img id="currentPhotoPreview" src="" alt="Current Photo" style="max-width: 100%; max-height: 200px; border-radius: 8px; display: block; margin-bottom: 10px;" />
              
            
              <div style="text-align: right;">
                <span id="removePhotoBtn" style="display: inline-flex; align-items: center; cursor: pointer; color: #f44336; font-size: 14px; font-weight: 500; padding: 5px 10px; border-radius: 5px; transition: background-color 0.3s;">
                  🗑️ Remove Photo
                </span>
              </div>
            </div>
          </div>
          
          <button type="submit" name="edit_official" class="btn-submit">Update Official</button>
        </form>
      </div>
    </div>

    <!-- Delete Official Modal -->
     <div id="deleteModal" class="delete-modal">
      <div class="delete-modal-content">
          <div class="delete-modal-icon">⚠️</div>
          <h2>Delete Official</h2>
          <p>Are you sure you want to delete this official?</p>
        <div class="delete-modal-buttons">
          <button class="delete-btn-cancel" id="cancelDelete">No, Cancel</button>
          <button class="delete-btn-confirm" id="confirmDelete">Yes, Delete</button>  
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

    <script src="../preloader/preloader.js"></script>
    <script src="../admin-dashboard.js"></script>
    <script src="../logout-modal.js"></script>
    <script>
      
      function openModal() {
        document.getElementById('addModal').style.display = 'block';
      }
      
      function closeModal() {
        document.getElementById('addModal').style.display = 'none';
      }
      
      function openEditModal(id, name, position, imagePath) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_position').value = position;
        document.getElementById('existing_image').value = imagePath;
        document.getElementById('remove_photo').value = '0';
        document.getElementById('edit_image').value = '';
        document.getElementById('edit_image').disabled = false;
        
        // Gets references to the photo wrapper and preview image elements
        const photoWrapper = document.getElementById('currentPhotoWrapper');
        const photoPreview = document.getElementById('currentPhotoPreview');
        // If yes, display the photo preview; if no, hide the wrapper
        if (imagePath && imagePath !== 'null' && imagePath !== '') {
          photoPreview.src = imagePath;
          photoWrapper.style.display = 'block';
        } else {
          photoWrapper.style.display = 'none';
        }
        
        document.getElementById('editModal').style.display = 'block';
      }
      
      function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
      }
      
      

     let deleteOfficialId = null;

     function deleteOfficial(id) {
        deleteOfficialId = id;
        document.getElementById('deleteModal').style.display = 'block';
      }

      function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
        deleteOfficialId = null;
      }

      document.getElementById('cancelDelete').addEventListener('click', function() {
        closeDeleteModal();
      });

      document.getElementById('confirmDelete').addEventListener('click', function() {
        if (deleteOfficialId) {
          window.location.href = 'admin-officials.php?delete_id=' + deleteOfficialId;
        }
      });

      
      // Handle remove photo checkbox
     document.getElementById('removePhotoBtn').addEventListener('click', function() {
        const photoWrapper = document.getElementById('currentPhotoWrapper');
        const removePhotoInput = document.getElementById('remove_photo');
        const fileInput = document.getElementById('edit_image');
        
        // Hide the photo preview immediately for visual feedback
        photoWrapper.style.display = 'none';
        
        // Set the hidden input to '1' to signal photo removal to backend
        removePhotoInput.value = '1';
        
        //  Disable and clear the file input to prevent conflicting actions
        fileInput.disabled = true;
        fileInput.value = '';
        
        //Show success message to confirm the action
        showPopupMessage('Photo will be removed on save', 'success');
      });
      
    
      // This provides visual confirmation for remove actions
      // Similar to the announcement page notification system
      function showPopupMessage(message, type = 'success') {
        let popup = document.getElementById('popupMessage');
        if (!popup) {
          // Create popup element if it doesn't exist
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

        // Set background color based on message type (success/error)
        popup.style.background = type === 'error' ? '#e74c3c' : '#4CAF50';
        popup.textContent = message;
        popup.style.opacity = '1';
        popup.style.display = 'block';

        // Auto-hide after 3 seconds with fade out effect
        setTimeout(() => {
          popup.style.opacity = '0';
          setTimeout(() => {
            popup.style.display = 'none';
          }, 500);
        }, 3000);
      }
      
      // Reset file input when a new file is selected
     document.getElementById('edit_image').addEventListener('change', function() {
        if (this.files.length > 0) {
          // Reset the remove flag since we're uploading a new photo
          document.getElementById('remove_photo').value = '0';
          // Hide the preview since it will be replaced with new photo
          document.getElementById('currentPhotoWrapper').style.display = 'none';
        }
      });
      
     document.getElementById('addModal').addEventListener('click', function(event) {
          if (event.target === this) {
            closeModal();
          }
        });

        document.getElementById('editModal').addEventListener('click', function(event) {
          if (event.target === this) {
            closeEditModal();
          }
        });

        document.getElementById('deleteModal').addEventListener('click', function(event) {
          if (event.target === this) {
            closeDeleteModal();
          }
        });
    </script>
  </body>
</html>

<?php
$conn->close();
?>