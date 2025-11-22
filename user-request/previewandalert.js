document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("documentRequestForm");
  const previewBtn = document.querySelector(".submit.btn");
  const alertBox = document.getElementById("alertBox");
  const previewModal = document.getElementById("previewModal");

  /* ✅ VALIDATE FORM */
  function validateForm() {
    const errors = [];

    form.querySelectorAll("[required]").forEach((field) => {
      const label =
        field.previousElementSibling?.textContent.replace("*", "").trim() ||
        "This field";

      if (field.type === "checkbox") {
        if (!field.checked)
          errors.push("You must agree to the Data Privacy Policy.");
      } else if (!field.value.trim()) {
        errors.push(`${label} is required.`);
      }
    });

    // Check file sizes (max 5MB)
    form.querySelectorAll('input[type="file"]').forEach((input) => {
      const file = input.files[0];
      const label =
        input.previousElementSibling?.textContent.replace("*", "").trim() ||
        "File";
      if (file && file.size > 5 * 1024 * 1024) {
        errors.push(`${label} must be less than 5MB.`);
      }
    });

    return errors;
  }

  /* ✅ SHOW ALERT (auto hide + fade out) */
  function showAlert(message, type = "error") {
    const messages = Array.isArray(message) ? message.join("<br>") : message;

    alertBox.className = `alert ${type}`;
    alertBox.innerHTML = messages;
    alertBox.style.opacity = "1";
    alertBox.style.display = "block";
    alertBox.scrollIntoView({ behavior: "smooth" });

    // Auto hide after 3s
    setTimeout(() => {
      alertBox.style.transition = "opacity 0.5s";
      alertBox.style.opacity = "0";
      setTimeout(() => {
        alertBox.style.display = "none";
        alertBox.className = "alert";
        alertBox.innerHTML = "";
      }, 500);
    }, 3000);
  }

  /* ✅ SHOW PREVIEW MODAL */
  function showPreview() {
    const errors = validateForm();
    if (errors.length > 0) {
      showAlert(errors, "error");
      return;
    }

    const formData = new FormData(form);
    const getSafe = (key) => formData.get(key) || "N/A";
    const getFileName = (key) =>
      formData.get(key) instanceof File && formData.get(key).name
        ? formData.get(key).name
        : "No file uploaded";

    const previewHTML = `
      <div class="preview-section">
        <h3>Personal Information</h3>
        <div class="preview-field"><span class="preview-label">Full Name:</span>
          <span class="preview-value">${getSafe("last_name")}, ${getSafe("first_name")} ${getSafe(
      "middle_name"
    )}</span></div>
        <div class="preview-field"><span class="preview-label">Date of Birth:</span><span class="preview-value">${getSafe(
          "date_of_birth"
        )}</span></div>
        <div class="preview-field"><span class="preview-label">Gender:</span><span class="preview-value">${getSafe(
          "gender-selection"
        )}</span></div>
        <div class="preview-field"><span class="preview-label">Place of Birth:</span><span class="preview-value">${getSafe(
          "place_of_birth"
        )}</span></div>
      </div>

      <div class="preview-section">
        <h3>Address</h3>
        <div class="preview-field"><span class="preview-label">Street:</span><span class="preview-value">${getSafe(
          "street"
        )}</span></div>
        <div class="preview-field"><span class="preview-label">Barangay:</span><span class="preview-value">${getSafe(
          "barangay"
        )}</span></div>
        <div class="preview-field"><span class="preview-label">City:</span><span class="preview-value">${getSafe(
          "city"
        )}</span></div>
      </div>

      <div class="preview-section">
        <h3>Document Request</h3>
        <div class="preview-field"><span class="preview-label">Document Type:</span><span class="preview-value">${getSafe(
          "document_type"
        )}</span></div>
        <div class="preview-field"><span class="preview-label">Purpose:</span><span class="preview-value">${getSafe(
          "purpose"
        )}</span></div>
        <div class="preview-field"><span class="preview-label">Quantity:</span><span class="preview-value">${getSafe(
          "quantity"
        )}</span></div>
        <div class="preview-field"><span class="preview-label">Price:</span><span class="preview-value">₱${parseFloat(
          getSafe("price") || 0
        ).toFixed(2)}</span></div>
      </div>

      <div class="preview-section">
        <h3>Uploaded Documents</h3>
        <div class="preview-field"><span class="preview-label">Selfie with ID:</span><span class="preview-value">${getFileName(
          "selfie_with_id"
        )}</span></div>
        <div class="preview-field"><span class="preview-label">ID Picture:</span><span class="preview-value">${getFileName(
          "id_picture"
        )}</span></div>
      </div>
    `;

    document.getElementById("previewBody").innerHTML = previewHTML;

    previewModal.style.display = "block";
    previewModal.style.opacity = "0";
    previewModal.style.transition = "opacity 0.3s ease";
    requestAnimationFrame(() => (previewModal.style.opacity = "1"));
  }

  /* ✅ CLOSE PREVIEW MODAL (with fade-out) */
  function closePreview() {
    previewModal.style.transition = "opacity 0.3s ease";
    previewModal.style.opacity = "0";
    setTimeout(() => {
      previewModal.style.display = "none";
    }, 300);
  }

  /* ✅ EVENT LISTENERS */
  previewBtn.addEventListener("click", showPreview);
  window.closePreview = closePreview;

  // Close modal when clicking outside
  window.onclick = (e) => {
    if (e.target === previewModal) closePreview();
  };
});
