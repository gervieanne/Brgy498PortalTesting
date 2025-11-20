<?php
ini_set('session.gc_maxlifetime', 21600);
ini_set('session.cookie_lifetime', 21600);
session_start();

$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "498portal";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// handle view request details
if (isset($_GET['view_request'])) {
    header('Content-Type: application/json');
    $request_id = intval($_GET['view_request']);
    
    $stmt = $conn->prepare("SELECT * FROM document_requests WHERE request_id = ?");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Format the data
        $response = [
            'success' => true,
            'request' => [
                'request_id' => $row['request_id'],
                'date_requested' => date('Y-m-d', strtotime($row['date_requested'])),
                'resident_name' => $row['resident_name'],
                'document_type' => $row['document_type'],
                'purpose' => $row['purpose'],
                'contact_number' => $row['contact_number'],
                'additional_notes' => $row['additional_notes'],
                'status' => $row['status'],
                'expected_date' => $row['expected_date'] ?? null,
                'rejection_reason' => $row['rejection_reason'] ?? null,
                'has_selfie' => !empty($row['selfie_image']),
                'selfie_image' => !empty($row['selfie_image']) ? $row['selfie_image'] : null,
                'has_id' => !empty($row['id_image']),
                'id_image' => !empty($row['id_image']) ? $row['id_image'] : null
            ]
        ];
        
        echo json_encode($response);
    } else {
        echo json_encode(['success' => false, 'message' => 'Request not found']);
    }
    
    $stmt->close();
    $conn->close();
    exit();
}

// Handle AJAX requests for status updates and deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
        if ($_POST['action'] === 'update_status') {
        $request_id = intval($_POST['request_id']);
        $new_status = $_POST['new_status'];
        $rejection_reason = isset($_POST['rejection_reason']) ? trim($_POST['rejection_reason']) : null;
        
        if ($new_status === 'rejected' && $rejection_reason) {
            $stmt = $conn->prepare("UPDATE document_requests SET status = ?, rejection_reason = ? WHERE request_id = ?");
            $stmt->bind_param("ssi", $new_status, $rejection_reason, $request_id);
        } else {
            $stmt = $conn->prepare("UPDATE document_requests SET status = ? WHERE request_id = ?");
            $stmt->bind_param("si", $new_status, $request_id);
        }
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update status']);
        }
        $stmt->close();
        $conn->close();
        exit();
    }
    
    // Handle delete request
    if ($_POST['action'] === 'delete_request') {
        $request_id = intval($_POST['request_id']);
        
        // Check if the request exists and its status
        $check_stmt = $conn->prepare("SELECT status FROM document_requests WHERE request_id = ?");
        $check_stmt->bind_param("i", $request_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Request not found']);
            $check_stmt->close();
            $conn->close();
            exit();
        }
        
        $row = $check_result->fetch_assoc();
        $status = $row['status'];
        $check_stmt->close();
        
        // Only allow deletion of completed or rejected requests
        if ($status !== 'completed' && $status !== 'rejected') {
            echo json_encode(['success' => false, 'message' => 'Only completed or rejected requests can be deleted']);
            $conn->close();
            exit();
        }
        
        // Proceed with deletion
        $stmt = $conn->prepare("DELETE FROM document_requests WHERE request_id = ?");
        $stmt->bind_param("i", $request_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Request deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete request: ' . $stmt->error]);
        }
        $stmt->close();
        $conn->close();
        exit();
    }
}

// Fetch document requests with filters
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$documentFilter = isset($_GET['document_type']) ? $_GET['document_type'] : 'all';
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "SELECT * FROM document_requests WHERE 1=1";

if ($statusFilter !== 'all') {
    $sql .= " AND status = '" . $conn->real_escape_string($statusFilter) . "'";
}

if ($documentFilter !== 'all') {
    $sql .= " AND document_type LIKE '%" . $conn->real_escape_string($documentFilter) . "%'";
}

if (!empty($searchTerm)) {
    // Extract numeric part if search term contains numbers
    $numericSearch = preg_replace('/\D/', '', $searchTerm);
    
    if (!empty($numericSearch)) {
        // Search by request_id or resident_name
        $sql .= " AND (request_id = " . intval($numericSearch) . " 
                  OR resident_name LIKE '%" . $conn->real_escape_string($searchTerm) . "%')";
    } else {
        // Only search by resident_name if no numbers
        $sql .= " AND resident_name LIKE '%" . $conn->real_escape_string($searchTerm) . "%'";
    }
}

