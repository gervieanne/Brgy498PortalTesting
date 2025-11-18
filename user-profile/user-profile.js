// Clock functionality
const zeroPad = (num) => (num < 10 ? "0" + num : num);

const formatTime = (date) => {
  let hours = date.getHours();
  const minutes = zeroPad(date.getMinutes());
  const seconds = zeroPad(date.getSeconds());
  const ampm = hours >= 12 ? "PM" : "AM";
  hours = hours % 12 || 12;
  return `${zeroPad(hours)}:${minutes}:${seconds} ${ampm}`;
};

const updateClock = () => {
  const clock = document.getElementById("clock");
  if (clock) clock.textContent = formatTime(new Date());
};

setInterval(updateClock, 1000);
updateClock();

// Burger menu functionality
const burgerMenu = document.getElementById("burgerMenu");
const sidebar = document.querySelector(".sidebar");

burgerMenu.addEventListener("click", function () {
  sidebar.classList.toggle("active");
});

// Close sidebar when clicking on a link
const sidebarLinks = document.querySelectorAll(".sidebar li a");
sidebarLinks.forEach((link) => {
  link.addEventListener("click", function () {
    sidebar.classList.remove("active");
  });
});

// Close sidebar when clicking outside
document.addEventListener("click", function (event) {
  if (
    !event.target.closest(".sidebar") &&
    !event.target.closest(".burger-menu")
  ) {
    sidebar.classList.remove("active");
  }
});

// Password Change Modal - FIXED
const passwordModal = document.getElementById("passwordModal");
const changePasswordBtn = document.getElementById("changePasswordBtn");
const closePasswordModal = document.getElementById("closePasswordModal");
const passwordChangeForm = document.getElementById("passwordChangeForm");

changePasswordBtn.addEventListener("click", () => {
  passwordModal.classList.add("show");
  document.getElementById("currentPassword").focus();
});

closePasswordModal.addEventListener("click", () => {
  passwordModal.classList.remove("show");
  passwordChangeForm.reset();
});

window.addEventListener("click", (e) => {
  if (e.target === passwordModal) {
    passwordModal.classList.remove("show");
    passwordChangeForm.reset();
  }
});

// Password toggle functionality for modal - FIXED
function togglePassword(fieldId) {
  const input = document.getElementById(fieldId);
  const toggleBtn = input.parentElement.querySelector(".password-toggle");
  const eyeIcon = toggleBtn.querySelector(".eye-icon");
  const eyeOffIcon = toggleBtn.querySelector(".eye-off-icon");

  if (input.type === "password") {
    input.type = "text";
    eyeIcon.style.display = "none";
    eyeOffIcon.style.display = "block";
  } else {
    input.type = "password";
    eyeIcon.style.display = "block";
    eyeOffIcon.style.display = "none";
  }
}

passwordChangeForm.addEventListener("submit", async (e) => {
  e.preventDefault();

  const submitBtn = e.target.querySelector(".submit-btn");
  submitBtn.disabled = true;
  submitBtn.textContent = "Updating...";

  const formData = new FormData(passwordChangeForm);

  try {
    const response = await fetch("../user-profile/change_password.php", {
      method: "POST",
      body: formData,
    });

    const data = await response.json();

    if (data.success) {
      showAlert(data.message, "success");
      passwordModal.classList.remove("show");
      passwordChangeForm.reset();
    } else {
      showAlert(data.message, "error");
    }
  } catch (error) {
    console.error("Error:", error);
    showAlert("An error occurred. Please try again.", "error");
  } finally {
    submitBtn.disabled = false;
    submitBtn.textContent = "Update Password";
  }
});

// Photo upload functionality
let stream = null;
let capturedBlob = null;
let isFromCamera = false;

// Edit profile picture
document
  .getElementById("editProfilePicture")
  .addEventListener("click", function () {
    document.getElementById("photoModal").style.display = "flex";
  });

function closePhotoModal() {
  document.getElementById("photoModal").style.display = "none";
  resetPhotoModal();
}

