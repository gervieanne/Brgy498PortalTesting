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

// Handle Update Resident
if (isset($_POST['update_resident'])) {
    $user_id = intval($_POST['user_id']);
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $middle_name = $conn->real_escape_string($_POST['middle_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    // Format: Last Name, First Name Middle Name
    $full_name = trim($last_name . ', ' . $first_name . ' ' . $middle_name);
    $date_of_birth = $conn->real_escape_string($_POST['date_of_birth']);
    $place_of_birth = $conn->real_escape_string($_POST['place_of_birth']);
    $sex = $conn->real_escape_string($_POST['sex']);
    $civil_status = $conn->real_escape_string($_POST['civil_status']);
    $occupation = $conn->real_escape_string($_POST['occupation']);
    $citizenship = $conn->real_escape_string($_POST['citizenship']);
    $address = $conn->real_escape_string($_POST['address']);
    $relation_to_household = $conn->real_escape_string($_POST['relation_to_household']);
    
    // Process contact number
    $contact_number_raw = trim($_POST['contact_number'] ?? '');
    $contact_number = '';
    
    if (!empty($contact_number_raw)) {
        $contact_number_clean = preg_replace('/[^0-9]/', '', $contact_number_raw);
        
        if (strlen($contact_number_clean) === 11 && substr($contact_number_clean, 0, 2) === '09') {
            $contact_number = $contact_number_clean;
        } else if (strlen($contact_number_clean) === 10 && $contact_number_clean[0] === '9') {
            $contact_number = '0' . $contact_number_clean;
        } else if (!empty($contact_number_clean)) {
            $_SESSION['error_message'] = "Invalid contact number format. Please use 09XXXXXXXXX (11 digits)";
            header("Location: admin-residents.php");
            exit();
        }
    }
    
    // Process email
    $email = trim($_POST['email'] ?? '');
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Invalid email format";
        header("Location: admin-residents.php");
        exit();
    }
    
    $conn->begin_transaction();
    
    try {
        // Update userprofile498
        $sql = "UPDATE userprofile498 SET 
                first_name = ?,
                full_name = ?,
                date_of_birth = ?,
                place_of_birth = ?,
                sex = ?,
                civil_status = ?,
                occupation = ?,
                citizenship = ?,
                address = ?,
                relation_to_household = ?,
                contact_number = ?,
                email = ?
                WHERE user_id = ?";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssssssi", 
            $first_name, $full_name, $date_of_birth, $place_of_birth, 
            $sex, $civil_status, $occupation, $citizenship, 
            $address, $relation_to_household, $contact_number, $email, $user_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update userprofile498: " . $stmt->error);
        }
        $stmt->close();
        
        // Update usercreds table
        $sql2 = "UPDATE usercreds SET 
                 contact_number = ?, 
                 email = ?,
                 full_name = ?,
                 address = ?,
                 date_of_birth = ?,
                 place_of_birth = ?,
                 sex = ?,
                 civil_status = ?,
                 occupation = ?,
                 citizenship = ?,
                 relation_to_household = ?
                 WHERE user_id = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("sssssssssssi", 
            $contact_number, $email, $full_name, $address, 
            $date_of_birth, $place_of_birth, $sex, $civil_status, 
            $occupation, $citizenship, $relation_to_household, $user_id);
        
        if (!$stmt2->execute()) {
            throw new Exception("Failed to update usercreds: " . $stmt2->error);
        }
        $stmt2->close();
        
        $conn->commit();
        $_SESSION['success_message'] = "Resident information updated successfully!";
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Error updating resident information: " . $e->getMessage();
    }
    
    header("Location: admin-residents.php");
    exit();
}

// Handle Delete Account
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    $conn->begin_transaction();
    
    try {
        $sql = "DELETE FROM userprofile498 WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $stmt->close();
        
        $sql = "DELETE FROM usercreds WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $stmt->close();
        
        $conn->commit();
        $_SESSION['success_message'] = "Resident deleted successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Error deleting resident: " . $e->getMessage();
    }
    
    header("Location: admin-residents.php");
    exit();
}

// Pagination and search
$entries_per_page = isset($_GET['entries']) ? intval($_GET['entries']) : 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $entries_per_page;
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Build query with search
$where_clause = "";
if (!empty($search)) {
    $where_clause = "WHERE up.first_name LIKE '%$search%' 
                     OR up.full_name LIKE '%$search%' 
                     OR up.address LIKE '%$search%'";
}

// Count total records
$count_sql = "SELECT COUNT(*) as total FROM userprofile498 up 
              INNER JOIN usercreds uc ON up.user_id = uc.user_id 
              $where_clause";
$count_result = $conn->query($count_sql);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $entries_per_page);

