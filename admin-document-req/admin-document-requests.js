function updateClock() {
  const now = new Date();
  const hours = now.getHours();
  const minutes = now.getMinutes();
  const seconds = now.getSeconds();
  const ampm = hours >= 12 ? "PM" : "AM";
  const displayHours = hours % 12 || 12;

  const timeString = `${displayHours.toString().padStart(2, "0")}:${minutes
    .toString()
    .padStart(2, "0")}:${seconds.toString().padStart(2, "0")} ${ampm}`;
  document.getElementById("clock").textContent = timeString;
}

setInterval(updateClock, 1000);
updateClock();

// Sidebar toggle
const burgerMenu = document.getElementById("burgerMenu");
const sidebar = document.getElementById("sidebar");

burgerMenu.addEventListener("click", () => {
  sidebar.classList.toggle("active");
});

// Close sidebar when clicking outside on mobile
document.addEventListener("click", (e) => {
  if (window.innerWidth <= 768) {
    if (!sidebar.contains(e.target) && !burgerMenu.contains(e.target)) {
      sidebar.classList.remove("active");
    }
  }
});

let allRows = []; // Store all table rows
let currentPage = 1;
let entriesPerPage = 10;

function initializePagination() {
  // Get all table rows (excluding header)
  const tableBody = document.getElementById('requestsTableBody');
  allRows = Array.from(tableBody.querySelectorAll('tr')).filter(row => 
    !row.querySelector('td[colspan]') // Exclude "no results" row
  );
  
  // Set up entries per page listener
  const entriesSelect = document.getElementById('entriesPerPage');
  entriesSelect.addEventListener('change', function() {
    entriesPerPage = parseInt(this.value);
    currentPage = 1;
    displayPage();
  });
  
  document.getElementById('prevBtn').addEventListener('click', () => {
    if (currentPage > 1) {
      currentPage--;
      displayPage();
    }
  });
  
  document.getElementById('nextBtn').addEventListener('click', () => {
    const totalPages = Math.ceil(allRows.length / entriesPerPage);
    if (currentPage < totalPages) {
      currentPage++;
      displayPage();
    }
  });
  
  // Initial display
  displayPage();
}

function displayPage() {
  if (allRows.length === 0) {
    document.querySelector('.pagination-info').textContent = 'Showing 0 entries';
    document.getElementById('prevBtn').disabled = true;
    document.getElementById('nextBtn').disabled = true;
    return;
  }
  
  const start = (currentPage - 1) * entriesPerPage;
  const end = start + entriesPerPage;
  const totalPages = Math.ceil(allRows.length / entriesPerPage);
  
  // Hide all rows first
  allRows.forEach(row => row.style.display = 'none');
  
  // Show only rows for current page
  allRows.slice(start, end).forEach(row => row.style.display = '');
  
  // Update pagination info
  const showing = Math.min(end, allRows.length);
  document.querySelector('.pagination-info').textContent = 
    `Showing ${start + 1} to ${showing} of ${allRows.length} entries`;
  
  // Update button states
  document.getElementById('prevBtn').disabled = currentPage === 1;
  document.getElementById('nextBtn').disabled = currentPage >= totalPages;
}

// Call on page load
window.addEventListener('DOMContentLoaded', initializePagination);

// pop up
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
  }, 2000);
}

// status 
let pendingStatusUpdate = null;


// Modal functions
function viewRequest(requestId) {
  // Fetch request details via AJAX
  fetch(`admin-document-requests.php?view_request=${requestId}`)
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.json();
    })
    .then(data => {
      if (data.success) {
        const req = data.request;

        // Basic info
        document.getElementById("modalRequestId").textContent = "#REQ-" + String(req.request_id).padStart(3, '0');
        document.getElementById("modalDate").textContent = req.date_requested;
        document.getElementById("modalName").textContent = req.resident_name;
        document.getElementById("modalDocType").textContent = req.document_type;
        document.getElementById("modalPurpose").textContent = req.purpose;
        document.getElementById("modalContact").textContent = req.contact_number || 'N/A';
        document.getElementById("modalNotes").textContent = req.additional_notes || 'None';
        
        // Status
        let statusClass = "status-" + req.status;
        let statusText = req.status.charAt(0).toUpperCase() + req.status.slice(1);
        if (req.status === 'ready') {
          statusText = 'Ready for Pickup';
        }
        document.getElementById("modalStatus").innerHTML = `<span class="status-badge ${statusClass}">${statusText}</span>`;
        document.getElementById("modalExpectedDate").textContent = req.expected_date || 'N/A';
        
        // Rejection reason
        const rejectionReasonRow = document.getElementById('rejectionReasonRow');
        const modalRejectionReason = document.getElementById('modalRejectionReason');
        if (req.status === 'rejected' && req.rejection_reason) {
          rejectionReasonRow.style.display = 'flex';
          modalRejectionReason.textContent = req.rejection_reason;
        } else {
          rejectionReasonRow.style.display = 'none';
        }

        // Selfie Row
        const selfieRow = document.getElementById('selfieRow');
        const modalSelfie = document.getElementById('modalSelfie');
        if (req.has_selfie && req.selfie_image) {
          selfieRow.style.display = 'flex';
          modalSelfie.src = req.selfie_image;
          modalSelfie.dataset.imagePath = req.selfie_image;
        } else {
          selfieRow.style.display = 'none';
        }

        // ID Row
        const idRow = document.getElementById('idRow');
        const modalIdPicture = document.getElementById('modalIdPicture');
        if (req.has_id && req.id_image) {
          idRow.style.display = 'flex';
          modalIdPicture.src = req.id_image;
          modalIdPicture.dataset.imagePath = req.id_image;
        } else {
          idRow.style.display = 'none';
        }

        // Show modal
        const modal = document.getElementById("viewModal");
        modal.classList.add("show");
        document.body.classList.add("modal-open");
        document.documentElement.classList.add("modal-open");

      } else {
        showPopupMessage("Failed to load request details", "error");
      }
    })
    .catch(error => {
      console.error("Error:", error);
      alert("An error occurred while fetching request details");
    });
}