$sql .= " ORDER BY date_requested DESC";
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
    <link rel="stylesheet" href="../admin-document-req/admin-document-requests.css" />
    <link rel="stylesheet" href="../preloader/preloader.css" />
    <link rel="stylesheet" href="../logout-modal.css" />
    <title>Document Requests</title>
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
            <a
              href="../admin-document-req/admin-document-requests.php"
              class="active"
              >Document Requests</a
            >
          </li>
          <li>
            <a href="../admin-announcement/admin-announcement.php">Announcement</a>
          </li>
        </ul>
      </nav>
    </div>

    <div class="main-container">
      <div class="header">
        <div class="header-left">
          <h1>DOCUMENT REQUESTS</h1>
          <p>Manage and Track all Document Requests</p>
        </div>
        <div class="header-right">
          <div class="clock" id="clock">12:00:00 AM</div>
          <a href="#" id="logoutBtn">
            <img src="../images/logoutbtn.png" alt="logout" class="logout-logo" />
          </a>
        </div>
      </div>

      <div class="content-container">
        <div class="content-header">
          <h2>Document Request List</h2>
        </div>

        <div class="filter-section">
          <div class="filter-group">
            <label>Show</label>
            <select id="entriesPerPage">
              <option value="10">10</option>
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
            <span>entries</span>
          </div>

          <div class="filter-group">
            <label>Status:</label>
            <select id="statusFilter">
              <option value="all">All Status</option>
              <option value="pending">Pending</option>
              <option value="processing">Processing</option>
              <option value="ready">Ready for Pickup</option>
              <option value="completed">Completed</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>

          <div class="filter-group">
            <label>Document Type:</label>
            <select id="documentFilter">
              <option value="all">All Documents</option>
              <option value="Barangay Certificate">Barangay Certificate</option>
              <option value="Certificate of Indigency">Certificate of Indigency</option>
              <option value="Proof of Residency">Proof of Residency</option>
              <option value="Barangay Business Permit">Barangay Business Permit</option>
              <option value="Barangay ID">Barangay ID</option>
              <option value="First Time Job Seeker Certificate">First Time Job Seeker Certificate</option>
            </select>
          </div>

          <div class="filter-group">
            <label>Search:</label>
            <input
              type="text"
              id="searchInput"
              class="search-input"
              placeholder="Search by name or request ID..."
            />
          </div>
        </div>

        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>Request ID</th>
                <th>Date Requested</th>
                <th>Requestor Name</th>
                <th>Document Type</th>
                <th>Purpose</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="requestsTableBody">
              <?php
              if ($result && $result->num_rows > 0) {
                  while($row = $result->fetch_assoc()) {
                      $statusClass = "status-" . $row['status'];
                      $statusText = ucfirst(str_replace('_', ' ', $row['status']));
                      if ($row['status'] === 'ready') {
                          $statusText = 'Ready for Pickup';
                      }
                      
                      echo "<tr data-request-id='{$row['request_id']}'>";
                      echo "<td>#REQ-" . str_pad($row['request_id'], 3, '0', STR_PAD_LEFT) . "</td>";
                      echo "<td>" . date('Y-m-d', strtotime($row['date_requested'])) . "</td>";
                      echo "<td>" . htmlspecialchars($row['resident_name']) . "</td>";
                      echo "<td>" . htmlspecialchars($row['document_type']) . "</td>";
                      echo "<td>" . htmlspecialchars($row['purpose']) . "</td>";
                      echo "<td><span class='status-badge {$statusClass}'>{$statusText}</span></td>";
                      echo "<td><div class='action-buttons'>";
                      echo "<button class='btn btn-view' onclick='viewRequest({$row['request_id']})'>View</button>";
                      
                      // Show appropriate action buttons based on status
                      if ($row['status'] === 'pending') {
                          echo "<button class='btn btn-approve' onclick='updateStatus({$row['request_id']}, \"processing\")'>Process</button>";
                          echo "<button class='btn btn-reject' onclick='openRejectModal({$row['request_id']})'>Reject</button>";
                      } elseif ($row['status'] === 'processing') {
                          echo "<button class='btn btn-approve' onclick='updateStatus({$row['request_id']}, \"ready\")'>Mark Ready</button>";
                      } elseif ($row['status'] === 'ready') {
                          echo "<button class='btn btn-approve' onclick='updateStatus({$row['request_id']}, \"completed\")'>Complete</button>";
                      }
                      
                      // Add delete button for completed and rejected requests
                      if ($row['status'] === 'completed' || $row['status'] === 'rejected') {
                          echo "<button class='btn btn-delete' onclick='deleteRequest({$row['request_id']})'>Delete</button>";
                      }
                      
                      echo "</div></td>";
                      echo "</tr>";
                  }
              } else {
                  echo "<tr><td colspan='7' style='text-align: center;'>No document requests found</td></tr>";
              }
              ?>
            </tbody>
          </table>
        </div>

        <div class="pagination">
          <div class="pagination-info" id="paginationInfo">
            <?php 
            $totalRows = $result ? $result->num_rows : 0;
            $showing = min(10, $totalRows);
            if ($totalRows > 0) {
              echo "Showing 1 to {$showing} of {$totalRows} entries";
            } else {
              echo "Showing 0 entries";
            }
            ?>
          </div>
          <div class="pagination-buttons">
            <button id="prevBtn" disabled>Previous</button>
            <button id="nextBtn" disabled>Next</button>
          </div>
        </div>
      </div>
    </div>

    <!-- status update modal -->
     <div id="statusModal" class="status-modal">
      <div class="status-modal-content">
          <div class="status-modal-icon">📁</div>
          <h2>Update Request Status</h2>
          <p id="statusText">Are you sure you want to update this request?</p>
        <div class="status-modal-buttons">
          <button class="status-btn-cancel" id="cancelStatus">No, Cancel</button>
          <button class="status-btn-confirm" id="confirmStatus">Yes, Update</button>  
        </div>
      </div>
    </div>

    <!-- delete modal -->
     <div id="deleteModal" class="delete-modal">
      <div class="delete-modal-content">
          <div class="delete-modal-icon">⚠️</div>
          <h2>Delete Request</h2>
          <p>Are you sure you want to delete this request?</p>
        <div class="delete-modal-buttons">
          <button class="delete-btn-cancel" id="cancelDeleteRequest">No, Cancel</button>
          <button class="delete-btn-confirm" id="confirmDeleteRequest">Yes, Delete</button>  
        </div>
      </div>
    </div>

    <!-- reject modal -->
    <div id="rejectModal" class="modal">
        <div class="modal-content">
        <div class="modal-header">
          <h2>REJECT REQUEST</h2>
          <button class="close-btn" onclick="closeRejectModal()">&times;</button>
        </div>
        <div class="modal-body">
          <p style="margin-bottom: 15px; color: #666;">Please provide a reason for rejecting this request:</p>
          <textarea 
            id="rejectionReason" 
            rows="5" 
            style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: 'Poppins', sans-serif; resize: vertical; font-size: 14px;" 
            placeholder="Enter rejection reason (e.g., Incomplete documents, Invalid ID, etc.)..."
            required>
          </textarea>
          <input type="hidden" id="rejectRequestId" value="">
        </div>
        <div class="modal-footer">
          <button class="btn-modal btn-cancel" onclick="closeRejectModal()">Cancel</button>
          <button class="btn-modal btn-confirm" onclick="confirmReject()" style="background-color: #dc3545;">Reject Now</button>
        </div>
      </div>
    </div>

    <!-- View Request Modal -->
    <div id="viewModal" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <h2>REQUEST DETAILS</h2>
          <button class="close-btn" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
          <div class="detail-row">
            <div class="detail-label">Request ID:</div>
            <div class="detail-value" id="modalRequestId"></div>
          </div>
          <div class="detail-row">
            <div class="detail-label">Date Requested:</div>
            <div class="detail-value" id="modalDate"></div>
          </div>
          <div class="detail-row">
            <div class="detail-label">Resident Name:</div>
            <div class="detail-value" id="modalName"></div>
          </div>
          <div class="detail-row">
            <div class="detail-label">Document Type:</div>
            <div class="detail-value" id="modalDocType"></div>
          </div>
          <div class="detail-row">
            <div class="detail-label">Purpose:</div>
            <div class="detail-value" id="modalPurpose"></div>
          </div>
          <div class="detail-row">
            <div class="detail-label">Contact Number:</div>
            <div class="detail-value" id="modalContact"></div>
          </div>
          <div class="detail-row">
            <div class="detail-label">Status:</div>
            <div class="detail-value" id="modalStatus"></div>
          </div>
          <div class="detail-row">
            <div class="detail-label">Expected Date to Receive:</div>
            <div class="detail-value" id="modalExpectedDate"></div>
          </div>
          <div class="detail-row">
            <div class="detail-label">Additional Notes:</div>
            <div class="detail-value" id="modalNotes"></div>
          </div>

          <div class="detail-row" id="rejectionReasonRow" style="display: none;">
            <div class="detail-label">Rejection Reason:</div>
            <div class="detail-value" id="modalRejectionReason" style="color: #dc3545; font-weight: 500;"></div>
          </div>

          <div class="detail-row image-row" id="selfieRow" style="display: none;">
            <div class="detail-label">Selfie with ID:</div>
            <div class="detail-value">
              <div class="image-container">
                <img id="modalSelfie" src="" alt="Selfie with ID" class="modal-image" onclick="openImageInNewTab('selfie')"/>
                <p class="image-hint">Click image to view full size.</p>
              </div>
            </div> 
          </div> 

          <div class="detail-row image-row" id="idRow" style="display: none;">
            <div class="detail-label">ID Picture:</div>
            <div class="detail-value">
              <div class="image-container">
                <img id="modalIdPicture" src="" alt="ID Picture" class="modal-image" onclick="openImageInNewTab('id')"/>
                <p class="image-hint">Click image to view full size.</p>
              </div> 
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-modal btn-cancel" onclick="closeModal()">
            Close
          </button>
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
  </body>
  <script src="../admin-document-req/admin-document-requests.js"></script>
  <script src="../preloader/preloader.js"></script>
  <script src="../logout-modal.js"></script>
</html>

<?php
$conn->close();
?>