function resetPhotoModal() {
  stopCamera();
  document.getElementById("cameraPreview").style.display = "none";
  document.getElementById("capturedImage").style.display = "none";
  document.getElementById("photoOptions").style.display = "flex";
  document.getElementById("cameraControls").style.display = "none";
  document.getElementById("uploadControls").style.display = "none";
  capturedBlob = null;
  isFromCamera = false;
}

async function openCamera() {
  try {
    stream = await navigator.mediaDevices.getUserMedia({
      video: {
        facingMode: "user",
        width: { ideal: 1280 },
        height: { ideal: 720 },
      },
      audio: false,
    });
    const video = document.getElementById("cameraPreview");
    video.srcObject = stream;
    video.style.display = "block";
    document.getElementById("photoOptions").style.display = "none";
    document.getElementById("cameraControls").style.display = "flex";
    document.getElementById("uploadControls").style.display = "none";
    document.getElementById("capturedImage").style.display = "none";
    isFromCamera = true;
  } catch (err) {
    console.error("Camera error:", err);
    showAlert(
      "Error accessing camera: " +
        err.message +
        ". Please ensure you have granted camera permissions.",
      "error"
    );
  }
}

function stopCamera() {
  if (stream) {
    stream.getTracks().forEach((track) => track.stop());
    stream = null;
  }
}

function capturePhoto() {
  const video = document.getElementById("cameraPreview");
  const canvas = document.getElementById("canvas");
  const context = canvas.getContext("2d");

  canvas.width = video.videoWidth;
  canvas.height = video.videoHeight;
  context.drawImage(video, 0, 0);

  canvas.toBlob(
    (blob) => {
      capturedBlob = blob;
      const img = document.getElementById("capturedImage");
      img.src = URL.createObjectURL(blob);
      img.style.display = "block";
      video.style.display = "none";
      document.getElementById("cameraControls").style.display = "none";
      document.getElementById("uploadControls").style.display = "flex";
      isFromCamera = true;
      updateRetakeButtonText();
      stopCamera();
    },
    "image/jpeg",
    0.9
  );
}

function retakePhoto() {
  if (isFromCamera) {
    // Camera mode: Reopen camera for capture again
    document.getElementById("capturedImage").style.display = "none";
    capturedBlob = null;
    openCamera();
  } else {
    // File upload mode: Open file browser again
    document.getElementById("fileInput").click();
  }
}

function handleFileSelect(event) {
  const file = event.target.files[0];
  if (file) {
    // Validate file size (5MB)
    if (file.size > 5242880) {
      showAlert("File too large. Maximum size is 5MB.", "error");
      event.target.value = "";
      return;
    }

    // Validate file type
    const allowedTypes = ["image/jpeg", "image/jpg", "image/png", "image/gif"];
    if (!allowedTypes.includes(file.type)) {
      showAlert("Invalid file type. Only JPG, PNG, and GIF allowed.", "error");
      event.target.value = "";
      return;
    }

    capturedBlob = file;
    const img = document.getElementById("capturedImage");
    img.src = URL.createObjectURL(file);
    img.style.display = "block";
    document.getElementById("photoOptions").style.display = "none";
    document.getElementById("uploadControls").style.display = "flex";
    isFromCamera = false;
    updateRetakeButtonText();
  }
  event.target.value = "";
}

function updateRetakeButtonText() {
  const retakeBtn = document.getElementById("retakeBtn");
  if (isFromCamera) {
    retakeBtn.innerHTML = "Retake";
  } else {
    retakeBtn.innerHTML = "Browse Files Again";
  }
}