// Fetch residents data
$sql = "SELECT 
    uc.user_id,
    up.first_name,
    up.full_name,
    TIMESTAMPDIFF(YEAR, up.date_of_birth, CURDATE()) as age,
    up.civil_status,
    up.sex as gender,
    up.date_of_birth,
    COALESCE(NULLIF(up.contact_number, ''), uc.contact_number) as contact_number,
    COALESCE(NULLIF(up.email, ''), uc.email) as email
FROM userprofile498 up
INNER JOIN usercreds uc ON up.user_id = uc.user_id
$where_clause
ORDER BY up.profile_id ASC
LIMIT $offset, $entries_per_page";

$result = $conn->query($sql);
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
    <link rel="stylesheet" href="../admin-residents-info/admin-residents.css" />
    <link rel="preloader" href="../preloader/preloader.css" />
    <link rel="stylesheet" href="../logout-modal.css" />
    <title>Residents Information</title>
    <style>
      .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 5px;
        font-size: 14px;
      }
      .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
      }
      .alert-error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
      }
    </style>
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
            <a href="../admin-residents-info/admin-residents.php" class="active"
              >Residents Information</a
            >
          </li>
          <li><a href="../admin-calendar/admin-calendar.php">Calendar</a></li>
          <li>
            <a href="../admin-document-req/admin-document-requests.php"
              >Document Requests</a>
          </li>
          <li>
            <a href="../admin-announcement/admin-announcement.php">Announcement</a>
          </li>
        </ul>
      </nav>
    </div>

    <div class="main-container">
      <div class="header">
        <div class="clock" id="clock">12:00:00 AM</div>
        <a href="#" id="logoutBtn">
          <img src="../images/logoutbtn.png" alt="logout" class="logout-logo" />
        </a>
      </div>

      <div class="content-box">
        <div class="content-header">Residents Information</div>

        <?php if (isset($_SESSION['success_message'])): ?>
          <div class="alert alert-success">
            <?php 
              echo $_SESSION['success_message']; 
              unset($_SESSION['success_message']);
            ?>
          </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
          <div class="alert alert-error">
            <?php 
              echo $_SESSION['error_message']; 
              unset($_SESSION['error_message']);
            ?>
          </div>
        <?php endif; ?>

        <div class="table-controls">
          <div class="show-entries">
            <span>Show</span>
            <select id="entriesSelect" onchange="changeEntries(this.value)">
              <option value="10" <?php echo $entries_per_page == 10 ? 'selected' : ''; ?>>10</option>
              <option value="25" <?php echo $entries_per_page == 25 ? 'selected' : ''; ?>>25</option>
              <option value="50" <?php echo $entries_per_page == 50 ? 'selected' : ''; ?>>50</option>
              <option value="100" <?php echo $entries_per_page == 100 ? 'selected' : ''; ?>>100</option>
            </select>
            <span>entries</span>
          </div>
          <div class="search-box">
            <span>Search:</span>
            <input type="text" id="searchInput" placeholder="" value="<?php echo htmlspecialchars($search); ?>" />
          </div>
        </div>

        <div class="residents-table">
          <table>
            <thead>
              <tr>
                <th>Last Name</th>
                <th>First Name</th>
                <th>Middle Name</th>
                <th>Age</th>
                <th>Status</th>
                <th>Gender</th>
                <th>Date of Birth</th>
                <th>Contact Number</th>
                <th>Email</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="tableBody">
              <?php
        if ($result->num_rows > 0) {
          while($row = $result->fetch_assoc()) {
            // Parse full name - Format: Last Name, First Name Middle Name
            $full_name_str = trim($row['full_name']);
            $first_name = '';
            $middle_name = '';
            $last_name = '';
            
            // Check if name contains comma (new format)
            if (strpos($full_name_str, ',') !== false) {
              // Format: Last Name, First Name Middle Name
              $parts = explode(',', $full_name_str, 2);
              $last_name = trim($parts[0]);
              $first_middle = trim($parts[1]);
              $name_parts = explode(' ', $first_middle);
              $first_name = !empty($name_parts) ? $name_parts[0] : '';
              $middle_name = count($name_parts) > 1 ? substr($name_parts[1], 0, 1) . '.' : '';
            } else {
              // Fallback for old format: First Middle Last
              $name_parts = explode(' ', $full_name_str);
              if (count($name_parts) > 2) {
                $first_name = $name_parts[0];
                $middle_parts = array_slice($name_parts, 1, -1);
                $middle_name = !empty($middle_parts) ? substr($middle_parts[0], 0, 1) . '.' : '';
                $last_name = end($name_parts);
              } else if (count($name_parts) == 2) {
                $first_name = $name_parts[0];
                $last_name = $name_parts[1];
              }
            }
            
            // Format contact display
            $contact_display = 'N/A';
            if (!empty($row['contact_number'])) {
              $cn = trim($row['contact_number']);
              if (strlen($cn) === 10 && $cn[0] !== '0') {
                $contact_display = '0' . $cn;
              } else {
                $contact_display = $cn;
              }
            }

            $email_display = !empty($row['email']) ? $row['email'] : 'N/A';

            echo "<tr>";
            echo "<td>" . htmlspecialchars($last_name) . "</td>";
            echo "<td>" . htmlspecialchars($first_name) . "</td>";
            echo "<td>" . htmlspecialchars($middle_name) . "</td>";
            echo "<td>" . htmlspecialchars($row['age']) . "</td>";
            echo "<td>" . htmlspecialchars($row['civil_status']) . "</td>";
            echo "<td>" . ($row['gender'] == 'M' ? 'Male' : 'Female') . "</td>";
            echo "<td>" . htmlspecialchars($row['date_of_birth']) . "</td>";
            echo "<td>" . htmlspecialchars($contact_display) . "</td>";
            echo "<td>" . htmlspecialchars($email_display) . "</td>";
            echo '<td class="action-buttons">';
            echo '<button class="btn-edit" onclick="editResident(' . $row['user_id'] . ')"><img src="../images/brush-logo.png" /></button>';
            echo '<button class="btn-delete" onclick="deleteResident(' . $row['user_id'] . ')"><img src="../images/delete-logo.png" /></button>';
            echo '</td>';
            echo "</tr>";
          }
        } else {
          echo '<tr><td colspan="10" style="text-align: center;">No residents found</td></tr>';
        }
              ?>
            </tbody>
          </table>
        </div>

        <div class="table-footer">
          <div class="showing-info">
            Showing <?php echo min($offset + 1, $total_records); ?> to <?php echo min($offset + $entries_per_page, $total_records); ?> of <?php echo $total_records; ?> entries
          </div>
          <div class="pagination">
            <button id="prevBtn" <?php echo $page <= 1 ? 'disabled' : ''; ?> onclick="changePage(<?php echo $page - 1; ?>)">Previous</button>
            <button id="nextBtn" <?php echo $page >= $total_pages ? 'disabled' : ''; ?> onclick="changePage(<?php echo $page + 1; ?>)">Next</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Resident Modal -->
    <div id="editModal" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <h2>Edit Resident Information</h2>
          <span class="close" onclick="closeEditModal()">&times;</span>
        </div>
        <form method="POST" action="admin-residents.php" id="editForm">
          <input type="hidden" name="update_resident" value="1">
          <input type="hidden" name="user_id" id="edit_user_id">
          
          <div class="form-row">
            <div class="form-group">
              <label>Last Name:</label>
              <input type="text" name="last_name" id="edit_last_name" required>
            </div>
            <div class="form-group">
              <label>First Name:</label>
              <input type="text" name="first_name" id="edit_first_name" required>
            </div>
            <div class="form-group">
              <label>Middle Name:</label>
              <input type="text" name="middle_name" id="edit_middle_name" required>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label>Date of Birth:</label>
              <input type="date" name="date_of_birth" id="edit_date_of_birth" required>
            </div>
            <div class="form-group">
              <label>Place of Birth:</label>
              <input type="text" name="place_of_birth" id="edit_place_of_birth" required>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label>Gender:</label>
              <select name="sex" id="edit_sex" required>
                <option value="M">Male</option>
                <option value="F">Female</option>
              </select>
            </div>
            <div class="form-group">
              <label>Civil Status:</label>
              <input type="text" name="civil_status" id="edit_civil_status" required>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label>Occupation:</label>
              <input type="text" name="occupation" id="edit_occupation" required>
            </div>
            <div class="form-group">
              <label>Citizenship:</label>
              <input type="text" name="citizenship" id="edit_citizenship" required>
            </div>
          </div>
          
          <div class="form-group">
            <label>Address:</label>
            <input type="text" name="address" id="edit_address" required>
          </div>
          
          <div class="form-group">
            <label>Relation to Household:</label>
            <input type="text" name="relation_to_household" id="edit_relation_to_household" required>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label>Contact Number:</label>
              <input type="text" name="contact_number" id="edit_contact_number" placeholder="09XXXXXXXXX" maxlength="11" pattern="09[0-9]{9}">
              <small style="color: #666; font-size: 12px;">Format: 09XXXXXXXXX (11 digits)</small>
            </div>
            <div class="form-group">
              <label>Email:</label>
              <input type="email" name="email" id="edit_email" placeholder="email@example.com">
            </div>
          </div>
          
          <div class="form-buttons">
            <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
            <button type="submit" class="btn-save">Save Changes</button>
          </div>
        </form>
      </div>
    </div>

     <!-- Delete Resident Modal -->
     <div id="deleteModal" class="delete-modal">
      <div class="delete-modal-content">
          <div class="delete-modal-icon">⚠️</div>
          <h2>Delete Resident</h2>
          <p>Are you sure you want to delete this Resident?</p>
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
      // Validate contact number format on input
      document.addEventListener('DOMContentLoaded', function() {
        const contactInput = document.getElementById('edit_contact_number');
        if (contactInput) {
          contactInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^0-9]/g, '');
            if (value.length > 11) value = value.slice(0, 11);
            e.target.value = value;
          });
        }
      });
      
      function changeEntries(entries) {
        const search = document.getElementById('searchInput').value;
        window.location.href = `admin-residents.php?entries=${entries}&search=${search}`;
      }
      
      function searchTable(search) {
        const entries = document.getElementById('entriesSelect').value;
        window.location.href = `admin-residents.php?entries=${entries}&search=${search}`;
      }
      
      function changePage(page) {
        const entries = document.getElementById('entriesSelect').value;
        const search = document.getElementById('searchInput').value;
        window.location.href = `admin-residents.php?entries=${entries}&search=${search}&page=${page}`;
      }
      
      function editResident(userId) {
        const editModal = document.getElementById('editModal');
        editModal.style.display = 'block';
        
        fetch(`get_residents_details.php?user_id=${userId}`)
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              const r = data.resident;
              document.getElementById('edit_user_id').value = r.user_id;
              // Parse full_name - Format: Last Name, First Name Middle Name
              let firstName = '';
              let middleName = '';
              let lastName = '';
              
              if (r.full_name.includes(',')) {
                // New format: Last Name, First Name Middle Name
                const parts = r.full_name.split(',').map(s => s.trim());
                lastName = parts[0] || '';
                const firstMiddle = parts[1] || '';
                const nameParts = firstMiddle.split(' ').filter(p => p);
                firstName = nameParts[0] || '';
                middleName = nameParts.slice(1).join(' ') || '';
              } else {
                // Fallback for old format: First Middle Last
                const nameParts = r.full_name.split(' ').filter(p => p);
                if (nameParts.length > 2) {
                  firstName = nameParts[0] || '';
                  middleName = nameParts.slice(1, -1).join(' ') || '';
                  lastName = nameParts[nameParts.length - 1] || '';
                } else if (nameParts.length === 2) {
                  firstName = nameParts[0] || '';
                  lastName = nameParts[1] || '';
                }
              }
              
              document.getElementById('edit_first_name').value = firstName;
              document.getElementById('edit_middle_name').value = middleName;
              document.getElementById('edit_last_name').value = lastName;
              document.getElementById('edit_date_of_birth').value = r.date_of_birth || '';
              document.getElementById('edit_place_of_birth').value = r.place_of_birth || '';
              document.getElementById('edit_sex').value = r.sex || 'M';
              document.getElementById('edit_civil_status').value = r.civil_status || '';
              document.getElementById('edit_occupation').value = r.occupation || '';
              document.getElementById('edit_citizenship').value = r.citizenship || '';
              document.getElementById('edit_address').value = r.address || '';
              document.getElementById('edit_relation_to_household').value = r.relation_to_household || '';
              document.getElementById('edit_contact_number').value = r.contact_number || '';
              document.getElementById('edit_email').value = r.email || '';
            } else {
              alert('Error loading resident details: ' + (data.message || 'Unknown error'));
              closeEditModal();
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('Error loading resident details. Please check console for details.');
            closeEditModal();
          });
      }
      
      function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
      }
      
      let deleteResidentUserId = null; 

      function deleteResident(userId) {
        deleteResidentUserId = userId; 
        document.getElementById('deleteModal').style.display = 'block';
      }

      function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
        deleteResidentUserId = null;
      }

      document.getElementById('cancelDelete').addEventListener('click', function() {
        closeDeleteModal();
      });

      document.getElementById('confirmDelete').addEventListener('click', function() {
        if (deleteResidentUserId) { 
          window.location.href = 'admin-residents.php?delete_id=' + deleteResidentUserId;
        }
      });

      
      function debounce(fn, delay) {
        let timer;
        return function(...args) {
          clearTimeout(timer);
          timer = setTimeout(() => fn.apply(this, args), delay);
        };
      }

      (function() {
        const searchInputEl = document.getElementById('searchInput');
        if (!searchInputEl) return;
        const debounced = debounce(function() {
          searchTable(searchInputEl.value);
        }, 1500);
        searchInputEl.addEventListener('input', debounced);
      })();

      window.onclick = function(event) {
        const editModal = document.getElementById('editModal');
        if (event.target == editModal) {
          closeEditModal();
        }
      }
    </script>
  </body>
</html>

<?php
$conn->close();
?>