// Rejected modal
function openRejectModal(requestId) {
  document.getElementById('rejectRequestId').value = requestId;
  document.getElementById('rejectionReason').value = '';
  const modal = document.getElementById("rejectModal");
  modal.classList.add("show");
  document.body.classList.add("modal-open");
  document.documentElement.classList.add("modal-open");
}

function closeRejectModal() {
  const modal = document.getElementById("rejectModal");
  modal.classList.remove("show");
  document.body.classList.remove("modal-open");
  document.documentElement.classList.remove("modal-open");

  document.getElementById('rejectionReason').value = '';
  document.getElementById('rejectRequestId').value = '';
}

// Confirm rejection
function confirmReject() {
  const requestId = document.getElementById('rejectRequestId').value;
  const reason = document.getElementById('rejectionReason').value.trim();
  
  if (!reason) {
    showPopupMessage('Please provide a reason for rejection', 'error');
    return;
  }
  
  const formData = new FormData();
  formData.append('action', 'update_status');
  formData.append('request_id', requestId);
  formData.append('new_status', 'rejected');
  formData.append('rejection_reason', reason);

  fetch('admin-document-requests.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    closeRejectModal();
    
    if (data.success) {
      showPopupMessage('Request rejected successfully', 'success');
      updateRowStatus(requestId, 'rejected');
    } else {
      showPopupMessage("Failed to reject request: " + data.message, 'error');
    }
  })
  .catch(error => {
    console.error("Error:", error);
    showPopupMessage("An error occurred while rejecting the request", 'error');
  });
}

// Open images in new tab
function openImageInNewTab(imageType) {
  const imagePath = imageType === 'selfie' 
    ? document.getElementById('modalSelfie').dataset.imagePath
    : document.getElementById('modalIdPicture').dataset.imagePath;
  
  if (imagePath) {
    window.open(imagePath, '_blank');
  }
}

function closeModal() {
  const modal = document.getElementById("viewModal");
  modal.classList.remove("show");
  document.body.classList.remove("modal-open");
  document.documentElement.classList.remove("modal-open");
}


function updateStatus(requestId, newStatus) {
   pendingStatusUpdate = { requestId, newStatus };

   const statusMessages = {
    'processing': 'To process this request, ensure all required documents are verified.',
    'ready': 'To mark this request as ready, confirm that the documents are prepared for pickup.',
    'completed': 'To complete this request, ensure the resident has picked up their documents.',
  };

  document.getElementById('statusText').textContent = statusMessages[newStatus] || 'Are you sure you want to update this request?';
document.getElementById('statusModal').style.display = 'block';
}

function closeStatusModal() {
  document.getElementById('statusModal').style.display = 'none';
  pendingStatusUpdate = null;
}

document.getElementById('cancelStatus').addEventListener('click', function() {
  closeStatusModal();
});

document.getElementById('confirmStatus').addEventListener('click', function() {
  if (!pendingStatusUpdate) return;

  const { requestId, newStatus } = pendingStatusUpdate;

  const formData = new FormData();
  formData.append('action', 'update_status');
  formData.append('request_id', requestId);
  formData.append('new_status', newStatus);

   fetch('admin-document-requests.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    closeStatusModal();
    
    if (data.success) {
      showPopupMessage(`Request status updated to: ${newStatus}`, 'success');
      updateRowStatus(requestId, newStatus);
    } else {
      showPopupMessage("Failed to update status: " + data.message, 'error');
    }
  })
  .catch(error => {
    console.error("Error:", error);
    closeStatusModal();
    showPopupMessage("An error occurred while updating the status", 'error');
  });
});

let deleteRequestId = null;