function uploadPhoto() {
  if (!capturedBlob) {
    showAlert("Please capture or select a photo first", "error");
    return;
  }

  const spinner = document.getElementById("uploadSpinner");
  spinner.style.display = "block";

  const uploadButtons = document.querySelectorAll("#uploadControls .photo-btn");
  uploadButtons.forEach((btn) => (btn.disabled = true));

  const formData = new FormData();
  formData.append("action", "upload_photo");
  formData.append("profile_photo", capturedBlob, "profile.jpg");

  fetch("../user-profile/update_profile.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      spinner.style.display = "none";
      uploadButtons.forEach((btn) => (btn.disabled = false));
      if (data.success) {
        showAlert("Profile picture updated successfully!", "success");
        closePhotoModal();
        setTimeout(() => location.reload(), 1500);
      } else {
        showAlert(data.message || "Failed to upload photo", "error");
      }
    })
    .catch((error) => {
      spinner.style.display = "none";
      uploadButtons.forEach((btn) => (btn.disabled = false));
      showAlert("Error uploading photo. Please try again.", "error");
      console.error("Error:", error);
    });
}

// Edit contact fields
function editField(field) {
  // Close any other open edit fields
  ["contact", "email"].forEach((f) => {
    if (f !== field) {
      document.getElementById(f + "-display").style.display = "block";
      document.getElementById(f + "-edit").style.display = "none";
    }
  });

  document.getElementById(field + "-display").style.display = "none";
  document.getElementById(field + "-edit").style.display = "block";
  document.getElementById(field + "-input").focus();
}

function cancelEdit(field) {
  const displayElement = document.getElementById(field + "-display");
  const originalValue = displayElement.textContent.trim();

  document.getElementById(field + "-input").value =
    originalValue !== "Not provided" ? originalValue : "";
  displayElement.style.display = "block";
  document.getElementById(field + "-edit").style.display = "none";
}

function saveContactInfo() {
  const contactValue = document.getElementById("contact-input").value.trim();
  const emailValue = document.getElementById("email-input").value.trim();

  // Validate contact number format
  if (contactValue) {
    const contactRegex = /^09[0-9]{9}$/;
    if (!contactRegex.test(contactValue)) {
      showAlert(
        "Invalid contact number format. Use: 09XXXXXXXXX (11 digits)",
        "error"
      );
      return;
    }
  }

  // Validate email format
  if (emailValue) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(emailValue)) {
      showAlert("Please enter a valid email address", "error");
      return;
    }
  }

  // At least one must be provided
  if (!contactValue && !emailValue) {
    showAlert("Please provide at least one contact method", "error");
    return;
  }

  // Show loading
  const saveBtns = document.querySelectorAll(".save-btn");
  saveBtns.forEach((btn) => {
    btn.disabled = true;
    btn.textContent = "Saving...";
  });

  const formData = new FormData();
  formData.append("action", "update_contact");
  formData.append("contact_number", contactValue);
  formData.append("email", emailValue);

  fetch("../user-profile/update_profile.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      saveBtns.forEach((btn) => {
        btn.disabled = false;
        btn.textContent = "Save";
      });

      if (data.success) {
        if (contactValue) {
          document.getElementById("contact-display").textContent =
            data.contact_number || contactValue;
        }
        if (emailValue) {
          document.getElementById("email-display").textContent =
            data.email || emailValue;
        }

        cancelEdit("contact");
        cancelEdit("email");
        showAlert(data.message, "success");
      } else {
        showAlert(data.message || "Failed to update", "error");
      }
    })
    .catch((error) => {
      saveBtns.forEach((btn) => {
        btn.disabled = false;
        btn.textContent = "Save";
      });
      showAlert("Error updating information. Please try again.", "error");
      console.error("Error:", error);
    });
}

function showAlert(message, type) {
  const alertContainer = document.getElementById("alertContainer");
  const alertDiv = document.createElement("div");
  alertDiv.className = `alert alert-${type}`;
  alertDiv.textContent = message;
  alertContainer.appendChild(alertDiv);

  setTimeout(() => {
    alertDiv.style.opacity = "0";
    setTimeout(() => alertDiv.remove(), 300);
  }, 5000);
}

// Close modal on outside click
window.onclick = function (event) {
  const modal = document.getElementById("photoModal");
  if (event.target == modal) {
    closePhotoModal();
  }
};
