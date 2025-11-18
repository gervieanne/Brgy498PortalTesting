// Logout Modal Functionality
const logoutBtn = document.getElementById("logoutBtn");
const logoutModal = document.getElementById("logoutModal");
const cancelLogout = document.getElementById("cancelLogout");
const confirmLogout = document.getElementById("confirmLogout");

// Show modal when logout button is clicked
if (logoutBtn) {
  logoutBtn.addEventListener("click", (e) => {
    e.preventDefault();
    e.stopPropagation();
    console.log("Logout button clicked"); // Debug log
    logoutModal.classList.add("show");
  });
}

// Hide modal when cancel button is clicked
if (cancelLogout) {
  cancelLogout.addEventListener("click", () => {
    logoutModal.classList.remove("show");
  });
}

// ✅ FIXED: Use absolute path to ensure it always works
if (confirmLogout) {
  confirmLogout.addEventListener("click", () => {
    console.log("=== LOGOUT DEBUG START ===");
    console.log("Confirm logout clicked");

    // Get current location details
    const currentPath = window.location.pathname;
    const currentHref = window.location.href;
    const currentOrigin = window.location.origin;

    console.log("Full URL:", currentHref);
    console.log("Path:", currentPath);
    console.log("Origin:", currentOrigin);

    // Use absolute path from root
    const logoutUrl = "/BRGY498PORTAL/logout.php";

    console.log("Using absolute logout URL:", logoutUrl);
    console.log("Full logout URL will be:", currentOrigin + logoutUrl);
    console.log("=== LOGOUT DEBUG END ===");

    // Perform redirect with absolute path
    window.location.href = logoutUrl;
  });
}

// Close modal when clicking outside the modal content
if (logoutModal) {
  logoutModal.addEventListener("click", (e) => {
    if (e.target === logoutModal) {
      logoutModal.classList.remove("show");
    }
  });
}

// Close modal with Escape key
document.addEventListener("keydown", (e) => {
  if (
    e.key === "Escape" &&
    logoutModal &&
    logoutModal.classList.contains("show")
  ) {
    logoutModal.classList.remove("show");
  }
});

/**
 * (Optional) Manual logout trigger
 */
function handleLogout() {
  const currentPath = window.location.pathname;
  const isInSubfolder =
    currentPath.includes("/admin-") &&
    !currentPath.endsWith("admin-dashboard.php");

  if (isInSubfolder) {
    window.location.href = "../logout.php";
  } else {
    window.location.href = "logout.php";
  }
}