// Delete request
function deleteRequest(requestId) {
  deleteRequestId = requestId;
  document.getElementById('deleteModal').style.display = 'block';
}

function closeDeleteModal() {
  document.getElementById('deleteModal').style.display = 'none';
  deleteRequestId = null;
}

document.getElementById('cancelDeleteRequest').addEventListener('click', function() {
  closeDeleteModal();
});

document.getElementById('confirmDeleteRequest').addEventListener('click', function() {
  if (!deleteRequestId) return;

  // Send AJAX request to delete the request
  const formData = new FormData();
  formData.append('action', 'delete_request');
  formData.append('request_id', deleteRequestId);

  fetch('admin-document-requests.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    closeDeleteModal();
    
    if (data.success) {
      showPopupMessage("Request deleted successfully", 'success');
       setTimeout(() => location.reload(), 1000);
    } else {
      showPopupMessage("Failed to delete request: " + (data.message || "Unknown error"), 'error');
    }
  })
  .catch(error => {
    console.error("Error:", error);
    closeDeleteModal();
    showPopupMessage("An error occurred while deleting the request", 'error');
  });
});

// Close modal when clicking outside
window.onclick = function (event) {
  const modal = document.getElementById("viewModal");
  if (event.target === modal) {
    closeModal();
  }
};

// Filter functionality with page reload
const statusFilter = document.getElementById("statusFilter");
const documentFilter = document.getElementById("documentFilter");
const searchInput = document.getElementById("searchInput");

// Apply filters by reloading page with query parameters
function applyFilters() {
  const status = statusFilter.value;
  const docType = documentFilter.value;
  const search = searchInput.value.trim();
  
  const params = [];
  
  if (status !== 'all') {
    params.push(`status=${encodeURIComponent(status)}`);
  }
  
  if (docType !== 'all') {
    params.push(`document_type=${encodeURIComponent(docType)}`);
  }
  
  if (search !== '') {
    params.push(`search=${encodeURIComponent(search)}`);
  }
  
  const url = params.length > 0 
    ? 'admin-document-requests.php?' + params.join('&')
    : 'admin-document-requests.php';
  
  window.location.href = url;
}

// Add event listeners for filters
statusFilter.addEventListener("change", applyFilters);
documentFilter.addEventListener("change", applyFilters);

// Add debounced search (1.5 seconds)
let searchTimeout;
searchInput.addEventListener("input", function() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(applyFilters, 1500);
});

// Set filter values from URL parameters on page load
window.addEventListener('DOMContentLoaded', function() {
  const urlParams = new URLSearchParams(window.location.search);
  
  const status = urlParams.get('status');
  if (status) {
    statusFilter.value = status;
  }
  
  const docType = urlParams.get('document_type');
  if (docType) {
    documentFilter.value = docType;
  }
  
  const search = urlParams.get('search');
  if (search) {
    searchInput.value = search;
  }
});

// globally
window.updateStatus = updateStatus;
window.deleteRequest = deleteRequest;
window.viewRequest = viewRequest;
window.openRejectModal = openRejectModal;
window.closeRejectModal = closeRejectModal;
window.confirmReject = confirmReject;
window.openImageInNewTab = openImageInNewTab;

// to prevent from refreshing the whole page, we will update only the affected row
function updateRowStatus(requestId, newStatus) {
  const row = document.querySelector(`tr[data-request-id="${requestId}"]`);
  if (!row) return;
  
  // Update status badge
  const statusCell = row.querySelector('.status-badge');
  if (statusCell) {
    let statusText = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
    if (newStatus === 'ready') {
      statusText = 'Ready for Pickup';
    }
    
    statusCell.className = `status-badge status-${newStatus}`;
    statusCell.textContent = statusText;
  }
  
  // Update action buttons
  const actionButtonsDiv = row.querySelector('.action-buttons');
  if (actionButtonsDiv) {
    // Keep the View button
    let buttonsHTML = `<button class='btn btn-view' onclick='viewRequest(${requestId})'>View</button>`;
    
    // Add appropriate buttons based on new status
    if (newStatus === 'pending') {
      buttonsHTML += `<button class='btn btn-approve' onclick='updateStatus(${requestId}, "processing")'>Process</button>`;
      buttonsHTML += `<button class='btn btn-reject' onclick='openRejectModal(${requestId})'>Reject</button>`;
    } else if (newStatus === 'processing') {
      buttonsHTML += `<button class='btn btn-approve' onclick='updateStatus(${requestId}, "ready")'>Mark Ready</button>`;
    } else if (newStatus === 'ready') {
      buttonsHTML += `<button class='btn btn-approve' onclick='updateStatus(${requestId}, "completed")'>Complete</button>`;
    } else if (newStatus === 'completed' || newStatus === 'rejected') {
      buttonsHTML += `<button class='btn btn-delete' onclick='deleteRequest(${requestId})'>Delete</button>`;
    }
    
    actionButtonsDiv.innerHTML = buttonsHTML;
  }
}
